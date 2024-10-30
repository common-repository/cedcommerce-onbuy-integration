<?php
/**
 * Category Mapping
 *
 * @package  OnBuy_Integration_By_CedCommerce
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

$file = CED_ONBUY_DIRPATH . 'admin/partials/ced-instructions.php';
if ( file_exists( $file ) ) {
	include_once $file;
}
$woo_store_categories = get_terms( 'product_cat' );
$shop_id              = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
$ced_onbuy_categories = array();


?>
<div id="profile_create_message"></div>
 
<div class="ced_onbuy_category_mapping_wrapper" id="ced_onbuy_category_mapping_wrapper">

	<div class="ced_onbuy_store_categories_listing" id="ced_onbuy_store_categories_listing">
	<div class="ced_onbuy_heading">
		<?php wp_nonce_field( 'save_category_search', 'save_category_submit' ); ?>
		<?php echo esc_html_e( get_onbuy_instuctions_html( 'SEARCH ONBUY CATEGORIES' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<table class="wp-list-table widefat fixed  ced_onbuy_global_settings_fields_table">
				
				<tbody>
					<tr>
						<th>
							<label><?php esc_html_e( 'Enter Keyword To Search', 'onbuy-integration-by-cedcommerce' ); ?></label>
							<span class="ced_ombuy_tooltip">[ Type in some keywords in order to get the Onbuy category list. For eg : Clothing ]</span>
						</th>
						<td>
							<input type="text" name="" id="ced_onbuy_category_search" class="ced_onbuy_inputs">
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
		<table class="wp-list-table widefat fixed striped posts ced_onbuy_store_categories_listing_table" id="ced_onbuy_store_categories_listing_table">
			<thead>
				<th ><b id = "check_select" style = "display:none"><?php esc_html_e( 'Select', 'onbuy-integration-for-woocommerce' ); ?></b></th>
				<th><b><?php esc_html_e( 'Store Categories', 'onbuy-integration-for-woocommerce' ); ?></b></th>
				<th colspan="4"><b><?php esc_html_e( 'Mapped with OnBuy Category', 'onbuy-integration-for-woocommerce' ); ?></b></th>
</thead>
			
							
						
			<tbody>
			
				<?php
				foreach ( $woo_store_categories as $key => $value ) {
					?>
					<tr class="ced_onbuy_store_category" id="<?php echo esc_attr( 'ced_onbuy_store_category_' . $value->term_id ); ?>">
						<td>
							<input type="checkbox" class="ced_onbuy_select_store_category_checkbox" name="ced_onbuy_select_store_category_checkbox[]" style = "display:none" data-categoryID="<?php echo esc_attr( $value->term_id ); ?>" ></input>
						</td>
						<td>
							<span class="ced_onbuy_store_category_name"><?php echo esc_attr( $value->name ); ?></span>
						</td>
						<?php
						$shop_id                        = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
						$category_mapped_to             = get_term_meta( $value->term_id, 'ced_onbuy_mapped_category', true );
						$already_mapped_categories_name = get_option( 'ced_woo_onbuy_mapped_categories_names', array() );
						$category_mapped_name_to        = isset( $already_mapped_categories_name[ $category_mapped_to ] ) ? $already_mapped_categories_name[ $category_mapped_to ] : '';
						if ( ! empty( $category_mapped_to ) && null != $category_mapped_to && ! empty( $category_mapped_name_to ) && null != $category_mapped_name_to ) {
							?>
							<td colspan="4">
								<span>
									<b><?php echo esc_attr( $category_mapped_name_to ); ?></b>
								</span>
							</td>
							<?php
						} else {
							?>
							<td colspan="4">
								<span class="ced_onbuy_category_not_mapped">
									<?php esc_html_e( 'Category Not Mapped', 'onbuy-integration-for-woocommerce' ); ?>
								</span>
							</td>
							<?php
						}
						?>
					</tr>

					<tr class="ced_onbuy_categories" id="<?php echo esc_attr( 'ced_onbuy_categories_' . $value->term_id ); ?>">
					<td></td>
						<td data-catlevel="1">
							<select class="ced_onbuy_level1_category ced_onbuy_select_category  select_boxes_cat_map" name="ced_onbuy_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>" data-shop_id="<?php echo esc_attr( $shop_id ); ?>" >	
							</select>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="ced_onbuy_category_mapping_header ced_onbuy_hidden" id="ced_onbuy_category_mapping_header">
		<a class="button-primary" href="" data-client-id="<?php echo esc_attr( $shop_id ); ?>" id="ced_onbuy_cancel_category_button">
			<?php esc_html_e( 'Cancel', 'onbuy-integration-for-woocommerce' ); ?>
		</a>
		<button class="button-primary" data-client-id="<?php echo esc_attr( $shop_id ); ?>" id="ced_onbuy_save_category_button">
			<?php esc_html_e( 'Save Maping', 'onbuy-integration-for-woocommerce' ); ?>
		</button>
	</div>
</div>

