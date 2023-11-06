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
		global $wpdb;
		$ifTableExist = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}cf7tozoholog'" );
		if ( ! $ifTableExist ) {
			$table_name      = $wpdb->prefix . 'cf7tozoholog';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                date datetime NULL DEFAULT NULL,
                text text NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

}
