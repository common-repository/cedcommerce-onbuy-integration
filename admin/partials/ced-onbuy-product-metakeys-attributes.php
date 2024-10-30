<?php

$arguements  = array(
	'numberposts' => 5,
	'post_type'   => array( 'product' ),
	's'           => $product_name,
	'fields'      => 'ids',
);
$product_ids = get_posts( $arguements );
$count       = 0;
if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
	$product_meta_data = array();
	foreach ( $product_ids as $key => $product_id ) {
		$product_object = wc_get_product( $product_id );
		if ( is_object( $product_object ) ) {
			$product_type       = $product_object->get_type();
			$product_attributes = $product_object->get_attributes();
			if ( is_array( $product_attributes ) && ! empty( $product_attributes ) ) {
				foreach ( $product_attributes as $slug => $attribute_object ) {
					if ( strpos( $slug, 'pa_' ) === false ) {
						if ( is_object( $attribute_object ) ) {
							$attribute_data = $attribute_object->get_data();
							$product_meta_data[ $count ]['attributes'][ $attribute_data['name'] ][0] = isset( $attribute_data['options'][0] ) ? $attribute_data['options'][0] : '';
						}
					}
				}
			}
			if ( 'variation' == $product_type ) {
				$parent_id                               = $product_object->get_parent_id();
				$parent_custom_data                      = get_post_custom( $parent_id );
				$product_meta_data[ $count ]['metakeys'] = $parent_custom_data;
			} elseif ( 'simple' == $product_type ) {
				$product_custom_data                     = get_post_custom( $product_id );
				$product_meta_data[ $count ]['metakeys'] = $product_custom_data;
			}
		}
		$count = ++$count;
	}
	$temp_array                = array();
	$meta_keys_to_be_displayed = array();
	foreach ( $product_meta_data as $index => $value ) {
		if ( isset( $value['metakeys'] ) ) {
			$meta_keys_to_be_displayed = array_merge( $value['metakeys'], $temp_array );
			$temp_array                = $value['metakeys'];
		} elseif ( isset( $value['attributes'] ) ) {
			$meta_keys_to_be_displayed = array_merge( $value['attributes'], $temp_array );
			$temp_array                = $value['attributes'];
		}
	}
	if ( isset( $meta_keys_to_be_displayed ) && is_array( $meta_keys_to_be_displayed ) && ! empty( $meta_keys_to_be_displayed ) ) {

		update_option( 'ced_onbuy_metakeys_to_be_displayed', $meta_keys_to_be_displayed );
		$html            = '';
		$html           .= '<table class="wp-list-table widefat fixed striped">';
		$html           .= '<tbody>';
		$html           .= '<tr><td colspan="4"><label>CHECK THE METAKEYS OR ATTRIBUTES</label></td></tr>';
		$html           .= '<tr><td><label>Select metakeys</label></td><td><label>Metakey / Attributes</label></td><td colspan="2"><label>Value</label></td>';
		$added_meta_keys = get_option( 'ced_onbuy_selected_metakeys', array() );
		foreach ( $meta_keys_to_be_displayed as $meta_key => $meta_data ) {
			$checked    = ( in_array( $meta_key, $added_meta_keys ) ) ? 'checked=checked' : '';
			$html      .= '<tr>';
			$html      .= '<td><input type="checkbox" class="ced_onbuy_meta_key" value="' . esc_attr( $meta_key ) . '" ' . esc_attr( $checked ) . '></td>';
			$html      .= '<td>' . esc_attr( $meta_key ) . '</td>';
			$meta_value = ! empty( $meta_data[0] ) ? $meta_data[0] : '';
			$html      .= '<td colspan="2">' . esc_attr( $meta_value ) . '</td>';
			$html      .= '</tr>';
		}
		$html .= '<tr><td colsapn="4"><button class="ced_onbuy_custom_button ced_onbuy_save_metakey_button">Save</button></td></tr>';
		$html .= '</tbody>';
		$html .= '</table>';
		echo json_encode(
			array(
				'status'  => 200,
				'message' => $html,
			)
		);
		die();
	}
} else {
	$html  = '';
	$html .= '<table class="wp-list-table widefat fixed striped">';
	$html .= '<tr><td><label class="ced_onbuy_error">No results found !! </label></td></tr>';
	$html .= '</table>';
	echo json_encode(
		array(
			'status'  => 400,
			'message' => $html,
		)
	);
	die();
}
