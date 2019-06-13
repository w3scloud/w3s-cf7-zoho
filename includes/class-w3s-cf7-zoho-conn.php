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
use zcrmsdk\crm\crud\ZCRMModule;



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
        ZCRMRestClient::initialize($this->zohoConfig);
    }

    public function createRecord($dataAray){
        // ToDo # need to connect with zoho
    }

    public function upsertRecord($dataAray){
        // ToDo # need to connect with zoho
    }

    public function getZohoFields(){
        
        try{
            $this->include_zoho();

            $moduleIns = ZCRMModule::getInstance("Leads");
            $apiResponse=$moduleIns->getAllFields();
            $fields=$apiResponse->getData();

            $formatedFields = array();

            foreach ($fields as $field) {
                $formatedFields[$field->getApiName()] = $field->getDataType();
            }

            return $formatedFields;

        }
        catch (ZCRMException $e)
        {
            echo $e->getCode();
            echo $e->getMessage();
            echo $e->getExceptionCode();
        }


    }


    public function getCF7Fields()
    {

        $current_cf7 =  WPCF7_ContactForm::get_instance($this->titanInstant->getOption('cf7_form'));

        $submission = $current_cf7->scan_form_tags('name');

//        return $submission;

        // ToDo # need to find the fields from contact form

    }


    private function setConfig(){

        $conf = include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/zoho-conn/config.php';
        if(!empty($conf)){
            $this->auth = true;
            $this->zohoConfig = $conf;
        }


    }





}