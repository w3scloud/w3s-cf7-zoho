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



class W3s_Cf7_Zoho_Conn {

    private $titanInstant;
    private $zohoConfig = array();
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
            ZCRMRestClient::initialize($this->zohoConfig);
        }
    }

    public function createRecord($dataAray){
        try{

            $this->include_zoho();

            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("leads");
            $records=array();


            foreach ($dataAray as $data){
                $record = ZCRMRecord::getInstance("leads",null);

                foreach ($data as $key => $value){
                    $record->setFieldValue( $key, $value );
                }

                array_push($records, $record);
            }

            $responseIn = $moduleIns->createRecords($records);
        } catch (ZCRMException $exception){

        }
    }

    public function upsertRecord($dataAray){
        try{

            $this->include_zoho();

            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("leads");
            $records=array();


            foreach ($dataAray as $data){
                $record = ZCRMRecord::getInstance("leads",null);

                foreach ($data as $key => $value){
                    $record->setFieldValue( $key, $value );
                }

                array_push($records, $record);
            }

            $responseIn = $moduleIns->upsertRecords($records);
        } catch (ZCRMException $exception){

        }
    }

    public function getZohoFields(){

        try{
            $this->include_zoho();

            $moduleIns = ZCRMModule::getInstance("Leads");
            $apiResponse=$moduleIns->getAllFields();
            $fields=$apiResponse->getData();

            $formatedFields = array();

            foreach ($fields as $field) {
                $formatedFields[$field->getApiName()] = "{$field->getApiName()} ({$field->getDataType()})" ;
            }

            return $formatedFields;
        }
        catch (ZCRMException $e)
        {
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
        return $cf7Fields;
    }


    private function setConfig(){
        if (file_exists(plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/config.php')){

            $conf = require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/config.php';
            if(!empty($conf)){
                $this->auth = true;
                $this->zohoConfig = $conf;
            }
        } else {
            $this->auth = false;
        }

    }





}