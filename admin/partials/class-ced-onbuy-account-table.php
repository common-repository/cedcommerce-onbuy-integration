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

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Onbuy_Account_Table
 *
 * @since 1.0.0
 */
class Ced_Onbuy_Account_Table extends WP_List_Table {

	/**
	 * Ced_Onbuy_Account_Table construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'onbuy Account', 'onbuy-integration-by-cedcommerce' ),
				'plural'   => __( 'onbuy Accounts', 'onbuy-integration-by-cedcommerce' ),
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

		$per_page = apply_filters( 'ced_onbuy_account_list_per_page', 10 );
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

		$this->items = self::getAccounts( $per_page, $current_page );

		$count = self::getCount();

		if ( ! $this->current_action() ) {
			$this->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $per_page,
					'total_pages' => ceil( $count / $per_page ),
				)
			);
			$this->items = self::getAccounts( $per_page, $current_page );
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
	public function getAccounts( $per_page = 10, $page_number = 1 ) {
		global $wpdb;
		$offset = ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts LIMIT %d OFFSET %d', $per_page, $offset ), 'ARRAY_A' );
		return $result;
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function getCount() {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE %d', 1 ), 'ARRAY_A' );
		return count( $result );
	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Accounts Linked.', 'onbuy-integration-by-cedcommerce' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="onbuy_account_id[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_name( $item ) {
		$title = '<strong>' . json_decode( $item['seller_data'], true )['seller_name'] . '</strong>';
		return $title;
	}

	/**
	 * Function for Shop Id column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_shop_id( $item ) {
		return $item['shop_id'];
	}

	/**
	 * Function for Acoount Status column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_account_status( $item ) {
		if ( 'inactive' == $item['account_status'] ) {
			return 'InActive';
		} else {
			return 'Active';
		}
	}

	/**
	 * Function for Location column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_company_name( $item ) {
		return json_decode( $item['seller_data'], true )['company_name'];
	}

	/**
	 * Function for Configure column
	 *
	 * @since 1.0.0
	 * @param array $item Account Data.
	 */
	public function column_configure( $item ) {
		$button_html = "<a class='button-primary' href='" . admin_url( 'admin.php?page=ced_onbuy&section=settings-view&shop_id=' . $item['shop_id'] ) . "'>" . __( 'Configure', 'onbuy-integration-by-cedcommerce' ) . '</a>';
		return $button_html;
	}

	/**
	 *  Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'name'           => __( 'Seller Name', 'onbuy-integration-by-cedcommerce' ),
			'shop_id'        => __( 'OnBuy Seller Id', 'onbuy-integration-by-cedcommerce' ),
			'company_name'   => __( 'Company Name', 'onbuy-integration-by-cedcommerce' ),
			'account_status' => __( 'Account Status', 'onbuy-integration-by-cedcommerce' ),
			'configure'      => __( 'Configure', 'onbuy-integration-by-cedcommerce' ),
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
			<div class="ced_onbuy_setting_header cedcommerce-top-border">
				<?php esc_attr( ced_onbuy_cedcommerce_logo() ); ?>
				<label class="manage_labels"><b><?php esc_html_e( 'ONBUY ACCOUNT', 'woocommerce-onbuy-integration' ); ?></b></label>
				<?php
				$count = self::getCount();
				if ( $count < 1 ) {
					$message = 'To start syncing your products and orders, begin with connecting your OnBuy account with the plugin using the Connect OnBuy Account option on top right.';
					echo '<a href="javascript:void(0)" class="ced_onbuy_add_account_button ced_onbuy_add_button button-primary">Connect OnBuy Account</a>';
				} else {
					$message = 'Start syncing your products, orders and boost sales. Click configure button to explore further.';
				}
				?>
			</div>
			<div class="">
				<div class="ced_onbuy_welcome_notice"><h3>Welcome to OnBuy Integration for WooCommerce! </h3>
				<?php
					echo '<h4>' . esc_attr( $message ) . '</h4>';
				?>
						
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'onbuy_accounts', 'onbuy_accounts_actions' );
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
		<div class="ced_onbuy_add_account_popup_main_wrapper">
			<div class="ced_onbuy_loader">
				<img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_onbuy_loading_img" >
			</div>
			<div class="ced_onbuy_add_account_popup_content">
				<div class="ced_onbuy_add_account_popup_header">
					<h5><?php esc_html_e( 'Authorise Your OnBuy Account', 'onbuy-integration-by-cedcommerce' ); ?></h5>
					<span class="ced_onbuy_add_account_popup_close">X</span>
				</div>
				<div class="ced_onbuy_add_account_popup_body">
					<h6><?php esc_html_e( 'Steps to authorise your account:', 'onbuy-integration-by-cedcommerce' ); ?></h6>
					<div class="ced_onbuy_add_account_button_wrapper">
						<p><b><i><a href="javascript:void(0);" class="" id="ced_onbuy_account_details" ><?php esc_html_e( 'Find your Account Details Here', 'onbuy-integration-by-cedcommerce' ); ?></a></i></b></p>
						<table class="widefat">
							<tr>
								<th>Seller Id</th>
								<td>
									<input type="text" id="ced_onbuy_seller_id" class="ced_onbuy_api_input"> 
								</td>
							</tr>
							<tr>
								<th>Consumer Key</th>
								<td>
									<input type="text" id="ced_onbuy_consumer_key" class="ced_onbuy_api_input"> 
								</td>
							</tr>
							<tr>
								<th>Secret Key</th>
								<td>
									<input type="text" id="ced_onbuy_secret_key" class="ced_onbuy_api_input"> 
								</td>
							</tr>
						</table>
						<a href="javascript:void(0);" id="ced_onbuy_authorise_account_button" class="button-primary ced-button-wrapper"><?php esc_html_e( 'Add Account', 'onbuy-integration-by-cedcommerce' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<div class="ced_contact_menu_wrap">
			<input type="checkbox" href="#" class="ced_menu_open" name="menu-open" id="menu-open" />
			<label class="ced_menu_button" for="menu-open">
				<img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/icon.png' ); ?>" alt="" title="Click to Chat">
			</label>
			<a href="https://join.skype.com/rzxfe8JrHbao" class="ced_menu_content ced_skype" target="_blank"> <i class="fa fa-skype" aria-hidden="true"></i> </a>
			<a href="https://chat.whatsapp.com/GgYqefNlVeJH0KcXZyOrkp" class="ced_menu_content ced_whatsapp" target="_blank"> <i class="fa fa-whatsapp" aria-hidden="true"></i> </a>
		</div>
		<?php
	}

	/**
	 * Function to get current action
	 *
	 * @since 1.0.0
	 */
	public function current_action() {

		if ( isset( $_GET['section'] ) ) {
			$action = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {

			if ( ! isset( $_POST['onbuy_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_accounts_actions'] ) ), 'onbuy_accounts' ) ) {
				return;
			}

			$action1 = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			$action2 = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';
			if ( -1 != $action1 ) {
				$action = $action1;
			}
			if ( -1 != $action2 ) {
				$action = $action2;
			}
			return $action;
		}
	}

	/**
	 * Function to perform bulk actions for name column
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {

		$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) || ( isset( $_GET['action2'] ) && 'bulk-delete' === $_GET['action2'] ) ) {

			if ( ! isset( $_POST['onbuy_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_accounts_actions'] ) ), 'onbuy_accounts' ) ) {
				return;
			}
			$account_ids = isset( $sanitized_array['onbuy_account_id'] ) ? ( ( $sanitized_array['onbuy_account_id'] ) ) : '';

			global $wpdb;
			foreach ( $account_ids as $account_id ) {
				$delete_status = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `id` = %d ', $account_id ) );
			}
			$redirect_url = get_admin_url() . 'admin.php?page=ced_onbuy';
			wp_redirect( $redirect_url );
			exit;
		} elseif ( 'bulk-enable' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-enable' === $_GET['action'] ) || ( isset( $_GET['action2'] ) && 'bulk-enable' === $_GET['action2'] ) ) {

			if ( ! isset( $_POST['onbuy_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_accounts_actions'] ) ), 'onbuy_accounts' ) ) {
				return;
			}

			$account_ids = isset( $sanitized_array['onbuy_account_id'] ) ? ( ( $sanitized_array['onbuy_account_id'] ) ) : '';

			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_onbuy_accounts';
			foreach ( $account_ids as $account_id ) {
				$wpdb->update( $table_name, array( 'account_status' => 'active' ), array( 'id' => $account_id ) );
			}
			$redirect_url = get_admin_url() . 'admin.php?page=ced_onbuy';
			wp_redirect( $redirect_url );
			exit;
		} elseif ( 'bulk-disable' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-disable' === $_GET['action'] ) || ( isset( $_GET['action2'] ) && 'bulk-disable' === $_GET['action2'] ) ) {

			if ( ! isset( $_POST['onbuy_accounts_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onbuy_accounts_actions'] ) ), 'onbuy_accounts' ) ) {
				return;
			}

			$account_ids = isset( $sanitized_array['onbuy_account_id'] ) ? ( ( $sanitized_array['onbuy_account_id'] ) ) : '';

			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_onbuy_accounts';
			foreach ( $account_ids as $account_id ) {
				$wpdb->update( $table_name, array( 'account_status' => 'inactive' ), array( 'id' => $account_id ) );
			}
			$redirect_url = get_admin_url() . 'admin.php?page=ced_onbuy';
			wp_redirect( $redirect_url );
			exit;
		} elseif ( isset( $_GET['section'] ) ) {
			$file = CED_ONBUY_DIRPATH . 'admin/partials/' . $this->current_action() . '.php';
			if ( file_exists( $file ) ) {
				echo "<div class='ced_onbuy_body'>";
				include_once $file;
				echo '</div>';
			}
		}
	}
}

$ced_onbuy_account_obj = new Ced_Onbuy_Account_Table();
$ced_onbuy_account_obj->prepareItems();
