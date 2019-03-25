<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://w3scloud.com/
 * @since      1.0.0
 *
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    W3s_Cf7_Zoho
 * @subpackage W3s_Cf7_Zoho/admin
 * @author     W3S Cloud Technology, shohag121 <info@w3scloud.com>
 */
class W3s_Cf7_Zoho_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;


		// titan framework options
		add_action( 'tf_create_options', array( $this, 'admin_options' ) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in W3s_Cf7_Zoho_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The W3s_Cf7_Zoho_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/w3s-cf7-zoho-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in W3s_Cf7_Zoho_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The W3s_Cf7_Zoho_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/w3s-cf7-zoho-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Register the options for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_options()
    {

        // get instance of w3s-cf7-zoho
        $titan = TitanFramework::getInstance('w3s-cf7-zoho');

        // create the admin panel
        $panel = $titan->createAdminPanel(array(
            'name' => 'Zoho with CF7',
            'desc' => 'Zoho Leads with Contact Form 7 Integration form.',
            'id' => 'w3s-cf7-zoho',
            'icon' => 'dashicons-vault',
            'position' => 58,
        ));

        // Create Authentication Tab
        $authTab = $panel->createTab(array(
            'name' => 'Authentication',
        ));

        // options for auth tab
//
//		$authTab->createOption( array(
//			'name' => 'Zoho Region',
//			'id' => 'zoho_region',
//			'type' => 'select',
//			'desc' => 'Select the zoho regional datacenter zone.',
//			'options' => array(
//				'com' => 'Global/US',
//				'eu' => 'Europe',
//				'cn' => 'China',
//				'in' => 'India',
//			),
//			'default' => 'com',
//		));

        if (!$titan->getOption('zoho_client_id')) {
            $authTab->createOption(array(
                'name' => 'Zoho Client ID',
                'id' => 'zoho_client_id',
                'type' => 'text',
                'desc' => 'Your Zoho App Client ID. To Generate, Please follow <a href="https://www.zoho.com/crm/help/developer/api/register-client.html" target="_blank">this instructions.</a>',
                'is_password' => false,
            ));

            $authTab->createOption(array(
                'name' => 'Zoho Client Secret',
                'id' => 'zoho_client_secret',
                'type' => 'text',
                'desc' => 'Your Zoho App Client Secret. To Generate, Please follow <a href="https://www.zoho.com/crm/help/developer/api/register-client.html" target="_blank">this instructions.</a>',
                'is_password' => true,
            ));

            $authTab->createOption(array(
                'name' => 'Zoho User Email',
                'id' => 'zoho_user_email',
                'type' => 'text',
                'desc' => 'Your Zoho login email address',
                'is_password' => false,
            ));
        }
        if ($titan->getOption('zoho_client_id')) {

            $authTab->createOption(array(
                'name' => 'Authorize Zoho Account',
                'type' => 'custom',
                'custom' => '<a href="https://zoho.com" class="button button-primary">Grant Access</a>',
            ));

        }

        if ($titan->getOption('zoho_client_id') && $titan->getOption('zoho_authorised')) {

            $authTab->createOption(array(
                'name' => 'Revoke Access to Zoho',
                'type' => 'ajax-button',
                'action' => 'w3s_cf7_zoho_revoke_action',
                'label' => array(
                    __('Revoke Access', 'default'),
                ),
                'class' => 'button-secondary',
            ));
        }

        if (!$titan->getOption('zoho_client_id')) {

            $authTab->createOption(array(
                'type' => 'save',
                'save' => 'Save & Bring Grant Access',
                'use_reset' => false,
            ));
        }


        // Create Integration Tab
        $intTab = $panel->createTab( array(
            'name' => 'Integration',
        ));

        $intTab->createOption( array(
            'name' => 'Select Contact form',
            'id' => 'cf7_form',
            'type' => 'select-posts',
            'desc' => 'Select Contact Form',
            'post_type' => 'wpcf7_contact_form',
        ));

        $intTab->createOption( array(
            'name' => 'Zoho Module',
            'id' => 'zoho_module',
            'type' => 'select',
            'desc' => 'Select the Zoho Module.',
            'options' => array(
                'Leads' => 'Leads',
                'Account' => 'Account',
            ),
            'default' => 'Leads',
        ));


        // save options
        $intTab->createOption( array(
            'type' => 'save',
            'save' => 'Save & Reload Fields',
            'use_reset' =>  false,
        ));



        // Create Fields Tab
        $filedTab = $panel->createTab( array(
            'name' => 'Fields',
        ));

/*
        $filedTab->createOption( array(
            'name' => 'Zoho Module',
            'id' => 'zoho_field_',
            'type' => 'select',
            'desc' => 'Select the Zoho Module.',
            'options' => array(
                'Leads' => 'Leads',
                'Account' => 'Account',
            ),
            'default' => 'Leads',
        ));

*/
        // save options
        $filedTab->createOption( array(
            'type' => 'save',
            'save' => 'Save Fields',
            'use_reset' =>  false,
        ));

	}


}
