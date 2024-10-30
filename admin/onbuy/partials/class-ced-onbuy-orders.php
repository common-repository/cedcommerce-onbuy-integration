<?php
/**
 * Gettting order related data
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class_CedOnbuyOrders
 *
 * @since 1.0.0
 * @param object $_instance Class instance.
 */
class Class_CedOnbuyOrders {

	/**
	 * The instance variable of this class.
	 *
	 * @since    1.0.0
	 * @var      object    $_instance    The instance variable of this class.
	 */

	public static $_instance;

	/**
	 * Class_CedOnbuyOrders Instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Class_CedOnbuyOrders construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependency();
	}

	/**
	 * Class_CedOnbuyOrders loading dependency.
	 *
	 * @since 1.0.0
	 */
	public function load_dependency() {
		$file_request = CED_ONBUY_DIRPATH . 'admin/onbuy/lib/class-ced-onbuy-request.php';
		if ( file_exists( $file_request ) ) {
			include_once $file_request;
		}
		$this->ced_onbuy_request = new Class_Ced_Onbuy_Request();
	}

	/**
	 * Function for getting orders from OnBuy
	 *
	 * @since 1.0.0
	 * @param int $shop_id OnBuy Shop Id.
	 */
	public function ced_onbuy_get_the_orders( $shop_id, $is_sync = false ) {
		$this->is_sync = $is_sync;
		do_action( 'ced_onbuy_refresh_token', $shop_id );

		$status  = get_option( 'ced_onbuy_order_status_to_sync_' . $shop_id, '' );
		$status  = empty( $status ) ? 'all' : $status;
		$action  = 'orders';
		$date    = '';
		$queries = 'site_id=2000&filter[status]=' . $status . '&limit=50&filter[modified_since]=' . $date;

		$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
		$orders       = $this->ced_onbuy_request->ced_onbuy_get_method( $action, $queries, $access_token );
		if ( isset( $orders['results'] ) ) {
			$orders = $orders['results'];

			$this->ced_create_local_order( $orders, $shop_id );
		}
	}

	/**
	 * Function for getting cancel orders from OnBuy
	 *
	 * @since 1.0.0
	 * @param int $shop_id OnBuy Shop Id.
	 */
	public function ced_onbuy_cancel_orders( $parameters = '', $shop_id = '' ) {
		do_action( 'ced_onbuy_refresh_token', $shop_id );
		$action       = 'orders/cancel';
		$queries      = 'site_id=2000';
		$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
		$response     = $this->ced_onbuy_request->ced_onbuy_put_method( $action, $queries, $access_token, $parameters );
		return $response;
	}

	/**
	 * Function for getting refund orders from OnBuy
	 *
	 * @since 1.0.0
	 * @param int $shop_id OnBuy Shop Id.
	 */
	public function ced_onbuy_refund_orders( $parameters = '', $shop_id = '' ) {
		do_action( 'ced_onbuy_refresh_token', $shop_id );
		$action       = 'orders/refund';
		$queries      = 'site_id=2000';
		$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
		$response     = $this->ced_onbuy_request->ced_onbuy_put_method( $action, $queries, $access_token, $parameters );
		return $response;
	}

	/**
	 * Function for getting ship orders from OnBuy
	 *
	 * @since 1.0.0
	 * @param int $shop_id OnBuy Shop Id.
	 */
	public function ced_onbuy_ship_orders( $parameters = '', $shop_id = '' ) {
		do_action( 'ced_onbuy_refresh_token', $shop_id );
		$action       = 'orders/dispatch';
		$queries      = '';
		$access_token = get_transient( 'ced_onbuy_refresh_token_' . $shop_id );
		$response     = $this->ced_onbuy_request->ced_onbuy_put_method( $action, $queries, $access_token, $parameters );
		return $response;
	}

	/**
	 * Function for creating a local order
	 *
	 * @since 1.0.0
	 * @param array $orders Order Details.
	 * @param int   $shop_id OnBuy Shop Id.
	 */
	public function ced_create_local_order( $orders, $shop_id = '' ) {
		if ( is_array( $orders ) && ! empty( $orders ) ) {
			$OrderItemsInfo = array();
			$neworder       = array();

			foreach ( $orders as $order ) {
				$OrderNumber          = $order['order_id'];
				$ShipToFirstName      = isset( $order['delivery_address']['name'] ) ? $order['delivery_address']['name'] : '';
				$ShipToAddress1       = isset( $order['delivery_address']['line_1'] ) ? $order['delivery_address']['line_1'] : '';
				$ShipToAddress2       = isset( $order['delivery_address']['line_2'] ) ? $order['delivery_address']['line_2'] : '';
				$ShippingAddress3     = isset( $order['delivery_address']['line_3'] ) ? $order['delivery_address']['line_3'] : '';
				$ShipToCityName       = isset( $order['delivery_address']['town'] ) ? $order['delivery_address']['town'] : '';
				$ShipToStateCode      = isset( $order['delivery_address']['county'] ) ? $order['delivery_address']['county'] : '';
				$ShipToZipCode        = isset( $order['delivery_address']['postcode'] ) ? $order['delivery_address']['postcode'] : '';
				$ShipToCountry        = isset( $order['delivery_address']['country_code'] ) ? $order['delivery_address']['country_code'] : '';
				$CustomerPhoneNumber  = isset( $order['buyer']['phone'] ) ? $order['buyer']['phone'] : '';
				$customerEmailaddress = isset( $order['buyer']['email'] ) ? $order['buyer']['email'] : '';

				$ShipToAddress1  = $ShipToAddress1 . $ShipToAddress2;
				$ShippingAddress = array(
					'first_name' => $ShipToFirstName,
					'phone'      => $CustomerPhoneNumber,
					'address_1'  => $ShipToAddress1,
					'address_2'  => $ShippingAddress3,
					'city'       => $ShipToCityName,
					'state'      => $ShipToStateCode,
					'postcode'   => $ShipToZipCode,
					'email'      => $customerEmailaddress,
					'country'    => $ShipToCountry,
				);

				$BillingToFirstName  = isset( $order['billing_address']['name'] ) ? $order['billing_address']['name'] : '';
				$BillingToAddress1   = isset( $order['billing_address']['line_1'] ) ? $order['billing_address']['line_1'] : '';
				$BillingToAddress2   = isset( $order['billing_address']['line_2'] ) ? $order['billing_address']['line_2'] : '';
				$BillingpingAddress3 = isset( $order['billing_address']['line_3'] ) ? $order['billing_address']['line_3'] : '';
				$BillingToCityName   = isset( $order['billing_address']['town'] ) ? $order['billing_address']['town'] : '';
				$BillingToStateCode  = isset( $order['billing_address']['county'] ) ? $order['billing_address']['county'] : '';
				$BillingToZipCode    = isset( $order['billing_address']['postcode'] ) ? $order['billing_address']['postcode'] : '';
				$BillingToCountry    = isset( $order['billing_address']['country_code'] ) ? $order['billing_address']['country_code'] : '';
				$BillingAddress      = array(
					'first_name' => $ShipToFirstName,
					'phone'      => $CustomerPhoneNumber,
					'address_1'  => $ShipToAddress1,
					'address_2'  => $ShippingAddress3,
					'city'       => $ShipToCityName,
					'state'      => $ShipToStateCode,
					'postcode'   => $ShipToZipCode,
					'email'      => $customerEmailaddress,
					'country'    => $ShipToCountry,
				);
				$address             = array(
					'shipping' => $ShippingAddress,
					'billing'  => $BillingAddress,
				);
				$OrderStatus         = $order['status'];
				$ordertotal          = isset( $order['total'] ) ? $order['total'] : '';
				$transactions        = $order['products'];
				if ( is_array( $transactions ) && ! empty( $transactions ) ) {
					$ItemArray = array();
					foreach ( $transactions as $transaction ) {
						$item       = array();
						$ID         = false;
						$OrderedQty = $transaction['quantity'];
						$CancelQty  = 0;
						$basePrice  = $transaction['unit_price'];
						$sku        = isset( $transaction['sku'] ) ? $transaction['sku'] : false;
						if ( $sku ) {
							global $wpdb;
							$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $sku ) );

							if ( ! empty( $product_id ) ) {
								$ID = $product_id;
							}
						}

						$item        = array(
							'OrderedQty' => $OrderedQty,
							'CancelQty'  => $CancelQty,
							'UnitPrice'  => $basePrice,
							'Sku'        => $sku,
							'ID'         => $ID,
						);
						$ItemArray[] = $item;
					}
				}
				$finalTax       = isset( $order['price_delivery'] ) ? $order['price_delivery'] : '';
				$OrderItemsInfo = array(
					'OrderNumber' => $OrderNumber,
					'OrderStatus' => $OrderStatus,
					'ItemsArray'  => $ItemArray,
					'tax'         => $finalTax,
				);
				$orderItems     = $transactions;

				$merchantOrderId = $OrderNumber;
				$purchaseOrderId = $OrderNumber;
				$fulfillmentNode = '';
				$orderDetail     = isset( $order ) ? $order : array();
				$OnbuyOrderMeta  = array(
					'merchant_order_id' => $merchantOrderId,
					'purchaseOrderId'   => $purchaseOrderId,
					'fulfillment_node'  => $fulfillmentNode,
					'order_detail'      => $orderDetail,
					'order_items'       => $orderItems,
				);

				$ced_onbuy_default_order_statuses = array(
					'All'                  => 'wc-pending',
					'Awaiting Dispatch'    => 'wc-on-hold',
					'Dispatched'           => 'wc-completed',
					'Complete'             => 'wc-completed',
					'Cancelled'            => 'wc-cancelled',
					'Cancelled By Seller'  => 'wc-cancelled',
					'Cancelled By Buyer'   => 'wc-cancelled',
					'Partially Dispatched' => 'wc-processing',
					'Partially Refunded'   => 'wc-refunded',
					'Refunded'             => 'wc-refunded',
				);

				$ced_onbuy_plugin_order_statuses = array(
					'All'                  => 'Fetched',
					'Awaiting Dispatch'    => 'Fetched',
					'Dispatched'           => 'Completed',
					'Complete'             => 'Completed',
					'Cancelled'            => 'Cancelled',
					'Cancelled By Seller'  => 'Cancelled',
					'Cancelled By Buyer'   => 'Cancelled',
					'Partially Dispatched' => 'Fetched',
					'Partially Refunded'   => 'Refunded',
					'Refunded'             => 'Refunded',
				);
				$ced_onbuy_mapped_order_statuses = get_option( '_ced_mapped_order_status' . $shop_id, array() );

				$woo_order_id = $this->ced_create_order( $address, $OrderItemsInfo, 'OnBuy', $OnbuyOrderMeta, $shop_id );
				if ( isset( $woo_order_id ) && ! empty( $woo_order_id ) ) {
					// ---------------------------
					$order_obj = wc_get_order( $woo_order_id );
					if ( is_object( $order_obj ) ) {
						$woo_order_status = isset( $ced_onbuy_mapped_order_statuses[ $OrderStatus ] ) ? $ced_onbuy_mapped_order_statuses[ $OrderStatus ] : $ced_onbuy_default_order_statuses[ $OrderStatus ];

						update_post_meta( $woo_order_id, '_onbuy_order_status', $OrderStatus );

						if ( ! empty( $woo_order_status ) ) {
							$order_obj->update_status( $woo_order_status );
						} else {
							$order_obj->update_status( $ced_onbuy_default_order_statuses[ $OrderStatus ] );
						}
					}
					// ---------------------------------
				}
			}
		}
	}

	/**
	 * Function for creating order in woocommerce
	 *
	 * @since 1.0.0
	 * @param array  $address Shipping and billing address.
	 * @param array  $order_items_info Order items details.
	 * @param string $framework_name Framework name.
	 * @param array  $order_meta Order meta details.
	 * @param string $creation_date Order creation date.
	 * @param int    $shop_id OnBuy Shop Id.
	 */
	public function ced_create_order( $address = array(), $OrderItemsInfo = array(), $frameworkName = 'ManoMano', $orderMeta = array(), $shop_id ) {
		global $cedonbuyhelper;
		$order_id = '';
		// =================================================
		$wp_upload_dir = wp_upload_dir();
		$base_dir      = $wp_upload_dir['basedir'];
		$upload_dir    = $base_dir . '/ced_onbuy_order_log_dir';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0777, true );
		}
		$fp             = fopen( $upload_dir . '/ced_onbuy_orders_' . gmdate( 'j.n.Y' ) . '.log', 'a' );
		$ced_onbuy_log  = '';
		$ced_onbuy_log .= 'Date and Time: ' . gmdate( 'F j, Y, g:i a' ) . "\r\n";
		// ====================================================
		$order_created = false;
		if ( count( $OrderItemsInfo ) ) {
			$OrderNumber = isset( $OrderItemsInfo['OrderNumber'] ) ? $OrderItemsInfo['OrderNumber'] : 0;
			$order_id    = $this->ced_is_onbuy_order_exists( $OrderNumber );

			if ( $order_id ) {
				$updated_status = isset( $OrderItemsInfo['OrderStatus'] ) ? $OrderItemsInfo['OrderStatus'] : '';

				if ( 'Dispatched' == $updated_status ) {
					$this->update_order_details( $order_id, $address, $updated_status );
				}
				return $order_id;
			}
			global $activity;
			$activity->action        = 'Fetch';
			$activity->type          = 'order';
			$activity->input_payload = $OrderItemsInfo;
			$activity->post_title    = 'OnBuy order : ' . $OrderNumber;
			$activity->post_id       = $OrderNumber;
			$activity->shop_id       = $shop_id;
			$activity->is_auto       = $this->is_sync;

			$response = array();
			if ( count( $OrderItemsInfo ) ) {
				$ItemsArray = isset( $OrderItemsInfo['ItemsArray'] ) ? $OrderItemsInfo['ItemsArray'] : array();
				if ( is_array( $ItemsArray ) ) {
					foreach ( $ItemsArray as $ItemInfo ) {
						$ProID = isset( $ItemInfo['ID'] ) ? intval( $ItemInfo['ID'] ) : '';

						$Sku = isset( $ItemInfo['Sku'] ) ? $ItemInfo['Sku'] : '';

						$ced_onbuy_log .= 'SKU : ' . $Sku . "\r\n";

						$MfrPartNumber = isset( $ItemInfo['MfrPartNumber'] ) ? $ItemInfo['MfrPartNumber'] : '';
						$Upc           = isset( $ItemInfo['UPCCode'] ) ? $ItemInfo['UPCCode'] : '';
						$Asin          = isset( $ItemInfo['ASIN'] ) ? $ItemInfo['ASIN'] : '';

						$params = array( '_sku' => $Sku );
						if ( ! $ProID ) {
							$ProID = $this->ced_umb_get_product_by( $params );
						}
						if ( ! $ProID ) {
							$ProID = $Sku;
						}

						$Qty                  = isset( $ItemInfo['OrderedQty'] ) ? intval( $ItemInfo['OrderedQty'] ) : 0;
						$UnitPrice            = isset( $ItemInfo['UnitPrice'] ) ? floatval( $ItemInfo['UnitPrice'] ) : 0;
						$ExtendUnitPrice      = isset( $ItemInfo['ExtendUnitPrice'] ) ? floatval( $ItemInfo['ExtendUnitPrice'] ) : 0;
						$ExtendShippingCharge = isset( $ItemInfo['ExtendShippingCharge'] ) ? floatval( $ItemInfo['ExtendShippingCharge'] ) : 0;
						$_product             = wc_get_product( $ProID );

						if ( is_wp_error( $_product ) ) {
							$response[] = 'No product found with sku :' . $Sku;
							continue;
						} elseif ( is_null( $_product ) ) {
							$response[]     = 'No product found with sku :' . $Sku;
							$ced_onbuy_log .= "\t\t\t\t\t\t\t\tError -Sku Does not Exist\r\n\n";
							fwrite( $fp, $ced_onbuy_log );
							fclose( $fp );
							continue;
						} elseif ( ! $_product ) {
							$response[]     = 'No product found with sku :' . $Sku;
							$ced_onbuy_log .= "\t\t\t\t\t\t\t\tError -Sku Does not Exist\r\n\n";
							fwrite( $fp, $ced_onbuy_log );
							fclose( $fp );
							continue;
						} else {
							if ( ! $order_created ) {
								$order_data = array(
									'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
									'customer_note' => __( 'Order from ', 'ced-umb-onbuy' ) . $frameworkName,
									'created_via'   => $frameworkName,
								);

								/* ORDER CREATED IN WOOCOMMERCE */
								$order = wc_create_order( $order_data );

								/* ORDER CREATED IN WOOCOMMERCE */

								if ( is_wp_error( $order ) ) {
									continue;
								} elseif ( false === $order ) {
									continue;
								} else {
									if ( WC()->version < '3.0.0' ) {
										$order_id = $order->id;
									} else {
										$order_id = $order->get_id();
									}

									update_post_meta( $order_id, '_ced_onbuy_order_id', $OrderNumber );
									$order_created = true;
									$response[]    = 'Order created successfuly with woocommerce order id : ' . $order_id;
								}
							}
							$_product->set_price( $UnitPrice );
							$_product->set_tax_class( 'zero-rate' );
							$order->add_product( $_product, $Qty );
							$order->calculate_totals( false );
						}
						// ===================================================================
						$message        = isset( $order_id ) ? 'Order Created Successfully' : 'Order Not Created';
						$ced_onbuy_log .= 'Onbuy Order ID : ' . $OrderNumber . "\r\n";
						$ced_onbuy_log .= 'WooCommerce Order ID : ' . $order_id . "\r\n";
						$ced_onbuy_log .= 'Status : ' . $OrderItemsInfo['OrderStatus'] . "\r\n";
						$ced_onbuy_log .= 'Message : ' . $message . "\r\n";
						$ced_onbuy_log .= '---------------------------------------------------------' . "\r\n";
						fwrite( $fp, $ced_onbuy_log );
						fclose( $fp );
						// =========================================================================
					}
				}

				if ( ! $order_created ) {
					return false;
				}
				$OrderItemAmount = isset( $orderMeta['order_detail']['products_price'] ) ? $orderMeta['order_detail']['products_price'] : 0;
				$ShippingAmount  = isset( $orderMeta['order_detail']['price_delivery'] ) ? $orderMeta['order_detail']['price_delivery'] : 0;
				$DiscountAmount  = isset( $orderMeta['order_detail'] ) ? $orderMeta['order_detail'] : 0;
				$RefundAmount    = isset( $orderMeta['order_detail'] ) ? $orderMeta['order_detail'] : 0;
				$ShipService     = isset( $orderMeta['order_detail']['delivery_service'] ) ? $orderMeta['order_detail']['delivery_service'] : '';

				if ( ! empty( $ShippingAmount ) ) {
					$Ship_params = array(
						'ShippingCost' => $ShippingAmount,
						'ShipService'  => $ShipService,
					);
					$this->ced_add_shipping_charge( $order, $Ship_params );
				}
				$onbuy_order_total = $order->get_shipping_total() + $order->get_total();
				$order->set_total( $onbuy_order_total );
				$order->save();

				$ShippingAddress = isset( $address['shipping'] ) ? $address['shipping'] : '';
				if ( is_array( $ShippingAddress ) && ! empty( $ShippingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {
						$order->set_address( $ShippingAddress, 'shipping' );
					} else {
						$type = 'shipping';
						foreach ( $ShippingAddress as $key => $value ) {
							if ( '' != $value && null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}

				$BillingAddress = isset( $address['billing'] ) ? $address['billing'] : '';
				if ( is_array( $BillingAddress ) && ! empty( $BillingAddress ) ) {
					if ( WC()->version < '3.0.0' ) {
						$order->set_address( $ShippingAddress, 'billing' );
					} else {
						$type = 'billing';
						foreach ( $BillingAddress as $key => $value ) {
							if ( '' != $value && null != $value && ! empty( $value ) ) {
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				$order->calculate_totals( false );
				$order->set_payment_method( 'check' );

				update_post_meta( $order_id, '_is_onbuy_order', 1 );
				update_post_meta( $order_id, '_onbuy_order_itemdata', $OrderItemsInfo );
				update_post_meta( $order_id, '_onbuy_order_complete_details', $orderMeta['order_detail'] );
				update_post_meta( $order_id, 'ced_onbuy_order_shop_id', $shop_id );
				update_post_meta( $order_id, '_onbuy_marketplace', $frameworkName );
				$updated_status = isset( $OrderItemsInfo['OrderStatus'] ) ? $OrderItemsInfo['OrderStatus'] : '';
				if ( 'Awaiting Dispatch' == $updated_status ) {
					update_post_meta( $order_id, '_onbuy_onbuy_order_status', 'Fetched' );
				} else {
					update_post_meta( $order_id, '_onbuy_onbuy_order_status', $updated_status );
				}

				update_post_meta( $order_id, '_onbuy_order_status', $updated_status );
			}
			$final_response = $response;
			if ( $order_created ) {
				$final_response = array( 'response' => array( 'results' => $response ) );
			}
			$activity->response = $final_response;
			$activity->execute();
			return $order_id;
		}
		return false;
	}

	/**
	 * Get conditional product id.
	 *
	 * @since 1.0.0
	 * @param array $params Parameters to find product in woocommerce.
	 */
	public function ced_umb_get_product_by( $params ) {
		global $wpdb;

		$where = '';
		if ( count( $params ) ) {
			$flag = false;
			foreach ( $params as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) && ! empty( $meta_key ) ) {
					if ( ! $flag ) {
						$where .= 'meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$flag   = true;
					} else {
						$where .= ' OR meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
					}
				}
			}
			if ( $flag ) {
				$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE %s LIMIT 1", $where ) );
				if ( $product_id ) {
					return $product_id;
				}
			}
		}
		return false;
	}

	/**
	 * Function to check  if order already exists
	 *
	 * @since 1.0.0
	 * @param int $order_number OnBuy Order Id.
	 */
	public function ced_is_onbuy_order_exists( $order_number = 0 ) {
		global $wpdb;
		if ( $order_number ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_onbuy_order_id' AND meta_value=%s LIMIT 1", $order_number ) );
			if ( $order_id ) {
				return $order_id;
			}
		}
		return false;
	}

	/**
	 * Function to add shipping data
	 *
	 * @since 1.0.0
	 * @param object $order Order details.
	 * @param array  $ship_params Shipping details.
	 */
	public static function ced_add_shipping_charge( $order, $ship_params = array() ) {
		$ship_name = isset( $ship_params['ShipService'] ) ? ( $ship_params['ShipService'] ) : 'UMB Default Shipping';
		$ship_cost = isset( $ship_params['ShippingCost'] ) ? $ship_params['ShippingCost'] : 0;
		$ship_tax  = isset( $ship_params['ShippingTax'] ) ? $ship_params['ShippingTax'] : 0;

		$item = new WC_Order_Item_Shipping();

		$item->set_method_title( 'Shipping type : ' . $ship_name );
		$item->set_method_id( $ship_name );
		$item->set_total( $ship_cost );
		$order->add_item( $item );

		$order->calculate_totals( false );
		$order->save();
	}

	public function update_order_details( $order_id = '', $address = array(), $updated_status = '' ) {

		$order            = wc_get_order( $order_id );
		$shipping_address = isset( $address['shipping'] ) ? $address['shipping'] : '';
		if ( is_array( $shipping_address ) && ! empty( $shipping_address ) ) {
			if ( WC()->version < '3.0.0' ) {
				$order->set_address( $shipping_address, 'shipping' );
			} else {
				$type = 'shipping';
				foreach ( $shipping_address as $key => $value ) {
					if ( ! empty( $value ) ) {
						update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
						if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
							$order->{"set_{$type}_{$key}"}( $value );
						}
					}
				}
			}
		}

		$billing_address = isset( $address['billing'] ) ? $address['billing'] : '';
		if ( is_array( $billing_address ) && ! empty( $billing_address ) ) {
			if ( WC()->version < '3.0.0' ) {
				$order->set_address( $shipping_address, 'billing' );
			} else {
				$type = 'billing';
				foreach ( $billing_address as $key => $value ) {
					if ( ! empty( $value ) ) {
						update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
						if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
							$order->{"set_{$type}_{$key}"}( $value );
						}
					}
				}
			}
		}

		update_post_meta( $order_id, '_onbuy_order_status', $updated_status );
	}
}
