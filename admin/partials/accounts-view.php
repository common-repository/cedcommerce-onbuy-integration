<?php
/**
 * Account Details
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
	include_once $file;
}

?>
<div class="ced_onbuy_account_configuration_wrapper">	
	<div class="ced_onbuy_account_configuration_fields">		
		<table class="wp-list-table widefat fixed striped ced_onbuy_account_configuration_fields_table">
			<tbody>				
				<tr>
					<th>
						<label><?php esc_html_e( 'Seller Id', 'onbuy-integration-by-cedcommerce' ); ?></label>
					</th>
					<td>
						<label><?php echo esc_attr( $shop_details['shop_id'] ); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Site Id', 'onbuy-integration-by-cedcommerce' ); ?></label>
					</th>
					<td>
						<label><?php echo esc_attr( json_decode( $shop_details['seller_data'] )->site_id ); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Seller Name', 'onbuy-integration-by-cedcommerce' ); ?></label>
					</th>
					<td>
						<label><?php echo esc_attr( json_decode( $shop_details['seller_data'] )->seller_name ); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Seller Company', 'onbuy-integration-by-cedcommerce' ); ?></label>
					</th>
					<td>
						<label><?php echo esc_attr( json_decode( $shop_details['seller_data'] )->company_name ); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php esc_html_e( 'Account Status', 'onbuy-integration-by-cedcommerce' ); ?></label>
					</th>
					<td>
						<?php
						if ( isset( $shop_details['account_status'] ) && 'inactive' == $shop_details['account_status'] ) {
							$inactive = 'selected';
							$active   = '';
						} else {
							$active   = 'selected';
							$inactive = '';
						}
						?>
						<select class="ced_onbuy_select select_boxes" id="ced_onbuy_account_status">
							<option><?php esc_html_e( '--Select Status--', 'onbuy-integration-by-cedcommerce' ); ?></option>
							<option value="active" <?php echo esc_attr( $active ); ?>><?php esc_html_e( 'Active', 'onbuy-integration-by-cedcommerce' ); ?></option>
							<option value="inactive" <?php echo esc_attr( $inactive ); ?>><?php esc_html_e( 'Inactive', 'onbuy-integration-by-cedcommerce' ); ?></option>
						</select>
						<a class="ced_onbuy_update_status_message" data-id="<?php echo esc_attr( $shop_details['id'] ); ?>" id="ced_onbuy_update_account_status" href="javascript:void(0);"><?php esc_html_e( 'Update Account Status', 'onbuy-integration-by-cedcommerce' ); ?></a>
					</td>
				</tr>			
			</tbody>
		</table>
	</div>

</div>
