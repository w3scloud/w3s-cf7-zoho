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




    private $titan;



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
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        add_action( 'cmb2_admin_init', array($this, 'w3s_cf7_post_action_for_metabox') );
        add_filter('plugin_action_links_w3s-cf7-zoho/w3s-cf7-zoho.php', array( $this,'w3s_cf7_add_plugin_page_settings_link'));
        add_action( 'wpcf7_before_send_mail', array( $this,'run_on_cf7_submit'), 10, 1);
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


    public function plugins_loaded(){
        // titan framework options
        add_action( 'after_setup_theme', array( $this, 'after_setup_theme_add_titan' ), 5 );
        add_action( 'tf_create_options', array( $this, 'admin_options' ), 0);


    }


    /**
     * Register the options for the admin area.
     *
     * @since    1.0.0
     */
    public function admin_options()
    {

        // get instance of w3s-cf7-zoho
//        $titan = TitanFramework::getInstance('w3s-cf7-zoho');

        $titan = $this->titan;

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


        if (isset($_GET[ 'post' ])){
            $post_id = $_GET[ 'post' ];

            if( get_post_type($post_id) == 'w3s_cf7' ) {

                $titan = $this->titan;

                $cmb = new_cmb2_box( array(
                    'id'            => 'w3s_cf7_fields_metabox',
                    'title'         => esc_html__( 'Field Mapping', 'w3s-cf7' ),
                    'object_types'  => array( 'w3s_cf7' ),
                    'context'    => 'normal',
                    'priority'   => 'high',
                    'show_names' => true, // Show field names on the left
                ));

                $zoho_conn = new W3s_Cf7_Zoho_Conn();
                $cf7fields = $zoho_conn->getCF7Fields( $titan->getOption( 'cf7_form' , $post_id )); // need to
                $zohoFields = $zoho_conn->getZohoFields();


                $group_field_id = $cmb->add_field( array(
                    'id'          => 'w3s_cf7_fields_repeat_group',
                    'type'        => 'group',
                    'description' => __( 'Map Contact form 7 fields to Zoho fields', 'w3s-cf7' ),
                    // 'repeatable'  => false, // use false if you want non-repeatable group
                    'options'     => array(
                        'group_title'       => __( 'Field Map {#}', 'w3s-cf7' ), // since version 1.1.4, {#} gets replaced by row number
                        'add_button'        => __( 'Map Another Field', 'w3s-cf7' ),
                        'remove_button'     => __( 'Remove Map', 'w3s-cf7' ),
                        'sortable'          => true,
                        'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'w3s-cf7' ), // Performs confirmation before removing group.
                    ),
                ) );


                // Id's for group's fields only need to be unique for the group. Prefix is not needed.
                $cmb->add_group_field( $group_field_id, array(
                    'name' => 'Manual Value',
                    'id'   => 'manual_value',
                    'type' => 'text',
                ) );

                $cmb->add_group_field( $group_field_id, array(
                    'name'             => 'CF7 Field Select',
                    'desc'             => 'Select an option',
                    'id'               => 'cf7_select',
                    'type'             => 'select',
                    'show_option_none' => true,
                    'options'          => $cf7fields,
                ));


                $cmb->add_group_field( $group_field_id, array(
                    'name'             => 'Zoho Field Select',
                    'desc'             => 'Select an option',
                    'id'               => 'zoho_select',
                    'type'             => 'select',
                    'show_option_none' => true,
                    'options'          => $zohoFields,
                ));


            }

        } else {
            $cmb = new_cmb2_box( array(
                'id'            => 'w3s_cf7_fields_metabox',
                'title'         => esc_html__( 'Field Mapping', 'w3s-cf7' ),
                'object_types'  => array( 'w3s_cf7' ),
                'context'    => 'normal',
                'priority'   => 'high',
                'show_names' => true, // Show field names on the left
            ));


            $cf7fields = array();
            $zohoFields = array();

            $group_field_id = $cmb->add_field( array(
                'id'          => 'w3s_cf7_fields_repeat_group',
                'type'        => 'group',
                'description' => __( 'Map Contact form 7 fields to Zoho fields', 'w3s-cf7' ),
                // 'repeatable'  => false, // use false if you want non-repeatable group
                'options'     => array(
                    'group_title'       => __( 'Field Map {#}', 'w3s-cf7' ), // since version 1.1.4, {#} gets replaced by row number
                    'add_button'        => __( 'Map Another Field', 'w3s-cf7' ),
                    'remove_button'     => __( 'Remove Map', 'w3s-cf7' ),
                    'sortable'          => true,
                    'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'w3s-cf7' ), // Performs confirmation before removing group.
                ),
            ) );


            // Id's for group's fields only need to be unique for the group. Prefix is not needed.
            $cmb->add_group_field( $group_field_id, array(
                'name' => 'Manual Value',
                'id'   => 'manual_value',
                'type' => 'text',
            ) );

            $cmb->add_group_field( $group_field_id, array(
                'name'             => 'CF7 Field Select',
                'desc'             => 'Select an option',
                'id'               => 'cf7_select',
                'type'             => 'select',
                'show_option_none' => true,
                'options'          => $cf7fields,
            ));


            $cmb->add_group_field( $group_field_id, array(
                'name'             => 'Zoho Field Select',
                'desc'             => 'Select an option',
                'id'               => 'zoho_select',
                'type'             => 'select',
                'show_option_none' => true,
                'options'          => $zohoFields,
            ));
        }

    }


    public function w3s_cf7_add_plugin_page_settings_link( $links ) {
        $link = '<a href="' .
            admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho' ) .
            '">' . __('Settings') . '</a>';
        array_unshift($links, $link);


        return $links;
    }


    public function after_setup_theme_add_titan(){
        $this->titan = TitanFramework::getInstance('w3s-cf7-zoho');
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

        $titan = $this->titan;
        $recordsArray = array();

//        die(var_dump($recordsArray));
        $args = array(
            'post_type' => 'w3s_cf7',
            'posts_per_page' => -1
        );
        // The Query for getting all integrations
        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();



                //check if the integration is for this contact form
                if (  $contact->id() == $titan->getOption( 'cf7_form' , get_the_ID()) ){

                    $contact_form = WPCF7_Submission::get_instance();
                    if ( $contact_form ){
                        $formData = $contact_form->get_posted_data();
                    }

                    $entries = get_post_meta( get_the_ID(), 'w3s_cf7_fields_repeat_group', true );


                    foreach ( $entries as $key => $entry ) {

                        if ( isset( $entry['manual_value'] ) ) {
                            $custom = esc_html( $entry['manual_value'] );
                        }
                        if ( isset( $entry['cf7_select'] ) ) {
                            $cf7_field = $entry['cf7_select'];
                        }
                        if ( isset( $entry['zoho_select'] ) ) {
                            $zohoField = $entry['zoho_select'];
                        }
                        if (!isset($zohoField)){
                            continue;
                        }
                        // if we are entering manual value
                        if (!isset($custom)){
                            $recordsArray[$zohoField] = $formData[$cf7_field];
                        } else {
                            $recordsArray[$zohoField] = $custom;
                        }

                    }



                }

            }

        }
        /* Restore original Post Data */
        wp_reset_postdata();
//
        die(var_dump($formData));

        if (!empty($recordsArray)){
            $zoho = new W3s_Cf7_Zoho_Conn();
            $zoho->createRecord($recordsArray);
        }



    }



}
