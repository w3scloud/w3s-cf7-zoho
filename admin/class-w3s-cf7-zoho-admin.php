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

        // REDIRECT URL

        $authTab->createOption( array(
            'name' => 'Information to create Zoho Client',
            'type' => 'heading',
        ) );
        // redirect url for Zoho Client
        $redirectURL = plugins_url( 'includes/zoho-conn/gen.php', dirname(__FILE__) );
        // Site url for zoho client
        $siteURL = parse_url(site_url())['host'];

        $authTab->createOption(array(
            'name' => 'Information',
            'type' => 'custom',
            'custom' => "<table class='form-table' colspan='2'>
        <tr>
            <th class='first'>Fields</th>
            <th class='second'>Value</th>
        </tr>
        <tr>
            <td class='first'><h3>Client Name:</h3></td>
            <td class='second'><code>W3S CF7 to CRM</code></td>
        </tr>        
        <tr>
            <td class='first'><h3>Client Domain:</h3></td>
            <td><code>$siteURL</code></td>
        </tr>
         <tr>
            <td class='first'><h3>Authorized redirect URIs:</h3></td>
            <td class='second'><code>$redirectURL</code></td>
        </tr>
        <tr>
            <td class='first'><h3>Client Type</h3></td>
            <td class='second'><code>Web Based</code></td>
        </tr>
    </table>",
        ));


        $authTab->createOption( array(
            'name' => 'Zoho Credentials',
            'type' => 'heading',
        ));
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

        $authTab->createOption(array(
            'name' => 'Zoho redirect URL',
            'id' => 'zoho_redirect_url',
            'type' => 'text',
            'desc' => 'Your wordpress redirect URL',
            'is_password' => false,
            'default' => $redirectURL,
            'hidden' => true,
        ));

        $authTab->createOption(array(
            'name' => 'Zoho API Base url',
            'id' => 'zoho_api_base_url',
            'type' => 'text',
            'desc' => 'Your Zoho API Base URL',
            'is_password' => false,
            'default' => '',
            'hidden' => true,
        ));
        
        $authTab->createOption(array(
            'name' => 'Zoho Account URL',
            'id' => 'zoho_account_url',
            'type' => 'text',
            'desc' => 'Your Zoho Account URL',
            'is_password' => false,
            'default' => '',
            'hidden' => true,
        ));
        $authTab->createOption(array(
            'name' => 'Zoho Authorised',
            'id' => 'zoho_authorised',
            'type' => 'enable',
            'desc' => 'Is ZOHO Auth Complete?',
            'is_password' => false,
            'default' => false,
            'hidden' => true,
        ));


        // appair auth button when app id ,secret and email present
        if ($titan->getOption('zoho_client_id') && $titan->getOption('zoho_client_secret') && $titan->getOption('zoho_user_email')) {


            $titan->setOption('zoho_redirect_url', $redirectURL);

            $zcid = $titan->getOption('zoho_client_id');
            $authURL = "<a href='https://accounts.zoho.com/oauth/v2/auth?scope=ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,aaaserver.profile.READ&client_id=$zcid&response_type=code&access_type=offline&redirect_uri=$redirectURL' class='button button-primary'>Grant Access</a>";

            $authTab->createOption(array(
                'name' => 'Authorize Zoho Account',
                'type' => 'custom',
                'custom' => $authURL,
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

        // save button for auth tab
        $authTab->createOption(array(
            'type' => 'save',
            'save' => 'Save & Bring Grant Access',
            'use_reset' => false,
        ));


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
                // 'Account' => 'Account',
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

//         $zohoCon = new W3s_Cf7_Zoho_Conn();
//
//         die(var_dump($zohoCon->getCF7Fields()));

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
