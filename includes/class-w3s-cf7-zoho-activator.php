<?php

/**
 * Fired during plugin activation
 *
 * @link       https://w3scloud.com/
 * @since      1.0.0
 *
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/includes
 * @author     W3S Cloud Technology, shohag121 <info@w3scloud.com>
 */
class W3s_Cf7_Zoho_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/w3s-cf7-zoho';
        if(!file_exists($upload_dir)) wp_mkdir_p($upload_dir);

        $filename = $upload_dir.'/zcrm_oauthtokens.txt';

        if(!file_exists($filename)) touch($filename);


	}

}
