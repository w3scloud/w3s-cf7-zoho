<?php

/**
 *
 * @link              https://w3scloud.com/
 * @since             1.0.0
 * @package           W3s_Cf7_Zoho
 *
 * @wordpress-plugin
 * Plugin Name:       W3SCloud Contact Form 7 to Zoho CRM
 * Plugin URI:        https://w3scloud.com
 * Description:       Zoho CRM Integration with Contact Form 7. Add Leads from Contact form 7 form entry.
 * Version:           3.0
 * Author:            W3SCloud Technology
 * Author URI:        https://w3scloud.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       w3s-cf7-zoho
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'W3S_CF7_ZOHO_VERSION', '3.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-w3s-cf7-zoho-activator.php
 */
function activate_w3s_cf7_zoho() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-w3s-cf7-zoho-activator.php';
	W3s_Cf7_Zoho_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-w3s-cf7-zoho-deactivator.php
 */
function deactivate_w3s_cf7_zoho() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-w3s-cf7-zoho-deactivator.php';
	W3s_Cf7_Zoho_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_w3s_cf7_zoho' );
register_deactivation_hook( __FILE__, 'deactivate_w3s_cf7_zoho' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-w3s-cf7-zoho.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_w3s_cf7_zoho() {

	$plugin = new W3s_Cf7_Zoho();
	$plugin->run();

}
run_w3s_cf7_zoho();