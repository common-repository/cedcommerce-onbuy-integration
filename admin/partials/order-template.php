<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

global $post;
$order_id                 = isset( $post->ID ) ? intval( $post->ID ) : '';
$onbuy_onbuy_order_status = get_post_meta( $order_id, '_onbuy_onbuy_order_status', true );
$_onbuy_order_details     = get_post_meta( $order_id, '_onbuy_order_details', true );
$merchant_order_id        = get_post_meta( $order_id, '_ced_onbuy_order_id', true );
$shop_id                  = get_post_meta( $order_id, 'ced_onbuy_order_shop_id', true );
$fulfillment_node         = get_post_meta( $order_id, 'fulfillment_node', true );
$order_detail             = get_post_meta( $order_id, '_onbuy_order_complete_details', true );
$order_item               = get_post_meta( $order_id, '_onbuy_order_itemdata', true );
$provider_list            = CED_ONBUY_DIRPATH . 'admin/onbuy/json/';
$provider_list            = $provider_list . 'provider.json';
if ( file_exists( $provider_list ) ) {
	$provider_list      = file_get_contents( $provider_list );
	$shipping_providers = json_decode( $provider_list, true );
}
$number_items             = 0;
$onbuy_onbuy_order_status = get_post_meta( $order_id, '_onbuy_onbuy_order_status', true );
$onbuy_order_status       = get_post_meta( $order_id, '_onbuy_order_status', true );
if ( empty( $onbuy_onbuy_order_status ) || 'Fetched' == $onbuy_onbuy_order_status ) {
	$onbuy_onbuy_order_status = __( 'Created', 'ced-onbuy' );
}
?>
<div id="onbuy_onbuy_order_settings" class="panel woocommerce_options_panel">
	<div class="ced_onbuy_loader">
		<img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_onbuy_loading_img" >
	</div>
	<div class="options_group">
		<p class="form-field">
			<h3><center>
			<?php
			esc_attr_e( 'ONBUY ORDER STATUS : ', 'ced-onbuy' );
			echo esc_attr( strtoupper( $onbuy_order_status ) );
			?>
			</center></h3>
		</p>
	</div>
	<?php
	$order_status = get_post_meta( $order_id, '_onbuy_order_status_template', true );
	if ( empty( $order_status ) ) {
		?>
	<div class="ced_onbuy_order_template">
		<table class="wp-list-table widefat fixed">
			<thead>
				<th>Complete Dispatch</th>
				<th>Partial Dispatch</th>
				<th>Cancel Order</th>
				<th>Refund Order</th>

			</thead>
			<tr>
				<td><input type="button" name="" data-id="ced_onbuy_complete_dispatch_template" class="ced_onbuy_button ced_onbuy_order_template_sbutton" value="Complete Dispatch"></td>
				<td><input type="button" name="" data-id="ced_onbuy_partials_dispatch_template" class="ced_onbuy_button ced_onbuy_order_template_sbutton" value="Partial Dispatch"></td>
				<td><input type="button" name="" data-id="ced_onbuy_cancel_template" class="ced_onbuy_button ced_onbuy_order_template_sbutton" value="Cancel Order"></td>
				<td><input type="button" name="" data-id="ced_onbuy_refund_template" class="ced_onbuy_button ced_onbuy_order_template_sbutton" value="Refund Order"></td>
			</tr>
		</table>
	</div>
		<?php
	}
	$complete_dispatch = ( 'complete_dispatch' == $order_status ) ? 'display:block' : 'display:none';
	$partials_dispatch = ( 'partials_dispatch' == $order_status ) ? 'display:block' : 'display:none';
	$cancel            = ( 'cancel' == $order_status ) ? 'display:block' : 'display:none';
	$refund            = ( 'refund' == $order_status ) ? 'display:block' : 'display:none';
	?>


<div id="ced_onbuy_complete_dispatch_template" style = "<?php echo esc_attr( $complete_dispatch ); ?>" >
	<input type="hidden" id="onbuy_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
	<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
	<input type="hidden" id="onbuy_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
	<h2 class="title"><?php esc_attr_e( 'Shipment Information', 'ced-onbuy' ); ?> </h2>
	<div id="ced_onbuy_complete_order_shipping">
		<table class="wp-list-table widefat fixed striped">
			<tbody>
				<?php
				$tracking_number = isset( $_onbuy_order_details['trackingNo'] ) ? $_onbuy_order_details['trackingNo'] : '';
				$provider        = isset( $_onbuy_order_details['provider'] ) ? $_onbuy_order_details['provider'] : '';
				?>
				<tr>
					<td><b><?php esc_attr_e( 'Tracking Number', 'ced-onbuy' ); ?></b></td>
					<td><input type="text" id="onbuy_onbuy_tracking_number_complete" value="<?php echo esc_attr( $tracking_number ); ?>"></td>
				</tr>
				<tr>
					<td><b><?php esc_attr_e( 'Shipping Provider', 'ced-onbuy' ); ?></b></td>
					<td>
						<select id="onbuy_shipping_providers_complete" name="onbuy_shipping_providers_complete">
							<?php
							$options = "<option value='0'>--Select Shipiing Provider--</option>";
							foreach ( $shipping_providers as $key => $value ) {
								if ( $value['tracking_id'] == $provider ) {
									$options .= '<option selected value="' . $value['tracking_id'] . '" data-url="' . $value['tracking_url'] . '">' . $value['name'];
								} else {
									$options .= '<option  value="' . $value['tracking_id'] . '" data-url="' . $value['tracking_url'] . '">' . $value['name'];
								}
							}
							print_r( $options );
							?>
						</select>

					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
	if ( empty( $order_status ) ) {
		?>
	<input data-order-type ="complete" type="button" class="button" id="ced_onbuy_shipment_submit" value="Submit Shipment">
		<?php
	}
	?>
</div>


<div id="ced_onbuy_partials_dispatch_template" style = "<?php echo esc_attr( $partials_dispatch ); ?>" >
	<input type="hidden" id="onbuy_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
	<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
	<input type="hidden" id="onbuy_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
	<h2 class="title"><?php esc_attr_e( 'Partial Shipment Information', 'ced-onbuy' ); ?>:                   
	</h2>
	<table class="wp-list-table widefat fixed striped">
		<tbody>
			<?php
			$tracking_number = isset( $_onbuy_order_details['trackingNo'] ) ? $_onbuy_order_details['trackingNo'] : '';
			$provider        = isset( $_onbuy_order_details['provider'] ) ? $_onbuy_order_details['provider'] : '';
			?>
			<tr>
				<td><b><?php esc_attr_e( 'Tracking Number', 'ced-onbuy' ); ?></b></td>
				<td><input type="text" id="onbuy_onbuy_tracking_number_partial" value=" <?php echo esc_attr( $tracking_number ); ?>"></td>
			</tr>
			<tr>
				<td><b><?php esc_attr_e( 'Shipping Provider', 'ced-onbuy' ); ?></b></td>
				<td>
					<select id="onbuy_shipping_providers_partial" name="onbuy_shipping_providers_partial">
						<?php
						$options = "<option value='0'>--Select Shipiing Provider--</option>";
						foreach ( $shipping_providers as $key => $value ) {
							if ( $value['tracking_id'] == $provider ) {
								$options .= '<option selected value="' . $value['tracking_id'] . '" data-url="' . $value['tracking_url'] . '">' . $value['name'];
							} else {
								$options .= '<option  value="' . $value['tracking_id'] . '" data-url="' . $value['tracking_url'] . '">' . $value['name'];
							}
						}
						print_r( $options );
						?>
					</select>

				</td>
			</tr>
		</tbody>
	</table>
	<table cellspacing="0" cellpadding="0" class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th class="line_cost sortable"><?php esc_attr_e( 'Sku', 'ced-onbuy' ); ?></th>
				<th class="line_cost sortable"><?php esc_attr_e( 'Qty Order', 'ced-onbuy' ); ?></th>
				<th class="line_cost sortable"><?php esc_attr_e( 'Qty To Shipped', 'ced-onbuy' ); ?></th>
			</tr>
		</thead>
		<tbody id="onbuy_order_line_items">
			<?php
			$count = 0;
			if ( is_array( $order_item['ItemsArray'] ) && ! empty( $order_item['ItemsArray'] ) ) {
				foreach ( $order_item['ItemsArray'] as $valdata ) {
					$sku = $valdata['Sku'];
					if ( $sku ) {
						global $wpdb;
						$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $sku ) );
						if ( ! empty( $product_id ) ) {
							$valdata['ID'] = $product_id;
						}
					}
					$order_qty  = $valdata['OrderedQty'];
					$cancel_qty = $valdata['CancelQty'];
					$unq_id     = ++$count;
					?>
					<tr id="<?php echo esc_attr( $unq_id ); ?>">

						<td class="line_cost sortable">
							<input type="text" size="50" name="sku<?php echo esc_attr( $unq_id ); ?>" value="<?php echo esc_attr( $sku ); ?>" data-p-id = "<?php echo esc_attr( $valdata['ID'] ); ?>" id="sku<?php echo esc_attr( $unq_id ); ?>" class="item_sku onbuy_order_set" readonly/>
						</td>

						<td  class="line_cost sortable">
							<input type="text" size="50" name="qty_order<?php echo esc_attr( $unq_id ); ?>" value="<?php echo esc_attr( $order_qty ); ?>" id="qty_order<?php echo esc_attr( $unq_id ); ?>" class="item_qty_order onbuy_order_set" readonly/>
						</td>
						<td  class="line_cost sortable">
							<input type="text"  size="50" name="qty_shipped<?php echo esc_attr( $unq_id ); ?>" value="<?php echo esc_attr( $order_qty ); ?>" id="qty_shipped<?php echo esc_attr( $unq_id ); ?>" class="item_qty_shipped onbuy_order_set" />
						</td>
					</tr>
					<?php
				}
			}
			?>
			</tbody>	
		</table>
		<?php
		if ( empty( $order_status ) ) {
			?>
		<input data-order-type ="partial" type="button" class="button" id="ced_onbuy_shipment_submit" value="Submit Shipment">
		<?php } ?>
	</div>


	<div id="ced_onbuy_cancel_template" style = "<?php echo esc_attr( $cancel ); ?>" >
		<?php	if ( 'cancel' == $order_status ) { ?>
		<h1 style="text-align:center;"><?php esc_attr_e( 'ORDER CANCELLED ', 'onbuy-integration-by-cedcommerce' ); ?></h1>
		<?php	} else { ?>
		<input type="hidden" id="onbuy_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
		<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
		<input type="hidden" id="onbuy_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<th>Cancellation reason</th>
				<th>Cancellation Additional Info</th>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="cancel_reason_id<?php echo esc_attr( $unq_id ); ?>" id="cancel_reason_id">
							<option value="0">--Select Reason--</option>
							<option value="1">Out of stock</option>
							<option value="2">Buyer cancelled order</option>
							<option value="3">Undeliverable address</option>
							<option value="4">Pricing error</option>
							<option value="5">Other issue (please specify)</option>
						</select>
					</td>
					<td>
						<input type="text" name="qty_cancel" size="50" placeholder="Information" id="cancel_info" class="ced_onbuy_cancel_info"/>
					</td>
				</tr>
			</tbody>
		</table>
			<?php
			if ( empty( $order_status ) ) {
				?>
		<input data-order_id ="<?php echo esc_attr( $order_id ); ?>" type="button" class="button" id="ced_onbuy_cancel_submit" value="Cancel Order">
				<?php
			}
		}
		?>
	</div>

	<div id="ced_onbuy_refund_template" style = "<?php echo esc_attr( $refund ); ?>" >
		<?php	if ( 'refund' == $order_status ) { ?>
		<h1 style="text-align:center;"><?php esc_attr_e( 'ORDER REFUND ', 'onbuy-integration-by-cedcommerce' ); ?></h1>
		<?php	} else { ?>
		<input type="hidden" id="onbuy_orderid" value="<?php echo esc_attr( $merchant_order_id ); ?>" readonly>
		<input type="hidden" id="woocommerce_orderid" value="<?php echo esc_attr( $order_id ); ?>">
		<input type="hidden" id="onbuy_shop_id" value="<?php echo esc_attr( $shop_id ); ?>">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<th>Reason for the Refund</th>
				<th>Refund Amount</th>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="refund_reason_id<?php echo esc_attr( $unq_id ); ?>" id="refund_reason_id">
							<option value="0">--Select Reason--</option>
							<option value="1">Customer Return</option>
							<option value="2">Item not Delivered</option>
							<option value="3">Item is Damaged/Faulty</option>
							<option value="4">Incorrect Item Sent</option>
							<option value="5">Other issue (please specify)</option>
							<option value="6">Order Cancelled</option>

						</select>
					</td>
					<td>
						<input type="text" name="qty_refund" size="50" placeholder="Value" id="refund_info" class="ced_onbuy_refund_info"/>
					</td>
				</tr>
			</tbody>
		</table>
			<?php
			if ( empty( $order_status ) ) {
				?>
		<input data-order_id ="<?php echo esc_attr( $order_id ); ?>" type="button" class="button" id="ced_onbuy_refund_submit" value="Refund Order">
				<?php
			}
		}
		?>
	</div>
</div>    
