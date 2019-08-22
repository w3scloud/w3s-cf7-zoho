<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://w3scloud.com/
 * @since      1.0.0
 *
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 */

/**
 * The core zoho plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 * @author     W3S Cloud Technology, shohag121 <info@w3scloud.com>
 */


use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\oauth\ZohoOAuth;


class W3s_Cf7_Zoho_Conn {

    /**
     * TitanFramework instance
     *
     * @since 1.0.0
     * @var TitanFramework
     */
    private $titanInstant;

    /**
     * Zoho Settings configuration
     *
     * @since 1.0.0
     * @var array
     */
    public $zohoConfig = array();

    /**
     * Zoho Authentication
     *
     * @since 1.0.0
     * @var bool|mixed
     */
    private $auth = false;

    /**
     * W3s_Cf7_Zoho_Conn constructor.
     *
     * @since 1.0.0
     */
    public function __construct(){
     
        $this->titanInstant = TitanFramework::getInstance( 'w3s-cf7-zoho' );
        $this->auth = $this->titanInstant->getOption('zoho_authorised');
        $this->setConfig();
    }

    /**
     * this function include the vendor auto load file
     * and initialize the Zoho functionality
     *
     * @since 1.0.0
     * @return void
     */
    private function include_zoho(){
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/vendor/autoload.php';
        $this->init_zoho();
    }

    /**
     * this function initialize ZCRMRestClient
     *
     * @since 1.0.0
     * @return void
     */
    private function init_zoho(){
        if ($this->auth) {
            try{
                ZCRMRestClient::initialize($this->zohoConfig);
            }catch (ZCRMException $exception){
                $this->auth = false;
                add_action('admin_notices', array($this, 'noticeAdmin'));
                exit;
            }

        }
    }

    /**
     * this function creates or update record to Zoho CRM on selected module
     *
     * @param array $dataArray
     * @param bool $upsert
     * @param string $module
     * @param array $files
     */
    public function createRecord($dataArray, $upsert = false, $module = 'Leads', $files = array()){
        try{

            $this->include_zoho();

            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
            $records = array();


            foreach ($dataArray as $data){
                $record = ZCRMRecord::getInstance( $module,null);

                foreach ($data as $key => $value){

                    $record->setFieldValue(
                            $this->removeDataType($key),
                            $this->prepareData(
                                    $value[0],
                                    $this->getDataType($key),
                                    $value[1]
                            )
                    );
                }

                array_push($records, $record);
            }

            if (!$upsert){
                $responseIn = $moduleIns->createRecords($records);
                do_action('w3s_cf7_zoho_on_create_record', $responseIn);
            } else {
                $responseIn = $moduleIns->upsertRecords($records);
                do_action('w3s_cf7_zoho_on_update_record', $responseIn);
            }


            $entityResponse = $responseIn->getEntityResponses()[0];
            if ("success" == $entityResponse->getStatus()){
                $createdRecordInstance = $entityResponse->getData();
                $entityID = $createdRecordInstance->getEntityId();

                if (!empty($files)){
                    foreach ($files as $fileName => $filePath){
                        $recordToUpload = ZCRMRecord::getInstance($module, $entityID);
                        $fileresponseIns = $recordToUpload->uploadAttachment($filePath);
                    }
                }


            }

        } catch (ZCRMException $exception){
            add_action('admin_notices', array($this, 'noticeAdmin'));
        }
    }

    /**
     * get all the fields of selected module
     *
     * @since 1.0.0
     * @param string $module
     * @return array
     */
    public function getZohoFields($module = 'Leads'){

        try{
            $this->include_zoho();

            $moduleIns = ZCRMModule::getInstance($module);
            $apiResponse = $moduleIns->getAllFields();
            $fields = $apiResponse->getData();

            $formatedFields = array();

            foreach ($fields as $field) {
                // get api name and data type
                $apiName = $field->getApiName(); $apiDataType = $field->getDataType();
                $formatedFields["{$apiDataType}_{$apiName}"] = "{$apiName} ({$apiDataType})";
            }

            return $formatedFields;
        }
        catch (ZCRMException $e)
        {
            add_action('admin_notices', array($this, 'noticeAdmin'));
            return array();
        }
    }


    /**
     * get the fields of selected contact form
     *
     * @since 1.0.0
     * @param $cf7_id
     * @return array|mixed|void
     */
    public function getCF7Fields($cf7_id)
    {
        if ($cf7_id == null ){
            return array();
        }
        $current_cf7 =  WPCF7_ContactForm::get_instance($cf7_id);
        $form = $current_cf7->prop('form');
        $re = '/(?<=\[)([^\]]+)/';
        preg_match_all($re, $form, $matches, PREG_SET_ORDER, 0);
        $cf7Fields = array();

        foreach ($matches as $match){
            if ($match[0] == '/acceptance') continue;
            $field =  explode(" ", str_replace("*","", $match[0]) );
            if ($field[0] == 'submit') continue;
            if ($field[0] == 'file') continue;
            $cf7Fields["{$field[0]}_{$field[1]}"] = "{$field[1]} ({$field[0]})";
        }

        $cf7Fields = apply_filters('w3s_cf7_zoho_cf7_fields', $cf7Fields);

        return $cf7Fields;
    }


    /**
     *
     * @since 1.1.2
     * @param string $sourceDataType
     * @param string $zohoDataType
     * @param mixed $data
     * @return mixed
     */
    private function prepareData($sourceDataType, $zohoDataType, $data)
    {
        // TODO: manipulate data and produce data with zoho data type.

        return $data;
    }

    /**
     * remove datatype at start of the string and return actual key
     *
     * @since   1.1.2
     * @param $key
     * @return string
     */
    public function removeDataType($key)
    {
        $keyArray = explode('_', $key, 2);
        return $keyArray[1];
    }


    /**
     * return only data type from start of the key
     *
     * @since    1.1.2
     * @param $key
     * @return string
     */
    public function getDataType($key)
    {
        $keyArray = explode('_', $key, 2);
        return $keyArray[0];
    }



    private function setConfig(){
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/w3s-cf7-zoho';

        if (file_exists($upload_dir.'/config.php')){

            $confFile = $upload_dir .'/config.php';

            $conf = require_once $confFile;
            if(!empty($conf)){
                $this->auth = true;
                $this->zohoConfig = $conf;
            }
        } else {
            $this->auth = false;
        }

    }

    public function noticeAdmin(){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Problem in your Zoho Authentication.', 'w3s-cf7-zoho'); ?></p>
        </div>
        <?php
    }

    public function genToken($grantToken, $config){

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/vendor/autoload.php';

        try {
            ZCRMRestClient::initialize($config);
            $oAuthClient = ZohoOAuth::getClientInstance();
            $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);

            do_action('w3s_cf7_zoho_after_token_generation');

            return true;
        }catch (ZCRMException $exception){
            add_action('admin_notices', array($this, 'noticeAdmin'));
            return false;
        }


    }





}