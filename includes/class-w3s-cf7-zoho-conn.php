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

    private $titanInstant;
    public $zohoConfig = array();
    private $auth = false;

    public function __construct(){
     
        $this->titanInstant = TitanFramework::getInstance( 'w3s-cf7-zoho' );
        $this->auth = $this->titanInstant->getOption('zoho_authorised');
        $this->setConfig();
    }


    private function include_zoho(){
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/vendor/autoload.php';
        $this->init_zoho();
    }

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
     * this function creates record to Zoho CRM selected module
     *
     * @param $dataArray
     * @param string $module
     */
    public function createRecord($dataArray, $module = 'Leads'){
        try{

            $this->include_zoho();

            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
            $records=array();


            foreach ($dataArray as $data){
                $record = ZCRMRecord::getInstance("leads",null);

                foreach ($data as $key => $value){
                    $record->setFieldValue( $key, $value );
                }

                array_push($records, $record);
            }

            $responseIn = $moduleIns->createRecords($records);

            do_action('w3s_cf7_zoho_on_create_record', $responseIn);

        } catch (ZCRMException $exception){
            add_action('admin_notices', array($this, 'noticeAdmin'));
        }
    }

    /**
     * this function create or update zoho records
     *
     * @param $dataArray
     * @param string $module
     */
    public function upsertRecord($dataArray, $module = 'Leads'){
        try{

            $this->include_zoho();

            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
            $records=array();


            foreach ($dataArray as $data){
                $record = ZCRMRecord::getInstance("leads",null);

                foreach ($data as $key => $value){
                    $record->setFieldValue( $key, $value );
                }

                array_push($records, $record);
            }

            $responseIn = $moduleIns->upsertRecords($records);

            do_action('w3s_cf7_zoho_on_update_record', $responseIn);

        } catch (ZCRMException $exception){
            add_action('admin_notices', array($this, 'noticeAdmin'));
        }
    }

    /**
     * @param string $module
     * @return array
     */
    public function getZohoFields($module = 'Leads'){

        try{
            $this->include_zoho();

            $moduleIns = ZCRMModule::getInstance($module);
            $apiResponse=$moduleIns->getAllFields();
            $fields=$apiResponse->getData();

            $formatedFields = array();

            foreach ($fields as $field) {
                $formatedFields[$field->getApiName()] = "{$field->getApiName()} ({$field->getDataType()})";
            }

            return $formatedFields;
        }
        catch (ZCRMException $e)
        {
            add_action('admin_notices', array($this, 'noticeAdmin'));
            return array();
        }
    }


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
            $field =  explode(" ", str_replace("*","", $match[0]) );
            if ($field[0] == 'submit') continue;
            $cf7Fields[$field[1]] = "{$field[1]} ({$field[0]})";
        }

        $cf7Fields = apply_filters('w3s_cf7_zoho_cf7_fields', $cf7Fields);

        return $cf7Fields;
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