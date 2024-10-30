<?php
if ( ! class_exists( 'Class_Ced_Onbuy_Products' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Onbuy_Integration_By_CedCommerce
	 * @subpackage Onbuy_Integration_By_CedCommerce/admin/clover-fr
	 */
	class Class_Ced_Onbuy_Products {

		/**
		 * The Instace of Class_Ced_Onbuy_Products.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of Class_Ced_Onbuy_Products class.
		 */
		private static $_instance;
		private $plugin_name;
		private $version;

		public $new_id;
		/**
		 * Class_Ced_Onbuy_Products Instance.
		 *
		 * Ensures only one instance of Class_Ced_Onbuy_Products is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return Class_Ced_Onbuy_Products instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {

			$this->load_dependency();

		}

		public function load_dependency() {
			$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
			if ( file_exists( $file_request ) ) {
				include_once $file_request;
			}
			$this->ced_onbuy_request = new Class_Ced_Onbuy_Request();
		}

		public function ced_onbuy_custom_log( $filedir, $filename, $ced_onbuy_log_data ) {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = $upload_dir . '/' . $filedir;
			if ( ! is_dir( $upload_dir ) ) {
				mkdir( $upload_dir, 0777 );
			}
			$fp = fopen( $upload_dir . '/' . $filename . gmdate( 'j.n.Y' ) . '.log', 'a' );
			fwrite( $fp, $ced_onbuy_log_data );
			fclose( $fp );
		}


		/**
		 * Uploading products to clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_doupload( $shop_id ) {
			$response              = $this->ced_upload_to_onbuy( $this->data, $shop_id );
			$this->upload_response = $response;
			return $response;

		}


		/**
		 * Syncing products to clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_do_product_sync( $product_code = '', $shop_id ) {
			$response = $this->ced_product_sync_from_onbuy( $product_code, $shop_id );
			return $response;
		}

		/**
		 * Updating products to clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_doupdate( $shop_id, $opc ) {
			$response = $this->ced_update_to_onbuy( $this->data, $shop_id, $opc );
			return $response;
		}

		/**
		 * Deleting products from clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_dodelete( $shop_id ) {
			$response = $this->ced_delete_from_onbuy( $this->data, $shop_id );
			return $response;
		}


		/**
		 * Update Inventory to clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_doUpdateInventory( $shop_id ) {
			$response = $this->update_stock_to_onbuy( $this->data, $shop_id );
			return $response;
		}


		/**
		 * Update stock to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function update_stock_to_onbuy( $parameters, $shop_id ) {
			$action       = 'listings/by-sku';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_put_method( $action, $queries, $access_token, $parameters );
			return $response;
		}

		/**
		 * Deleting product from clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_delete_from_onbuy( $parameters, $shop_id ) {
			$action       = 'listings/by-sku';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_delete_method( $action, $queries, $access_token, $parameters );
			return $response;
		}

		/**
		 * Uploading product to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_upload_to_onbuy( $parameters, $shop_id ) {
			$action       = 'products';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_post_method( $action, $queries, $access_token, $parameters );
			return $response;
		}


		/**
		 * Sync product to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_product_sync_from_onbuy( $parameters, $shop_id ) {
			$action       = 'products';
			$queries      = 'site_id=2000&filter[query]=' . $parameters . '&filter[field]=product_code';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
			return $response;
		}

		/**
		 * Update product to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_update_to_onbuy( $parameters, $shop_id, $opc ) {
			$action       = 'products';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_put_method( $action, $queries, $access_token, $parameters, $opc );
			return $response;
		}

		/**
		 * Function for preparing product data to be uploaded
		 *
		 * @since 1.0.0
		 * @param array $pro_ids Product Ids.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_onbuy_prepare_data_for_uploading( $pro_ids = array(), $shop_id, $is_sync ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_onbuy_queue';

			$ced_onbuy_log  = '';
			$ced_onbuy_log .= 'Date and Time: ' . date_i18n( 'Y-m-d H:i:s' ) . "\r\n";
			foreach ( $pro_ids as $key => $value ) {

				$prod_data = wc_get_product( $value );
				if ( ! is_object( $prod_data ) ) {
					continue;
				}
				$this->prod_id    = $value;
				$ced_onbuy_log   .= 'SKU : ' . $prod_data->get_sku() . "\r\n";
				$type             = $prod_data->get_type();
				$already_uploaded = get_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id, true );
				if ( empty( $already_uploaded ) ) {
					if ( 'variable' == $type ) {
						$prepared_data[] = $this->ced_get_variable_formatted_data( $value, $shop_id );
					} else {
						$prepared_data[] = $this->ced_get_formatted_data( $value, $shop_id );
					}
				} else {
					$response_data[ $value ] = 'products already uploaded';
					$ced_onbuy_log          .= 'Message : Products Already Uploaded ' . "\r\n";
					$ced_onbuy_log          .= 'OPC : ' . $already_uploaded . "\r\n";
				}
			}
			if ( isset( $prepared_data ) && ! empty( $prepared_data ) && is_array( $prepared_data ) ) {
				$dataToPost['site_id']  = '2000';
				$dataToPost['products'] = $prepared_data;
				$this->data             = json_encode( $dataToPost );
				$response               = self::ced_doupload( $shop_id );

				if ( isset( $response['results'] ) && ! empty( $response['results'] ) && is_array( $response['results'] ) ) {
					foreach ( $prepared_data as $key1 => $value1 ) {
						foreach ( $response['results'] as $key => $value ) {

							if ( isset( $value['success'] ) && $value['success'] ) {
								$queue_to_insert[ $value['uid'] ] = $value['queue_id'];

								update_post_meta( $value['uid'], '_ced_onbuy_queue_id_' . $shop_id, $value['queue_id'] );
								$response_data[ $value['uid'] ] = 'Product Id - ' . $value['uid'] . ' Uploaded Successfully Queued For Process , Please Check The Queue Status';
								$ced_onbuy_log                 .= 'Product ID : ' . $value['uid'] . "\r\n";
								$ced_onbuy_log                 .= 'Queue ID : ' . $value['queue_id'] . "\r\n";
								$ced_onbuy_log                 .= 'Message : Uploaded Successfully Queued For Process , Please Check The Queue Status' . "\r\n";

							} elseif ( ! $value['success'] ) {
								$response_data[ $value['uid'] ] = $value['error'];
								$ced_onbuy_log                 .= 'Error details : ' . $value['error'] . "\r\n";

							}

							if ( $key1 == $key ) {
								// ========= FOR CRON ACTIVITY ==================
								global $activity;
								$activity->action        = 'Upload';
								$activity->type          = 'product';
								$activity->input_payload = $value1;
								$activity->response      = $value;
								$activity->post_id       = $value1['uid'];
								$activity->shop_id       = $shop_id;
								$activity->is_auto       = $is_sync;
								$activity->post_title    = $value1['product_name'];
								$activity->execute();
								// ===================================
							}
						}
					}
				} else {
					$response_data[ $shop_id ] = $response['error']['message'];
					$ced_onbuy_log            .= 'Error details : ' . $response['error']['message'] . "\r\n";

				}
				if ( isset( $queue_to_insert ) && ! empty( $queue_to_insert ) ) {
					$wpdb->insert(
						$table_name,
						array(
							'queue_id'   => json_encode( $queue_to_insert ),
							'shop_id'    => $shop_id,
							'post_time'  => time(),
							'queue_type' => 'Create Product',
						)
					);
				}
			} else {
				$response_data[ $shop_id ] = 'There are some issues in data preparation , please consult with support';
			}
			$ced_onbuy_log .= '---------------------------------------------------------' . "\r\n";
			$this->ced_onbuy_custom_log( 'ced_onbuy_upload_product_log_dir', 'ced_onbuy_upload_product_log', $ced_onbuy_log );

			return $response_data;
		}

		/**
		 * Function for preparing product data to be updated
		 *
		 * @since 1.0.0
		 * @param array $pro_ids Product Ids.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_onbuy_prepare_data_for_updating( $pro_ids = array(), $shop_id, $is_sync ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_onbuy_queue';

			$ced_onbuy_log  = '';
			$ced_onbuy_log .= 'Date and Time: ' . date_i18n( 'Y-m-d H:i:s' ) . "\r\n";

			foreach ( $pro_ids as $key => $value ) {
				$prod_data = wc_get_product( $value );
				if ( ! is_object( $prod_data ) ) {
					continue;
				}
				$this->prod_id  = $value;
				$ced_onbuy_log .= 'SKU : ' . $prod_data->get_sku() . "\r\n";

				$type          = $prod_data->get_type();
				$onbuy_item_id = get_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id, true );

				if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
					$ced_onbuy_log .= 'OPC : ' . $onbuy_item_id . "\r\n";

					if ( 'variable' == $type ) {
						$variableData   = array();
						$variableData[] = $this->ced_get_variable_formatted_data( $value, $shop_id, $onbuy_item_id );
						$prepared_data  = $variableData;

					} else {
						$prepared_data[] = $this->ced_get_formatted_data( $value, $shop_id, $onbuy_item_id );
					}
				} else {
					$response_data[ $value ] = 'Products Not Found On OnBuy';
					$ced_onbuy_log          .= 'Message : Products Not Found On OnBuy' . "\r\n";

				}
			}

			if ( isset( $prepared_data[0]['variants'] ) ) {
				$childarray = $prepared_data[0]['variants'];

				unset( $prepared_data[0]['variants'] );
				$prepared_data = array_merge( $prepared_data, $childarray );
			}

			if ( isset( $prepared_data ) && ! empty( $prepared_data ) && is_array( $prepared_data ) ) {
				$opc = $prepared_data[0]['opc'];

				global $wpdb;

				$store_products = get_posts(
					array(
						'numberposts' => -1,
						'post_type'   => 'product',
						'meta_query'  => array(
							array(
								'key'     => '_ced_onbuy_listing_id_' . $shop_id,
								'value'   => $opc,
								'compare' => 'EXISTS',
							),
						),
					)
				);

				$store_products   = $store_products[0]->ID;
				$product_to_check = wc_get_product( $store_products );
				if ( is_object( $product_to_check ) ) {
					$type = $product_to_check->get_type();
				}
				if ( 'variable' == $type ) {
					$opc = '';
				}

				$dataToPost['site_id']  = '2000';
				$dataToPost['products'] = $prepared_data;

				$this->data = json_encode( $dataToPost );
				$response   = self::ced_doupdate( $shop_id, $opc );

				if ( isset( $response['products'] ) && ! empty( $response['products'] ) && is_array( $response['products'] ) ) {
					foreach ( $response['products'] as $key => $value ) {
						if ( isset( $value['success'] ) && $value['success'] ) {
							$queue_to_insert[] = $value['queue_id'];
							$response_data[]   = 'Product Id - Updated Successfully Queued For Process , Please Check The Queue Status';
							$ced_onbuy_log    .= 'Queue ID : ' . $value['queue_id'] . "\r\n";
							$ced_onbuy_log    .= 'Message : Updated Successfully' . "\r\n";

						} elseif ( empty( $value['success'] ) ) {
							$id                   = isset( $value['uid'] ) ? $value['uid'] : $shop_id;
							$response_data[ $id ] = $value['error']['message'];
							$ced_onbuy_log       .= 'Error details : ' . $value['error']['message'] . "\r\n";
							$ced_onbuy_log       .= 'Message : Not Updated' . "\r\n";

						}
					}
				} else {
					if ( isset( $response['success'] ) && ! empty( $response['success'] ) && 1 == $response['success'] ) {
						$queue_to_insert[] = $response['queue_id'];
						$response_data[]   = 'Product Id - Updated Successfully Queued For Process , Please Check The Queue Status';
					} elseif ( empty( $response['success'] ) ) {
						$id                   = isset( $response['uid'] ) ? $response['uid'] : $shop_id;
						$response_data[ $id ] = $response['error']['message'];
					}
				}
				if ( isset( $queue_to_insert ) && ! empty( $queue_to_insert ) ) {
					$wpdb->insert(
						$table_name,
						array(
							'queue_id'   => json_encode( $queue_to_insert ),
							'shop_id'    => $shop_id,
							'post_time'  => time(),
							'queue_type' => 'Update Product',
						)
					);
				}
			}
			$ced_onbuy_log .= '---------------------------------------------------------' . "\r\n";
			$this->ced_onbuy_custom_log( 'ced_onbuy_update_product_log_dir', 'ced_onbuy_update_product_log', $ced_onbuy_log );
			// ========= FOR CRON ACTIVITY ==================
			global $activity;
			$activity->action        = 'Update';
			$activity->type          = 'product';
			$activity->input_payload = $prepared_data;
			$activity->response      = $response;
			$activity->post_id       = $this->prod_id;
			$activity->shop_id       = $shop_id;
			$activity->is_auto       = $is_sync;
			$activity->post_title    = $prod_data->get_title();
			$activity->execute();
			// ===================================
			return $response_data;
		}

		/**
		 * Function for preparing product data to be deleted
		 *
		 * @since 1.0.0
		 * @param array $pro_ids Product Ids.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_onbuy_prepare_data_for_delete( $pro_ids = array(), $shop_id, $is_sync ) {

			$ced_onbuy_log  = '';
			$ced_onbuy_log .= 'Date and Time: ' . date_i18n( 'Y-m-d H:i:s' ) . "\r\n";

			foreach ( $pro_ids as $key => $value ) {
				$prod_data = wc_get_product( $value );
				if ( ! is_object( $prod_data ) ) {
					continue;
				}
				$this->prod_id  = $value;
				$ced_onbuy_log .= 'SKU : ' . $prod_data->get_sku() . "\r\n";

				$type          = $prod_data->get_type();
				$onbuy_item_id = get_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id, true );
				if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
					$ced_onbuy_log .= 'OPC : ' . $onbuy_item_id . "\r\n";

					if ( 'variable' == $type ) {
						$variations = $_product->get_available_variations();
						foreach ( $variations as $key => $variation ) {
							$skudata[] = get_post_meta( $variation['variation_id'], '_sku', true );
						}
					} else {
						$skudata[] = get_post_meta( $value, '_sku', true );
					}
				} else {
					$response_data[ $value ] = 'Products Not Found On OnBuy';
					$ced_onbuy_log          .= 'Products Not Found On OnBuy' . "\r\n";
				}
			}
			if ( isset( $skudata ) && ! empty( $skudata ) && is_array( $skudata ) ) {
				$dataToPost['site_id'] = '2000';
				$dataToPost['skus']    = $skudata;
				$ced_onbuy_log        .= "Processing sku's : " . $skudata . "\r\n";
				$this->data            = json_encode( $dataToPost );
				$response              = self::ced_dodelete( $shop_id );
				if ( isset( $response['success'] ) && ! empty( $response['success'] ) ) {
					foreach ( $response['results'] as $sku => $value ) {
						$ced_onbuy_log .= 'Return SKU : ' . $sku . "\r\n";
						if ( 'ok' == $value['status'] ) {
							$proId = wc_get_product_id_by_sku( $sku );

							$parent_product_id = wp_get_post_parent_id( $proId );

							if ( isset( $parent_product_id ) && ! empty( $parent_product_id ) ) {
								delete_post_meta( $parent_product_id, '_ced_onbuy_listing_id_' . $shop_id );
							}

							delete_post_meta( $proId, '_ced_onbuy_listing_id_' . $shop_id );

							$response_data[ $proId ] = 'Product Id ' . $proId . ' Deleted successfully';
							$ced_onbuy_log          .= 'Product Id : ' . $proId . 'Deleted successfully' . "\r\n";

						} else {
							$response_data[ $proId ] = $value['error'];
						}
					}
				} else {
					$response_data[ $shop_id ] = 'Error details - ' . $response['error']['message'];
					$ced_onbuy_log            .= 'Error details - ' . $response['error']['message'] . "\r\n";
				}
			}
			$ced_onbuy_log .= '---------------------------------------------------------' . "\r\n";
			$this->ced_onbuy_custom_log( 'ced_onbuy_remove_listing_log_dir', 'ced_onbuy_remove_listing_log', $ced_onbuy_log );
			// ========= FOR CRON ACTIVITY ==================
			global $activity;
			$activity->action        = 'Delete';
			$activity->type          = 'product';
			$activity->input_payload = $skudata;
			$activity->response      = $response;
			$activity->post_id       = $this->prod_id;
			$activity->shop_id       = $shop_id;
			$activity->is_auto       = $is_sync;
			$activity->post_title    = $prod_data->get_title();
			$activity->execute();
			// ===================================
			return $response_data;
		}

		public function ced_get_formatted_data_create_listing( $pro_ids = '', $shop_id = '', $onbuy_item_id = '' ) {
			$args         = array();
			$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );

			$global = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );

			$product = wc_get_product( $pro_ids );
			if ( ! is_object( $product ) ) {
				return;
			}
			$price = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_price' );
			if ( $product->get_type() == 'variation' ) {
				$parent_id           = $product->get_parent_id();
				$parentproduct       = wc_get_product( $parent_id );
				$parent_product_data = $parentproduct->get_data();
			}
			if ( WC()->version > '3.0.0' ) {
				if ( is_object( $product ) ) {
					$product_data = $product->get_data();
					$product_type = $product->get_type();
				}

				$quantity = (int) get_post_meta( $pro_ids, '_stock', true );
				$sku      = $product_data['sku'];

				$stock = (int) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_stock' );
				if ( empty( $stock ) && '' == $stock ) {
					$stock = (int) $quantity;
				}
				if ( empty( $price ) ) {
					$price = (float) $product_data['price'];
					if ( 'variable' == $product_type ) {
						$variations = $product->get_available_variations();
						if ( isset( $variations['0']['display_regular_price'] ) ) {
							$price = (float) $variations['0']['display_regular_price'];
						}
					}
				}
			}
			$sku = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_sku' );

			// ================================================================
			$ced_update_decreased_price   = get_option( 'ced_update_decreased_price' );
			$ced_onbuy_max_limit_meta_key = get_option( 'ced_onbuy_max_limit' );
			if ( ! empty( $ced_onbuy_max_limit_meta_key ) ) {
				$ced_onbuy_max_limit_value = get_post_meta( $pro_ids, $ced_onbuy_max_limit_meta_key, true );
			}

			$winning_status       = get_post_meta( $pro_ids, 'ced_onbuy_winning_price_status_' . $shop_id, true );
			$ced_onbuy_lead_price = get_post_meta( $pro_ids, 'ced_onbuy_lead_price_' . $shop_id, true );

			$ced_buybox_price_type = get_option( 'ced_onbuy_buybox_price_type' );
			if ( empty( $winning_status ) && 'yes' == $ced_update_decreased_price && ! empty( $ced_onbuy_lead_price ) ) {
				if ( ! empty( $ced_buybox_price_type ) ) {

					$ced_buybox_price = get_option( 'ced_onbuy_buybox_price' );

					if ( ! empty( $ced_buybox_price ) ) {
						if ( ! empty( $ced_onbuy_max_limit_value ) ) {
							if ( 'Fixed_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) $ced_onbuy_lead_price - $ced_buybox_price;
								if ( ( $reduced_price > $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;
								}
							} elseif ( 'Percentage_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) ( $ced_onbuy_lead_price - ( ( $ced_buybox_price / 100 ) * $ced_onbuy_lead_price ) );
								if ( ( $reduced_price < $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;
								}
							}
						}
					}
				}
			} else {
				$conversion_rate = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_conversion_rate' );
				if ( ! empty( $conversion_rate ) ) {
					$price = (float) $price * $conversion_rate;
					if ( ! empty( $rrp ) ) {
						$rrp = $rrp * $conversion_rate;
					}
				}
				$markup_type = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup_type' );
				if ( ! empty( $markup_type ) ) {
					$markup_value = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup' );
					if ( ! empty( $markup_value ) ) {
						if ( 'Fixed_Increased' == $markup_type ) {
							$price = (float) $price + $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp + $markup_value;
							}
						} elseif ( 'Fixed_Decreased' == $markup_type ) {
							$price = (float) $price - $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp - $markup_value;
							}
						} elseif ( 'Percentage_Increased' == $markup_type ) {
							$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
							}
						} elseif ( 'Percentage_Decreased' == $markup_type ) {
							$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
							}
						}
					}
				}
			}
			$condition               = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_condition' );
			$handling_time           = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_handling_time' );
			$args['delivery_weight'] = get_post_meta( $pro_ids, '_weight', true );
			$args['condition']       = $condition;
			$args['opc']             = $onbuy_item_id;
			if ( $stock < 0 ) {
				$stock         = 0;
				$args['stock'] = $stock;
			} else {
				$args['stock'] = $stock;
			}
			$args['price'] = $price;
			// =====================================
			$sale_price = get_post_meta( $pro_ids, 'ced_onbuy_sale_price', true );
			if ( ! empty( $sale_price ) ) {
				$sale_start_date = get_post_meta( $pro_ids, 'ced_sale_price_dates_from', true );
				$sale_end_date   = get_post_meta( $pro_ids, 'ced_sale_price_dates_to', true );

				$args['listings'][ $condition ]['sale_price'] = $sale_price;
				if ( empty( $sale_start_date ) ) {
					$sale_start_date = '';
				}

				if ( empty( $sale_end_date ) ) {
					$sale_end_date = '';
				}
				$args['listings'][ $condition ]['sale_start_date'] = $sale_start_date;
				$args['listings'][ $condition ]['sale_end_date']   = $sale_end_date;
			}

			$boost_percent = get_post_meta( $pro_ids, 'ced_onbuy_boost_percent', true );
			if ( empty( $boost_percent ) && '' == $boost_percent ) {
				$boost_percent = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_boost_percent' );
			}
			$args['boost_marketing_commission'] = $boost_percent;
			// ====================================
			$args['handling_time'] = $handling_time;
			$args['sku']           = $sku;
			return $args;

		}
		// =====================================================================

		/**
		 * Check winning price of a product to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_check_winning_price_to_onbuy( $parameters, $shop_id ) {
			$action       = 'listings/check-winning';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );

			$response = $this->ced_onbuy_request->ced_onbuy_get_method_check_win_price( $action, $queries, $access_token, $parameters );

			if ( isset( $response['results'] ) && ! empty( $response['results'] ) ) {
				foreach ( $response['results'] as $key => $value ) {

					$prod_id = wc_get_product_id_by_sku( $value['sku'] );
					if ( ! empty( $prod_id ) ) {
						update_post_meta( $prod_id, 'ced_onbuy_winning_price_data_' . $shop_id, $value );
						update_post_meta( $prod_id, 'ced_onbuy_lead_price_' . $shop_id, $value['lead_price'] );
						update_post_meta( $prod_id, 'ced_onbuy_winning_price_status_' . $shop_id, $value['winning'] );
					}
				}
			}
			return $response;
		}
		// ===============================================================================

		/**
		 * Create Listing of products to clover
		 *
		 * @since 1.0.0
		 * @param int $product_id Product ID.
		 * @param int $shop_id Clover Shop Id.
		 */
		public function ced_doCreateListing( $shop_id ) {
			$response = $this->ced_create_listing_to_onbuy( $this->data, $shop_id );
			return $response;
		}

		/**
		 * Uploading product to clover
		 *
		 * @since 1.0.0
		 * @param array $parameters Parameters required on Clover.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_create_listing_to_onbuy( $parameters, $shop_id ) {
			$action       = 'listings';
			$queries      = '';
			$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
			$response     = $this->ced_onbuy_request->ced_onbuy_post_method( $action, $queries, $access_token, $parameters );
			return $response;
		}

		public function ced_onbuy_prepare_data_for_create_listing( $pro_ids = array(), $shop_id, $is_sync ) {
			if ( empty( $pro_ids ) ) {
				return;
			}
			if ( isset( $pro_ids ) && ! empty( $pro_ids ) && is_array( $pro_ids ) ) {
				$response_data = array();
				foreach ( $pro_ids as $key => $pro_id ) {
					$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );

					$product = wc_get_product( $pro_id );
					if ( ! is_object( $product ) ) {
						continue;
					}
					$this->prod_id = $pro_id;
					$type          = $product->get_type();

					if ( 'variable' == $type ) {

						$variations = $product->get_available_variations();
						if ( isset( $variations ) && ! empty( $variations ) && is_array( $variations ) ) {
							foreach ( $variations as $variation ) {
								$variation_id = $variation['variation_id'];

								$product_code = get_post_meta( $variation_id, 'ced_onbuy_ean', true );
								if ( empty( $product_code ) && '' == $product_code ) {
									$product_code = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_ean' );
								}
								if ( ! empty( $product_code ) ) {
									$product_code_len = strlen( $product_code );
									$count            = 13;
									$ean_diff         = $count - $product_code_len;

									for ( $i = 0; $i < $ean_diff; $i++ ) {
										$product_code = '0' . $product_code;
									}
								}
								if ( isset( $product_code ) && ! empty( $product_code ) ) {
									$response = $this->ced_do_product_sync( $product_code, $shop_id );
									if ( isset( $response['results'][0]['opc'] ) && ! empty( $response['results'][0]['opc'] ) ) {
										$proIdsForCreate[] = $variation_id;

										$variation = wc_get_product( $variation_id );
										$parent_id = $variation->get_parent_id();

										update_post_meta( $parent_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results'][0]['opc'] );
										update_post_meta( $variation_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results'][0]['opc'] );

										update_post_meta( $variation_id, '_ced_onbuy_listing_id_data_' . $shop_id, $response['results'] );
									} else {
										$response_data[ $variation_id ] = 'No listing found on OnBuy.';
									}
								} else {
									$response_data[ $pro_id ] = 'Product Code required For Creating Listing.';
								}
							}
						}
					} else {
						$product_code = get_post_meta( $pro_id, 'ced_onbuy_ean', true );
						if ( empty( $product_code ) && '' == $product_code ) {
							$product_code = $this->ced_fetch_meta_value_of_product( $pro_id, '_ced_onbuy_ean' );
						}
						if ( ! empty( $product_code ) ) {
							$product_code_len = strlen( $product_code );
							$count            = 13;
							$ean_diff         = $count - $product_code_len;

							for ( $i = 0; $i < $ean_diff; $i++ ) {
								$product_code = '0' . $product_code;
							}
						}
						if ( isset( $product_code ) && ! empty( $product_code ) ) {
							$response = $this->ced_do_product_sync( $product_code, $shop_id );
							if ( isset( $response['results'][0]['opc'] ) && ! empty( $response['results'][0]['opc'] ) ) {
								$proIdsForCreate[] = $pro_id;
								update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results'][0]['opc'] );
								update_post_meta( $pro_id, '_ced_onbuy_listing_id_data_' . $shop_id, $response['results'] );
							} else {
								$response_data[ $pro_id ] = 'No Product Found For Creating Listing.';
							}
						} else {
							$response_data[ $pro_id ] = 'Product Code required For Creating Listing.';
						}
					}
				}
				if ( isset( $proIdsForCreate ) && ! empty( $proIdsForCreate ) && is_array( $proIdsForCreate ) ) {
					foreach ( $proIdsForCreate as $key => $value ) {
						$onbuy_item_id = get_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id, true );
						if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
							$prepared_data[] = $this->ced_get_formatted_data_create_listing( $value, $shop_id, $onbuy_item_id );

						}
					}
				}

				if ( isset( $prepared_data ) && ! empty( $prepared_data ) && is_array( $prepared_data ) ) {

					$dataToPost['site_id']  = '2000';
					$dataToPost['listings'] = $prepared_data;
					$this->data             = json_encode( $dataToPost );
					$response               = self::ced_doCreateListing( $shop_id );
					if ( isset( $response['success'] ) ) {
						foreach ( $response['results'] as $key => $value ) {
							$response_data[ $value['sku'] ] = 'Product Sku - ' . $value['sku'] . ' Created successfully';
							if ( isset( $value['error'] ) && ! empty( $value['error'] ) ) {
								$response_data[ $value['sku'] ] = 'Error details - ' . $value['error'];
							}
						}
					} else {
						$response_data[ $shop_id ] = 'Error details - ' . $response['error']['message'];
					}
				}
				// ========= FOR CRON ACTIVITY ==================
				global $activity;
				$activity->action        = 'Create Listing';
				$activity->type          = 'product';
				$activity->input_payload = $prepared_data;
				$activity->response      = $response;
				$activity->post_id       = $this->prod_id;
				$activity->shop_id       = $shop_id;
				$activity->is_auto       = $is_sync;
				$activity->post_title    = $product->get_title();
				$activity->execute();
				// ===================================
				return $response_data;
			}
		}

		/**
		 * Function for preparing product data to update stock
		 *
		 * @since 1.0.0
		 * @param array $pro_ids Product Ids.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_onbuy_prepare_data_for_update_stock( $pro_ids = array(), $shop_id, $is_sync ) {
			$ced_onbuy_inventory_log  = '';
			$ced_onbuy_inventory_log .= 'Date and Time: ' . date_i18n( 'Y-m-d H:i:s' ) . "\r\n";
			foreach ( $pro_ids as $key => $value ) {
				$prod_data = wc_get_product( $value );
				if ( ! is_object( $prod_data ) ) {
					continue;
				}
				$this->prod_id = $value;
				$type          = $prod_data->get_type();
				$onbuy_item_id = get_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id, true );
				if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
					if ( 'variable' == $type ) {
						$variableData = array();
						$variableData = $this->ced_get_variable_formatted_data_inventory( $value, $shop_id, $onbuy_item_id );
						if ( isset( $variableData ) && ! empty( $variableData ) && is_array( $variableData ) ) {
							foreach ( $variableData as $key => $value ) {
								$prepared_data[] = $value;
							}
						}
					} else {
						$prepared_data[] = $this->ced_get_formatted_data_inventory( $value, $shop_id, $onbuy_item_id );
					}
				} else {
					$response_data[ $value ] = 'Products Not Found On OnBuy';
				}
			}
			if ( isset( $prepared_data ) && ! empty( $prepared_data ) && is_array( $prepared_data ) ) {
				$dataToPost['site_id']  = '2000';
				$dataToPost['listings'] = $prepared_data;
				$this->data             = json_encode( $dataToPost );
				$response               = self::ced_doUpdateInventory( $shop_id );
				if ( isset( $response['success'] ) ) {
					$message = 'Updated Successfully';
					foreach ( $prepared_data as $key1 => $value1 ) {
						foreach ( $response['results'] as $key => $value ) {
							$response_data[ $value['sku'] ] = 'Product Sku - ' . $value['sku'] . ' Updated successfully';
							$ced_onbuy_inventory_log       .= 'SKU : ' . $value['sku'] . "\r\n";
							$ced_onbuy_inventory_log       .= 'Price : ' . $value['price'] . "\r\n";
							$ced_onbuy_inventory_log       .= 'Stock : ' . $value['stock'] . "\r\n";
							if ( isset( $value['error'] ) && ! empty( $value['error'] ) ) {
								$response_data[ $value['sku'] ] = 'Error details - ' . $value['error'];
								$ced_onbuy_inventory_log       .= 'Error details : ' . $value['error'] . "\r\n";
								$message                        = 'Not Updated';
							}
							if ( $key1 == $key ) {
								$prod_id   = wc_get_product_id_by_sku( $value1['sku'] );
								$prod_data = wc_get_product( $prod_id );
								// ========= FOR CRON ACTIVITY ==================
								global $activity;
								$activity->action        = 'Update';
								$activity->type          = 'product_inventory';
								$activity->input_payload = $value1;
								$activity->response      = $value;
								$activity->post_id       = $prod_id;
								$activity->shop_id       = $shop_id;
								$activity->is_auto       = $is_sync;
								$activity->post_title    = $prod_data->get_title();
								$activity->execute();
								// ===================================
							}
						}
					}
				} else {
					$response_data[ $shop_id ] = 'Error details - ' . $response['error']['message'];
					$ced_onbuy_inventory_log  .= 'Error details : ' . $response['error']['message'] . "\r\n";

				}
				$ced_onbuy_inventory_log .= 'Message : ' . $message . "\r\n";
				$ced_onbuy_inventory_log .= '---------------------------------------------------------' . "\r\n";
			}
			$this->ced_onbuy_custom_log( 'ced_onbuy_inventory_log_dir', 'ced_onbuy_inventory_log', $ced_onbuy_inventory_log );

			return $response_data;
		}

		/**
		 * Function for preparing product data to synced product
		 *
		 * @since 1.0.0
		 * @param array $pro_ids Product Ids.
		 * @param int   $shop_id Clover Shop Id.
		 */
		public function ced_onbuy_prepare_data_for_product_sync( $pro_ids = array(), $shop_id ) {
			if ( empty( $pro_ids ) ) {
				return;
			}
			$ced_onbuy_log  = '';
			$ced_onbuy_log .= 'Date and Time: ' . date_i18n( 'Y-m-d H:i:s' ) . "\r\n";

			$attribute_key = get_option( 'ced_onbuy_product_sync_scheduler_key_' . $shop_id, '' );
			if ( ! empty( $attribute_key ) ) {
				foreach ( $pro_ids as $key => $pro_id ) {
					$ced_onbuy_log .= 'Product ID : ' . $pro_id . "\r\n";
					$product        = wc_get_product( $pro_id );
					if ( is_object( $product ) ) {
						$sku            = $product->get_sku();
						$ced_onbuy_log .= 'SKU : ' . $sku . "\r\n";
					}
					$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );
					$product_code = $this->ced_fetch_meta_value_of_product( $pro_id, $attribute_key );
					if ( isset( $product_code ) && ! empty( $product_code ) ) {
						$response = $this->ced_do_product_sync( $product_code, $shop_id );
						if ( isset( $response['results']['opc'] ) && ! empty( $response['results']['opc'] ) ) {
							update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $response['results']['opc'] );
							update_post_meta( $pro_id, '_ced_onbuy_listing_id_data_' . $shop_id, $response['results'] );
							$ced_onbuy_log .= 'OPC : ' . $response['results']['opc'] . "\r\n";
							$ced_onbuy_log .= 'Name : ' . $response['results']['name'] . "\r\n";
							$ced_onbuy_log .= 'Product Codes : ' . $response['results']['product_codes'] . "\r\n";
							$message        = 'Found!';
						} else {
							$message = 'Not Found!';
						}
					}
				}
				$ced_onbuy_log .= 'Message : ' . $message . "\r\n";
				$ced_onbuy_log .= '---------------------------------------------------------' . "\r\n";
			}
			$this->ced_onbuy_custom_log( 'ced_onbuy_exist_prod_sync_log_dir', 'ced_onbuy_exist_prod_sync_log', $ced_onbuy_log );
		}

		/**
		 * Function for preparing  product data
		 *
		 * @since 1.0.0
		 * @param int $pro_ids Product Id.
		 * @param int $shop_id Clover Shop Id.
		 * @param int $clover_item_id Clover Product Listing Id.
		 */
		public function ced_get_formatted_data( $pro_ids = '', $shop_id = '', $onbuy_item_id = '' ) {
			$args = array();
			// ==============================================================
			$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );
			// ==============================================================
			$is_profile_assigned = $this->is_profile_assigned( $pro_ids );

			$global  = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );// product data setting
			$product = wc_get_product( $pro_ids );
			if ( ! is_object( $product ) ) {
				return;
			}
			$price = (float) get_post_meta( $pro_ids, 'ced_onbuy_price', true );
			if ( empty( $price ) && '' == $price ) {
				$price = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_price' );

			}

			$deliveries = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_deliveries' );

			if ( $deliveries ) {
				global $wpdb;
				$result  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts' ), 'ARRAY_A' );
				$shop_id = get_option( 'ced_onbuy_shop_id' );
				foreach ( $result as $key => $value ) {
					if ( $value['shop_id'] == $shop_id ) {
						$deliveries_option = json_decode( $value['seller_deliveries'] );
					}
				}
				foreach ( $deliveries_option as $k => $v ) {
					if ( $v->template_name == $deliveries ) {

						$delivery = $v->seller_delivery_template_id;
					}
				}
			}
			if ( $product->get_type() == 'variation' ) {
				$parent_id           = $product->get_parent_id();
				$parentproduct       = wc_get_product( $parent_id );
				$parent_product_data = $parentproduct->get_data();
			}
			if ( WC()->version > '3.0.0' ) {
				if ( is_object( $product ) ) {
					$product_data = $product->get_data();
					$product_type = $product->get_type();
				}

				$quantity = (int) get_post_meta( $pro_ids, '_stock', true );
				$sku      = $product_data['sku'];

				$description = get_post_meta( $pro_ids, 'ced_onbuy_description', true );
				if ( empty( $description ) && '' == $description ) {
					$description = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_description' );

					if ( empty( $description ) && '' == $description ) {
						$description = $product_data['description'] . ' ' . $product_data['short_description'];
					}
				}

				$title = get_post_meta( $pro_ids, 'ced_onbuy_title', true );
				if ( empty( $title ) && '' == $title ) {
					$title = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_title' );
					if ( empty( $title ) && '' == $title ) {
						$title = $product_data['name'];
					}
				}

				$stock = (int) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_stock' );
				if ( empty( $stock ) && '' == $stock ) {
					$stock = (int) $quantity;
				}

				if ( empty( $price ) ) {
					$price = (float) $product_data['price'];
					if ( 'variable' == $product_type ) {
						$variations = $product->get_available_variations();
						if ( isset( $variations['0']['display_regular_price'] ) ) {
							$price = (float) $variations['0']['display_regular_price'];
						}
					}
				}
			}
			$sku = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_sku' );
			if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
				$args['opc'] = $onbuy_item_id;
			}
			$rrp = get_post_meta( $pro_ids, 'ced_onbuy_rrp', true );
			if ( empty( $rrp ) && '' == $rrp ) {
				$rrp = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_rrp' );

			}

			// ================================================================
			$ced_update_decreased_price   = get_option( 'ced_update_decreased_price' );
			$ced_onbuy_max_limit_meta_key = get_option( 'ced_onbuy_max_limit' );
			if ( ! empty( $ced_onbuy_max_limit_meta_key ) ) {
				$ced_onbuy_max_limit_value = get_post_meta( $pro_ids, $ced_onbuy_max_limit_meta_key, true );
			}

			$winning_status       = get_post_meta( $pro_ids, 'ced_onbuy_winning_price_status_' . $shop_id, true );
			$ced_onbuy_lead_price = get_post_meta( $pro_ids, 'ced_onbuy_lead_price_' . $shop_id, true );

			$ced_buybox_price_type = get_option( 'ced_onbuy_buybox_price_type' );
			if ( empty( $winning_status ) && 'yes' == $ced_update_decreased_price && ! empty( $ced_onbuy_lead_price ) ) {
				if ( ! empty( $ced_buybox_price_type ) ) {

					$ced_buybox_price = get_option( 'ced_onbuy_buybox_price' );

					if ( ! empty( $ced_buybox_price ) ) {
						if ( ! empty( $ced_onbuy_max_limit_value ) ) {
							if ( 'Fixed_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) $ced_onbuy_lead_price - $ced_buybox_price;
								if ( ( $reduced_price > $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;

								}
							} elseif ( 'Percentage_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) ( $ced_onbuy_lead_price - ( ( $ced_buybox_price / 100 ) * $ced_onbuy_lead_price ) );
								if ( ( $reduced_price < $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;
								}
							}
						}
					}
				}
			} else {
				$conversion_rate = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_conversion_rate' );

				if ( ! empty( $conversion_rate ) ) {
					$price = (float) $price * $conversion_rate;
					if ( ! empty( $rrp ) ) {
						$rrp = $rrp * $conversion_rate;
					}
				}
				$markup_type = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup_type' );

				$global = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );
				if ( ! empty( $markup_type ) ) {
					$markup_value = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup' );
					if ( ! empty( $markup_value ) ) {
						if ( 'Fixed_Increased' == $markup_type ) {
							$price = (float) $price + $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp + $markup_value;
							}
						} elseif ( 'Fixed_Decreased' == $markup_type ) {
							$price = (float) $price - $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp - $markup_value;
							}
						} elseif ( 'Percentage_Increased' == $markup_type ) {
							$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
							}
						} elseif ( 'Percentage_Decreased' == $markup_type ) {
							$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
							}
						}
					}
				}
			}
			$condition = get_post_meta( $pro_ids, 'ced_onbuy_condition', true );
			if ( empty( $condition ) && '' == $condition ) {
				$condition = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_condition' );

			}
			$handling_time = get_post_meta( $pro_ids, 'ced_onbuy_handling_time', true );
			if ( empty( $handling_time ) && '' == $handling_time ) {
				$handling_time = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_handling_time' );

			}
			$product_code = get_post_meta( $pro_ids, 'ced_onbuy_ean', true );
			if ( empty( $product_code ) && '' == $product_code ) {
				$product_code = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_ean' );

			}
			if ( ! empty( $product_code ) ) {
				$product_code_len = strlen( $product_code );
				$count            = 13;
				$ean_diff         = $count - $product_code_len;

				for ( $i = 0; $i < $ean_diff; $i++ ) {
					$product_code = '0' . $product_code;
				}
			}
			$published      = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_published' );
			$summary_points = array();
			for ( $i = 0; $i < 6; $i++ ) {
				$summary = '';
				$summary = substr( get_post_meta( $pro_ids, 'ced_onbuy_summary_points' . $i, true ), 0, 500 );
				if ( ! empty( $summary ) ) {
					$summary_points[] = $summary;
				}
			}

			if ( empty( $summary_points[0] ) ) {
				$summary          = substr( $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_summary_points' ), 0, 500 );
				$summary_points[] = $summary;

			}

			$mpn = get_post_meta( $pro_ids, 'ced_onbuy_mpn', true );
			if ( empty( $mpn ) && '' == $mpn ) {
				$mpn = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_mpn' );

			}
			$brand = get_post_meta( $pro_ids, 'ced_onbuy_brand', true );
			if ( empty( $brand ) && '' == $brand ) {
				$brand = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_brand' );

			}
			$condition = ! empty( $condition ) ? $condition : 'new';
			// ====================================================
			$sale_price = get_post_meta( $pro_ids, 'ced_onbuy_sale_price', true );
			if ( ! empty( $sale_price ) ) {
				$sale_start_date                              = get_post_meta( $pro_ids, 'ced_sale_price_dates_from', true );
				$sale_end_date                                = get_post_meta( $pro_ids, 'ced_sale_price_dates_to', true );
				$args['listings'][ $condition ]['sale_price'] = $sale_price;
				if ( empty( $sale_start_date ) ) {
					$sale_start_date = '';
				}

				if ( empty( $sale_end_date ) ) {
					$sale_end_date = '';
				}
				$args['listings'][ $condition ]['sale_start_date'] = $sale_start_date;
				$args['listings'][ $condition ]['sale_end_date']   = $sale_end_date;

			}

			$boost_percent = get_post_meta( $pro_ids, 'ced_onbuy_boost_percent', true );
			if ( empty( $boost_percent ) && '' == $boost_percent ) {
				$boost_percent = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_boost_percent' );
			}
			$args['boost_marketing_commission'] = $boost_percent;
			// =========================================================================

			$image_url            = wp_get_attachment_image_src( get_post_thumbnail_id( $pro_ids ), 'full' );
			$attachment_ids       = $product->get_gallery_image_ids();
			$alternate_image_urls = array();
			if ( count( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$alternate_image_urls[] = wp_get_attachment_url( $attachment_id );
				}
			}
			$videos     = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_video' );
			$deliveries = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_video' );
			if ( isset( $videos ) && '' != $videos ) {
				$args['videos'][0]['label'] = 'youtube';
				$args['videos'][0]['url']   = $videos;
			}

			// ============================== Profiling Features Code =============================================
			if ( isset( $product_data['category_ids'] ) && ! empty( $product_data['category_ids'] ) ) {
				foreach ( $product_data['category_ids'] as $key => $value ) {
					$get_onbuy_category_id_data = get_term_meta( $value );
					$get_onbuy_category_id      = isset( $get_onbuy_category_id_data['ced_onbuy_mapped_category'] ) ? $get_onbuy_category_id_data['ced_onbuy_mapped_category'] : '';
					if ( isset( $get_onbuy_category_id ) && ! empty( $get_onbuy_category_id ) ) {
						foreach ( $get_onbuy_category_id as $index => $onbuy_cat ) {
							$category_id = $onbuy_cat;
							if ( ! empty( $category_id ) ) {
								break;
							}
						}
					}
				}
			}
			$features = array();
			$file     = CED_ONBUY_DIRPATH . 'admin/onbuy/class-onbuy.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
			$ced_onbuy_instance           = new Class_Ced_Onbuy_Manager();
			$onbuy_fetched_categories_fea = $ced_onbuy_instance->ced_onbuy_get_category_features( $category_id, $shop_id );
			if ( isset( $onbuy_fetched_categories_fea['results'] ) && ! empty( $onbuy_fetched_categories_fea['results'] ) ) {
				foreach ( $onbuy_fetched_categories_fea['results'] as $key => $value ) {
					foreach ( $value['options'] as $option_key => $option_value ) {
						$attr_value = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $value['feature_id'] );
						if ( ! empty( $attr_value ) && ( $option_value['option_id'] == $attr_value ) ) {
							$features[] = array(
								'option_id' => $option_value['option_id'],
								'name'      => $option_value['name'],
							);
						}
					}
				}
			}
			// ==================================== Profiling Technical Details ============================================
			$onbuy_fetched_categories_tech_details = $ced_onbuy_instance->ced_onbuy_get_category_tech_details( $category_id, $shop_id );
			if ( isset( $onbuy_fetched_categories_tech_details['results'] ) && ! empty( $onbuy_fetched_categories_tech_details['results'] ) ) {
				foreach ( $onbuy_fetched_categories_tech_details['results'][0]['options'] as $key => $details ) {

					$id   = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $details['detail_id'] );
					$unit = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $details['detail_id'] . '+' );
					if ( ( 'Material' == $details['name'] ) || ( 'Colour' == $details['name'] ) || ( 'Weight' == $details['name'] ) || ( 'Length' == $details['name'] ) || ( 'Width' == $details['name'] ) || 'Height' == $details['name'] ) {
						if ( 'Material' == $details['name'] ) {
							if ( ! empty( $id ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $id;
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							}
						}
						if ( 'Colour' == $details['name'] ) {
							if ( ! empty( $id ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $id;
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							}
						}
						if ( 'Weight' == $details['name'] ) {
							$product_weight = get_post_meta( $pro_ids, '_weight', true );
							if ( ! empty( $product_weight ) && ( '0' != $product_weight ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_weight;
								$val[ $details['detail_id'] ]['unit']      = 'kg';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
						if ( 'Height' == $details['name'] ) {
							$product_height = get_post_meta( $pro_ids, '_height', true );
							if ( ! empty( $product_height ) && ( '0' !== $product_height ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_height;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
						if ( 'Length' == $details['name'] ) {
							$product_length = get_post_meta( $pro_ids, '_length', true );
							if ( ! empty( $product_length ) && ( '0' != $product_length ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_length;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}

						if ( 'Width' == $details['name'] ) {
							$product_width = get_post_meta( $pro_ids, '_width', true );
							if ( ! empty( $product_width ) && ( '0' != $product_width ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_width;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
					} else {
						if ( ! empty( $id ) ) {
							$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
							$val[ $details['detail_id'] ]['value']     = $id;
							if ( ! empty( $val ) ) {
								$args['technical_details'] = $val;
							}
						}
					}
				}
			}
			$category = $category_id;
			// ===============================================================================================================
			$args['category_id'] = $category;

			if ( empty( $args['technical_details'] ) ) {
				$args['technical_details'] = '';
			} else {
				$args['technical_details'] = array_values( $args['technical_details'] );
			}
			$args['uid'] = $pro_ids;
			if ( $stock < 0 ) {
				$stock                                   = 0;
				$args['listings'][ $condition ]['stock'] = $stock;
			} else {
				$args['listings'][ $condition ]['stock'] = $stock;
			}

			$args['listings'][ $condition ]['price'] = $price;
			if ( isset( $deliveries ) && ! empty( $deliveries ) ) {
				$args['listings'][ $condition ]['delivery_template_id'] = $deliveries;
			} else {
				$args['listings'][ $condition ]['handling_time'] = $handling_time;
			}
			$args['listings'][ $condition ]['sku']                  = $sku;
			$args['listings'][ $condition ]['delivery_weight']      = get_post_meta( $pro_ids, '_weight', true );
			$args['listings'][ $condition ]['delivery_template_id'] = ! empty( $delivery ) ? $delivery : '';
			$args['product_codes']                                  = ! empty( $product_code ) ? array( $product_code ) : '';
			$args['description']                                    = $description;
			$args['product_name']                                   = $title;
			$args['published']                                      = $published;
			if ( isset( $rrp ) && ! empty( $rrp ) ) {
				$args['rrp'] = $rrp;
			}
			$args['summary_points'] = ! empty( $summary_points ) ? $summary_points : '';
			$args['mpn']            = $mpn;

			$custom_image_url = get_post_meta( $args['uid'], '_custom_onbuy_image', true );

			if ( ! empty( $custom_image_url ) ) {
				$args['default_image'] = $custom_image_url;
			} else {
				$args['default_image'] = ! empty( $image_url[0] ) ? $image_url[0] : '';
			}
			$args['additional_images'] = ! empty( $alternate_image_urls ) ? $alternate_image_urls : '';
			$args['brand_name']        = $brand;
			$args['features']          = $features;

			return $args;

		}

		public function is_profile_assigned( $product_id ) {
			global $wpdb;

			// empty attr in variation get resolved
			$_product = wc_get_product( $product_id );
			if ( $_product->get_type() == 'variation' ) {
				$parent_id   = $_product->get_parent_id();
				$product     = wc_get_product( $parent_id )->get_data();
				$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
			} else {
				$product     = $_product->get_data();
				$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
			}
			// =============================================
			foreach ( $category_id as $key => $value ) {
				$profile_id = get_term_meta( $value, 'ced_onbuy_profile_id', true );

				if ( ! empty( $profile_id ) ) {
					break;
				}
			}

			if ( isset( $profile_id ) && ! empty( $profile_id ) ) {
				$this->is_profile_assigned_to_product = true;
				$profile_data                         = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id`=%d ", $profile_id ), 'ARRAY_A' );
				if ( is_array( $profile_data ) ) {
					$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
					$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();
				}
			} else {
				$this->is_profile_assigned_to_product = false;
			}
			$this->profile_data = isset( $profile_data ) ? $profile_data : '';
		}

		public function ced_get_formatted_data_inventory( $pro_ids = '', $shop_id = '', $onbuy_item_id = '' ) {
			$args         = array();
			$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );

			$global = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );

			$product = wc_get_product( $pro_ids );
			if ( ! is_object( $product ) ) {
				return;
			}

			$price = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_price' );

			// ============================================================
			if ( $product->get_type() == 'variation' ) {
				$parent_id           = $product->get_parent_id();
				$parentproduct       = wc_get_product( $parent_id );
				$parent_product_data = $parentproduct->get_data();
			}
			if ( WC()->version > '3.0.0' ) {
				if ( is_object( $product ) ) {
					$product_data = $product->get_data();
					$product_type = $product->get_type();
				}

				$quantity = (int) get_post_meta( $pro_ids, '_stock', true );
				$sku      = $product_data['sku'];

				$stock = (int) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_stock' );
				if ( empty( $stock ) && '' == $stock ) {
					$stock = (int) $quantity;
				}

				if ( empty( $price ) ) {
					$price = (float) $product_data['price'];
					if ( 'variable' == $product_type ) {
						$variations = $product->get_available_variations();
						if ( isset( $variations['0']['display_regular_price'] ) ) {
							$price = (float) $variations['0']['display_regular_price'];
						}
					}
				}
			}
			$sku = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_sku' );

			// ================================================================
			$ced_update_decreased_price   = get_option( 'ced_update_decreased_price' );
			$ced_onbuy_max_limit_meta_key = get_option( 'ced_onbuy_max_limit' );
			if ( ! empty( $ced_onbuy_max_limit_meta_key ) ) {
				$ced_onbuy_max_limit_value = get_post_meta( $pro_ids, $ced_onbuy_max_limit_meta_key, true );
			}

			$winning_status       = get_post_meta( $pro_ids, 'ced_onbuy_winning_price_status_' . $shop_id, true );
			$ced_onbuy_lead_price = get_post_meta( $pro_ids, 'ced_onbuy_lead_price_' . $shop_id, true );

			$ced_buybox_price_type = get_option( 'ced_onbuy_buybox_price_type' );
			if ( empty( $winning_status ) && 'yes' == $ced_update_decreased_price && ! empty( $ced_onbuy_lead_price ) ) {
				if ( ! empty( $ced_buybox_price_type ) ) {

					$ced_buybox_price = get_option( 'ced_onbuy_buybox_price' );

					if ( ! empty( $ced_buybox_price ) ) {
						if ( ! empty( $ced_onbuy_max_limit_value ) ) {
							if ( 'Fixed_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) $ced_onbuy_lead_price - $ced_buybox_price;
								if ( ( $reduced_price > $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;

								}
							} elseif ( 'Percentage_Decreased' == $ced_buybox_price_type ) {
								$reduced_price = (float) ( $ced_onbuy_lead_price - ( ( $ced_buybox_price / 100 ) * $ced_onbuy_lead_price ) );
								if ( ( $reduced_price < $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
									$price = $reduced_price;
								} else {
									$price = $ced_onbuy_max_limit_value;
								}
							}
						}
					}
				}
			} else {
				$conversion_rate = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_conversion_rate' );
				if ( ! empty( $conversion_rate ) ) {
					$price = (float) $price * $conversion_rate;
					if ( ! empty( $rrp ) ) {
						$rrp = $rrp * $conversion_rate;
					}
				}

				$markup_type = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup_type' );
				if ( ! empty( $markup_type ) ) {
					$markup_value = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup' );
					if ( ! empty( $markup_value ) ) {
						if ( 'Fixed_Increased' == $markup_type ) {
							$price = (float) $price + $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp + $markup_value;
							}
						} elseif ( 'Fixed_Decreased' == $markup_type ) {
							$price = (float) $price - $markup_value;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp - $markup_value;
							}
						} elseif ( 'Percentage_Increased' == $markup_type ) {
							$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
							}
						} elseif ( 'Percentage_Decreased' == $markup_type ) {
							$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
							if ( ! empty( $rrp ) ) {
								$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
							}
						}
					}
				}
			}
			$condition     = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_condition' );
			$handling_time = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_handling_time' );
			if ( $stock < 0 ) {
				$stock         = 0;
				$args['stock'] = $stock;
			} else {
				$args['stock'] = $stock;
			}

			$args['price'] = $price;
			// =====================================
			$sale_price = get_post_meta( $pro_ids, 'ced_onbuy_sale_price', true );
			if ( ! empty( $sale_price ) ) {
				$sale_start_date                              = get_post_meta( $pro_ids, 'ced_sale_price_dates_from', true );
				$sale_end_date                                = get_post_meta( $pro_ids, 'ced_sale_price_dates_to', true );
				$args['listings'][ $condition ]['sale_price'] = $sale_price;
				if ( empty( $sale_start_date ) ) {
					$sale_start_date = '';
				}

				if ( empty( $sale_end_date ) ) {
					$sale_end_date = '';
				}
				$args['listings'][ $condition ]['sale_start_date'] = $sale_start_date;
				$args['listings'][ $condition ]['sale_end_date']   = $sale_end_date;
			}

			$boost_percent = get_post_meta( $pro_ids, 'ced_onbuy_boost_percent', true );
			if ( empty( $boost_percent ) && '' == $boost_percent ) {
				$boost_percent = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_boost_percent' );
			}
			$args['boost_marketing_commission'] = $boost_percent;
			// ====================================
			$args['handling_time'] = $handling_time;
			$args['sku']           = $sku;

			return $args;
		}

		public function ced_get_variable_formatted_data( $pro_ids = '', $shop_id = '', $onbuy_item_id = '' ) {
			$proData             = array();
			$args                = array();
			$profile_data        = $this->ced_onbuy_get_setting_data( $shop_id );
			$is_profile_assigned = $this->is_profile_assigned( $pro_ids );

			$global = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );

			$product = wc_get_product( $pro_ids );
			if ( ! is_object( $product ) ) {
				return;
			}
			$price = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_price' );
			if ( $product->get_type() == 'variation' ) {
				$parent_id           = $product->get_parent_id();
				$parentproduct       = wc_get_product( $parent_id );
				$parent_product_data = $parentproduct->get_data();
			}
			if ( WC()->version > '3.0.0' ) {
				if ( is_object( $product ) ) {
					$product_data = $product->get_data();
					$product_type = $product->get_type();
				}

				$quantity    = (int) get_post_meta( $pro_ids, '_stock', true );
				$sku         = $product_data['sku'];
				$description = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_description' );
				if ( empty( $description ) && '' == $description ) {
					$description = $product_data['description'] . ' ' . $product_data['short_description'];
				}
				$title = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_title' );
				if ( empty( $title ) && '' == $title ) {
					$title = $product_data['name'];
				}

				$stock = (int) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_stock' );
				if ( empty( $stock ) && '' == $stock ) {
					$stock = (int) $quantity;
				}

				if ( empty( $price ) ) {
					$price = (float) $product_data['price'];
					if ( 'variable' == $product_type ) {
						$variations = $product->get_available_variations();
						if ( isset( $variations['0']['display_regular_price'] ) ) {
							$price = (float) $variations['0']['display_regular_price'];
						}
					}
				}
			}
			if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
				$args['opc'] = $onbuy_item_id;
			}
			$sku             = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_sku' );
			$rrp             = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_rrp' );
			$conversion_rate = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_conversion_rate' );
			if ( ! empty( $conversion_rate ) ) {
				$price = (float) $price * $conversion_rate;
				if ( ! empty( $rrp ) ) {
					$rrp = $rrp * $conversion_rate;
				}
			}
			$markup_type = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup_type' );
			if ( ! empty( $markup_type ) ) {
				$markup_value = (float) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_product_markup' );
				if ( ! empty( $markup_value ) ) {
					if ( 'Fixed_Increased' == $markup_type ) {
						$price = (float) $price + $markup_value;
						if ( ! empty( $rrp ) ) {
							$rrp = $rrp + $markup_value;
						}
					} elseif ( 'Fixed_Decreased' == $markup_type ) {
						$price = (float) $price - $markup_value;
						if ( ! empty( $rrp ) ) {
							$rrp = $rrp - $markup_value;
						}
					} elseif ( 'Percentage_Increased' == $markup_type ) {
						$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
						if ( ! empty( $rrp ) ) {
							$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
						}
					} elseif ( 'Percentage_Decreased' == $markup_type ) {
						$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
						if ( ! empty( $rrp ) ) {
							$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
						}
					}
				}
			}
			$condition     = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_condition' );
			$handling_time = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_handling_time' );
			$product_code  = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_ean' );
			if ( ! empty( $product_code ) ) {
				$product_code_len = strlen( $product_code );
				$count            = 13;
				$ean_diff         = $count - $product_code_len;

				for ( $i = 0; $i < $ean_diff; $i++ ) {
					$product_code = '0' . $product_code;
				}
			}
			$published      = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_published' );
			$summary_points = array();
			for ( $i = 0; $i < 6; $i++ ) {
				$summary = '';
				$summary = substr( get_post_meta( $pro_ids, 'ced_onbuy_summary_points' . $i, true ), 0, 500 );
				if ( ! empty( $summary ) ) {
					$summary_points[] = $summary;
				}
			}

			if ( empty( $summary_points[0] ) ) {
				$summary          = substr( $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_summary_points' ), 0, 500 );
				$summary_points[] = $summary;

			}

			$mpn   = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_mpn' );
			$brand = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_brand' );

			$image_url            = wp_get_attachment_image_src( get_post_thumbnail_id( $pro_ids ), 'full' );
			$attachment_ids       = $product->get_gallery_image_ids();
			$alternate_image_urls = array();
			if ( count( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$alternate_image_urls[] = wp_get_attachment_url( $attachment_id );
				}
			}
			$videos = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_video' );
			if ( isset( $videos ) && '' != $videos ) {
				$args['videos'][0]['label'] = 'youtube';
				$args['videos'][0]['url']   = $videos;
			}

			$deliveries = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_deliveries' );

			if ( $deliveries ) {
				global $wpdb;
				$result  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts' ), 'ARRAY_A' );
				$shop_id = get_option( 'ced_onbuy_shop_id' );
				foreach ( $result as $key => $value ) {
					if ( $value['shop_id'] == $shop_id ) {
						$deliveries_option = json_decode( $value['seller_deliveries'] );
					}
				}
				foreach ( $deliveries_option as $k => $v ) {
					if ( $v->template_name == $deliveries ) {

						$delivery = $v->seller_delivery_template_id;
					}
				}
			}

			// ============================== Profiling Features Code =============================================
			if ( isset( $product_data['category_ids'] ) && ! empty( $product_data['category_ids'] ) ) {
				foreach ( $product_data['category_ids'] as $key => $value ) {
					$get_onbuy_category_id_data = get_term_meta( $value );
					$get_onbuy_category_id      = isset( $get_onbuy_category_id_data['ced_onbuy_mapped_category'] ) ? $get_onbuy_category_id_data['ced_onbuy_mapped_category'] : '';
					if ( isset( $get_onbuy_category_id ) && ! empty( $get_onbuy_category_id ) ) {
						foreach ( $get_onbuy_category_id as $index => $onbuy_cat ) {
							$category_id = $onbuy_cat;
							if ( ! empty( $category_id ) ) {
								break;
							}
						}
					}
				}
			}
			$features = array();
			$file     = CED_ONBUY_DIRPATH . 'admin/onbuy/class-onbuy.php';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
			$ced_onbuy_instance           = new Class_Ced_Onbuy_Manager();
			$onbuy_fetched_categories_fea = $ced_onbuy_instance->ced_onbuy_get_category_features( $category_id, $shop_id );
			if ( isset( $onbuy_fetched_categories_fea['results'] ) && ! empty( $onbuy_fetched_categories_fea['results'] ) ) {
				foreach ( $onbuy_fetched_categories_fea['results'] as $key => $value ) {
					foreach ( $value['options'] as $option_key => $option_value ) {
						$attr_value = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $value['feature_id'] );
						if ( ! empty( $attr_value ) && ( $option_value['option_id'] == $attr_value ) ) {
							$features[] = array(
								'option_id' => $option_value['option_id'],
								'name'      => $option_value['name'],
							);
						}
					}
				}
			}
			// ==================================== Profiling Technical Details ============================================
			$val                                   = array();
			$onbuy_fetched_categories_tech_details = $ced_onbuy_instance->ced_onbuy_get_category_tech_details( $category_id, $shop_id );
			if ( isset( $onbuy_fetched_categories_tech_details['results'] ) && ! empty( $onbuy_fetched_categories_tech_details['results'] ) ) {
				foreach ( $onbuy_fetched_categories_tech_details['results'][0]['options'] as $key => $details ) {

					$id   = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $details['detail_id'] );
					$unit = $this->ced_fetch_meta_value_of_product( $pro_ids, $category_id . '_' . $details['detail_id'] . '+' );
					if ( ( 'Material' == $details['name'] ) || ( 'Colour' == $details['name'] ) || ( 'Weight' == $details['name'] ) || ( 'Length' == $details['name'] ) || ( 'Width' == $details['name'] ) || 'Height' == $details['name'] ) {
						if ( 'Material' == $details['name'] ) {
							if ( ! empty( $id ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $id;
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							}
						}
						if ( 'Colour' == $details['name'] ) {
							if ( ! empty( $id ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $id;
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							}
						}
						if ( 'Weight' == $details['name'] ) {
							$product_weight = get_post_meta( $pro_ids, '_weight', true );
							if ( ! empty( $product_weight ) && ( '0' != $product_weight ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_weight;
								$val[ $details['detail_id'] ]['unit']      = 'kg';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
						if ( 'Height' == $details['name'] ) {
							$product_height = get_post_meta( $pro_ids, '_height', true );
							if ( ! empty( $product_height ) && ( '0' !== $product_height ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_height;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
						if ( 'Length' == $details['name'] ) {
							$product_length = get_post_meta( $pro_ids, '_length', true );
							if ( ! empty( $product_length ) && ( '0' != $product_length ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_length;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}

						if ( 'Width' == $details['name'] ) {
							$product_width = get_post_meta( $pro_ids, '_width', true );
							if ( ! empty( $product_width ) && ( '0' != $product_width ) ) {
								$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
								$val[ $details['detail_id'] ]['value']     = $product_width;
								$val[ $details['detail_id'] ]['unit']      = 'cm';
								if ( ! empty( $val ) ) {
									$args['technical_details'] = $val;
								}
							} else {
								if ( ! empty( $id ) ) {
									$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
									$val[ $details['detail_id'] ]['value']     = $id;
									$val[ $details['detail_id'] ]['unit']      = $unit;
									if ( ! empty( $val ) ) {
										$args['technical_details'] = $val;
									}
								}
							}
						}
					} else {
						if ( ! empty( $id ) ) {
							$val[ $details['detail_id'] ]['detail_id'] = $details['detail_id'];
							$val[ $details['detail_id'] ]['value']     = $id;
							if ( ! empty( $val ) ) {
								$args['technical_details'] = $val;
							}
						}
					}
				}
			}
			$category = $category_id;
			// ===============================================================================================================

			$parent_sku          = $sku;
			$args['category_id'] = $category;
			if ( empty( $args['technical_details'] ) ) {
				$args['technical_details'] = '';
			} else {
				$args['technical_details'] = array_values( $args['technical_details'] );
			}
			$condition   = ! empty( $condition ) ? $condition : 'new';
			$args['uid'] = $pro_ids;
			if ( $stock < 0 ) {
				$stock                                   = 0;
				$args['listings'][ $condition ]['stock'] = $stock;
			} else {
				$args['listings'][ $condition ]['stock'] = $stock;
			}
			$args['listings'][ $condition ]['price'] = $price;
			if ( isset( $deliveries ) && ! empty( $deliveries ) ) {
				$args['listings'][ $condition ]['delivery_template_id'] = $delivery;
			} else {
				$args['listings'][ $condition ]['handling_time'] = $handling_time;
			}
			$args['listings'][ $condition ]['delivery_weight'] = get_post_meta( $pro_ids, '_weight', true );
			$args['listings'][ $condition ]['sku']             = $sku;
			$args['product_codes']                             = ! empty( $product_code ) ? array( $product_code ) : '';
			$args['description']                               = $description;
			$args['product_name']                              = $title;
			$args['published']                                 = $published;
			if ( isset( $rrp ) && ! empty( $rrp ) ) {
				$args['rrp'] = $rrp;
			}
			$args['summary_points'] = $summary_points;
			$args['mpn']            = $mpn;

			$custom_image_url = get_post_meta( $args['uid'], '_custom_onbuy_image', true );

			if ( ! empty( $custom_image_url ) ) {
				$args['default_image'] = $custom_image_url;
			} else {
				$args['default_image'] = ! empty( $image_url[0] ) ? $image_url[0] : '';
			}
			$args['additional_images'] = ! empty( $alternate_image_urls ) ? $alternate_image_urls : '';
			$args['brand_name']        = $brand;
			$args['features']          = $features;

			$attr_variations = $product->get_variation_attributes();
			if ( isset( $attr_variations ) && ! empty( $attr_variations ) && is_array( $attr_variations ) && empty( $onbuy_item_id ) ) {
				$attr_count = 1;
				foreach ( $attr_variations as $key => $value ) {
					$key                                      = str_replace( 'attribute_', '', $key );
					$key                                      = wc_attribute_label( $key );
					$args[ 'variant_' . $attr_count ]['name'] = $key;
					$attr_count                               = ++$attr_count;
				}
			}
			$variations           = $product->get_available_variations();
			$final_onbuy_products = array();
			if ( isset( $variations ) && ! empty( $variations ) && is_array( $variations ) ) {
				foreach ( $variations as $variation ) {
					$variation_id  = $variation['variation_id'];
					$onbuy_item_id = get_post_meta( $variation_id, '_ced_onbuy_listing_id_' . $shop_id, true );
					$_product      = wc_get_product( $variation_id );
					$var_data      = $_product->get_data();
					$attributes    = $_product->get_attributes();

					if ( isset( $attributes ) && is_array( $attributes ) && empty( $onbuy_item_id ) ) {
						$variant_count = 1;
						foreach ( $attributes as $key => $attribute_value ) {
							$key          = str_replace( 'attribute_', '', $key );
							$term         = get_term_by( 'slug', $attribute_value, $key );
							$product_term = $attribute_value;
							if ( is_object( $term ) ) {
								$product_term = $term->name;
							}

							$variation_data[ 'variant_' . $variant_count ]['name'] = $product_term;
							$variant_count = ++$variant_count;
						}
					}
					$quantity    = (int) $_product->get_stock_quantity();
					$variant_sku = $_product->get_sku();

					$price = (float) get_post_meta( $variation_id, 'ced_onbuy_price', true );
					if ( empty( $price ) && '' == $price ) {
						$price = (float) $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_price' );

					}
					$title = get_post_meta( $variation_id, 'ced_onbuy_title', true );
					if ( empty( $title ) && '' == $title ) {
						$title = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_title' );
						if ( empty( $title ) && '' == $title ) {
							$title = $var_data['name'];
						}
					}

					$stock = (int) $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_stock' );
					if ( empty( $stock ) && '' == $stock ) {
						$stock = (int) $quantity;
					}

					$description = get_post_meta( $variation_id, 'ced_onbuy_description', true );
					if ( empty( $description ) && '' == $description ) {
						$description = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_description' );
						if ( empty( $description ) && '' == $description ) {
							$description = $var_data['description'];
						}
					}
					if ( isset( $onbuy_item_id ) && ! empty( $onbuy_item_id ) ) {
						$variation_data['opc'] = $onbuy_item_id;
					}
					$rrp = get_post_meta( $variation_id, 'ced_onbuy_rrp', true );
					if ( empty( $rrp ) && '' == $rrp ) {

						$rrp = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_rrp' );

					}

					// ================================================================
					$ced_update_decreased_price   = get_option( 'ced_update_decreased_price' );
					$ced_onbuy_max_limit_meta_key = get_option( 'ced_onbuy_max_limit' );
					if ( ! empty( $ced_onbuy_max_limit_meta_key ) ) {
						$ced_onbuy_max_limit_value = get_post_meta( $pro_ids, $ced_onbuy_max_limit_meta_key, true );
					}

					$winning_status       = get_post_meta( $pro_ids, 'ced_onbuy_winning_price_status_' . $shop_id, true );
					$ced_onbuy_lead_price = get_post_meta( $pro_ids, 'ced_onbuy_lead_price_' . $shop_id, true );

					$ced_buybox_price_type = get_option( 'ced_onbuy_buybox_price_type' );
					if ( empty( $winning_status ) && 'yes' == $ced_update_decreased_price && ! empty( $ced_onbuy_lead_price ) ) {
						if ( ! empty( $ced_buybox_price_type ) ) {
							$ced_buybox_price = get_option( 'ced_onbuy_buybox_price' );

							if ( ! empty( $ced_buybox_price ) ) {
								if ( ! empty( $ced_onbuy_max_limit_value ) ) {
									if ( 'Fixed_Decreased' == $ced_buybox_price_type ) {
										$reduced_price = (float) $ced_onbuy_lead_price - $ced_buybox_price;
										if ( ( $reduced_price > $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
											$price = $reduced_price;
										} else {
											$price = $ced_onbuy_max_limit_value;
										}
									} elseif ( 'Percentage_Decreased' == $ced_buybox_price_type ) {
										$reduced_price = (float) ( $ced_onbuy_lead_price - ( ( $ced_buybox_price / 100 ) * $ced_onbuy_lead_price ) );
										if ( ( $reduced_price < $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
											$price = $reduced_price;
										} else {
											$price = $ced_onbuy_max_limit_value;
										}
									}
								}
							}
						}
					} else {
						$conversion_rate = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_conversion_rate' );
						if ( ! empty( $conversion_rate ) ) {
							$price = (float) $price * $conversion_rate;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp * $conversion_rate;
							}
						}
						$markup_type = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_product_markup_type' );
						if ( ! empty( $markup_type ) ) {
							$markup_value = (float) $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_product_markup' );
							if ( ! empty( $markup_value ) ) {
								if ( 'Fixed_Increased' == $markup_type ) {
									$price = (float) $price + $markup_value;
									if ( ! empty( $rrp ) ) {
										$rrp = $rrp + $markup_value;
									}
								} elseif ( 'Fixed_Decreased' == $markup_type ) {
									$price = (float) $price - $markup_value;
									if ( ! empty( $rrp ) ) {
										$rrp = $rrp - $markup_value;
									}
								} elseif ( 'Percentage_Increased' == $markup_type ) {
									$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
									if ( ! empty( $rrp ) ) {
										$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
									}
								} elseif ( 'Percentage_Decreased' == $markup_type ) {
									$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
									if ( ! empty( $rrp ) ) {
										$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
									}
								}
							}
						}
					}
					$condition = get_post_meta( $variation_id, 'ced_onbuy_condition', true );
					if ( empty( $condition ) && '' == $condition ) {

						$condition = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_condition' );

					}
					$handling_time = get_post_meta( $variation_id, 'ced_onbuy_handling_time', true );
					if ( empty( $handling_time ) && '' == $handling_time ) {

						$handling_time = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_handling_time' );

					}

					$product_code = get_post_meta( $variation_id, 'ced_onbuy_ean', true );
					if ( empty( $product_code ) && '' == $product_code ) {

						$product_code = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_ean' );

					}
					if ( ! empty( $product_code ) ) {
						$product_code_len = strlen( $product_code );
						$count            = 13;
						$ean_diff         = $count - $product_code_len;

						for ( $i = 0; $i < $ean_diff; $i++ ) {
							$product_code = '0' . $product_code;
						}
					}
					$published = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_published' );

					$summary_points = array();
					for ( $i = 0; $i < 6; $i++ ) {
						$summary = '';
						$summary = substr( get_post_meta( $variation_id, 'ced_onbuy_summary_points' . $i, true ), 0, 500 );
						if ( ! empty( $summary ) ) {
							$summary_points[] = $summary;
						}
					}

					if ( empty( $summary_points[0] ) ) {
						$summary          = substr( $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_summary_points' ), 0, 500 );
						$summary_points[] = $summary;

					}

					$mpn = get_post_meta( $variation_id, 'ced_onbuy_mpn', true );
					if ( empty( $mpn ) && '' == $mpn ) {

						$mpn = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_mpn' );

					}

					$brand = get_post_meta( $variation_id, 'ced_onbuy_brand', true );
					if ( empty( $brand ) && '' == $brand ) {

						$brand = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_brand' );

					}
					$condition = ! empty( $condition ) ? $condition : 'new';
					// =====================================
					$sale_price = get_post_meta( $variation_id, 'ced_onbuy_sale_price', true );
					if ( ! empty( $sale_price ) ) {
						$sale_start_date = get_post_meta( $variation_id, 'ced_sale_price_dates_from', true );
						$sale_end_date   = get_post_meta( $variation_id, 'ced_sale_price_dates_to', true );

						$variation_data['listings'][ $condition ]['sale_price'] = $sale_price;
						if ( empty( $sale_start_date ) ) {
							$sale_start_date = '';
						}

						if ( empty( $sale_end_date ) ) {
							$sale_end_date = '';
						}

						$variation_data['listings'][ $condition ]['sale_start_date'] = $sale_start_date;
						$variation_data['listings'][ $condition ]['sale_end_date']   = $sale_end_date;
					}

					$boost_percent = get_post_meta( $variation_id, 'ced_onbuy_boost_percent', true );
					if ( empty( $boost_percent ) && '' == $boost_percent ) {
						$boost_percent = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_boost_percent' );
					}
					$variation_data['boost_marketing_commission'] = $boost_percent;
					// ====================================

					$image_url            = ! empty( wp_get_attachment_image_src( get_post_thumbnail_id( $variation_id ), 'full' ) ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $variation_id ), 'full' ) : wp_get_attachment_image_src( get_post_thumbnail_id( $pro_ids ), 'full' );
					$attachment_ids       = $product->get_gallery_image_ids();
					$alternate_image_urls = array();
					if ( count( $attachment_ids ) ) {
						foreach ( $attachment_ids as $attachment_id ) {
							$alternate_image_urls[] = wp_get_attachment_url( $attachment_id );
						}
					}
					$videos = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_video' );
					if ( isset( $videos ) && '' != $videos ) {

						$variation_data['videos'][0]['label'] = 'youtube';
						$variation_data['videos'][0]['url']   = $videos;
					}

					$deliveries = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_deliveries' );

					if ( $deliveries ) {
						global $wpdb;
						$result  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts' ), 'ARRAY_A' );
						$shop_id = get_option( 'ced_onbuy_shop_id' );
						foreach ( $result as $key => $value ) {
							if ( $value['shop_id'] == $shop_id ) {
								$deliveries_option = json_decode( $value['seller_deliveries'] );
							}
						}
						foreach ( $deliveries_option as $k => $v ) {
							if ( $v->template_name == $deliveries ) {

								$delivery = $v->seller_delivery_template_id;
							}
						}
					}

					$variation_data['uid'] = $variation_id;
					if ( $stock < 0 ) {
						$stock = 0;
						$variation_data['listings'][ $condition ]['stock'] = $stock;
					} else {
						$variation_data['listings'][ $condition ]['stock'] = $stock;
					}
					$variation_data['listings'][ $condition ]['price'] = $price;
					if ( isset( $deliveries ) && ! empty( $deliveries ) ) {
						$variation_data['listings'][ $condition ]['delivery_template_id'] = isset( $delivery ) ? $delivery : '';
					} else {
						$variation_data['listings'][ $condition ]['handling_time'] = $handling_time;
					}
					$variation_data['listings'][ $condition ]['group_sku'] = $parent_sku;
					$sku = ! empty( $variant_sku ) ? $variant_sku : $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_sku' );
					$variation_data['listings'][ $condition ]['delivery_weight'] = get_post_meta( $variation_id, '_weight', true );
					$variation_data['listings'][ $condition ]['sku']             = $variant_sku;
					$variation_data['product_codes']                             = ! empty( $product_code ) ? array( $product_code ) : '';
					$variation_data['description']                               = $description;
					$variation_data['product_name']                              = $title;
					$variation_data['published']                                 = $published;
					if ( isset( $rrp ) && ! empty( $rrp ) ) {
						$variation_data['rrp'] = $rrp;
					}

					$variation_data['summary_points']    = $summary_points;
					$variation_data['default_image']     = ! empty( $image_url[0] ) ? $image_url[0] : '';
					$variation_data['additional_images'] = ! empty( $alternate_image_urls ) ? $alternate_image_urls : '';
					$variation_data['brand_name']        = $brand;
					$final_onbuy_products[]              = $variation_data;
					$proData[]                           = $variation_data;
				}
			}
			$args['variants'] = $final_onbuy_products;
			if ( ! empty( $onbuy_item_id ) ) {
				return $proData;
			}
			return $args;
		}

		public function ced_get_variable_formatted_data_inventory( $pro_ids = '', $shop_id = '', $onbuy_item_id = '' ) {

			$paren_data   = array();
			$args         = array();
			$profile_data = $this->ced_onbuy_get_setting_data( $shop_id );

			$global = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );

			$product = wc_get_product( $pro_ids );
			if ( ! is_object( $product ) ) {
				return;
			}
			$price = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_price' );
			if ( 'variation' == $product->get_type() ) {
				$parent_id           = $product->get_parent_id();
				$parentproduct       = wc_get_product( $parent_id );
				$parent_product_data = $parentproduct->get_data();
			}
			if ( WC()->version > '3.0.0' ) {
				if ( is_object( $product ) ) {
					$product_data = $product->get_data();
					$product_type = $product->get_type();
				}

				$quantity = (int) get_post_meta( $pro_ids, '_stock', true );
				$sku      = $product_data['sku'];

				$stock = (int) $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_stock' );
				if ( empty( $stock ) && '' == $stock ) {
					$stock = (int) $quantity;
				}
				if ( empty( $price ) ) {
					$price = (float) $product_data['price'];
					if ( 'variable' == $product_type ) {
						$variations = $product->get_available_variations();
						if ( isset( $variations['0']['display_regular_price'] ) ) {
							$price = (float) $variations['0']['display_regular_price'];
						}
					}
				}
			}
			$sku = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_sku' );

			$handling_time = $this->ced_fetch_meta_value_of_product( $pro_ids, '_ced_onbuy_handling_time' );
			if ( $stock < 0 ) {
				$stock               = 0;
				$paren_data['stock'] = $stock;
			} else {
				$paren_data['stock'] = $stock;
			}
			$paren_data['price']         = $price;
			$paren_data['handling_time'] = $handling_time;
			$paren_data['sku']           = $sku;
			$args[]                      = $paren_data;
			$variations                  = $product->get_available_variations();
			if ( isset( $variations ) && ! empty( $variations ) && is_array( $variations ) ) {
				foreach ( $variations as $variation ) {
					$variation_id = $variation['variation_id'];

					$_product    = wc_get_product( $variation_id );
					$var_data    = $_product->get_data();
					$quantity    = (int) $_product->get_stock_quantity();
					$variant_sku = $_product->get_sku();
					$price       = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_price' );

					// ================================================================
					$ced_update_decreased_price   = get_option( 'ced_update_decreased_price' );
					$ced_onbuy_max_limit_meta_key = get_option( 'ced_onbuy_max_limit' );
					if ( ! empty( $ced_onbuy_max_limit_meta_key ) ) {
						$ced_onbuy_max_limit_value = get_post_meta( $pro_ids, $ced_onbuy_max_limit_meta_key, true );
					}

					$winning_status       = get_post_meta( $pro_ids, 'ced_onbuy_winning_price_status_' . $shop_id, true );
					$ced_onbuy_lead_price = get_post_meta( $pro_ids, 'ced_onbuy_lead_price_' . $shop_id, true );

					$ced_buybox_price_type = get_option( 'ced_onbuy_buybox_price_type' );

					if ( empty( $winning_status ) && 'yes' == $ced_update_decreased_price && ! empty( $ced_onbuy_lead_price ) ) {
						if ( ! empty( $ced_buybox_price_type ) ) {
							$ced_buybox_price = get_option( 'ced_onbuy_buybox_price' );

							if ( ! empty( $ced_buybox_price ) ) {
								if ( ! empty( $ced_onbuy_max_limit_value ) ) {
									if ( 'Fixed_Decreased' == $ced_buybox_price_type ) {
										$reduced_price = (float) $ced_onbuy_lead_price - $ced_buybox_price;
										if ( ( $reduced_price > $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
											$price = $reduced_price;
										} else {
											$price = $ced_onbuy_max_limit_value;
										}
									} elseif ( 'Percentage_Decreased' == $ced_buybox_price_type ) {
										$reduced_price = (float) ( $ced_onbuy_lead_price - ( ( $ced_buybox_price / 100 ) * $ced_onbuy_lead_price ) );
										if ( ( $reduced_price < $ced_onbuy_max_limit_value ) || ( $reduced_price == $ced_onbuy_max_limit_value ) ) {
											$price = $reduced_price;
										} else {
											$price = $ced_onbuy_max_limit_value;
										}
									}
								}
							}
						}
					} else {
						$conversion_rate = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_conversion_rate' );
						if ( ! empty( $conversion_rate ) ) {
							$price = (float) $price * $conversion_rate;
							if ( ! empty( $rrp ) ) {
								$rrp = $rrp * $conversion_rate;
							}
						}
						$markup_type = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_product_markup_type' );
						if ( ! empty( $markup_type ) ) {
							$markup_value = (float) $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_product_markup' );
							if ( ! empty( $markup_value ) ) {
								if ( 'Fixed_Increased' == $markup_type ) {
									$price = (float) $price + $markup_value;
									if ( ! empty( $rrp ) ) {
										$rrp = $rrp + $markup_value;
									}
								} elseif ( 'Fixed_Decreased' == $markup_type ) {
									$price = (float) $price - $markup_value;
									if ( ! empty( $rrp ) ) {
										$rrp = $rrp - $markup_value;
									}
								} elseif ( 'Percentage_Increased' == $markup_type ) {
									$price = (float) ( $price + ( ( $markup_value / 100 ) * $price ) );
									if ( ! empty( $rrp ) ) {
										$rrp = ( $rrp + ( ( $markup_value / 100 ) * $rrp ) );
									}
								} elseif ( 'Percentage_Decreased' == $markup_type ) {
									$price = (float) ( $price - ( ( $markup_value / 100 ) * $price ) );
									if ( ! empty( $rrp ) ) {
										$rrp = ( $rrp - ( ( $markup_value / 100 ) * $rrp ) );
									}
								}
							}
						}
					}
					$sku           = ! empty( $sku ) ? $sku : $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_sku' );
					$handling_time = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_handling_time' );
					if ( $stock < 0 ) {
						$stock                   = 0;
						$variation_data['stock'] = (int) $stock;
					} else {
						$variation_data['stock'] = (int) $stock;
					}
					$variation_data['price'] = $price;
					// =====================================
					$sale_price = get_post_meta( $variation_id, 'ced_onbuy_sale_price', true );
					if ( ! empty( $sale_price ) ) {
						$sale_start_date = get_post_meta( $variation_id, 'ced_sale_price_dates_from', true );
						$sale_end_date   = get_post_meta( $variation_id, 'ced_sale_price_dates_to', true );

						$variation_data['listings'][ $condition ]['sale_price'] = $sale_price;
						if ( empty( $sale_start_date ) ) {
							$sale_start_date = '';
						}

						if ( empty( $sale_end_date ) ) {
							$sale_end_date = '';
						}

						$variation_data['listings'][ $condition ]['sale_start_date'] = $sale_start_date;
						$variation_data['listings'][ $condition ]['sale_end_date']   = $sale_end_date;
					}

					$boost_percent = get_post_meta( $variation_id, 'ced_onbuy_boost_percent', true );
					if ( empty( $boost_percent ) && '' == $boost_percent ) {
						$boost_percent = $this->ced_fetch_meta_value_of_product( $variation_id, '_ced_onbuy_boost_percent' );
					}
					$variation_data['boost_marketing_commission'] = $boost_percent;
					// ====================================
					$variation_data['handling_time'] = $handling_time;
					$variation_data['sku']           = $variant_sku;

					$args[] = $variation_data;
				}
			}
			return $args;
		}

		public function ced_onbuy_get_setting_data( $shop_id = '' ) {
			if ( empty( $shop_id ) ) {
				return 'shop not selected';
			}
			$setting_data = get_option( 'ced_onbuy_global_settings_' . $shop_id, false );
			if ( isset( $setting_data[ $shop_id ] ) && ! empty( $setting_data[ $shop_id ] ) ) {
				$this->global_setting_data            = $setting_data[ $shop_id ];
				$this->is_profile_assigned_to_product = true;
			} else {
				$this->is_profile_assigned_to_product = false;
			}
		}

		public function ced_fetch_meta_value_of_product( $pro_ids, $meta_key ) {
			if ( isset( $this->is_profile_assigned_to_product ) && $this->is_profile_assigned_to_product ) {
				$_product = wc_get_product( $pro_ids );
				if ( ! is_object( $_product ) ) {
					return;
				}
				if ( $_product->get_type() == 'variation' ) {
					$parent_id = $_product->get_parent_id();
				} else {
					$parent_id = '0';
				}

				if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $meta_key ] ) ) {
					$profile_data      = $this->profile_data[ $meta_key ];
					$temp_profile_data = $profile_data;

					if ( isset( $temp_profile_data['default'] ) && ! empty( $temp_profile_data['default'] ) && ! is_null( $temp_profile_data['default'] ) ) {
						$value = $temp_profile_data['default'];
					} elseif ( isset( $temp_profile_data['metakey'] ) && ! empty( $temp_profile_data['metakey'] ) && 'null' != $temp_profile_data['metakey'] ) {

						if ( strpos( $temp_profile_data['metakey'], 'umb_pattr_' ) !== false ) {

							$woo_attribute = explode( 'umb_pattr_', $temp_profile_data['metakey'] );
							$woo_attribute = end( $woo_attribute );

							if ( $_product->get_type() == 'variation' ) {
								$var_product = wc_get_product( $parent_id );
								$attributes  = $var_product->get_variation_attributes();
								if ( isset( $attributes[ 'attribute_pa_' . $woo_attribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $woo_attribute ] ) ) {
									$woo_attribute_value = $attributes[ 'attribute_pa_' . $woo_attribute ];
									if ( '0' != $parent_id ) {
										$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
									} else {
										$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
									}
								} elseif ( isset( $attributes[ 'attribute_' . $woo_attribute ] ) && ! empty( $attributes[ 'attribute_' . $woo_attribute ] ) ) {

									$woo_attribute_value = $attributes[ 'attribute_' . $woo_attribute ];

									if ( '0' != $parent_id ) {
										$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
									} else {
										$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
									}
								} else {
									$woo_attribute_value = $var_product->get_attribute( 'pa_' . $woo_attribute );
									$woo_attribute_value = explode( ',', $woo_attribute_value );
									$woo_attribute_value = $woo_attribute_value[0];

									if ( '0' != $parent_id ) {
										$product_terms = get_the_terms( $parent_id, 'pa_' . $woo_attribute );
									} else {
										$product_terms = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
									}
								}
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $woo_attribute_value ) {
											$woo_attribute_value = $tempvalue->name;
											break;
										}
									}
									if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
										$value = $woo_attribute_value;
									} else {
										$value = get_post_meta( $pro_ids, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							} else {
								$woo_attribute_value = $_product->get_attribute( 'pa_' . $woo_attribute );
								$product_terms       = get_the_terms( $pro_ids, 'pa_' . $woo_attribute );
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $woo_attribute_value ) {
											$woo_attribute_value = $tempvalue->name;
											break;
										}
									}
									if ( isset( $woo_attribute_value ) && ! empty( $woo_attribute_value ) ) {
										$value = $woo_attribute_value;
									} else {
										$value = get_post_meta( $pro_ids, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							}
						}
						if ( empty( $value ) ) {
							$value = get_post_meta( $pro_ids, $temp_profile_data['metakey'], true );
							if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
								$value = wp_get_attachment_image_url( get_post_meta( $pro_ids, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $pro_ids, '_thumbnail_id', true ), 'thumbnail' ) : '';
							}
							if ( ! isset( $value ) || empty( $value ) || is_null( $value ) || '0' == $value || 'null' == $value ) {
								if ( '0' == $parent_id ) {
									$value = get_post_meta( $parent_id, $temp_profile_data['metakey'], true );
									if ( '_thumbnail_id' == $temp_profile_data['metakey'] ) {
										$value = wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parent_id, '_thumbnail_id', true ), 'thumbnail' ) : '';
									}

									if ( ! isset( $value ) || empty( $value ) || is_null( $value ) ) {
										$value = get_post_meta( $pro_ids, $meta_key, true );
									}
								} else {
									$value = get_post_meta( $pro_ids, $meta_key, true );
								}
							}
						}
					} else {
						$value = get_post_meta( $pro_ids, $meta_key, true );
					}
				} else {
					$value = get_post_meta( $pro_ids, $meta_key, true );
				}
				return $value;
			}
		}

	}
}
