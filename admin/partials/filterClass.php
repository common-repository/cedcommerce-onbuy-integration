<?php
/**
 * Product Filters in manage products
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
 * Ced_Onbuy_FilterClass
 *
 * @since 1.0.0
 */
class Ced_Onbuy_FilterClass {


	/**
	 * Function for filtering products
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_filters_on_products() {
		if ( ( isset( $_POST['status_sorting'] ) && ! empty( $_POST['status_sorting'] ) ) || ( isset( $_POST['pro_cat_sorting'] ) && ! empty( $_POST['pro_cat_sorting'] ) ) || ( isset( $_POST['pro_type_sorting'] ) && ! empty( $_POST['pro_type_sorting'] ) ) || ( isset( $_POST['pro_type_status'] ) && ! empty( $_POST['pro_type_status'] ) ) || ( isset( $_POST['pro_stock_status'] ) && ! empty( $_POST['pro_stock_status'] ) ) || ( isset( $_POST['pro_per_page'] ) && ! empty( $_POST['pro_per_page'] ) ) ) {

			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}

			$status_sorting   = isset( $_POST['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['status_sorting'] ) ) : '';
			$pro_cat_sorting  = isset( $_POST['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_cat_sorting'] ) ) : '';
			$pro_type_sorting = isset( $_POST['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_type_sorting'] ) ) : '';
			$pro_type_status  = isset( $_POST['pro_type_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_type_status'] ) ) : '';
			$pro_stock_status = isset( $_POST['pro_stock_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_stock_status'] ) ) : '';
			$pro_per_page     = isset( $_POST['pro_per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_per_page'] ) ) : '';
			$current_url      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			wp_redirect( $current_url . '&status_sorting=' . $status_sorting . '&pro_cat_sorting=' . $pro_cat_sorting . '&pro_type_sorting=' . $pro_type_sorting . '&pro_type_status=' . $pro_type_status . '&pro_stock_status=' . $pro_stock_status . '&pro_per_page=' . $pro_per_page );
			exit;
		} else {
			$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
			$url     = admin_url( 'admin.php?page=ced_onbuy&section=class-onbuylistproducts&shop_id=' . $shop_id );
			wp_redirect( $url );
			exit;
		}
	}

	/**
	 * Function for searching a product
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_product_search_box() {

		if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
			return;
		}

		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
			$current_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$searchdata  = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
			$searchdata  = str_replace( ' ', ',', $searchdata );
			wp_redirect( $current_url . '&s=' . ( $searchdata ) . '&shop_id=' . ( $shop_id ) );
			exit;
		} else {
			$url = admin_url( 'admin.php?page=ced_onbuy&section=class-onbuylistproducts&shop_id=' . ( $shop_id ) );
			wp_redirect( $url );
			exit;
		}
	}
}
