<?php
if ( ! class_exists( 'Class_Ced_Onbuy_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Onbuy_Integration_By_CedCommerce
	 * @subpackage Onbuy_Integration_By_CedCommerce/admin/onbuy-fr
	 */
	class Class_Ced_Onbuy_Manager {

		/**
		 * The Instace of Class_Ced_Onbuy_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of Class_Ced_Onbuy_Manager class.
		 */
		private static $_instance;
		private $plugin_name;
		private $version;
		public $new_id;
		/**
		 * Class_Ced_Onbuy_Manager Instance.
		 *
		 * Ensures only one instance of Class_Ced_Onbuy_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return Class_Ced_Onbuy_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {

			$this->load_dependency();
			add_action( 'ced_onbuy_refresh_token', array( $this, 'ced_onbuy_refresh_token' ) );
			add_action( 'admin_init', array( $this, 'ced_onbuy_schedules' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'ced_onbuy_update_inventory_on_order_creation' ), 10, 1 );
			add_action( 'updated_post_meta', array( $this, 'ced_realtime_sync_inventory_to_onbuy' ), 12, 4 );
		}

		public function ced_onbuy_schedules() {
			if ( isset( $_GET['shop_id'] ) && ! empty( $_GET['shop_id'] ) ) {
				$shop_id = sanitize_text_field( $_GET['shop_id'] );

				$render_data_on_global_settings       = get_option( 'ced_onbuy_global_settings', array() );
				$order_schedule_info                  = isset( $render_data_on_global_settings[ $shop_id ]['order_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['order_schedule_info'] : '';
				$ced_onbuy_auto_upload_product        = isset( $render_data_on_global_settings[ $shop_id ]['auto_upload_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['auto_upload_schedule_info'] : '';
				$ced_onbuy_product_sync_schedule_info = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_info'] : '';
				$attribute_key                        = get_option( 'ced_onbuy_product_sync_scheduler_key_' . $shop_id, '' );
				if ( ! empty( $attribute_key ) && ! empty( $ced_onbuy_product_sync_schedule_info ) ) {
					if ( ! wp_get_schedule( 'ced_onbuy_product_sync_scheduler_job_' . $shop_id ) ) {
						wp_schedule_event( time(), 'ced_onbuy_6min', 'ced_onbuy_product_sync_scheduler_job_' . $shop_id );
					}
				}

				if ( ! wp_get_schedule( 'ced_onbuy_process_queue_scheduler_job_' . $shop_id ) ) {
					wp_schedule_event( time(), 'ced_onbuy_10min', 'ced_onbuy_process_queue_scheduler_job_' . $shop_id );
				}
			}
		}

		/**
		 * ******************************************************
		 * Real time Sync product form Wooocommerce to onbuy shop.
		 * ******************************************************
		 *
		 * @param $meta_id    Udpated product meta meta_id of the product.
		 * @param $product_id Updated meta value of the product id.
		 * @param $meta_key   Update products meta key.
		 * @param $mta_value  Udpated changed meta value of the post.
		 */
		public function ced_realtime_sync_inventory_to_onbuy( $meta_id, $product_id, $meta_key, $meta_value ) {

			// If tha is changed by _stock only.
			if ( '_stock' == $meta_key || '_price' == $meta_key ) {
				// Active shop name
				$shop_id = get_option( 'ced_onbuy_shop_id', '' );

				$_product = wc_get_product( $product_id );
				if ( ! wp_get_schedule( 'ced_onbuy_inventory_scheduler_job_' . $shop_id ) || ! is_object( $_product ) ) {
					return;
				}
				// All products by product id
				// check if it has variations.
				if ( $_product->get_type() == 'variation' ) {
					$product_id = $_product->get_parent_id();
				}
				/**
				 * *******************************************
				 *   CALLING FUNCTION TO UDPATE THE INVENTORY
				 * *******************************************
				 */
				$this->ced_prepare_product_html_for_update_stock( array( $product_id ), $shop_id, true );
			}
		}

		public function ced_onbuy_update_inventory_on_order_creation( $order_id ) {
			if ( empty( $order_id ) ) {
				return;
			}
			$product_ids   = array();
			$inventory_log = array();
			$order_obj     = wc_get_order( $order_id );
			$order_items   = $order_obj->get_items();
			if ( is_array( $order_items ) && ! empty( $order_items ) ) {
				foreach ( $order_items as $key => $value ) {
					$product_id    = $value->get_data()['product_id'];
					$product_ids[] = $product_id;
				}
			}
			if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
				$response        = $this->ced_prepare_product_html_for_update_stock( $product_ids, '', true );
				$inventory_log[] = $response;
			}
		}
		// ==================================================================================================
		public function ced_onbuy_refresh_token( $shop_id = '' ) {

			$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
			if ( file_exists( $file_request ) ) {
				include_once $file_request;
			}

			global $wpdb;
			$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `shop_id` = %d', $shop_id ), 'ARRAY_A' );

			$consumerKey       = isset( $result[0]['consumer_key'] ) ? $result[0]['consumer_key'] : '';
			$secretKey         = isset( $result[0]['consumer_secret'] ) ? $result[0]['consumer_secret'] : '';
			$ced_onbuy_request = new Class_Ced_Onbuy_Request( $consumerKey, $secretKey );
			$action            = 'auth/request-token';
			$refresh_token     = $ced_onbuy_request->sendCurlPostMethod( $action );
			if ( isset( $refresh_token['access_token'] ) && ! empty( $refresh_token['access_token'] ) ) {
				set_transient( 'ced_onbuy_refresh_token_' . $shop_id, $refresh_token['access_token'], 14 * 60 );
			}
		}

		public function load_dependency() {
			$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
			if ( file_exists( $file_request ) ) {
				include_once $file_request;
			}
			$this->ced_onbuy_request = new Class_Ced_Onbuy_Request();

			$file_product = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-products.php';
			if ( file_exists( $file_product ) ) {
				include_once $file_product;
			}
			$this->ced_onbuy_product = new Class_Ced_Onbuy_Products();
		}

		public function ced_get_validation_account( $consumerKey = '', $secretKey = '', $sellerId = '' ) {

			$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
			if ( file_exists( $file_request ) ) {
				include_once $file_request;
			}
			$ced_onbuy_request = new Class_Ced_Onbuy_Request( $consumerKey, $secretKey );
			$action            = 'auth/request-token';
			$refresh_token     = $ced_onbuy_request->sendCurlPostMethod( $action );
			if ( isset( $refresh_token['access_token'] ) && ! empty( $refresh_token['access_token'] ) ) {
				$action           = 'sellers/' . $sellerId;
				$sellerData       = $ced_onbuy_request->ced_onbuy_get_method( $action, '', $refresh_token['access_token'] );
				$action           = 'sellers/deliveries';
				$queries          = 'site_id=2000';
				$sellerDeliveries = $ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $refresh_token['access_token'] );
				global $wpdb;
				$table_name = $wpdb->prefix . 'ced_onbuy_accounts';
				$wpdb->insert(
					$table_name,
					array(
						'account_status'    => 'active',
						'shop_id'           => (int) $sellerId,
						'consumer_key'      => $consumerKey,
						'consumer_secret'   => $secretKey,
						'seller_data'       => json_encode( $sellerData['results'] ),
						'seller_deliveries' => json_encode( $sellerDeliveries['results'] ),
					)
				);
				set_transient( 'ced_onbuy_refresh_token_' . $sellerId, $refresh_token['access_token'], 14 * 60 );
				return '200';
			} else {
				return 'Unauthorised';
			}
		}

		public function ced_onbuy_fetch_category_method( $keyword = '', $shop_id = '' ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );
			$action       = 'categories';
			$queries      = 'site_id=2000&limit=100&filter[name]=' . $keyword . '&filter[can_list_in]=1';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$categories   = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
			return $categories;
		}

		public function ced_onbuy_fetch_queue_status( $queue_id = '', $shop_id = '' ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );
			$action         = 'queues/' . $queue_id;
			$queries        = 'site_id=2000';
			$access_token   = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$queue_response = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
			return $queue_response;
		}

		public function ced_onbuy_get_category_features( $catId = '', $shop_id = '' ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );
			$action       = 'categories/' . $catId . '/features';
			$queries      = 'site_id=2000&limit=10&offset=0';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$categories   = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
			return $categories;
		}

		public function ced_onbuy_get_category_tech_details( $catId = '', $shop_id = '' ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );
			$action       = 'categories/' . $catId . '/technical-details';
			$queries      = 'site_id=2000';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$categories   = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
			return $categories;
		}

		public function ced_onbuy_process_queue( $pro_ids = array(), $shop_id ) {
			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}

			foreach ( $pro_ids as $key => $pro_id ) {
				$_product = wc_get_product( $pro_id );

				if ( ! is_object( $_product ) ) {
					continue;
				}
				$type     = $_product->get_type();
				$queue_id = get_post_meta( $pro_id, '_ced_onbuy_queue_id_' . $shop_id, true );

				if ( isset( $queue_id ) && ! empty( $queue_id ) ) {
					$response = $this->ced_onbuy_fetch_queue_status( $queue_id, $shop_id );
					if ( isset( $response['results']['opc'] ) && ! empty( $response['results']['opc'] ) ) {
						if ( 'variable' == $type ) {
							update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results']['opc'] );
							if ( $response['results']['variant_opcs'] ) {
								$variant_opcs = $response['results']['variant_opcs'];
								$_product     = wc_get_product( $pro_id );
								$variations   = $_product->get_available_variations();
								foreach ( $variations as $key => $variation ) {
									update_post_meta( $variation['variation_id'], '_ced_onbuy_listing_id_' . $shop_id, $variant_opcs[ $key ] );
								}
							}
						} else {
							update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results']['opc'] );
						}
						delete_post_meta( $pro_id, '_ced_onbuy_queue_id_' . $shop_id );
						delete_post_meta( $pro_id, '_ced_onbuy_error' . $shop_id );
					} elseif ( isset( $response['results']['status'] ) && 'failed' == $response['results']['status'] ) {
						update_post_meta( $pro_id, '_ced_onbuy_error' . $shop_id, $response['results']['error_message'] );
					}
				}
			}
		}

		public function ced_prepare_product_html_for_upload( $pro_ids = array(), $shop_id, $is_sync = false ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_uploading( $pro_ids, $shop_id, $is_sync );
			return $response;
		}
		// -------------------------------------------------------------------------------------------
		public function ced_prepare_product_html_for_check_winning( $skus = array(), $shop_id ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $skus ) ) {
				$skus = array( $skus );
			}
			$response = $this->ced_onbuy_product->ced_check_winning_price_to_onbuy( $skus, $shop_id );
			return $response;
		}
		// ------------------------------------------------------------------------------------------
		public function ced_prepare_product_html_for_update( $pro_ids = array(), $shop_id, $is_sync = false ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_updating( $pro_ids, $shop_id, $is_sync );
			return $response;
		}

		public function ced_prepare_product_html_for_delete( $pro_ids = array(), $shop_id, $is_sync = false ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_delete( $pro_ids, $shop_id, $is_sync );
			return $response;
		}

		public function ced_prepare_product_html_for_update_stock( $pro_ids = array(), $shop_id, $is_sync = false ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_update_stock( $pro_ids, $shop_id, $is_sync );
			return $response;
		}

		public function ced_prepare_product_html_for_create_listing( $pro_ids = array(), $shop_id, $is_sync = false ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_create_listing( $pro_ids, $shop_id, $is_sync );
			return $response;
		}

		public function ced_prepare_product_html_for_product_sync( $pro_ids = array(), $shop_id ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			if ( ! is_array( $pro_ids ) ) {
				$pro_ids = array( $pro_ids );
			}
			$response = $this->ced_onbuy_product->ced_onbuy_prepare_data_for_product_sync( $pro_ids, $shop_id );
			return $response;
		}

		public function ced_create_onbuy_order( $shop_id ) {
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			$response = $this->ced_onbuy_order->ced_create_onbuy_order_post( $shop_id );
			return $response;
		}

		// =============================== Profile Creating =============================================

		/**
		 * OnBuy Create Auto Profiles
		 *
		 * @since    1.0.0
		 */
		public function ced_onbuy_create_auto_profiles( $onbuy_mapped_categories = array(), $onbuy_mapped_categories_name = array(), $shop_id = '' ) {
			global $wpdb;
			$woo_store_categories           = get_terms( 'product_cat' );
			$already_mapped_categories      = get_option( 'ced_woo_onbuy_mapped_categories', array() );
			$already_mapped_categories_name = get_option( 'ced_woo_onbuy_mapped_categories_name', array() );

			if ( ! empty( $onbuy_mapped_categories ) ) {
				foreach ( $onbuy_mapped_categories as $key => $value ) {
					$profile_already_created = get_term_meta( $key, 'ced_onbuy_profile_created', true );
					$created_profile_id      = get_term_meta( $key, 'ced_onbuy_profile_id', true );
					if ( ! empty( $profile_already_created ) && 'yes' == $created_profile_id ) {

						$new_profile_need_to_be_created = $this->check_if_new_profile_need_to_be_created( $key, $value );

						if ( ! $new_profile_need_to_be_created ) {
							continue;
						} else {
							$this->reset_mapped_category_data( $key, $value );
						}
					}
					$woo_categories     = array();
					$categoryAttributes = array();

					$profileName = isset( $onbuy_mapped_categories_name[ $value ] ) ? $onbuy_mapped_categories_name[ $value ] : 'Profile for onbuy - Category Id : ' . $value;

					$profile_id = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `profile_name` = %s", $profileName ), 'ARRAY_A' );
					if ( ! isset( $profile_id[0]['id'] ) && empty( $profile_id[0]['id'] ) ) {
						$is_active       = 1;
						$marketplaceName = 'onbuy';

						foreach ( $onbuy_mapped_categories as $key1 => $value1 ) {
							if ( $value1 == $value ) {
								$woo_categories[] = $key1;
							}
						}
						$profileData    = array();
						$profileData    = $this->prepare_profile_data( $value, $woo_categories );
						$profileDetails = array(
							'profile_name'   => $profileName,
							'profile_status' => 'active',
							'shop_id'        => $shop_id,
							'profile_data'   => json_encode( $profileData ),
							'woo_categories' => json_encode( $woo_categories ),
						);
						$profileId      = $this->insert_onbuy_profile( $profileDetails );
					} else {
						$woo_categories     = array();
						$profileId          = $profile_id[0]['id'];
						$profile_categories = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id` = %d ", $profileId ), 'ARRAY_A' );
						$woo_categories     = json_decode( $profile_categories[0]['woo_categories'], true );
						$woo_categories[]   = $key;
						$table_name         = $wpdb->prefix . 'ced_onbuy_profiles';
						$wpdb->update(
							$table_name,
							array(
								'woo_categories' => json_encode( array_unique( $woo_categories ) ),
							),
							array( 'id' => $profileId )
						);
					}
					foreach ( $woo_categories as $key12 => $value12 ) {
						update_term_meta( $value12, 'ced_onbuy_profile_created', 'yes' );
						update_term_meta( $value12, 'ced_onbuy_profile_id', $profileId );
						update_term_meta( $value12, 'ced_onbuy_mapped_category', $value );
					}
				}
			}
		}

		/**
		 * Onbuy Check if Profile Need to be Created
		 *
		 * @since    1.0.0
		 */
		public function check_if_new_profile_need_to_be_created( $woo_category_id = '', $onbuy_category_id = '' ) {

			$old_onbuy_category_mapped = get_term_meta( $woo_category_id, 'ced_onbuy_mapped_category', true );
			if ( $old_onbuy_category_mapped == $onbuy_category_id ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Onbuy Update Mapped Category data
		 *
		 * @since    1.0.0
		 */
		public function reset_mapped_category_data( $woo_category_id = '', $onbuy_category_id = '' ) {

			update_term_meta( $woo_category_id, 'ced_onbuy_mapped_category', $onbuy_category_id );

			delete_term_meta( $woo_category_id, 'ced_onbuy_profile_created' );

			$created_profile_id = get_term_meta( $woo_category_id, 'ced_onbuy_profile_id', true );

			delete_term_meta( $woo_category_id, 'ced_onbuy_profile_id' );

			$this->remove_category_mapping_from_profile( $created_profile_id, $woo_category_id );
		}

		/**
		 * Onbuy Remove previous mapped profile
		 *
		 * @since    1.0.0
		 */
		public function remove_category_mapping_from_profile( $createdProfileId = '', $wooCategoryId = '' ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_onbuy_profiles';
			$profile_data     = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id`=%s ", $createdProfileId ), 'ARRAY_A' );
			if ( is_array( $profile_data ) ) {

				$profile_data  = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$wooCategories = isset( $profile_data['woo_categories'] ) ? json_decode( $profile_data['woo_categories'], true ) : array();
				if ( is_array( $wooCategories ) && ! empty( $wooCategories ) ) {
					$categories = array();
					foreach ( $wooCategories as $key => $value ) {
						if ( $value != $wooCategoryId ) {
							$categories[] = $value;
						}
					}
					$categories = json_encode( $categories );
					$wpdb->update( $profileTableName, array( 'woo_categories' => $categories ), array( 'id' => $createdProfileId ) );
				}
			}
		}

		/**
		 * Onbuy Prepare Profile data
		 *
		 * @since    1.0.0
		 */
		public function prepare_profile_data( $onbuy_category_id, $woo_categories = '' ) {

			$global_settings    = get_option( 'ced_onbuy_global_settings', array() );
			$shipping_templates = get_option( 'ced_onbuy_details', array() );

			$onbuy_shop_global_settings = isset( $global_settings ) ? $global_settings : array();
			$profile_data               = array();
			$selected_shipping_template = isset( $shipping_templates['shippingTemplateId'] ) ? $shipping_templates['shippingTemplateId'] : null;

			$profile_data['_umb_onbuy_category']['default']         = $onbuy_category_id;
			$profile_data['_umb_onbuy_category']['metakey']         = null;
			$profile_data['_ced_onbuy_shipping_profile']['default'] = $selected_shipping_template;
			$profile_data['_ced_onbuy_shipping_profile']['metakey'] = null;

			if ( isset( $onbuy_shop_global_settings['product_data'] ) && ! empty( $onbuy_shop_global_settings['product_data'] ) ) {
				foreach ( $onbuy_shop_global_settings['product_data'] as $key => $value ) {
					$profile_data[ $key ]['default'] = isset( $value['default'] ) ? $value['default'] : '';
					$profile_data[ $key ]['metakey'] = isset( $value['metakey'] ) ? $value['metakey'] : '';

				}
			}
			return $profile_data;
		}

		/**
		 * Onbuy Insert Profiles In database
		 *
		 * @since    1.0.0
		 */
		public function insert_onbuy_profile( $profile_details ) {

			global $wpdb;
			$profile_table_name = $wpdb->prefix . 'ced_onbuy_profiles';

			$wpdb->insert( $profile_table_name, $profile_details );

			$profile_id = $wpdb->insert_id;
			return $profile_id;
		}

		// ======================= Edit Profile View ======================================

		public function get_custom_products_fields() {
			$shop_id = get_option( 'ced_onbuy_shop_id' );
			global $wpdb;
			$shop_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `shop_id` = %d ', $shop_id ), 'ARRAY_A' );
			$shop_details = $shop_details[0];
			if ( isset( $shop_details['seller_deliveries'] ) && ! empty( $shop_details['seller_deliveries'] ) ) {
				$seller_deliveries = json_decode( $shop_details['seller_deliveries'], true );
				if ( isset( $seller_deliveries ) && ! empty( $seller_deliveries ) ) {
					foreach ( $seller_deliveries as $key => $value ) {
						$deliveries_option[ $value['delivery_type_id'] ] = $value['template_name'];
					}
					$deliveries_option = array_unique( $deliveries_option );
				}
			}
			if ( isset( $deliveries_option ) && ! empty( $deliveries_option ) ) {
				foreach ( $deliveries_option as $key => $value ) {
					$html = array(
						'type'     => '_select',
						'id'       => '_ced_onbuy_deliveries',
						'fields'   => array(
							'id'          => '_ced_onbuy_deliveries',
							'label'       => __( 'Onbuy Deliveries Method', 'woocommerce-onbuy-integration' ),
							'desc_tip'    => true,
							'description' => __( 'Onbuy Deliveries Method.', 'woocommerce-onbuy-integration' ),
							'type'        => 'select',
							'options'     => array(
								$key => __( $value ),
							),
							'class'       => 'wc_input_price',
						),
						'required' => false,
					);
				}
			}

			$required_fields = array(
				array(

					'type'   => '_hidden',
					'id'     => '_umb_onbuy_category',
					'fields' => array(
						'id'          => '_umb_onbuy_category',
						'label'       => __( 'onbuy Category', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the onbuy category.', 'woocommerce-onbuy-integration' ),
						'type'        => 'hidden',
						'class'       => 'wc_input_price',
					),

				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_stock',
					'fields' => array(
						'id'          => '_ced_onbuy_stock',
						'label'       => __( 'Stock', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product Stock.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'placeholder' => 'Stock',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_=ced_onbuy_title',
					'fields' => array(
						'id'          => '_ced_onbuy_title',
						'label'       => __( 'Title', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product name or title must not exceed more than 150 characters.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_description',
					'fields' => array(
						'id'          => '_ced_onbuy_description',
						'label'       => __( 'Description', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product description must not exceed more than 50,000 characters.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_price',
					'fields' => array(
						'id'          => '_ced_onbuy_price',
						'label'       => __( 'Price', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product price should not be empty.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'     => '_select',
					'id'       => '_ced_onbuy_condition',
					'fields'   => array(
						'id'          => '_ced_onbuy_condition',
						'label'       => __( 'Condition', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Choose the product condition from the dropdown.', 'woocommerce-onbuy-integration' ),
						'type'        => 'select',
						'is_required' => true,
						'options'     => array(
							'new'                  => __( 'New Product' ),
							'diamond'              => __( 'Refurbished (Diamond)' ),
							'platinum'             => __( 'Refurbished (Platinum)' ),
							'gold'                 => __( 'Refurbished (Gold)' ),
							'silver'               => __( 'Refurbished (Silver)' ),
							'bronze'               => __( 'Refurbished (Bronze)' ),
							'refurbished-ungraded' => __( 'Refurbished' ),
							'excellent'            => __( 'Excellent' ),
							'verygood'             => __( 'Very Good' ),
							'good'                 => __( 'Good' ),
							'average'              => __( 'Average' ),
							'belowaverage'         => __( 'Below Average' ),

						),
						'class'       => 'wc_input_price',
					),
					'required' => true,
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_ean',
					'fields' => array(
						'id'          => '_ced_onbuy_ean',
						'label'       => __( 'Product Code', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product code or barcode must be a 13-digits valid code. It should not be GTIN reserved or invalid code. If the products having Brand exemption then no need of product code for the products.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_brand',
					'fields' => array(
						'id'          => '_ced_onbuy_brand',
						'label'       => __( 'Brand', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'If the products having the Brand exemption for the products then make sure brand name must be same on woocommerce as well as OnBuy. For ex: and or & is treated as different thing while uploading.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_mpn',
					'fields' => array(
						'id'          => '_ced_onbuy_mpn',
						'label'       => __( 'Mpn', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Prodcut Mpn can be sku or product code of the products.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_handling_time',
					'fields' => array(
						'id'          => '_ced_onbuy_handling_time',
						'label'       => __( 'Handling Time', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Handling Time.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => true,
						'class'       => 'wc_input_price',
					),
				),

				$html,

				array(
					'type'     => '_select',
					'id'       => '_ced_onbuy_published',
					'fields'   => array(
						'id'          => '_ced_onbuy_published',
						'label'       => __( 'Published', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies whether the product should be published to the site once its created.', 'woocommerce-onbuy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'1' => __( 'True' ),
							'0' => __( 'False' ),
						),
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_summary_points',
					'fields' => array(
						'id'          => '_ced_onbuy_summary_points',
						'label'       => __( 'Summary Points', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product summary points must not exceed more than 500 characters.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_video',
					'fields' => array(
						'id'          => '_ced_onbuy_video',
						'label'       => __( 'Videos', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product video url.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_rrp',
					'fields' => array(
						'id'          => '_ced_onbuy_rrp',
						'label'       => __( 'Retail Price', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Product Retail price.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'     => '_select',
					'id'       => '_ced_onbuy_product_markup_type',
					'fields'   => array(
						'id'          => '_ced_onbuy_product_markup_type',
						'label'       => __( 'Increase Price By', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Increase price by a certain amount in the actual price of the product when uploading on onbuy.', 'woocommerce-onbuy-integration' ),
						'type'        => 'select',
						'options'     => array(
							'Fixed_Increased'      => __( 'Fixed Increased' ),
							'Fixed_Decreased'      => __( 'Fixed Decreased' ),
							'Percentage_Increased' => __( 'Percentage Increased' ),
							'Percentage_Decreased' => __( 'Percentage Decreased' ),
						),
						'class'       => 'wc_input_price',
					),
					'required' => false,
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_product_markup',
					'fields' => array(
						'id'          => '_ced_onbuy_product_markup',
						'label'       => __( 'Markup Value', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Enter the markup value to be added in the price. Eg : 10.', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_conversion_rate',
					'fields' => array(
						'id'          => '_ced_onbuy_conversion_rate',
						'label'       => __( 'Conversion Rate', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Enter the conversion rate to be added in the price. Eg : 10', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_onbuy_boost_percent',
					'fields' => array(
						'id'          => '_ced_onbuy_boost_percent',
						'label'       => __( 'Boost Percentage', 'woocommerce-onbuy-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Enter the boost percentage to be apply on the products. The value should not be greater than 10. Eg : 10', 'woocommerce-onbuy-integration' ),
						'type'        => 'text',
						'is_required' => false,
						'class'       => 'wc_input_price',
					),
				),
			);

			return $required_fields;
		}

		/*
		* Function to render dropdown html
		*/
		public function render_dropdown_html( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = false ) {
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				foreach ( $values as $key => $value ) {
					if ( ! empty( $value['option_id'] ) ) {
						$previousValue = isset( $additionalInfo['value']['default'] ) ? $additionalInfo['value']['default'] : '';
					} else {
						$previousValue = isset( $additionalInfo['value'] ) ? $additionalInfo['value'] : '';
					}
				}
			}
			?>
			<!-- <p class="form-field _umb_id_type_field "> -->
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				<label for=""><?php echo esc_attr( $attribute_name ); ?></label>
				<?php
				if ( $is_required ) {
					?>
					<span style="color: red; margin-left:5px; ">*</span>
					<?php
				}
				if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
					ced_onbuy_tool_tip( $attribute_description );
				}
				?>
			</td>
			<td>
				<select id="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" class="select short" style="">
					<?php
					echo '<option value="">-- Select --</option>';
					foreach ( $values as $key => $value ) {
						if ( ! empty( $value['option_id'] ) ) {
							if ( $previousValue == $value['option_id'] ) {
								echo '<option value="' . esc_attr( $value['option_id'] ) . '" selected>' . esc_attr( $value['name'] ) . '</option>';
							} else {
								echo '<option value="' . esc_attr( $value['option_id'] ) . '">' . esc_attr( $value['name'] ) . '</option>';
							}
						} else {
							if ( $previousValue == $key ) {
								echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
							} else {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
							}
						}
					}
					?>
				</select>
			</td>

			<!-- </p> -->
			<?php
		}

			/*
		* Function to render input text html
		*/
		public function render_input_text_html( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}
			?>
			<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				<label for=""><?php echo esc_attr( $attribute_name ); ?></label>
				<?php
				if ( $conditionally_required ) {
					?>
					<span style="color: red; margin-left:5px; ">*</span>
					<?php
				}
				if ( ! is_null( $attribute_description ) && ! empty( $attribute_description ) ) {
					ced_onbuy_tool_tip( $attribute_description );
				}

				?>
			</td>

			<td>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="text" /> 
			</td>

			<!-- </p> -->
			<?php
		}

		public function render_input_text_html_hidden( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}
			?>
				<input type="hidden" name="<?php echo esc_attr( $marketPlace . '[]' ); ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
				<td>
				</label>
			</td>
			<td>
				<label></label>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName . '[' . $indexToUse . ']' ); ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="hidden" /> 
			</td>

			<?php
		}
	}
}
