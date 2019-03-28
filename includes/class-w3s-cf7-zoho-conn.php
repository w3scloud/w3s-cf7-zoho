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
class W3s_Cf7_Zoho_Conn {

    private $titanInstant;
    private $zohoConfig = array();
    private $auth = false;

    public function __construct(){
        $this->include_zoho();
        $this->titanInstant = TitanFramework::getInstance( 'w3s-cf7-zoho' );
        $this->auth = $this->titanInstant->getOption('zoho_auth_done');
    }


    private function include_zoho(){
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '/zoho-conn/vendor/autoload.php';
    }

    public function createRecord($dataAray){
        // ToDo # need to connect with zoho
    }

    public function upsertRecord($dataAray){
        // ToDo # need to connect with zoho
    }

    public function getFields($dataAray){
        // ToDo # need to connect with zoho
    }





}