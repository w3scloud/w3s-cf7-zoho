<?php

/**
 *
 * @link              https://w3scloud.com/
 * @since             1.0.0
 * @package           W3s_Cf7_Zoho
 *
 * @wordpress-plugin
 * Plugin Name:       W3SCloud Contact Form 7 to Zoho CRM
 * Plugin URI:        https://w3scloud.com/cf7-zoho/
 * Description:       Zoho CRM Integration with Contact Form 7. Add Leads from Contact form 7 form entry.
 * Version:           2.3
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

// if ( ! function_exists( 'w3sccf7z_fs' ) ) {
// 	// Create a helper function for easy SDK access.
// 	function w3sccf7z_fs() {
// 		global $w3sccf7z_fs;

// 		if ( ! isset( $w3sccf7z_fs ) ) {
// 			// Include Freemius SDK.
// 			require_once dirname( __FILE__ ) . '/freemius/start.php';

// 			$w3sccf7z_fs = fs_dynamic_init(
// 				array(
// 					'id'                  => '8858',
// 					'slug'                => 'w3s-cf7-zoho',
// 					'premium_slug'        => 'w3s-cf7-zoho-premium',
// 					'type'                => 'plugin',
// 					'public_key'          => 'pk_472c69ca65f1be708ce325706ae1b',
// 					'is_premium'          => false,
// 					'has_addons'          => false,
// 					// If your plugin is a serviceware, set this option to false.
// 					'has_premium_version' => false,
// 					'has_paid_plans'      => false,
// 					'is_org_compliant'    => false,
// 					'menu'                => array(
// 						'first-path' => 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho',
// 						'account'    => false,
// 						'contact'    => false,
// 						'support'    => false,
// 						'slug'       => 'w3s-cf7-zoho',
// 						'parent'     => array(
// 							'slug' => 'edit.php?post_type=w3s_cf7',
// 						),
// 					),
// 					// Set the SDK to work in a sandbox mode (for development & testing).
// 					// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
// 					// 'secret_key'          => 'sk_ubb4yN3mzqGR2x8#P7r5&@*xC$utE',
// 				)
// 			);
// 		}

// 		return $w3sccf7z_fs;
// 	}

// 	// Init Freemius.
// 	w3sccf7z_fs();
// 	// Signal that SDK was initiated.
// 	do_action( 'w3sccf7z_fs_loaded' );
// }

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'W3S_CF7_ZOHO_VERSION', '2.3' );

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
