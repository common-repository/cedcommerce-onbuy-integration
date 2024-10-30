<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/includes
 */
class Onbuy_Integration_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @var      Onbuy_Integration_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ONBUY_INTEGRATION_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = ONBUY_INTEGRATION_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'onbuy-integration-by-cedcommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Onbuy_Integration_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Onbuy_Integration_For_Woocommerce_I18n. Defines internationalization functionality.
	 * - Ced_Onbuy_Integration_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Onbuy_Integration_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-onbuy-integration-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-onbuy-integration-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-onbuy-integration-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-onbuy-integration-for-woocommerce-public.php';

		$this->loader = new Onbuy_Integration_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Onbuy_Integration_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Onbuy_Integration_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {
		global $wpdb;
		$plugin_admin = new Ced_Onbuy_Integration_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_onbuy_add_menus', 22 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ced_onbuy_add_order_metabox' );
		$this->loader->add_action( 'ced_onbuy_feed_details', $plugin_admin, 'ced_onbuy_feed_details', 2, 22 );
		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_onbuy_add_marketplace_menus_to_array', 13 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_authorise_account', $plugin_admin, 'ced_onbuy_authorise_account', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_change_account_status', $plugin_admin, 'ced_onbuy_change_account_status', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_search_categories', $plugin_admin, 'ced_onbuy_search_categories', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_manage_woo_category_dropdown', $plugin_admin, 'ced_onbuy_manage_woo_category_dropdown', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_manage_woo_selected_category_dropdown', $plugin_admin, 'ced_onbuy_manage_woo_selected_category_dropdown', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_get_product_meta_keys_and_attributes', $plugin_admin, 'ced_onbuy_get_product_meta_keys_and_attributes', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_save_metakeys', $plugin_admin, 'ced_onbuy_save_metakeys', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_process_bulk_action', $plugin_admin, 'ced_onbuy_process_bulk_action', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_get_orders', $plugin_admin, 'ced_onbuy_get_orders', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_cancel_order', $plugin_admin, 'ced_onbuy_cancel_order', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_refund_order', $plugin_admin, 'ced_onbuy_refund_order', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_complete_dispatch_order', $plugin_admin, 'ced_onbuy_complete_dispatch_order', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_partial_dispatch_order', $plugin_admin, 'ced_onbuy_partial_dispatch_order', 22 );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'ced_onbuy_cron_schedules' );
		$this->loader->add_filter( 'woocommerce_email_enabled_new_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_customer_processing_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_cancelled_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_customer_completed_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_customer_on_hold_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_customer_refunded_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_enabled_customer_failed_order', $plugin_admin, 'ced_onbuy_marketplace_email_restrict', 10, 2 );
		$this->loader->add_filter( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'ced_onbuy_render_product_fields_html_for_variations', 10, 3 );
		$this->loader->add_filter( 'woocommerce_save_product_variation', $plugin_admin, 'ced_onbuy_save_variation_data', 10, 3 );
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'ced_onbuy_custom_product_tabs' );
		$this->loader->add_filter( 'woocommerce_product_data_panels', $plugin_admin, 'ced_onbuy_custom_panel_tab' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'ced_onbuy_save_metadata' );

		$this->loader->add_action( 'wp_ajax_ced_onbuy_delete_profile', $plugin_admin, 'ced_onbuy_delete_profile', 22 );

		$this->loader->add_action( 'wp_ajax_ced_onbuy_search_product_name', $plugin_admin, 'ced_onbuy_search_product_name', 22 );
		$this->loader->add_action( 'wp_ajax_ced_onbuy_get_product_metakeys', $plugin_admin, 'ced_onbuy_get_product_metakeys', 22 );

		$active_shops = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `account_status` = %s ', 'active' ), 'ARRAY_A' );
		foreach ( $active_shops as $key => $value ) {
			if ( ! get_transient( 'ced_onbuy_refresh_token_' . $value['shop_id'] ) ) {
				$this->ced_onbuy_refresh_token( $value['consumer_key'], $value['consumer_secret'], $value['shop_id'] );
			}
			$this->loader->add_action( 'ced_onbuy_inventory_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_inventory_schedule_manager' );
			$this->loader->add_action( 'ced_onbuy_auto_product_upload_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_auto_product_upload_schedule_manager' );
			$this->loader->add_action( 'ced_onbuy_check_winning_price_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_check_winning_price' );
			$this->loader->add_action( 'ced_onbuy_order_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_order_schedule_manager' );
			$this->loader->add_action( 'ced_onbuy_product_sync_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_product_sync_schedule_manager' );
			$this->loader->add_action( 'ced_onbuy_process_queue_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_onbuy_process_queue_schedule_manager' );

		}
		$this->loader->add_action( 'ced_onbuy_order_scheduler_job_', $plugin_admin, 'ced_onbuy_order_schedule_manager' );
		$this->loader->add_action( 'ced_onbuy_inventory_scheduler_job_', $plugin_admin, 'ced_onbuy_inventory_schedule_manager' );
		$this->loader->add_action( 'ced_onbuy_auto_product_upload_scheduler_job_', $plugin_admin, 'ced_onbuy_auto_product_upload_schedule_manager' );

		$this->loader->add_action( 'save_post', $plugin_admin, 'ced_save_metabox_data' );

		$this->loader->add_action( 'updated_post_meta', $plugin_admin, 'ced_onbuy_update_stock', 10, 4 );

		$this->loader->add_filter( 'wp_ajax_ced_onbuy_map_categories_to_store', $plugin_admin, 'ced_onbuy_map_categories_to_store' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_public_hooks() {

		$plugin_public = new Onbuy_Integration_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	public function ced_onbuy_refresh_token( $consumer_key = '', $consumer_secret = '', $seller_id = '' ) {
		$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$ced_onbuy_request = new Class_Ced_Onbuy_Request( $consumer_key, $consumer_secret );
		$action            = 'auth/request-token';
		$refresh_token     = $ced_onbuy_request->sendCurlPostMethod( $action );
		if ( isset( $refresh_token['access_token'] ) && ! empty( $refresh_token['access_token'] ) ) {
			set_transient( 'ced_onbuy_refresh_token_' . $seller_id, $refresh_token['access_token'], 14 * 60 );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Onbuy_Integration_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
