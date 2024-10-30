<?php
/**
 * Profiles Listing
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_ONBUY_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	include_once $file;
}

$file = CED_ONBUY_DIRPATH . 'admin/partials/ced-onbuy-instructions.php';
if ( file_exists( $file ) ) {
	include_once $file;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Ced_Onbuy_Profile_Listing extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Onbuy Profile', 'woocommerce-onbuy-integration' ), // singular name of the listed records
				'plural'   => __( 'Onbuy Profiles', 'woocommerce-onbuy-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	public function prepare_items() {

		global $wpdb;
		$per_page = apply_filters( 'ced_onbuy_profile_list_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_profiles( $per_page, $current_page );

		$count = self::get_count();

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	public function get_profiles( $per_page = 10, $page_number = 1 ) {

		global $wpdb;
		$shop_id   = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$offset    = ( $page_number - 1 ) * $per_page;
		$tableName = $wpdb->prefix . 'ced_onbuy_profiles';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_onbuy_profiles ORDER BY `id` DESC LIMIT %d OFFSET %d", $per_page, $offset ), 'ARRAY_A' );
		return $result;

	}

	/**
	 * Function to count number of responses in result
	 */
	public function get_count() {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		global $wpdb;
		$tableName = $wpdb->prefix . 'ced_onbuy_profiles';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}ced_onbuy_profiles" ), 'ARRAY_A' );
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Profiles Created.', 'woocommerce-onbuy-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="onbuy_profile_ids[]" value="%s" />',
			$item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_profile_name( $item ) {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

		$profile_data = json_decode( $item['profile_data'], true );
		$cat_id       = isset( $profile_data['_umb_onbuy_category']['default'] ) ? (int) $profile_data['_umb_onbuy_category']['default'] : '';

		$title           = '<strong>' . $item['profile_name'] . '</strong>';
		$url             = admin_url( 'admin.php?page=ced_onbuy&profile_id=' . $item['id'] . '&category_id=' . $cat_id . '&section=ced-onbuy-profile-listing&panel=edit&shop_id=' . $shop_id );
		$actions['edit'] = '<a href=' . $url . '>Edit</a>';
		print_r( $title );
		return $this->row_actions( $actions, true );
	}


	public function column_profile_status( $item ) {

		if ( 'inactive' == $item['profile_status'] ) {
			return 'InActive';
		} else {
			return 'Active';
		}
	}


	public function column_woo_categories( $item ) {

		$woo_categories = json_decode( $item['woo_categories'], true );

		if ( ! empty( $woo_categories ) ) {
			foreach ( $woo_categories as $key => $value ) {
				$term = get_term_by( 'id', $value, 'product_cat' );
				if ( $term ) {
					echo '<p>' . esc_attr( $term->name ) . '</p>';
				}
			}
		}
	}

	public function column_edit_profiles( $item ) {
		$shop_id  = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$cat_id   = isset( $item['profile_data']['_umb_onbuy_category']['default'] ) ? (int) $item['profile_data']['_umb_onbuy_category']['default'] : '';
		$edit_url = admin_url( 'admin.php?page=ced_onbuy&profile_id=' . $item['id'] . '&section=ced-onbuy-profile-listing&panel=edit&shop_id=' . $shop_id );
		echo "<a class='button-primary' href='" . esc_url( $edit_url ) . "'>Edit</a>";
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'profile_name'   => __( 'Profile Name', 'woocommerce-onbuy-integration' ),
			'profile_status' => __( 'Profile Status', 'woocommerce-onbuy-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-onbuy-integration' ),
		);
		$columns = apply_filters( 'ced_onbuy_alter_profiles_table_columns', $columns );
		return $columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'woocommerce-onbuy-integration' ),
		);
		return $actions;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		?>
		<div class="ced_onbuy_wrap ced_onbuy_wrap_extn">		
			<div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'onbuy_profiles', 'onbuy_profiles_actions' );
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}

	public function current_action() {

		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? sanitize_text_field( wp_unslash( $_GET['panel'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			if ( ! isset( $_POST['onbuy_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_profiles_actions'] ) ), 'onbuy_profiles' ) ) {
				return;
			}
			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			return $action;
		}
	}

	public function process_bulk_action() {

		if ( ! session_id() ) {
			session_start();
		}
		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {

			if ( ! isset( $_POST['onbuy_profiles_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_profiles_actions'] ) ), 'onbuy_profiles' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$profile_ids     = isset( $sanitized_array['onbuy_profile_ids'] ) ? $sanitized_array['onbuy_profile_ids'] : array();
			if ( is_array( $profile_ids ) && ! empty( $profile_ids ) ) {

				global $wpdb;

				$tableName = $wpdb->prefix . 'ced_onbuy_profiles';

				$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

				foreach ( $profile_ids as $index => $pid ) {

					$product_ids_assigned = get_option( 'ced_onbuy_product_ids_in_profile_' . $pid, array() );
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_onbuy_profile_assigned' );
					}

					$term_id = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_onbuy_profiles WHERE `id` = %d", $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
					foreach ( $term_id as $key => $value ) {
						delete_term_meta( $value, 'ced_onbuy_profile_created' );
						delete_term_meta( $value, 'ced_onbuy_profile_id' );
						delete_term_meta( $value, 'ced_onbuy_mapped_category' );
					}
				}

				foreach ( $profile_ids as $id ) {
					$wpdb->delete( $tableName, array( 'id' => $id ) );
				}

				$redirectURL = get_admin_url() . 'admin.php?page=ced_onbuy&section=ced-onbuy-profile-listing&shop_id=' . $shop_id;
				wp_redirect( $redirectURL );
			}
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {

			require_once CED_ONBUY_DIRPATH . 'admin/partials/ced-onbuy-profile-edit-view.php';
		}
	}
}

$ced_onbuy_profile_obj = new Ced_Onbuy_Profile_Listing();
$ced_onbuy_profile_obj->prepare_items();
