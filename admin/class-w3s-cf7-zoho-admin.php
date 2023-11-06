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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'init', array( $this, 'w3s_cf7_post_type' ), 0 );
		add_action( 'init', array( $this, 'admin_options' ), 1 );
		add_action( 'cmb2_admin_init', array( $this, 'w3s_cf7_post_action_for_metabox' ) );

		add_action( 'load-w3s_cf7_page_w3s-cf7-zoho', array( $this, 'processTokenGeneration' ), 0 );
		add_filter( 'plugin_action_links_w3s-cf7-zoho/w3s-cf7-zoho.php', array( $this, 'w3s_cf7_add_plugin_page_settings_link' ) );
		add_action( 'wpcf7_before_send_mail', array( $this, 'run_on_cf7_submit' ), 10, 1 );
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
	public function enqueue_scripts( $hook_suffix ) {

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

		$cpt = 'w3s_cf7';
		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
			$screen = get_current_screen();
			if ( is_object( $screen ) && $cpt == $screen->post_type ) {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/w3s-cf7-zoho-admin.js', array( 'jquery' ), $this->version, false );
			}
		}

	}

	public function plugins_loaded() {

	}

	/**
	 * Register the options for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_options() {
		new AdminMenusForZoho();
	}

	public function w3s_cf7_post_action_for_metabox() {

		$zoho_crm_modules = array(
			'Leads' => 'Leads',
		);
		$contact_forms    = array_column(
			get_posts(
				array(
					'post_type'   => 'wpcf7_contact_form',
					'numberposts' => -1,
				)
			),
			'post_title',
			'ID'
		);

		$integrationField = new_cmb2_box(
			array(
				'id'           => 'w3s_cf7_integration_metabox',
				'title'        => esc_html__( 'Integration', 'w3s-cf7-zoho' ),
				'object_types' => array( 'w3s_cf7' ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$integrationField->add_field(
			array(
				'name'             => 'Enable Integration',
				'id'               => 'integration_enable_disable',
				'desc'             => 'Select option',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => array(
					0 => 'Disable',
					1 => 'Enable',
				),
			)
		);

		$integrationField->add_field(
			array(
				'name'             => 'Zoho CRM Module',
				'id'               => 'w3s-cf7-zoho_zoho_module',
				'desc'             => 'Select option',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => $zoho_crm_modules,
			)
		);
		$integrationField->add_field(
			array(
				'name'             => 'Select Contact form',
				'id'               => 'w3s-cf7-zoho_cf7_form',
				'desc'             => 'Select option',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => $contact_forms,
			)
		);

		// ===============================================

		$cmb = new_cmb2_box(
			array(
				'id'           => 'w3s_cf7_fields_metabox',
				'title'        => esc_html__( 'Field Mapping', 'w3s-cf7-zoho' ),
				'object_types' => array( 'w3s_cf7' ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$cf7fields  = array();
		$zohoFields = array();
		$zoho_conn  = null;
		if ( isset( $_GET['post'] ) ) {
			$post_id = intval( sanitize_text_field( $_GET['post'] ) );
			if ( get_post_type( $post_id ) == 'w3s_cf7' ) {
				$zoho_conn  = new W3s_Cf7_Zoho_Conn();
				$formID     = get_post_meta( $post_id, 'w3s-cf7-zoho_cf7_form', true );
				$cf7fields  = $zoho_conn->getCF7Fields( $formID ); // need to
				$zohoFields = $zoho_conn->getZohoFields( get_post_meta( $post_id, 'w3s-cf7-zoho_zoho_module', true ) );
			}
		}

		$group_field_id = $cmb->add_field(
			array(
				'id'          => 'w3s_cf7_fields_repeat_group',
				'type'        => 'group',
				'description' => __( 'Map Contact form 7 fields to Zoho CRM fields', 'w3s-cf7-zoho' ),
				// 'repeatable'  => false, // use false if you want non-repeatable gro                                                   up
				'options'     => array(
					'group_title'    => __( 'Field Map {#}', 'w3s-cf7-zoho' ), // since version 1.1.4, {#} gets replaced by row number
					'add_button'     => __( 'Map Another Field', 'w3s-cf7-zoho' ),
					'remove_button'  => __( 'Remove Map', 'w3s-cf7-zoho' ),
					'sortable'       => true,
					'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'w3s-cf7-zoho' ), // Performs confirmation before removing group.
				),
			)
		);

		// Id's for group's fields only need to be unique for the group. Prefix is not needed.
		$cmb->add_group_field(
			$group_field_id,
			array(
				'name' => 'Manual Value',
				'id'   => 'manual_value',
				'type' => 'text',
			)
		);

		$cmb->add_group_field(
			$group_field_id,
			array(
				'name'             => 'CF7 Field Select',
				'desc'             => 'Select an option',
				'id'               => 'cf7_select',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => $cf7fields,
			)
		);

		$cmb->add_group_field(
			$group_field_id,
			array(
				'name'             => 'Zoho Field Select',
				'desc'             => 'Select an option',
				'id'               => 'zoho_select',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => $zohoFields,
			)
		);

	}

	public function w3s_cf7_add_plugin_page_settings_link( $links ) {
		$link = '<a href="' .
			admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho' ) .
			'">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $link );

		return $links;
	}

	// Register Custom Post Type
	public function w3s_cf7_post_type() {

		$labels = array(
			'name'                  => _x( 'Integrations', 'Post Type General Name', 'w3s-cf7-zoho' ),
			'singular_name'         => _x( 'Integration', 'Post Type Singular Name', 'w3s-cf7-zoho' ),
			'menu_name'             => __( 'Integrations', 'w3s-cf7-zoho' ),
			'name_admin_bar'        => __( 'Integration', 'w3s-cf7-zoho' ),
			'archives'              => __( 'Integration Archives', 'w3s-cf7-zoho' ),
			'attributes'            => __( 'Integration Attributes', 'w3s-cf7-zoho' ),
			'parent_item_colon'     => __( 'Parent Integration:', 'w3s-cf7-zoho' ),
			'all_items'             => __( 'All Integrations', 'w3s-cf7-zoho' ),
			'add_new_item'          => __( 'Add New Integration', 'w3s-cf7-zoho' ),
			'add_new'               => __( 'Add New', 'w3s-cf7-zoho' ),
			'new_item'              => __( 'New Integration', 'w3s-cf7-zoho' ),
			'edit_item'             => __( 'Edit Integration', 'w3s-cf7-zoho' ),
			'update_item'           => __( 'Update Integration', 'w3s-cf7-zoho' ),
			'view_item'             => __( 'View Integration', 'w3s-cf7-zoho' ),
			'view_items'            => __( 'View Integrations', 'w3s-cf7-zoho' ),
			'search_items'          => __( 'Search Integration', 'w3s-cf7-zoho' ),
			'not_found'             => __( 'Not found', 'w3s-cf7-zoho' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'w3s-cf7-zoho' ),
			'featured_image'        => __( 'Featured Image', 'w3s-cf7-zoho' ),
			'set_featured_image'    => __( 'Set featured image', 'w3s-cf7-zoho' ),
			'remove_featured_image' => __( 'Remove featured image', 'w3s-cf7-zoho' ),
			'use_featured_image'    => __( 'Use as featured image', 'w3s-cf7-zoho' ),
			'insert_into_item'      => __( 'Insert into integration', 'w3s-cf7-zoho' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'w3s-cf7-zoho' ),
			'items_list'            => __( 'Items list', 'w3s-cf7-zoho' ),
			'items_list_navigation' => __( 'Items list navigation', 'w3s-cf7-zoho' ),
			'filter_items_list'     => __( 'Filter items list', 'w3s-cf7-zoho' ),
		);
		$args   = array(
			'label'               => __( 'Integration', 'w3s-cf7-zoho' ),
			'description'         => __( 'Integration to Zoho CRM with Contact Form 7', 'w3s-cf7-zoho' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 40,
			'menu_icon'           => 'dashicons-vault',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		);
		register_post_type( 'w3s_cf7', $args );

	}

	public function run_on_cf7_submit( $contact ) {

		$zoho      = new W3s_Cf7_Zoho_Conn();
		$args      = array(
			'post_type'      => 'w3s_cf7',
			'posts_per_page' => -1,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$integrationEnableDisable = get_post_meta( get_the_ID(), 'integration_enable_disable', true ); // ss00
				$cf7ID                    = get_post_meta( get_the_ID(), 'w3s-cf7-zoho_cf7_form', true );// ss00

				$formData = array();

				// check if the integration is for this contact form
				if ( ( $contact->id() == $cf7ID ) && $integrationEnableDisable ) {

					$contact_form = WPCF7_Submission::get_instance();
					$formData     = $contact_form->get_posted_data();

					$entries = get_post_meta( get_the_ID(), 'w3s_cf7_fields_repeat_group', true );

					$record       = array();
					$recordsArray = array();

					if ( is_array( $entries ) ) {
						foreach ( $entries as $entry ) {
							$custom = $cf7_field = $zohoField = '';
							if ( isset( $entry['manual_value'] ) ) {
								$custom = esc_html( $entry['manual_value'] );
							}
							if ( isset( $entry['cf7_select'] ) ) {
								$cf7_field = esc_html( $entry['cf7_select'] );
							}
							if ( isset( $entry['zoho_select'] ) ) {
								$zohoField = esc_html( $entry['zoho_select'] );
							} else {
								continue;
							}

							// if we are entering manual value
							if ( $custom != '' ) {
								$record[ $zohoField ] = array( 'text', $custom );
							} else {
								$record[ $zohoField ] = array( $zoho->getDataType( $cf7_field ), $formData[ $zoho->removeDataType( $cf7_field ) ] );
							}
						}
					}

					array_push( $recordsArray, $record );

					// check if its upsert
					$upsert = get_post_meta( get_the_ID(), 'w3s-cf7-zoho_is_upsert', true );// ss00

					// multiple module support
					$module = get_post_meta( get_the_ID(), 'w3s-cf7-zoho_zoho_module', true );// ss00

					// support file upload
					$enableFileUpload = get_post_meta( get_the_ID(), 'w3s-cf7-zoho_enable_file', true );// ss00
					$files            = array();
					if ( $enableFileUpload ) {
						$files = $contact_form->uploaded_files();
					}

					$zoho->createRecord( $recordsArray, $upsert, $module, $files );
				}
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	}

	public function processTokenGeneration() {

		if ( isset( $_GET['code'] ) ) {

			

			$thisPageUrl = admin_url( 'edit.php?post_type=w3s_cf7&page=w3s-cf7-zoho' );

			try {

				$infos = new ZohoAuthInfos(); // ss00

				$apiBase = '';

				if ( $_GET['location'] == 'us' ) {
					$apiBase = 'https://www.zohoapis.com';
				} elseif ( $_GET['location'] == 'eu' ) {
					$apiBase = 'https://www.zohoapis.eu';
				} elseif ( $_GET['location'] == 'cn' ) {
					$apiBase = 'https://www.zohoapis.com.cn';
				} elseif ( $_GET['location'] == 'in' ) {
					$apiBase = 'https://www.zohoapis.in';
				} else {
					$apiBase = 'https://www.zohoapis.com';
				}

				$accountURL = esc_url( $_GET['accounts-server'] );

				$infos->setInfo( 'zoho_api_base_url', $apiBase )->storeInfo();// ss00
				$infos->setInfo( 'zoho_account_url', $accountURL )->storeInfo();// ss00

				$redirectURLEncoded = urlencode_deep( $thisPageUrl );

				$config = array(
					'apiBaseUrl'       => $apiBase,
					'client_id'        => $infos->getInfo( 'zoho_client_id' ),
					'client_secret'    => $infos->getInfo( 'zoho_client_secret' ),
					'redirect_uri'     => $redirectURLEncoded,
					'accounts_url'     => $accountURL,
					'currentUserEmail' => $infos->getInfo( 'zoho_user_email' ),
					'code'			   => sanitize_text_field( $_GET['code'] ),
					'code_use'		   => false,
					'access_type'      => 'offline',
					'apiVersion'       => 'v2',
					'time'             => time(), // ss00 # no need this column. just for change every authentication
				);

			

				$zoho_conn = new W3s_Cf7_Zoho_Conn();
				$conn = true;//$zoho_conn->genToken($config);

				


				// if ( $conn ) {
				// 	// var_dump($conn);die;
				// 	$infos->setInfo( 'zoho_authorized', true )->storeInfo();// ss00
				// 	$storeConfigs = update_option( '_zoho_config', serialize( $config ) ); // ss00

				// 	if ( $storeConfigs ) {
				// 		add_action( 'admin_notices', array( $this, 'admin_notice_on_success' ) );
				// 	} else {
				// 		add_action( 'admin_notices', array( $this, 'admin_notice_on_error' ) );
				// 	}
				// } else {
				// 	// var_dump("stop");die;
				// 	add_action( 'admin_notices', array( $this, 'admin_notice_on_error' ) );
				// }
			} catch ( Exception $e ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_on_error2' ) );
			}

			echo "<script>setTimeout(function(){window.history.pushState(null, document.title, '$thisPageUrl');},500)</script>";

		}

	}

	public function admin_notice_on_success() {         ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e( 'Zoho Authentication Successful!', 'w3s-cf7-zoho' ); ?></p>
</div>
<?php
	}

	public function admin_notice_on_error() {
		?>
<div class="notice notice-error is-dismissible">
    <p><?php _e( 'Zoho Authentication Error! Please check your credentials and try again. ', 'w3s-cf7-zoho' ); ?></p>
</div>
<?php
	}

	public function admin_notice_on_error2() {
		?>
<div class="notice notice-error is-dismissible">
    <p><?php _e( 'Already authenticated or something wrong!', 'w3s-cf7-zoho' ); ?></p>
</div>
<?php
	}
}