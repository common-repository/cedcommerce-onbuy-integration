<?php
/**
 * Accounts Table
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
 * Ced_Onbuy_Queue_Table
 *
 * @since 1.0.0
 */
class Ced_Onbuy_Queue_Table extends WP_List_Table {

	/**
	 * Ced_Onbuy_Queue_Table construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Onbuy Queue', 'onbuy-integration-by-cedcommerce' ),
				'plural'   => __( 'Onbuy Queues', 'onbuy-integration-by-cedcommerce' ),
				'ajax'     => false,
			)
		);

	}

	/**
	 * Function to prepare account data to be displayed
	 *
	 * @since 1.0.0
	 */
	public function prepareItems() {
		global $wpdb;
		$per_page = apply_filters( 'ced_onbuy_queue_list_per_page', 100 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->getSortableColumns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::getQueues( $per_page, $current_page );
		$count       = self::getCount();
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
		if ( ! $this->current_action() ) {
			$this->items = self::getQueues( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 * Function to get all the accounts
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function getQueues( $per_page = 10, $page_number = 1 ) {
		global $wpdb;
		$offset  = ( $page_number - 1 ) * $per_page;
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';

		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_queue WHERE `shop_id` = %d  ORDER BY `id` DESC LIMIT %d OFFSET %d', $shop_id, $per_page, $offset ), 'ARRAY_A' );
		return $result;
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function getCount() {
		global $wpdb;
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
		$result  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_queue WHERE `shop_id` = %d ', $shop_id ), 'ARRAY_A' );
		return count( $result );
	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Queue Found.', 'onbuy-integration-by-cedcommerce' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="onbuy_queue_id[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_id( $item ) {
		$title           = '<strong>' . $item['id'] . '</strong>';
		$request_page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		$request_section = isset( $_REQUEST['section'] ) ? sanitize_text_field( $_REQUEST['section'] ) : '';
		$shop_id         = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
		$actions         = array(
			'viewDetails' => sprintf( '<a href="?page=%s&action=%s&section=%s&queue_id=%s&shop_id=%s">View Details</a>', esc_attr( $request_page ), 'viewDetails', esc_attr( $request_section ), $item['id'], $shop_id ),
		);
		return $title . $this->row_actions( $actions, true );
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_shop_id( $item ) {
		$title = '<strong>' . $item['shop_id'] . '</strong>';
		return $title;
	}

	/**
	 * Function for Shop Id column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_post_time( $item ) {
		return gmdate( 'r', $item['post_time'] );
	}


	/**
	 * Function for Location column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_queue_type( $item ) {
		return $item['queue_type'];
	}

	/**
	 *  Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'id'         => __( 'Id', 'onbuy-integration-by-cedcommerce' ),
			'shop_id'    => __( 'Shop Id', 'onbuy-integration-by-cedcommerce' ),
			'post_time'  => __( 'Time', 'onbuy-integration-by-cedcommerce' ),
			'queue_type' => __( 'Type', 'onbuy-integration-by-cedcommerce' ),
		);

		$columns = apply_filters( 'ced_onbuy_alter_feed_table_columns', $columns );
		return $columns;
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
	 * Returns an associative array containing the bulk action
	 *
	 * @since 1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'onbuy-integration-by-cedcommerce' ),
		);

		return $actions;
	}

	/**
	 * Function to get changes in html
	 *
	 * @since 1.0.0
	 */
	public function renderHTML() {
		?>
		<div class="ced_onbuy_wrap ced_onbuy_wrap_extn">
			<div>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="ced_onbuy_list_products_table">
						<div class="meta-box-sortables ui-sortable">
							<form method="post" action="">
								<?php
								wp_nonce_field( 'onbuy_queue', 'onbuy_queue_actions' );
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

	/**
	 * Function to perform bulk actions for name column
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {
		$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) || ( isset( $_GET['action2'] ) && 'bulk-delete' === $_GET['action2'] ) ) {

			if ( ! isset( $_POST['onbuy_queue_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_queue_actions'] ) ), 'onbuy_queue' ) ) {
				return;
			}
			$account_ids = isset( $sanitized_array['onbuy_queue_id'] ) ? ( ( $sanitized_array['onbuy_queue_id'] ) ) : '';

			global $wpdb;
			foreach ( $account_ids as $account_id ) {
				$delete_status = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'ced_onbuy_queue WHERE `id` = %d ', $account_id ) );
			}
			$shop_id      = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
			$redirect_url = get_admin_url() . 'admin.php?page=ced_onbuy&section=class-onbuyQueueManagement&shop_id=' . $shop_id;
			wp_redirect( $redirect_url );
			exit;
		} elseif ( isset( $_GET['section'] ) ) {
			if ( 'viewDetails' === $this->current_action() || ( isset( $_GET['action'] ) && 'viewDetails' === $_GET['action'] ) ) {
				$shop_id  = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
				$queue_id = isset( $_GET['queue_id'] ) ? sanitize_text_field( $_GET['queue_id'] ) : '';
				$urlToUse = get_admin_url() . 'admin.php?page=ced_onbuy&section=class-onbuyQueueManagement&shop_id=' . $shop_id;
				echo '<div class="ced_onbuy_wrap">';
				echo '<div class="back ced_onbuy_add_button"><label class ="manage_labels"><a href="' . esc_attr( $urlToUse ) . '"><b style = "color:white">Go Back</b></a></label></div>';
				do_action( 'ced_onbuy_feed_details', $queue_id, $shop_id );
				echo '<div>';
			} else {
				$file = CED_ONBUY_DIRPATH . 'admin/partials/' . $this->current_action() . '.php';
				if ( file_exists( $file ) ) {
					include_once $file;
				}
			}
		}
	}
}

$ced_onbuy_account_obj = new Ced_Onbuy_Queue_Table();
$ced_onbuy_account_obj->prepareItems();
