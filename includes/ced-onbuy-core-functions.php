<?php
/**
 * Core Functions
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function ced_onbuy_check_woocommerce_active() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	return false;
}

/**
 * Callback function for display html.
 *
 * @since 1.0.0
 */
function get_onbuy_instuctions_html( $label = 'Instructions' ) {
	?>
	<div class="ced_onbuy_parent_element">
		<h2>
			<label><?php echo esc_html_e( $label, 'onbuy-woocommerce-integration' ); ?></label>
			<span class="dashicons dashicons-arrow-down-alt2 ced_onbuy_instruction_icon"></span>
		</h2>
	</div>
	<?php
}

function deactivate_ced_onbuy_woo_missing() {
	deactivate_plugins( CED_ONBUY_PLUGIN_BASENAME );
	add_action( 'admin_notices', 'ced_onbuy_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

function ced_onbuy_woo_missing_notice() {

	// translators: %s: search term !!
	echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( esc_html( __( 'OnBuy Integration By CedCommerce requires WooCommerce to be installed and active. You can download %s from here.', 'onbuy-integration-by-cedcommerce' ) ), '<a href="https://wordpress.org/plugins/woocommerce//" target="_blank">WooCommerce</a>' ) . '</p></div>';
}

function ced_onbuy_account_data( $shop_id = '' ) {
	global $wpdb;
	$shop_details = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `shop_id`= %d', $shop_id ), 'ARRAY_A' );
	return $shop_details;
}

function ced_onbuy_inactive_shops( $shop_id = '' ) {
	global $wpdb;
	$in_active_shops = $wpdb->get_results( $wpdb->prepare( 'SELECT `shop_id` FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `account_status` = %s ', 'inactive' ), 'ARRAY_A' );

	foreach ( $in_active_shops as $key => $value ) {
		if ( $value['shop_id'] === $shop_id ) {
			return true;
		}
	}
}

function ced_onbuy_get_woo_categories() {
	$woo_store_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
	foreach ( $woo_store_categories as $key => $value ) {
		$categories[ $value->name ] = $value->term_id;
	}
	return $categories;
}

function ced_onbuy_render_html( $meta_keys_to_be_displayed = array(), $added_meta_keys = array() ) {
	$html  = '';
	$html .= '<table class="wp-list-table widefat fixed striped">';

	if ( isset( $meta_keys_to_be_displayed ) && is_array( $meta_keys_to_be_displayed ) && ! empty( $meta_keys_to_be_displayed ) ) {
		$total_items  = count( $meta_keys_to_be_displayed );
		$pages        = ceil( $total_items / 10 );
		$current_page = 1;
		$counter      = 0;
		$break_point  = 1;

		foreach ( $meta_keys_to_be_displayed as $meta_key => $meta_data ) {
			$display = 'display : none';
			if ( 0 == $counter ) {
				if ( 1 == $break_point ) {
					$display = 'display : contents';
				}
				$html .= '<tbody style="' . esc_attr( $display ) . '" class="ced_onbuy_metakey_list_' . $break_point . '  			ced_onbuy_metakey_body">';
				$html .= '<tr><td colspan="3"><label>CHECK THE METAKEYS OR ATTRIBUTES</label></td>';
				$html .= '<td class="ced_onbuy_pagination"><span>' . $total_items . ' items</span>';
				$html .= '<button class="button ced_onbuy_navigation" data-page="1" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><<</b></button>';
				$html .= '<button class="button ced_onbuy_navigation" data-page="' . esc_attr( $break_point - 1 ) . '" ' . ( ( 1 == $break_point ) ? 'disabled' : '' ) . ' ><b><</b></button><span>' . $break_point . ' of ' . $pages;
				$html .= '</span><button class="button ced_onbuy_navigation" data-page="' . esc_attr( $break_point + 1 ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>></b></button>';
				$html .= '<button class="button ced_onbuy_navigation" data-page="' . esc_attr( $pages ) . '" ' . ( ( $pages == $break_point ) ? 'disabled' : '' ) . ' ><b>>></b></button>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr><td><label>Select</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';

			}
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= "<td><input type='checkbox' class='ced_onbuy_meta_key' value='" . esc_attr( $meta_key ) . "' " . $checked . '></input></td>';
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = ! empty( $meta_data[0] ) ? $meta_data[0] : '';
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
			++$counter;
			if ( 10 == $counter ) {
				$counter = 0;
				++$break_point;
				$html .= '</tbody>';
			}
		}
	} else {
		$html .= '<tr><td colspan="4" class="onbuy-error">No data found. Please search the metakeys.</td></tr>';
	}
	$html .= '</table>';
	return $html;
}


function ced_onbuy_cedcommerce_logo() {
	?>
	<a href="https://cedcommerce.com" target="_blank"><img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/ced-logo.png' ); ?> "></a>
	<?php
}

function ced_onbuy_tool_tip( $tip = '' ) {
	print_r( "</br><span class='cedcommerce-tip'>['" . $tip . "']</span>" );
}
