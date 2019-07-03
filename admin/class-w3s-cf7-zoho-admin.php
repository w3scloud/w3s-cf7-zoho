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



        add_action( 'init', array( $this, 'w3s_cf7_post_type' ), 0 );
        // titan framework options
        add_action( 'tf_create_options', array( $this, 'admin_options' ) );

        add_action( 'load-post.php', array( $this, 'w3s_cf7_post_action_for_metabox' ) , 0 );
        // add the action
        add_action( 'wpcf7_before_send_mail', array( $this,'run_on_cf7_submit'), 10, 1 );

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
        $panel = $titan->createAdminPage(array(
            'name' => 'Zoho Auth Settings',
            'desc' => 'Zoho Leads with Contact Form 7 Integration form.',
            'id' => 'w3s-cf7-zoho',
            'parent' => 'edit.php?post_type=w3s_cf7'
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

        $metaBox = $titan->createMetaBox( array(
            'name' => 'Integration',
            'post_type' => 'w3s_cf7',
        ));

        $metaBox->createOption( array(
            'name' => 'Enable Integration',
            'id' => 'is_enabled',
            'type' => 'enable',
            'default' => true,
            'desc' => 'Enable or disable this integration',
        ));

        $metaBox->createOption( array(
            'name' => 'Select Contact form',
            'id' => 'cf7_form',
            'type' => 'select-posts',
            'desc' => 'Select Contact Form',
            'post_type' => 'wpcf7_contact_form',
        ));


    }

    public function w3s_cf7_post_action_for_metabox( ) {
        $post_id = $_GET[ 'post' ];

        if( get_post_type($post_id) == 'w3s_cf7' ) {


            $titan = TitanFramework::getInstance('w3s-cf7-zoho');


            $zoho_conn = new W3s_Cf7_Zoho_Conn();
            $cf7fields = $zoho_conn->getCF7Fields( $titan->getOption( 'cf7_form' , $post_id )); // need to
            $zohoFields = $zoho_conn->getZohoFields();


//            die(var_dump($cf7fields));



            $metaBox = $titan->createMetaBox( array(
                'name' => 'Field Mapping',
                'post_type' => 'w3s_cf7',
            ));
            $metaBox->createOption( array(
                'name' => 'Field Map 1',
                'type' => 'heading',
            ) );
            $metaBox->createOption( array(
                'name' => 'Contact Form 7 Field',
                'id' => 'cf7_field_1',
                'type' => 'select',
                'desc' => 'Select the Contact form 7 field.',
                'options' => $cf7fields,
            ));

            $metaBox->createOption( array(
                'name' => 'Match Zoho Field',
                'id' => 'zoho_field_1',
                'type' => 'select',
                'desc' => 'Select the Zoho field.',
                'options' => $zohoFields,
            ));
            $metaBox->createOption( array(
                'name' => 'Field Map 2',
                'type' => 'heading',
            ) );
            $metaBox->createOption( array(
                'name' => 'Contact Form 7 Field',
                'id' => 'cf7_field_2',
                'type' => 'select',
                'desc' => 'Select the Contact form 7 field.',
                'options' => $cf7fields,
            ));

            $metaBox->createOption( array(
                'name' => 'Match Zoho Field',
                'id' => 'zoho_field_2',
                'type' => 'select',
                'desc' => 'Select the Zoho field.',
                'options' => $zohoFields,
            ));
            $metaBox->createOption( array(
                'name' => 'Field Map 3',
                'type' => 'heading',
            ) );
            $metaBox->createOption( array(
                'name' => 'Contact Form 7 Field',
                'id' => 'cf7_field_3',
                'type' => 'select',
                'desc' => 'Select the Contact form 7 field.',
                'options' => $cf7fields,
            ));

            $metaBox->createOption( array(
                'name' => 'Match Zoho Field',
                'id' => 'zoho_field_3',
                'type' => 'select',
                'desc' => 'Select the Zoho field.',
                'options' => $zohoFields,
            ));
            $metaBox->createOption( array(
                'name' => 'Field Map 4',
                'type' => 'heading',
            ) );
            $metaBox->createOption( array(
                'name' => 'Contact Form 7 Field',
                'id' => 'cf7_field_4',
                'type' => 'select',
                'desc' => 'Select the Contact form 7 field.',
                'options' => $cf7fields,
            ));

            $metaBox->createOption( array(
                'name' => 'Match Zoho Field',
                'id' => 'zoho_field_4',
                'type' => 'select',
                'desc' => 'Select the Zoho field.',
                'options' => $zohoFields,
            ));
            $metaBox->createOption( array(
                'name' => 'Field Map 5',
                'type' => 'heading',
            ) );
            $metaBox->createOption( array(
                'name' => 'Contact Form 7 Field',
                'id' => 'cf7_field_5',
                'type' => 'select',
                'desc' => 'Select the Contact form 7 field.',
                'options' => $cf7fields,
            ));

            $metaBox->createOption( array(
                'name' => 'Match Zoho Field',
                'id' => 'zoho_field_5',
                'type' => 'select',
                'desc' => 'Select the Zoho field.',
                'options' => $zohoFields,
            ));
        }

    }






    // Register Custom Post Type
    public function w3s_cf7_post_type() {

        $labels = array(
            'name'                  => _x( 'Integrations', 'Post Type General Name', 'w3s_cf7' ),
            'singular_name'         => _x( 'Integration', 'Post Type Singular Name', 'w3s_cf7' ),
            'menu_name'             => __( 'Zoho with CF7', 'w3s_cf7' ),
            'name_admin_bar'        => __( 'Integration', 'w3s_cf7' ),
            'archives'              => __( 'Integration Archives', 'w3s_cf7' ),
            'attributes'            => __( 'Integration Attributes', 'w3s_cf7' ),
            'parent_item_colon'     => __( 'Parent Integration:', 'w3s_cf7' ),
            'all_items'             => __( 'All Integrations', 'w3s_cf7' ),
            'add_new_item'          => __( 'Add New Integration', 'w3s_cf7' ),
            'add_new'               => __( 'Add New', 'w3s_cf7' ),
            'new_item'              => __( 'New Integration', 'w3s_cf7' ),
            'edit_item'             => __( 'Edit Integration', 'w3s_cf7' ),
            'update_item'           => __( 'Update Integration', 'w3s_cf7' ),
            'view_item'             => __( 'View Integration', 'w3s_cf7' ),
            'view_items'            => __( 'View Integrations', 'w3s_cf7' ),
            'search_items'          => __( 'Search Integration', 'w3s_cf7' ),
            'not_found'             => __( 'Not found', 'w3s_cf7' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'w3s_cf7' ),
            'featured_image'        => __( 'Featured Image', 'w3s_cf7' ),
            'set_featured_image'    => __( 'Set featured image', 'w3s_cf7' ),
            'remove_featured_image' => __( 'Remove featured image', 'w3s_cf7' ),
            'use_featured_image'    => __( 'Use as featured image', 'w3s_cf7' ),
            'insert_into_item'      => __( 'Insert into integration', 'w3s_cf7' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'w3s_cf7' ),
            'items_list'            => __( 'Items list', 'w3s_cf7' ),
            'items_list_navigation' => __( 'Items list navigation', 'w3s_cf7' ),
            'filter_items_list'     => __( 'Filter items list', 'w3s_cf7' ),
        );
        $args = array(
            'label'                 => __( 'Integration', 'w3s_cf7' ),
            'description'           => __( 'Integration to Zoho with Contact Form 7', 'w3s_cf7' ),
            'labels'                => $labels,
            'supports'              => array( 'title'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 40,
            'menu_icon'             => 'dashicons-vault',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type( 'w3s_cf7', $args );

    }


    public function run_on_cf7_submit( $contact ) {

//        die(var_dump($contact->id()));

        $titan = TitanFramework::getInstance('w3s-cf7-zoho');


        die(var_dump($titan->getOption( 'cf7_field_1' , '14')));


        $contact_form = WPCF7_Submission::get_instance();
        if ( $contact_form ){
            $formData = $contact_form->get_posted_data();
        }

        $recordsArray = array();


        $args = array(
            'post_type' => 'w3s_cf7',
            'posts_per_page' => -1
        );
        // The Query for getting all integrations
        $the_query = new WP_Query( $args );

        // check integration are there
        if ( $the_query->have_posts() ) {

            // bring all integration
            while ( $the_query->have_posts() ) {

                $the_query->the_post();

                //check if the integration is for this contact form
                if ( ( $contact->id() == $titan->getOption( 'cf7_form' , get_the_ID()) ) && ($titan->getOption( 'is_enabled' , get_the_ID() == true ) ) ){



                    // initiate a blank Lead Instant
                    $record = ZCRMRecord::getInstance("Leads",null);
                    // populate fields


                    $cf7_field_1 = $titan->getOption( 'cf7_field_1' , get_the_ID() );
                    $zoho_field_1 = $titan->getOption( 'zoho_field_1' , get_the_ID() );

                    if (( $cf7_field_1 != null ) && ($zoho_field_1 != null)){
                        $record->setFieldValue($zoho_field_1, $formData[$cf7_field_1]);
                    }




                    // setup and push to array
                    $recordsArray[] = $record;


                }

            }

        }
        /* Restore original Post Data */
        wp_reset_postdata();




        if (!empty($recordsArray)){
            $zcrmModuleIns = ZCRMModule::getInstance("Leads");
            // $bulkAPIResponse=$zcrmModuleIns->upsertRecords($recordsArray); // Create or update
            $bulkAPIResponse = $zcrmModuleIns->createRecords($recordsArray); // Create record

            //dd($bulkAPIResponse);
        }



    }








}
