<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$profile_id = isset( $_GET['profile_id'] ) ? sanitize_text_field( wp_unslash( $_GET['profile_id'] ) ) : '';

global $wpdb;
$shop_id      = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
$table_name   = $wpdb->prefix . 'ced_onbuy_profiles';
$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id`=%d", $profile_id ), 'ARRAY_A' );

if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_onbuy_profile_save_button'] ) ) {

	if ( ! isset( $_POST['profile_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['profile_settings_submit'] ) ), 'ced_onbuy_profile_save_button' ) ) {
		return;
	}
	$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$profileName     = $sanitized_array['ced_onbuy_profile_name'];
	$marketplaceName = isset( $sanitized_array['marketplaceName'] ) ? $sanitized_array['marketplaceName'] : 'all';
	foreach ( $sanitized_array['ced_onbuy_required_common'] as $key ) {
		$arrayToSave = array();
		isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = $sanitized_array[ $key ][0] : $arrayToSave['default'] = '';
		if ( '_umb_' . $marketplaceName . '_subcategory' == $key ) {
			isset( $sanitized_array[ $key ] ) ? $arrayToSave['default'] = $sanitized_array[ $key ] : $arrayToSave['default'] = '';
		}
		if ( '_umb_onbuy_category' == $key && empty( $profile_id ) ) {
			$profileCategoryNames = array();
			for ( $i = 1; $i < 8; $i++ ) {
				$profileCategoryNames[] = isset( $sanitized_array[ 'ced_onbuy_level' . $i . '_category' ] ) ? $sanitized_array[ 'ced_onbuy_level' . $i . '_category' ] : '';
			}
			$CategoryNames = array();
			foreach ( $profileCategoryNames as $key1 => $value1 ) {
				$CategoryNames[] = explode( ',', $value1[0] );
			}
			foreach ( $CategoryNames as $key2 => $value2 ) {
				if ( ! empty( $CategoryNames[ $key2 ][0] ) ) {
					$profile_category_id = $CategoryNames[ $key2 ][0];
				}
			}
			$category_id = $profile_category_id;
			isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = $category_id : $arrayToSave['default'] = '';
			isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['units']   = $category_id : $arrayToSave['units'] = '';


		}
		isset( $sanitized_array[ $key . '_attibuteMeta' ] ) ? $arrayToSave['metakey'] = $sanitized_array[ $key . '_attibuteMeta' ] : $arrayToSave['metakey'] = 'null';
		$updateinfo[ $key ] = $arrayToSave;
	}

	$updateinfo['selected_product_id']            = isset( $sanitized_array['selected_product_id'] ) ? sanitize_text_field( wp_unslash( $sanitized_array['selected_product_id'] ) ) : '';
	$updateinfo['selected_product_name']          = isset( $sanitized_array['ced_sears_pro_search_box'] ) ? sanitize_text_field( wp_unslash( $sanitized_array['ced_sears_pro_search_box'] ) ) : '';
	$updateinfo['_umb_onbuy_category']['default'] = isset( $_GET['category_id'] ) ? sanitize_text_field( wp_unslash( $_GET['category_id'] ) ) : '';

	$updateinfo = json_encode( $updateinfo );
		echo '<div class="notice notice-success is-dismissible">
				<p>' . esc_attr( __( 'Profile saved Successfully!', 'sample-text-domain' ) ) . '</p>
			</div>';
		$wpdb->update(
			$table_name,
			array(
				'profile_name'   => $profileName,
				'profile_status' => 'Active',
				'profile_data'   => $updateinfo,

			),
			array( 'id' => $profile_id )
		);

}
$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id`=%s ", $profile_id ), 'ARRAY_A' );

if ( ! empty( $profile_data ) ) {
	$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );

	$profile_category_data = isset( $profile_category_data ) ? $profile_category_data : '';
	$profile_category_id   = isset( $profile_category_data['_umb_onbuy_category']['default'] ) ? (int) $profile_category_data['_umb_onbuy_category']['default'] : '';

	$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;

	$profile_category_id = isset( $_GET['category_id'] ) ? sanitize_text_field( wp_unslash( $_GET['category_id'] ) ) : '';

	$file = CED_ONBUY_DIRPATH . 'admin/onbuy/class-ced-onbuy.php';
	if ( file_exists( $file ) ) {
		include_once $file;
	}
	$ced_onbuy_instance                    = new Class_Ced_Onbuy_Manager();
	$onbuy_fetched_categories_fea          = $ced_onbuy_instance->ced_onbuy_get_category_features( $profile_category_id, $shop_id );
	$onbuy_fetched_categories_tech_details = $ced_onbuy_instance->ced_onbuy_get_category_tech_details( $profile_category_id, $shop_id );
	$fields                                = $ced_onbuy_instance->get_custom_products_fields();

	$onbuy_fetched_categories_fea          = isset( $onbuy_fetched_categories_fea['results'] ) ? $onbuy_fetched_categories_fea['results'] : '';
	$onbuy_fetched_categories_tech_details = isset( $onbuy_fetched_categories_tech_details['results'][0]['options'] ) ? $onbuy_fetched_categories_tech_details['results'][0]['options'] : '';
	if ( ! empty( $onbuy_fetched_categories_fea ) ) {
		update_option( 'ced_onbuy_fetched_categories_fea_' . $profile_category_id, $onbuy_fetched_categories_fea );
	}
	if ( ! empty( $onbuy_fetched_categories_tech_details ) ) {
		update_option( 'ced_onbuy_fetched_categories_tech_details_' . $profile_category_id, $onbuy_fetched_categories_tech_details );
	}
}
$attributes    = wc_get_attribute_taxonomies();
$attrOptions   = array();
$addedMetaKeys = get_option( 'ced_onbuy_selected_metakeys', array() );
$addedMetaKeys = array_merge( $addedMetaKeys, array( '_woocommerce_title', '_woocommerce_short_description', '_woocommerce_description' ) );

if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $metaKey ) {
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}

/* select dropdown setup */
ob_start();
$fieldID  = '{{*fieldID}}';
$selectId = $fieldID . '_attibuteMeta';
echo '<select id="' . esc_attr( $selectId ) . '" name="' . esc_attr( $selectId ) . '">';
echo '<option value="null"> -- select -- </option>';
if ( is_array( $attrOptions ) ) {
	foreach ( $attrOptions as $attrKey => $attrName ) :
		echo '<option value="' . esc_attr( $attrKey ) . '">' . esc_attr( $attrName ) . '</option>';
	endforeach;
}
echo '</select>';
$selectDropdownHTML = ob_get_clean();

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
<form action="" method="post">

	<?php wp_nonce_field( 'ced_onbuy_profile_save_button', 'profile_settings_submit' ); ?>

	<div class="ced_onbuy_heading">
		<?php echo esc_html_e( get_onbuy_instuctions_html( 'BASIC INFORMATION' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">
			<table class="wp-list-table fixed widefat ced_onbuy_config_table">
				<tr>
					<td>
						<label class="ced_ombuy_tooltip"><?php esc_html_e( 'Profile Name', 'woocommerce-onbuy-integration' ); ?></label>
					</td>
					<?php

					if ( isset( $profile_data['profile_name'] ) ) {
						?>
						<td>
							<input type="text" name="ced_onbuy_profile_name" value="<?php echo esc_attr( $profile_data['profile_name'] ); ?>">
						</td>
						<?php
					}
					?>
				</tr>
			</table>
		</div>
	</div>
	<div class="ced_onbuy_heading">
			<?php echo esc_html_e( get_onbuy_instuctions_html( 'PRODUCT EXPORT SETTINGS' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">
			<table class="wp-list-table ced_onbuy_global_settings">
				<tr>
						<td><b>Onbuy Attribute</b></td>
						<td><b>Default Value</b></td>
						<td><b>Pick Value From</b></td>
					</tr>
				<?php
				$attributes_to_escape       = array();
				$requiredInAnyCase          = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
				$global_settings_field_data = get_option( 'ced_onbuy_global_settings', '' );
				$marketPlace                = 'ced_onbuy_required_common';
				$productID                  = 0;
				$categoryID                 = '';
				$indexToUse                 = 0;
				if ( ! empty( $profile_data ) ) {
					$data = json_decode( $profile_data['profile_data'], true );
				}

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $key => $value ) {
						$isText   = true;
						$field_id = trim( $value['fields']['id'], '_' );
						if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
							$attributeNameToRender  = ucfirst( $value['fields']['label'] );
							$attributeNameToRender .= '<span class="ced_onbuy_wal_required"> [ Required ]</span>';
						} else {
							$attributeNameToRender = ucfirst( $value['fields']['label'] );
						}
						$is_required = isset( $value['fields']['is_required'] ) ? $value['fields']['is_required'] : false;
						$default     = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
						echo '<tr class="form-field _umb_id_type_field ">';
						if ( '_select' == $value['type'] ) {
							$valueForDropdown = $value['fields']['options'];
							if ( '_umb_id_type' == $value['fields']['id'] ) {
								unset( $valueForDropdown['null'] );
							}
							$ced_onbuy_instance->render_dropdown_html(
								$field_id,
								$attributeNameToRender,
								$valueForDropdown,
								$categoryID,
								$productID,
								$marketPlace,
								$value['fields']['description'],
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $default,
								),
								$is_required
							);
							$isText = true;

						} elseif ( '_text_input' == $value['type'] ) {
							$ced_onbuy_instance->render_input_text_html(
								$field_id,
								$attributeNameToRender,
								$categoryID,
								$productID,
								$marketPlace,
								$value['fields']['description'],
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $default,
								),
								$is_required
							);
						} elseif ( '_hidden' == $value['type'] ) {

							$profile_category_id = isset( $profile_category_id ) ? $profile_category_id : '';
							$ced_onbuy_instance->render_input_text_html_hidden(
								$field_id,
								$attributeNameToRender,
								$categoryID,
								$productID,
								$marketPlace,
								$value['fields']['description'],
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $profile_category_id,
								),
								$is_required
							);
							$isText = false;
						}

						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';
							if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && ! empty( $data[ $value['fields']['id'] ]['metakey'] ) ) {
								$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r( $updatedDropdownHTML );
						}
						echo '</td>';
						echo '</tr>';
					}
				}
				?>
			</table>
		</div>
	</div>
	<div class="ced_onbuy_heading">
			<?php echo esc_html_e( get_onbuy_instuctions_html( 'CATEGORY TECHNICAL DETAILS' ) ); ?>
		<div class="ced_onbuy_child_element">
			<table class="wp-list-table ced_onbuy_global_settings">
				<?php
				$onbuy_fetched_categories_tech_details = get_option( 'ced_onbuy_fetched_categories_tech_details_' . $profile_category_id );
				if ( ! empty( $onbuy_fetched_categories_tech_details ) ) {

					?>
				<tr>
						<td><b>Onbuy Attribute</b></td>
						<td><b>Default Value</b></td>
						<td><b>Pick Value From</b></td>
					</tr>
					<?php
					foreach ( $onbuy_fetched_categories_tech_details as $key => $value ) {
						$categoryID   = $profile_category_id;
						$attribute_id = $value['detail_id'];
						$isText       = false;
						$field_id     = trim( $value['detail_id'], '_' );
						$data_id      = $categoryID . '_' . $field_id;
						$default      = isset( $data[ $data_id ] ) ? $data[ $data_id ] : '';
						$default      = isset( $default['default'] ) ? $default['default'] : '';
						echo '<tr class="form-field _umb_brand_field ">';

						$isText = true;
						$ced_onbuy_instance->render_input_text_html(
							$field_id,
							ucfirst( $value['name'] ),
							$categoryID,
							$productID,
							$marketPlace,
							'The ' . ucfirst( $value['name'] ) . ' attribute value of technical detail should not be 0 or empty.',
							$indexToUse,
							array(
								'case'  => 'profile',
								'value' => $default,
							)
						);
						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';

							if ( isset( $data[ $data_id ] ) && 'null' != $data[ $data_id ] ) {
								$previousSelectedValue = $data[ $data_id ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $data_id, $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r( $updatedDropdownHTML );
						}
						echo '</td>';
						if ( isset( $value['units'] ) && is_array( $value['units'] ) && ! empty( $value['units'] ) ) {
							$valueForDropdown     = $value['units'];
							$tempValueForDropdown = array();
							foreach ( $valueForDropdown as $key => $_value ) {
								$tempValueForDropdown[ $key ] = $_value;
							}
							$valueForDropdown = $tempValueForDropdown;
							$field_id         = $field_id . '+';
							$data_id          = $categoryID . '_' . $field_id;
							$default          = '';
							$default          = isset( $data[ $data_id ] ) ? $data[ $data_id ] : '';
							$default          = isset( $default['default'] ) ? $default['default'] : '';
							$ced_onbuy_instance->render_dropdown_html(
								$field_id,
								'',
								$valueForDropdown,
								$categoryID,
								$productID,
								$marketPlace,
								'',
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $default,
								)
							);
							$isText = true;
						}
						echo '</tr>';
					}
				} else {
					echo '<b>No Technical Details For This Category</b>';
				}
				?>
			</table>
		</div>
	</div>
	<div class="ced_onbuy_heading">
		<?php echo esc_html_e( get_onbuy_instuctions_html( 'Category specific features' ) ); ?>
		<div class="ced_onbuy_child_element">
			<table class="wp-list-table ced_onbuy_global_settings widefat fixed">
				<?php if ( ! empty( $onbuy_fetched_categories_fea ) ) { ?>
				<tr>
						<td><b>OnBuy Attribute</b></td>
						<td><b>Default Value</b></td>
					</tr>
					<?php
					$onbuy_fetched_categories_fea = get_option( 'ced_onbuy_fetched_categories_fea_' . $profile_category_id );

					foreach ( $onbuy_fetched_categories_fea as $key => $value ) {
						$categoryID   = $profile_category_id;
						$required     = $value['required'];
						$attribute_id = $value['feature_id'];
						$isText       = false;
						$field_id     = trim( $value['feature_id'], '_' );
						$data_id      = $categoryID . '_' . $field_id;
						$default      = isset( $data[ $data_id ] ) ? $data[ $data_id ] : '';

						echo '<tr class="form-field _umb_brand_field ">';

						$valueForDropdown     = $value['options'];
						$tempValueForDropdown = array();
						foreach ( $valueForDropdown as $key => $_value ) {
							$tempValueForDropdown[ $key ] = $_value;
						}
						$valueForDropdown = $tempValueForDropdown;

						$ced_onbuy_instance->render_dropdown_html(
							$field_id,
							ucfirst( $value['name'] ),
							$valueForDropdown,
							$categoryID,
							$productID,
							$marketPlace,
							ucfirst( $value['name'] ),
							$indexToUse,
							array(
								'case'  => 'profile',
								'value' => $default,
							),
							$required
						);
						$isText = true;

						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';
							if ( isset( $data[ $data_id ] ) && 'null' != $data[ $data_id ] ) {
								$previousSelectedValue = $data[ $data_id ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $data_id, $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
						}
						echo '</td>';
						echo '</tr>';
					}
				} else {
					echo '<b>No Features For This Category</b>';
				}

				?>

			</table>
		</div>
		</div>
	<div class="ced-button-wrapper">
		<button  name="ced_onbuy_profile_save_button" class="button-primary"><?php esc_html_e( 'Save Profile', 'woocommerce-onbuy-integration' ); ?></button>			
	</div>
</form>
