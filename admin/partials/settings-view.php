<?php
/**
 * Global settings section
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_ONBUY_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

$file = CED_ONBUY_DIRPATH . 'admin/partials/ced-instructions.php';
if ( file_exists( $file ) ) {
	require_once $file;

}

if ( isset( $_POST['global_settings'] ) ) {
	if ( ! isset( $_POST['global_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['global_settings_submit'] ) ), 'global_settings' ) ) {
		return;
	}

	$sanitized_array      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$settings             = array();
	$settings             = get_option( 'ced_onbuy_global_settings_' . $shop_id, array() );
	$settings[ $shop_id ] = isset( $sanitized_array['ced_onbuy_global_settings'] ) ? ( $sanitized_array['ced_onbuy_global_settings'] ) : array();
	update_option( 'ced_onbuy_global_settings_' . $shop_id, $settings );
	update_option( 'ced_onbuy_order_status_to_sync_' . $shop_id, $settings[ $shop_id ]['ced_onbuy_order_status'] );
	update_option( 'ced_onbuy_restrict_woo_mails', $settings[ $shop_id ]['ced_onbuy_restrict_woo_emails'] );

	update_option( 'ced_update_decreased_price', $settings[ $shop_id ]['ced_update_decreased_price'] );
	update_option( 'ced_onbuy_buybox_price_type', $settings[ $shop_id ]['ced_onbuy_buybox_price_type'] );
	update_option( 'ced_onbuy_buybox_price', $settings[ $shop_id ]['ced_onbuy_buybox_price'] );
	update_option( 'ced_onbuy_max_limit', $settings[ $shop_id ]['ced_onbuy_max_limit'] );

	wp_clear_scheduled_hook( 'ced_onbuy_inventory_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_auto_product_upload_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_check_winning_price_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( ' ' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_product_sync_scheduler_job_' . $shop_id );
		update_option( 'onbuy_auto_syncing' . $shop_id, 'off' );

	update_option( '_ced_mapped_order_status' . $shop_id, $settings[ $shop_id ]['_ced_mapped_order_status'] );


		update_option( 'onbuy_auto_syncing' . $shop_id, 'on' );
		wp_clear_scheduled_hook( 'ced_onbuy_inventory_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_auto_product_upload_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_check_winning_price_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_order_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_product_sync_scheduler_job_' . $shop_id );
		wp_clear_scheduled_hook( 'ced_onbuy_process_queue_scheduler_job_' . $shop_id );
		$inventory_schedule                  = isset( $_POST['ced_onbuy_global_settings']['ced_onbuy_inventory_schedule_info'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['ced_onbuy_inventory_schedule_info'] ) ) : '';
		$order_schedule                      = isset( $_POST['ced_onbuy_global_settings']['order_schedule_info'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['order_schedule_info'] ) ) : '';
		$auto_product_schedule               = isset( $_POST['ced_onbuy_global_settings']['auto_upload_schedule_info'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['auto_upload_schedule_info'] ) ) : '';
		$check_win_price_schedule            = isset( $_POST['ced_onbuy_global_settings']['ced_win_price_schedule_info'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['ced_win_price_schedule_info'] ) ) : '';
		$product_sync_schedule               = isset( $_POST['ced_onbuy_global_settings']['ced_onbuy_product_sync_schedule_info'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['ced_onbuy_product_sync_schedule_info'] ) ) : '';
		$ced_onbuy_product_sync_schedule_key = isset( $_POST['ced_onbuy_global_settings']['ced_onbuy_product_sync_schedule_key'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_onbuy_global_settings']['ced_onbuy_product_sync_schedule_key'] ) ) : '';

		wp_schedule_event( time(), 'ced_onbuy_6min', 'ced_onbuy_process_queue_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_process_queue_scheduler_job_' . $shop_id, $shop_id );
	if ( ! empty( $inventory_schedule ) ) {
		wp_schedule_event( time(), $inventory_schedule, 'ced_onbuy_inventory_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_inventory_scheduler_job_' . $shop_id, $shop_id );
	}if ( ! empty( $order_schedule ) ) {
		wp_schedule_event( time(), $order_schedule, 'ced_onbuy_order_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_order_scheduler_job_' . $shop_id, $shop_id );
	}if ( ! empty( $product_sync_schedule ) && ! empty( $ced_onbuy_product_sync_schedule_key ) ) {
		wp_schedule_event( time(), $product_sync_schedule, 'ced_onbuy_product_sync_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_product_sync_scheduler_job_' . $shop_id, $shop_id );
		update_option( 'ced_onbuy_product_sync_scheduler_key_' . $shop_id, $ced_onbuy_product_sync_schedule_key );
	}if ( ! empty( $auto_product_schedule ) ) {
		wp_schedule_event( time(), $auto_product_schedule, 'ced_onbuy_auto_product_upload_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_auto_product_upload_scheduler_job_' . $shop_id, $shop_id );
	}if ( ! empty( $check_win_price_schedule ) ) {
		wp_schedule_event( time(), $check_win_price_schedule, 'ced_onbuy_check_winning_price_scheduler_job_' . $shop_id );
		update_option( 'ced_onbuy_check_winning_price_scheduler_job_' . $shop_id, $shop_id );
	}

	echo '<div class="notice notice-success" ><p>' . esc_html( __( 'Settings Saved Successfully', 'onbuy-integration-by-cedcommerce' ) ) . '</p></div>';
}
$attributes = wc_get_attribute_taxonomies();
if ( isset( $shop_details['seller_deliveries'] ) && ! empty( $shop_details['seller_deliveries'] ) ) {
	$seller_deliveries = json_decode( $shop_details['seller_deliveries'], true );
	if ( isset( $seller_deliveries ) && ! empty( $seller_deliveries ) ) {
		foreach ( $seller_deliveries as $key => $value ) {
			$deliveries_option[ $value['delivery_type_id'] ] = $value['template_name'];
		}
		$deliveries_option = array_unique( $deliveries_option );
	}
}
$addedMetaKeys = get_option( 'ced_onbuy_selected_metakeys', false );
if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $metaKey ) {
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}
$render_data_on_global_settings = get_option( 'ced_onbuy_global_settings_' . $shop_id, false );
?>

	<div class="ced_onbuy_heading">
	<?php esc_html_e( get_onbuy_instuctions_html( 'METAKEYS AND ATTRIBUTES LIST', 'onbuy-integration-by-cedcommerce' ) ); ?>
	<div class="ced_onbuy_child_element">

		<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
			<label class="basic_heading ced_onbuy_render_meta_key_search_box_toggle"><?php esc_html_e( 'SEARCH FOR PRODUCT METAKEYS AND ATTRIBUTES FOR MAPPING', 'onbuy-integration-by-cedcommerce' ); ?></label>
			<div class="ced_onbuy_render_meta_key_search_box">
				<table class="wp-list-table widefat fixed striped">
					<tr>
						<td><label>Enter the product name</label></td>
						<td colspan="2"><input type="text" name="" id="ced_onbuy_search_product_name">
							<ul class="ced-onbuy-search-product-list">
								</ul>
						</td>
					</tr>
				</table>
				<div class="ced_onbuy_render_meta_keys_content1">
					<?php

						$meta_keys_to_be_displayed = get_option( 'ced_onbuy_metakeys_to_be_displayed', array() );

						$added_meta_keys = get_option( 'ced_onbuy_selected_metakeys', array() );

						$metakey_html = ced_onbuy_render_html( $meta_keys_to_be_displayed, $added_meta_keys );
						print_r( $metakey_html );
					?>
				</div>
			</div>
		</div>
	</div>
	</div>


<form method="post" action="">
	<?php wp_nonce_field( 'global_settings', 'global_settings_submit' ); ?>

	<div class="ced_onbuy_heading">
										
		<label class="basic_heading ced_onbuy_render_order_setting_toggle"><?php esc_html_e( get_onbuy_instuctions_html( 'ORDER SETTING', 'onbuy-integration-by-cedcommerce' ) ); ?></label>
		<div class="ced_onbuy_render_order_setting_content ced_onbuy_child_element">
			<table class="wp-list-table fixed widefat">
				<tbody class="ced_onbuy_setting" style="<?php echo esc_attr( $style ); ?>" >

					<tr>
						<?php
						$onbuy_order_status = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_order_status'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_order_status'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Onbuy Orders Status To Sync', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_order_status]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_onbuy_order_status">
								<option value=""><?php esc_html_e( '--Select--', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'all' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="all"><?php esc_html_e( 'All', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<!-- <option <?php echo ( 'open' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="open"><?php esc_html_e( 'Open', 'onbuy-integration-by-cedcommerce' ); ?></option> -->
								<option <?php echo ( 'awaiting_dispatch' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="awaiting_dispatch"><?php esc_html_e( 'Awaiting Dispatch', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'dispatched' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="dispatched"><?php esc_html_e( 'Dispatched', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'complete' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="complete"><?php esc_html_e( 'Complete', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'cancelled' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="cancelled"><?php esc_html_e( 'Cancelled', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'cancelled_by_seller' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="cancelled_by_seller"><?php esc_html_e( 'Cancelled By Seller', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'cancelled_by_buyer' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="cancelled_by_buyer"><?php esc_html_e( 'Cancelled By Buyer', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'partially_dispatched' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="partially_dispatched"><?php esc_html_e( 'Partially Dispatched', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'partially_refunded' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="partially_refunded"><?php esc_html_e( 'Partially Refunded', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'refunded' == $onbuy_order_status ) ? 'selected' : ''; ?>  value="refunded"><?php esc_html_e( 'Refunded', 'onbuy-integration-by-cedcommerce' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<?php
						$restrict_woo_emails = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_restrict_woo_emails'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_restrict_woo_emails'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Woocommerce Mail Restriction For OnBuy Orders', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_restrict_woo_emails]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_onbuy_restrict_woo_emails">
								<option value=""><?php esc_html_e( '--Select--', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'enable' == $restrict_woo_emails ) ? 'selected' : ''; ?>  value="enable"><?php esc_html_e( 'Enable', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'disable' == $restrict_woo_emails ) ? 'selected' : ''; ?>  value="disable"><?php esc_html_e( 'Disabled', 'onbuy-integration-by-cedcommerce' ); ?></option>
							</select>
						</td>
					</tr>
				
					<?php

					$ced_onbuy_order_statuses = array( 'All', 'Awaiting Dispatch', 'Dispatched', 'Complete', 'Cancelled', 'Cancelled By Seller', 'Cancelled By Buyer', 'Partially Dispatched', 'Partially Refunded', 'Refunded' );
					$ced_woo_order_statuses   = wc_get_order_statuses();

					?>
					<tr>
						<th><b><?php esc_html_e( 'ONBUY ORDER STATUS', 'onbuy-integration-by-cedcommerce' ); ?></b></th>
						<th><b><?php esc_html_e( 'MAPPED WITH WOOCOMMERCE STATUS', 'onbuy-integration-by-cedcommerce' ); ?></b></th>
					</tr>
					<?php
					foreach ( $ced_onbuy_order_statuses as $onbuy_status ) {

						$ced_mapped_order_status = isset( $render_data_on_global_settings[ $shop_id ]['_ced_mapped_order_status'][ $onbuy_status ] ) ? sanitize_text_field( $render_data_on_global_settings[ $shop_id ]['_ced_mapped_order_status'][ $onbuy_status ] ) : '';

						?>
					<tr>
						
						<td>
							<label><?php esc_html_e( $onbuy_status, 'onbuy-integration-by-cedcommerce' ); ?></label>
						</td>
					
						<td>
							
							<select name='ced_onbuy_global_settings[_ced_mapped_order_status][<?php echo esc_html( $onbuy_status ); ?>]' >
								<option value="">--Select--</option>
								<?php
								if ( ! empty( $ced_woo_order_statuses ) ) {
									foreach ( $ced_woo_order_statuses as $order_key => $order_value ) {
										if ( $ced_mapped_order_status == $order_key ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $order_key ); ?>"><?php echo esc_attr( $order_value ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
						<?php } ?>
					</tr>
				</tbody>
			</table>	
		</div>
	</div>

	<div class="ced_onbuy_heading">

		<label class="basic_heading ced_onbuy_render_scheduler_setting_toggle"><?php esc_html_e( get_onbuy_instuctions_html( 'SCHEDULER SETTING', 'onbuy-integration-by-cedcommerce' ) ); ?></label>
		<div class="ced_onbuy_render_scheduler_setting_content ced_onbuy_child_element">
			<table class="wp-list-table fixed widefat">
				
				<tbody class="ced_onbuy_scheduler_info">
					<tr>
						<?php
						$inventory_schedule = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_inventory_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_inventory_schedule_info'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Inventory/Price Sync Scheduler', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_inventory_schedule_info]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_onbuy_inventory_schedule_info">
								<option <?php echo ( '0' == $inventory_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'daily' == $inventory_schedule ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'twicedaily' == $inventory_schedule ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_6min' == $inventory_schedule ) ? 'selected' : ''; ?> value="ced_onbuy_6min"><?php esc_html_e( 'Every 6 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_10min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_10min"><?php esc_html_e( 'Every 10 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_15min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_15min"><?php esc_html_e( 'Every 15 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_30min' == $inventory_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_30min"><?php esc_html_e( 'Every 30 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>

							</select>
						</td>
					</tr>

					<tr>
						<?php
						$order_schedule = isset( $render_data_on_global_settings[ $shop_id ]['order_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['order_schedule_info'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Order Sync Scheduler', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[order_schedule_info]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="order_schedule_info">
								<option <?php echo ( '0' == $order_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'daily' == $order_schedule ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'twicedaily' == $order_schedule ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_6min' == $order_schedule ) ? 'selected' : ''; ?> value="ced_onbuy_6min"><?php esc_html_e( 'Every 6 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_10min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_10min"><?php esc_html_e( 'Every 10 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_15min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_15min"><?php esc_html_e( 'Every 15 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_30min' == $order_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_30min"><?php esc_html_e( 'Every 30 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>

							</select>
						</td>
					</tr>

					<tr>
						<?php
						$product_sync_schedule     = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_info'] : '';
						$product_sync_schedule_key = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_key'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_product_sync_schedule_key'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Existing Product Sync Scheduler', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_product_sync_schedule_info]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_onbuy_product_sync_schedule_info">
								<option <?php echo ( '0' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'daily' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'twicedaily' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_6min' == $product_sync_schedule ) ? 'selected' : ''; ?> value="ced_onbuy_6min"><?php esc_html_e( 'Every 6 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_10min' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_10min"><?php esc_html_e( 'Every 10 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_15min' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_15min"><?php esc_html_e( 'Every 15 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_30min' == $product_sync_schedule ) ? 'selected' : ''; ?>  value="ced_onbuy_30min"><?php esc_html_e( 'Every 30 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>

							</select>
						</td>
						<td>
							<b>Where Identifier Stored  </b>
							<select name="ced_onbuy_global_settings[ced_onbuy_product_sync_schedule_key]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_onbuy_product_sync_schedule_key">

								<option value="">--Select--</option>
								<?php
								if ( ! empty( $attrOptions ) ) {
									foreach ( $attrOptions as $key => $value ) {
										if ( $product_sync_schedule_key == $key ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
					</tr>

					<tr>

						<?php
						$auto_upload = isset( $render_data_on_global_settings[ $shop_id ]['auto_upload_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['auto_upload_schedule_info'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Auto upload product Sync Scheduler', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[auto_upload_schedule_info]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="auto_upload_schedule_info">
								<option <?php echo ( '0' == $auto_upload ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'daily' == $auto_upload ) ? 'selected' : ''; ?>  value="daily"><?php esc_html_e( 'Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'twicedaily' == $auto_upload ) ? 'selected' : ''; ?>  value="twicedaily"><?php esc_html_e( 'Twice Daily', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_6min' == $auto_upload ) ? 'selected' : ''; ?> value="ced_onbuy_6min"><?php esc_html_e( 'Every 6 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_10min' == $auto_upload ) ? 'selected' : ''; ?>  value="ced_onbuy_10min"><?php esc_html_e( 'Every 10 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_15min' == $auto_upload ) ? 'selected' : ''; ?>  value="ced_onbuy_15min"><?php esc_html_e( 'Every 15 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_30min' == $auto_upload ) ? 'selected' : ''; ?>  value="ced_onbuy_30min"><?php esc_html_e( 'Every 30 Minutes', 'onbuy-integration-by-cedcommerce' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="ced_onbuy_heading">

		<?php esc_html_e( get_onbuy_instuctions_html( 'BUY BOX SETTING', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element">
			<table class="wp-list-table fixed widefat">
				
				
				<tbody class="">
					<tr>
						<?php
						$ced_win_price = isset( $render_data_on_global_settings[ $shop_id ]['ced_win_price_schedule_info'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_win_price_schedule_info'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Check Winning price from OnBuy', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_win_price_schedule_info]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" data-fieldId="ced_win_price_schedule_info">
								<option <?php echo ( '0' == $ced_win_price ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disable', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'ced_onbuy_30min' == $ced_win_price ) ? 'selected' : ''; ?>  value="ced_onbuy_30min"><?php esc_html_e( 'Enable', 'onbuy-integration-by-cedcommerce' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<?php
						$ced_update_decreased_price = isset( $render_data_on_global_settings[ $shop_id ]['ced_update_decreased_price'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_update_decreased_price'] : '';


						$style = 'none';
						if ( 'yes' == $ced_update_decreased_price ) {
							$style = 'table-row';
						}
						?>
						<th>
							<label><?php esc_html_e( 'Want to update decreased price?', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_update_decreased_price]" class="ced_onbuy_select ced_onbuy_global_select_box select_boxes_scheduler" id="ced_update_decreased_price">
								<option value=""><?php esc_html_e( '--Select--', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'yes' == $ced_update_decreased_price ) ? 'selected' : ''; ?>  value="yes"><?php esc_html_e( 'Yes', 'onbuy-integration-by-cedcommerce' ); ?></option>
								
							</select>
						</td>
					</tr>
					<tr class="ced_onbuy_enable_buybox_setting" style="display: <?php echo esc_html( $style ); ?>;">
						<?php
						$ced_onbuy_buybox_price_type = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_buybox_price_type'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_buybox_price_type'] : '';
						?>
						<th>
							<label><?php esc_html_e( 'Decreased Price by', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_buybox_price_type]" id = "ced_buybox_type">
								<option value=""><?php esc_html_e( '--Select--', 'onbuy-integration-by-cedcommerce' ); ?></option>
								<option <?php echo ( 'Fixed_Decreased' == $ced_onbuy_buybox_price_type ) ? 'selected' : ''; ?> value="Fixed_Decreased"><?php esc_html_e( 'Fixed amount of Buy Box Price', 'onbuy-integration-by-cedcommerce' ); ?></option>
			
								<option <?php echo ( 'Percentage_Decreased' == $ced_onbuy_buybox_price_type ) ? 'selected' : ''; ?> value="Percentage_Decreased"><?php esc_html_e( 'Fixed percentage of Buy Box Price', 'onbuy-integration-by-cedcommerce' ); ?></option>
							</select>
						</td>
					</tr>
					<?php
					$style = 'none';
					if ( 'Fixed_Decreased' == $ced_onbuy_buybox_price_type || 'Percentage_Decreased' == $ced_onbuy_buybox_price_type ) {
						$style = 'table-row';
					}
					?>
					<tr class="ced_onbuy_buybox_decreased_price" style="display: <?php echo esc_html( $style ); ?>;">
						<th>
							<?php
							$ced_onbuy_buybox_price = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_buybox_price'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_buybox_price'] : '';

							?>
							<label><?php esc_html_e( 'Enter value to be decreased', 'onbuy-integration-by-cedcommerce' ); ?></label>
						</th>
						<td>
							<input placeholder="<?php esc_html_e( 'Enter value', 'onbuy-integration-by-cedcommerce' ); ?>" class="ced_onbuy_disabled_text_field ced_onbuy_inputs" type="text" value="<?php echo esc_attr( $ced_onbuy_buybox_price ); ?>" id="ced_onbuy_buybox_price" name="ced_onbuy_global_settings[ced_onbuy_buybox_price]">
						</td>
						<th>
							<?php
							$ced_onbuy_max_limit = isset( $render_data_on_global_settings[ $shop_id ]['ced_onbuy_max_limit'] ) ? $render_data_on_global_settings[ $shop_id ]['ced_onbuy_max_limit'] : '';

							?>
							<b><label><?php esc_html_e( 'Set maximum limit to be decreased', 'onbuy-integration-by-cedcommerce' ); ?></label></b>
						</th>
						<td>
							<select name="ced_onbuy_global_settings[ced_onbuy_max_limit]">
								<option value="">--Select--</option>
								<?php
								if ( ! empty( $attrOptions ) ) {
									foreach ( $attrOptions as $key => $value ) {
										if ( $ced_onbuy_max_limit == $key ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
					</tr>
				</tbody>
				
			</table>
		</div>
	</div>

	<div class="ced-button-wrapper">
		<button id="save_global_settings"  name="global_settings" class="button-primary" ><?php esc_html_e( 'Save', 'onbuy-integration-by-cedcommerce' ); ?></button>
	</div>
</form>
