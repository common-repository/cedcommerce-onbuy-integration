<?php
/**
 * Product listing in manage products
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

$file = CED_ONBUY_DIRPATH . 'admin/partials/ced-instructions.php';
if ( file_exists( $file ) ) {
	require_once $file;

}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_OnBuy_List_Products
 *
 * @since 1.0.0
 */
class Ced_OnBuy_List_Products extends WP_List_Table {


	/**
	 * Ced_OnBuy_List_Products construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'onbuy-integration-by-cedcommerce' ),
				'plural'   => __( 'Products', 'onbuy-integration-by-cedcommerce' ),
				'ajax'     => true,
			)
		);

	}

	/**
	 * Function for preparing data to be displayed
	 *
	 * @since 1.0.0
	 */
	public function prepareItems() {
		global $wpdb;

		$per_page  = apply_filters( 'ced_onbuy_products_per_page', 10 );
		$per_page  = ! empty( $_GET['pro_per_page'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_per_page'] ) ) : $per_page;
		$post_type = 'product';
		$columns   = $this->get_columns();
		$hidden    = array();
		$sortable  = $this->getSortableColumns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		$this->items = self::cedonbuyGetProductDetails( $per_page, $current_page, $post_type );

		$count = self::get_count( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
		if ( ! $this->current_action() ) {
			$this->items = self::cedonbuyGetProductDetails( $per_page, $current_page, $post_type );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function get_count( $per_page, $page_number ) {
		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array( 'post_type' => 'product' );
		}
		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$product_data = $loop->found_posts;

		return $product_data;
	}

	/**
	 * Function for get product data
	 *
	 * @since 1.0.0
	 * @param      int    $per_page    Results per page.
	 * @param      int    $page_number   Page number.
	 * @param      string $post_type   Post type.
	 */
	public function cedonbuyGetProductDetails( $per_page = '', $page_number = '', $post_type = '' ) {
		$filter_file = CED_ONBUY_DIRPATH . 'admin/partials/filterClass.php';
		if ( file_exists( $filter_file ) ) {
			include_once $filter_file;
		}
		$shop_id                  = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$instance_of_filter_class = new Ced_Onbuy_FilterClass();

		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) || isset( $args['s'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
			);
		}

		$loop = new WP_Query( $args );

		$product_data   = $loop->posts;
		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$prodID         = $value->ID;
			$productDATA    = wc_get_product( $prodID );
			$productDATA    = $productDATA->get_data();
			$woo_products[] = $productDATA;
		}
		if ( isset( $_POST['filter_button'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_onbuy_filters_on_products();
		} elseif ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
			if ( ! isset( $_POST['manage_product_filters'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manage_product_filters'] ) ), 'manage_products' ) ) {
				return;
			}
			$woo_products = $instance_of_filter_class->ced_onbuy_product_search_box();
		}
		return $woo_products;
	}

	/**
	 * Text displayed when no data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Products To Show.', 'onbuy-integration-by-cedcommerce' );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @since 1.0.0
	 */
	public function getSortableColumns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="onbuy_products_ids[]" class="onbuy_products_id" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_name( $item ) {
		$product           = wc_get_product( $item['id'] );
		$product_type      = $product->get_type();
		$shop_name         = isset( $_GET['shop_name'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_name'] ) ) : '';
		$editUrl           = get_edit_post_link( $item['id'], '' );
		$actions['id']     = '<strong>ID :' . $item['id'] . '</strong>';
		$actions['status'] = '<strong>' . ucwords( $item['status'] ) . '</strong>';
		$actions['type']   = '<strong>' . ucwords( $product_type ) . '</strong>';
		echo '<b><a class="ced_etsy_prod_name" href="' . esc_attr( $editUrl ) . '" >' . esc_attr( $item['name'] ) . '</a></b>';
		return $this->row_actions( $actions, true );

	}


	/**
	 * Function for category column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_category( $item ) {
		foreach ( $item['category_ids'] as $key => $value ) {
			$wooCategory = get_term_by( 'id', $value, 'product_cat', 'ARRAY_A' );
			echo esc_attr( $wooCategory['name'] ) . '</br>';
		}
	}


	/**
	 * Function for onbuy_win_price column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_onbuy_win_price( $item ) {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

		$currency_symbol = get_woocommerce_currency_symbol();

		$lead_price = get_post_meta( $item['id'], 'ced_onbuy_lead_price_' . $shop_id, true );

		$winning_status = get_post_meta( $item['id'], 'ced_onbuy_winning_price_status_' . $shop_id, true );
		if ( 1 == $winning_status && ! empty( $lead_price ) ) {
			return '<b>Lead Price :</b>' . ( $currency_symbol ) . '&nbsp<b class="success_upload_on_onbuy">' . ( $lead_price ) . '</b></br><b>Your Price :</b>' . ( $currency_symbol ) . '&nbsp<b class="success_upload_on_onbuy">' . ( $item['price'] ) . '</b></br><b class="success_upload_on_onbuy">Winning!</b>';
		} elseif ( empty( $winning_status ) && empty( $lead_price ) ) {
			return '<b class="not_completed">No Status</b>';
		} else {
			return '<b>Lead Price :</b>' . ( $currency_symbol ) . '&nbsp <b class="success_upload_on_onbuy">' . ( $lead_price ) . '</b></br><b>Your Price :</b>' . ( $currency_symbol ) . '&nbsp<b class="success_upload_on_onbuy">' . ( $item['price'] ) . '</b></br><b class="not_completed">Not Winning</b>';
		}
	}


	/**
	 * Function for product type column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_status( $item ) {
		$shop_id     = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$status      = get_post_meta( $item['id'], '_ced_onbuy_listing_id_' . ( $shop_id ), true );
		$product_url = get_post_meta( $item['id'], 'ced_onbuy_product_url_' . ( $shop_id ), true );

		if ( isset( $status ) && ! empty( $status ) ) {
			echo '<b  class="success_upload_on_onbuy" id="' . esc_attr( $item['id'] ) . '">' . esc_html( __( 'Uploaded', 'onbuy-integration-by-cedcommerce' ) ) . '</b>';
			echo '  <a href="' . esc_url( $product_url ) . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>';
		} else {
			$error_message = get_post_meta( $item['id'], '_ced_onbuy_error' . ( $shop_id ), true );
			echo '<b style ="color:red" class="" id="' . esc_attr( $item['id'] ) . '">' . esc_html( __( 'Not Uploaded', 'onbuy-integration-by-cedcommerce' ) ) . '</b>';
			if ( ! empty( $error_message ) ) {
				echo "\n<a href='javascript:void(0)' class='ced_onbuy_view_error'>View error</a>";
				print_r( "<div style='display:none;' class='ced_onbuy_error_message'>$error_message</div>" );
			}
		}
	}




	public function column_profile( $item ) {
		$shop_id                      = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$get_profile_id_of_prod_level = get_post_meta( $item['id'], 'ced_onbuy_profile_assigned' . $shop_id, true );
		if ( ! empty( $get_profile_id_of_prod_level ) ) {
			global $wpdb;
			$profile_name = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id`=%s ", $get_profile_id_of_prod_level ), 'ARRAY_A' );

			echo '<b>' . esc_attr( $profile_name[0]['profile_name'] ) . '</b>';
			$profile_id = $get_profile_id_of_prod_level;
		} else {
			$get_onbuy_category_id = '';
			$category_ids          = isset( $item['category_ids'] ) ? $item['category_ids'] : array();
			foreach ( $category_ids as $index => $data ) {
				$get_onbuy_category_id_data = get_term_meta( $data );
				$get_onbuy_category_id      = isset( $get_onbuy_category_id_data['ced_onbuy_mapped_category'] ) ? $get_onbuy_category_id_data['ced_onbuy_mapped_category'] : '';

				if ( ! empty( $get_onbuy_category_id ) ) {
					break;
				}
			}
			if ( ! empty( $get_onbuy_category_id ) ) {
				foreach ( $get_onbuy_category_id as $key => $onbuy_id ) {
					$get_onbuy_profile_assigned = get_option( 'ced_woo_onbuy_mapped_categories_names' );
					$get_onbuy_profile_assigned = isset( $get_onbuy_profile_assigned[ $onbuy_id ] ) ? $get_onbuy_profile_assigned[ $onbuy_id ] : '';
				}
				if ( isset( $get_onbuy_profile_assigned ) && ! empty( $get_onbuy_profile_assigned ) ) {
					echo '<b>' . esc_attr( $get_onbuy_profile_assigned ) . '</b>';
				}
			} else {
				echo '<b><span class="not_completed">' . esc_html( __( 'Not Assigned', 'onbuy-integration-by-cedcommerce' ) ) . '</span></b>';
			}
			$profile_id = isset( $get_onbuy_category_id_data['ced_onbuy_profile_id'] ) ? $get_onbuy_category_id_data['ced_onbuy_profile_id'] : '';
			$profile_id = isset( $profile_id[0] ) ? $profile_id[0] : '';

		}
		if ( ! empty( $profile_id ) ) {
			$edit_profile_url = admin_url( 'admin.php?page=ced_onbuy&profile_id=' . $profile_id . '&category_id=' . $get_onbuy_category_id[0] . '&section=ced-onbuy-profile-listing&panel=edit&shop_id=' . $shop_id );

			$actions['edit'] = '<a href="' . $edit_profile_url . '">' . __( 'Edit', 'onbuy-integration-by-cedcommerce' ) . '</a>';
			return $this->row_actions( $actions, true );
		}
	}

	public function column_details( $item ) {
		$price = isset( $item['price'] ) ? $item['price'] : '';

		echo '<p>';
		echo '<strong>Regular price: </strong>' . esc_attr( $item['regular_price'] ) . '</br>';
		echo '<strong>Selling price: </strong>' . esc_attr( $price ) . '</br>';
		echo '<strong>SKU : </strong>' . esc_attr( $item['sku'] ) . '</br>';
		echo "<strong>Stock status: </strong><span class='" . esc_attr( $item['stock_status'] ) . "'>" . esc_attr( ucwords( $item['stock_status'] ) ) . '</span></br>';
		echo '<strong>Stock qty: </strong>' . esc_attr( $item['stock_quantity'] ) . '</br>';
		echo '</p>';
	}

	/**
	 * Function for image column
	 *
	 * @since 1.0.0
	 * @param array $item Product Data.
	 */
	public function column_image( $item ) {
		$image = wp_get_attachment_url( $item['image_id'] );
		return '<img height="50" width="50" src="' . $image . '">';
	}


	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'image'           => __( 'Image', 'onbuy-integration-by-cedcommerce' ),
			'name'            => __( 'Name', 'onbuy-integration-by-cedcommerce' ),
			'profile'         => __( 'Profile', 'onbuy-integration-by-cedcommerce' ),
			'onbuy_win_price' => __( 'OnBuy Winning Price', 'onbuy-integration-by-cedcommerce' ),
			'details'         => __( 'Details', 'woocommerce-etsy-integration' ),
			'category'        => __( 'Category', 'onbuy-integration-by-cedcommerce' ),
			'status'          => __( 'Status', 'onbuy-integration-by-cedcommerce' ),
		);
		$columns = apply_filters( 'ced_onbuy_alter_product_table_columns', $columns );
		return $columns;
	}

	/**
	 * Function to get the filtered data
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function GetFilteredData( $per_page, $page_number ) {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		if ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['pro_type_status'] ) || isset( $_GET['pro_per_page'] ) || isset( $_GET['pro_stock_status'] ) || isset( $_GET['s'] ) ) {

			if ( ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
				$pro_cat_sorting = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
				if ( ! empty( $pro_cat_sorting ) ) {
					$selected_cat          = array( $pro_cat_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_cat';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_cat;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_type_sorting'] ) ) {
				$pro_type_sorting = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
				if ( ! empty( $pro_type_sorting ) ) {
					$selected_type         = array( $pro_type_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_type';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_type;
					$args['tax_query'][]   = $tax_query;
				}
			}
			if ( ! empty( $_REQUEST['status_sorting'] ) ) {
				$status_sorting = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
				if ( ! empty( $status_sorting ) ) {
					$meta_query = array();
					if ( 'Uploaded' == $status_sorting ) {
						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(
							'key'     => '_ced_onbuy_listing_id_' . ( $shop_id ),
							'value'   => ' ',
							'compare' => '!=',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => '_ced_onbuy_listing_id_' . ( $shop_id ),
							'compare' => 'NOT EXISTS',
						);
					}
					$args['meta_query'] = $meta_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_type_status'] ) ) {
				$pro_type_status = isset( $_GET['pro_type_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_status'] ) ) : '';
				if ( ! empty( $pro_type_status ) ) {
					$args['post_status'] = array( $pro_type_status );
				}
			}

			if ( ! empty( $_REQUEST['pro_stock_status'] ) ) {
				$pro_stock_status = isset( $_GET['pro_stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_stock_status'] ) ) : '';
				if ( ! empty( $pro_stock_status ) ) {
					$meta_query[]       = array(
						'key'   => '_stock_status',
						'value' => $pro_stock_status,
					);
					$args['meta_query'] = $meta_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_per_page'] ) ) {
				$per_page               = isset( $_GET['pro_per_page'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_per_page'] ) ) : '';
				$args['posts_per_page'] = $per_page;
			}

			if ( ! empty( $_REQUEST['s'] ) ) {
				$search_by = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
				if ( ! empty( $search_by ) ) {
					$args['s'] = $search_by;
				}
			}
			$args['post_type']      = 'product';
			$args['posts_per_page'] = $per_page;
			$args['paged']          = $page_number;

			return $args;
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since 1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array();
		return $actions;
	}

	/**
	 * Render bulk actions
	 *
	 * @since 1.0.0
	 * @param      string $which    Where the apply button is placed.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( 'top' == $which ) :
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();
				/**
				 * Filters the list table Bulk Actions drop-down.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen, usually a string.
				 *
				 * This filter can currently only be used to remove bulk actions.
				 *
				 * @since 3.5.0
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
				$two            = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html( __( 'Select bulk action' ) ) . '</label>';
			echo '<select name="action' . esc_attr( $two ) . '" class="bulk-action-selector ">';
			echo '<option value="-1">' . esc_html( __( 'Bulk Actions' ) ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";
			echo "<input type='button' id='ced_onbuy_bulk_operation' class='button-primary' value='Apply'>";
			echo "\n";
		endif;
	}

	/**
	 * Function for rendering html
	 *
	 * @since 1.0.0
	 */
	public function renderHTML() {
		?>
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<?php
					$status_actions = array(
						'Uploaded'    => __( 'Synced', 'onbuy-integration-by-cedcommerce' ),
						'NotUploaded' => __( 'Un-Synced', 'onbuy-integration-by-cedcommerce' ),
					);
					$product_types  = get_terms( 'product_type', array( 'hide_empty' => false ) );
					// ---------------------------------------------
					$product_status = array(
						'publish' => __( 'Publish', 'onbuy-integration-by-cedcommerce' ),
						'draft'   => __( 'Draft', 'onbuy-integration-by-cedcommerce' ),
					);

					$product_stock_status = array(
						'instock'    => __( 'In Stock', 'onbuy-integration-by-cedcommerce' ),
						'outofstock' => __( 'Out Of Stock', 'onbuy-integration-by-cedcommerce' ),
					);

					$product_per_page = array(
						'10'  => __( '10 per page', 'onbuy-integration-by-cedcommerce' ),
						'20'  => __( '20 per page', 'onbuy-integration-by-cedcommerce' ),
						'50'  => __( '50 per page', 'onbuy-integration-by-cedcommerce' ),
						'100' => __( '100 per page', 'onbuy-integration-by-cedcommerce' ),
					);
					// -----------------------------------------------
					$temp_array = array();
					foreach ( $product_types as $key => $value ) {
						if ( 'simple' == $value->name || 'variable' == $value->name ) {
							$temp_array_type[ $value->term_id ] = ucfirst( $value->name );
						}
					}
					$product_types = $temp_array_type;

					$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
					$temp_array         = array();
					foreach ( $product_categories as $key => $value ) {
						$temp_array[ $value->term_id ] = $value->name;
					}
					$product_categories = $temp_array;

					$previous_selected_status           = isset( $_GET['status_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['status_sorting'] ) ) : '';
					$previous_selected_cat              = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_cat_sorting'] ) ) : '';
					$previous_selected_type             = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_sorting'] ) ) : '';
					$previous_selected_pro_status       = isset( $_GET['pro_type_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_type_status'] ) ) : '';
					$previous_selected_pro_per_page     = isset( $_GET['pro_per_page'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_per_page'] ) ) : '';
					$previous_selected_pro_stock_status = isset( $_GET['pro_stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_stock_status'] ) ) : '';

					echo '<div class="ced_onbuy_wrap">';
					echo '<form method="post" action="">';
					wp_nonce_field( 'manage_products', 'manage_product_filters' );
					echo '<div class="ced_onbuy_top_wrapper">';
					echo '<select name="status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Status', 'onbuy-integration-by-cedcommerce' ) ) . '</option>';
					foreach ( $status_actions as $name => $title ) {
						$selected_status = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class           = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_status ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					$previous_selected_cat = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( $_GET['pro_cat_sorting'] ) : '';

					$dropdown_cat_args = array(
						'name'            => 'pro_cat_sorting',
						'show_count'      => 1,
						'hierarchical'    => 1,
						'depth'           => 10,
						'taxonomy'        => 'product_cat',
						'class'           => 'select_boxes_product_page',
						'selected'        => $previous_selected_cat,
						'show_option_all' => 'Product Category',

					);
					wp_dropdown_categories( $dropdown_cat_args );

					echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Type', 'onbuy-integration-by-cedcommerce' ) ) . '</option>';
					foreach ( $product_types as $name => $title ) {
						$selected_type = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_type_status" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Post Status', 'onbuy-integration-by-cedcommerce' ) ) . '</option>';
					foreach ( $product_status as $name => $title ) {
						$selected_type = ( $previous_selected_pro_status == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_stock_status" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Stock Status', 'onbuy-integration-by-cedcommerce' ) ) . '</option>';
					foreach ( $product_stock_status as $name => $title ) {
						$selected_type = ( $previous_selected_pro_stock_status == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_per_page" class="select_boxes_product_page">';
					echo '<option value="">' . esc_html( __( 'Product Per Page', 'onbuy-integration-by-cedcommerce' ) ) . '</option>';
					foreach ( $product_per_page as $name => $title ) {
						$selected_type = ( $previous_selected_pro_per_page == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					$this->search_box( 'Search Products', 'search_id', 'search_product' );
					submit_button( __( 'Filter', 'onbuy-integration-by-cedcommerce' ), 'action', 'filter_button', false, array() );
					echo '</div>';
					echo '</form>';
					echo '</div>';

					$bulk_actions = array(
						'upload_product'       => array(
							'label' => 'Upload Products',
							'class' => 'success',
						),
						'update_product'       => array(
							'label' => 'Update Products',
							'class' => 'primary',
						),
						'update_stock'         => array(
							'label' => 'Update Stock & Price',
							'class' => 'cool',
						),
						'create_listing'       => array(
							'label' => 'Create Listing',
							'class' => 'warm',
						),
						'remove_product'       => array(
							'label' => 'Remove Product',
							'class' => 'fail',
						),
						'mark_as_not_uploaded' => array(
							'label' => 'Mark as Not Uploaded',
							'class' => 'cool',
						),
					);
						echo "<div class='ced_onbuy_bulk_actions'>";
					foreach ( $bulk_actions as $action => $params ) {
						echo "<input type='button'  class='" . esc_attr( $params['class'] ) . "' id='ced_onbuy_bulk_operation' data-operation='" . esc_attr( $action ) . "' value='" . esc_attr( $params['label'] ) . "'>";
					}
						echo '</div>';
					?>

					<form method="post">
						<?php
						$this->display();
						?>
					</form>

				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="ced_onbuy_preview_product_popup_main_wrapper"></div>
		<?php

	}
}

$ced_onbuy_products_obj = new Ced_OnBuy_List_Products();
$ced_onbuy_products_obj->prepareItems();
