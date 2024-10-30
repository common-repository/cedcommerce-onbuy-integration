<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package   Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/admin
 */
class Ced_Onbuy_Integration_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependency();

		add_action( 'wp_ajax_ced_onbuy_load_more_logs', array( $this, 'ced_onbuy_load_more_logs' ) );
		add_action( 'manage_edit-shop_order_columns', array( $this, 'ced_onbuy_add_table_columns' ), 999 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'ced_onbuy_manage_table_columns' ), 999, 2 );
		add_action( 'ced_onbuy_auto_product_upload_scheduler_job', array( $this, 'ced_onbuy_auto_product_upload_schedule_manager' ) );
	}

	public function ced_onbuy_load_more_logs() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$parent          = isset( $sanitized_array['parent'] ) ? $sanitized_array['parent'] : '';
			$offset          = isset( $sanitized_array['offset'] ) ? (int) $sanitized_array['offset'] : '';
			$total           = isset( $sanitized_array['total'] ) ? (int) $sanitized_array['total'] : '';

			$log_info = get_option( $parent, '' );
			if ( empty( $log_info ) ) {
				$log_info = array();
			} else {
				$log_info = json_decode( $log_info, true );
			}
			$log_info   = array_slice( $log_info, (int) $offset, 50 );
			$is_disable = 'no';
			$html       = '';
			if ( ! empty( $log_info ) ) {
				$offset += count( $log_info );
				foreach ( $log_info as $key => $info ) {

					$html .= "<tr class='ced_onbuy_log_rows'>";
					$html .= "<td><span class='log_item_label log_details'><a>" . ( $info['post_title'] ) . "</a></span><span class='log_message' style='display:none;'><h3>Input payload for " . ( $info['post_title'] ) . '</h3><button id="ced_close_log_message">Close</button><pre>' . ( ! empty( $info['input_payload'] ) ? json_encode( $info['input_payload'], JSON_PRETTY_PRINT ) : '' ) . '</pre></span></td>';
					$html .= "<td><span class=''>" . $info['action'] . '</span></td>';
					$html .= "<td><span class=''>" . $info['time'] . '</span></td>';
					$html .= "<td><span class=''>" . ( $info['is_auto'] ? 'Automatic' : 'Manual' ) . '</span></td>';
					$html .= '<td>';
					if ( ( isset( $info['response']['success'] ) && 'true' == $info['response']['success'] ) || ! empty( $info['response']['product_listing_id'] ) ) {
						$html .= "<span class='onbuy_log_success log_details'>Success</span>";
					} else {
						$html .= "<span class='onbuy_log_fail log_details'>Failed</span>";
					}
					$html .= "<span class='log_message' style='display:none;'><h3>Response payload for " . ( $info['post_title'] ) . '</h3><button id="ced_close_log_message">Close</button><pre>' . ( ! empty( $info['response'] ) ? json_encode( $info['response'], JSON_PRETTY_PRINT ) : '' ) . '</pre></span>';
					$html .= '</td>';
					$html .= '</tr>';
				}
			}
			if ( $offset >= $total ) {
				$is_disable = 'yes';
			}
			echo json_encode(
				array(
					'html'       => $html,
					'offset'     => $offset,
					'is_disable' => $is_disable,
				)
			);
			wp_die();
		}
	}

	public function ced_onbuy_add_table_columns( $columns ) {
		$modified_columns = array();
		foreach ( $columns as $key => $value ) {
			$modified_columns[ $key ] = $value;
			if ( 'order_number' == $key ) {
				$modified_columns['order_from'] = '<span title="Order source">Order source</span>';
			}
		}
		return $modified_columns;
	}


	public function ced_onbuy_manage_table_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'order_from':
				$_ced_onbuy_order_id = get_post_meta( $post_id, '_ced_onbuy_order_id', true );
				if ( ! empty( $_ced_onbuy_order_id ) ) {
					$onbuy_icon = CED_ONBUY_URL . 'admin/images/onbuy-card.png';
					echo '<p><img src="' . esc_url( $onbuy_icon ) . '" height="35" width="60"></p>';
				}
		}
	}

	public function load_dependency() {
		$file_onbuy = CED_ONBUY_DIRPATH . 'admin/onbuy/class-onbuy.php';
		if ( file_exists( $file_onbuy ) ) {
			include_once $file_onbuy;
		}
		$this->ced_onbuy_instance = new Class_Ced_Onbuy_Manager();

		require_once CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-activities.php';
		$activity            = new Onbuy_Activities();
		$GLOBALS['activity'] = $activity;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $pagenow;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Onbuy_Integration_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Onbuy_Integration_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( isset( $_GET['page'] ) && ( 'ced_onbuy' == $_GET['page'] || 'cedcommerce-integrations' == $_GET['page'] ) ) {
			wp_enqueue_style( 'ced-boot-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '2.0.0', 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/onbuy-integration-for-woocommerce-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $pagenow;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Onbuy_Integration_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Onbuy_Integration_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$ajax_nonce     = wp_create_nonce( 'ced-onbuy-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
			'shop_id'    => ! empty( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : 'cedcommerce',
		);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/onbuy-integration-for-woocommerce-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/license.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ced_onbuy_admin_obj', $localize_array );
	}


	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_add_menus.
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_add_menus() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'onbuy-integration-by-cedcommerce' ), __( 'CedCommerce', 'onbuy-integration-by-cedcommerce' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), plugins_url( 'onbuy-integration-for-woocommerce/admin/images/logo1.png' ), 12 );
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
		}
	}

	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_marketplace_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_marketplace_listing_page() {
		$active_marketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
			require CED_ONBUY_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}

	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_add_marketplace_menus_to_array.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function ced_onbuy_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'OnBuy',
			'slug'            => 'onbuy-integration-by-cedcommerce',
			'menu_link'       => 'ced_onbuy',
			'instance'        => $this,
			'function'        => 'ced_onbuy_accounts_page',
			'card_image_link' => CED_ONBUY_URL . 'admin/images/onbuy-card.png',
		);
		return $menus;
	}

	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_accounts_page.
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_accounts_page() {
		$file_accounts = CED_ONBUY_DIRPATH . 'admin/partials/class-ced-onbuy-account-table.php';
		if ( file_exists( $file_accounts ) ) {
			echo '<div class="ced_onbuy_body">';
			include_once $file_accounts;
			echo '</div>';
		}

	}


	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_cron_schedules.
	 *
	 * @since 1.0.0
	 * @param array $schedules Cron Schedules.
	 */
	public function ced_onbuy_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_onbuy_6min'] ) ) {
			$schedules['ced_onbuy_6min'] = array(
				'interval' => 6 * 60,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_onbuy_10min'] ) ) {
			$schedules['ced_onbuy_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_onbuy_15min'] ) ) {
			$schedules['ced_onbuy_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_onbuy_30min'] ) ) {
			$schedules['ced_onbuy_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}

	public function ced_onbuy_marketplace_email_restrict( $enable, $order ) {
		if ( empty( $order ) ) {
			return $enable;
		}
		$orderId     = $order->get_id();
		$marketplace = get_post_meta( $orderId, '_onbuy_marketplace', true );
		$enable      = get_option( 'ced_onbuy_restrict_woo_mails', true );
		if ( 'enable' == $enable ) {
			if ( 'OnBuy' == $marketplace ) {
				$enable = false;
			}
		}
		return $enable;
	}

	public function ced_onbuy_save_variation_data( $post_id ) {
		if ( ! isset( $_POST['ced_product_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_product_settings_submit'] ) ), 'ced_product_settings' ) ) {
			return;
		}
		if ( empty( $post_id ) ) {
			return;
		}

		$sanitized_array     = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$ced_onbuy_condition = isset( $_POST['ced_onbuy_condition'] ) ? sanitize_text_field( $_POST['ced_onbuy_condition'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_condition', $ced_onbuy_condition );

		$ced_onbuy_price = isset( $_POST['ced_onbuy_price'] ) ? sanitize_text_field( $_POST['ced_onbuy_price'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_price', $ced_onbuy_price );

		$ced_onbuy_sale_price = isset( $_POST['ced_onbuy_sale_price'] ) ? sanitize_text_field( $_POST['ced_onbuy_sale_price'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_sale_price', $ced_onbuy_sale_price );

		$ced_sale_price_dates_from = isset( $_POST['ced_sale_price_dates_from'] ) ? sanitize_text_field( $_POST['ced_sale_price_dates_from'] ) : '';
		update_post_meta( $post_id, 'ced_sale_price_dates_from', $ced_sale_price_dates_from );

		$ced_sale_price_dates_to = isset( $_POST['ced_sale_price_dates_to'] ) ? sanitize_text_field( $_POST['ced_sale_price_dates_to'] ) : '';
		update_post_meta( $post_id, 'ced_sale_price_dates_to', $ced_sale_price_dates_to );

		$ced_onbuy_brand = isset( $_POST['ced_onbuy_brand'] ) ? sanitize_text_field( $_POST['ced_onbuy_brand'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_brand', $ced_onbuy_brand );

		$ced_onbuy_ean = isset( $_POST['ced_onbuy_ean'] ) ? sanitize_text_field( $_POST['ced_onbuy_ean'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_ean', $ced_onbuy_ean );

		$ced_onbuy_mpn = isset( $_POST['ced_onbuy_mpn'] ) ? sanitize_text_field( $_POST['ced_onbuy_mpn'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_mpn', $ced_onbuy_mpn );

		$ced_onbuy_handling_time = isset( $_POST['ced_onbuy_handling_time'] ) ? sanitize_text_field( $_POST['ced_onbuy_handling_time'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_handling_time', $ced_onbuy_handling_time );

		$ced_onbuy_summary_points1 = isset( $_POST['ced_onbuy_summary_points1'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points1'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points1', $ced_onbuy_summary_points1 );

		$ced_onbuy_summary_points2 = isset( $_POST['ced_onbuy_summary_points2'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points2'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points2', $ced_onbuy_summary_points2 );

		$ced_onbuy_summary_points3 = isset( $_POST['ced_onbuy_summary_points3'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points3'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points3', $ced_onbuy_summary_points3 );

		$ced_onbuy_summary_points4 = isset( $_POST['ced_onbuy_summary_points4'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points4'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points4', $ced_onbuy_summary_points4 );

		$ced_onbuy_summary_points5 = isset( $_POST['ced_onbuy_summary_points5'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points5'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points5', $ced_onbuy_summary_points5 );

		$ced_onbuy_rrp = isset( $_POST['ced_onbuy_rrp'] ) ? sanitize_text_field( $_POST['ced_onbuy_rrp'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_rrp', $ced_onbuy_rrp );

		$ced_onbuy_boost_percent = isset( $_POST['ced_onbuy_boost_percent'] ) ? sanitize_text_field( $_POST['ced_onbuy_boost_percent'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_boost_percent', $ced_onbuy_boost_percent );

		if ( isset( $_POST['ced_onbuy_var_data'] ) ) {
			foreach ( $sanitized_array['ced_onbuy_var_data'] as $var_id => $var_data ) {
				foreach ( $var_data as $meta_key => $meta_value ) {
					update_post_meta( $var_id, $meta_key, $meta_value );

				}
			}
		}
	}

	public function ced_onbuy_render_product_fields_html_for_variations( $loop, $variation_data, $variation ) {
		global $post;
		?><div id="ced_onbuy_variation_data_fields_wrap" style='max-height: 350px;min-height: 350px;
overflow: scroll;'>
			<label id="ced_onbuy_variation_data_label">OnBuy Variation Product Data</label>
			<span class="dashicons ced_instruction_icon dashicons-arrow-up-alt2"></span>
			<div id="ced_onbuy_variation_data_fields">
				<div id='onbuy_product_fields' class='panel woocommerce_options_panel'>
					<div class='options_group'>
						<form>
							<?php wp_nonce_field( 'ced_product_settings', 'ced_product_settings_submit' ); ?>
						</form>
						<?php
						$options                   = array(
							'0'                    => '--select condition--',
							'new'                  => 'New',
							'diamond'              => 'Refurbished (Diamond)',
							'platinum'             => 'Refurbished (Platinum)',
							'gold'                 => 'Refurbished (Gold)',
							'silver'               => 'Refurbished (Silver)',
							'bronze'               => 'Refurbished (Bronze)',
							'refurbished-ungraded' => 'Refurbished',
							'excellent'            => 'Excellent',
							'verygood'             => 'Very Good',
							'good'                 => 'Good',
							'average'              => 'Average',
							'belowaverage'         => 'Below Average',
						);
						$ced_onbuy_condition       = get_post_meta( $variation->ID, 'ced_onbuy_condition', true );
						$ced_onbuy_price           = get_post_meta( $variation->ID, 'ced_onbuy_price', true );
						$ced_onbuy_sale_price      = get_post_meta( $variation->ID, 'ced_onbuy_sale_price', true );
						$ced_sale_price_dates_from = get_post_meta( $variation->ID, 'ced_sale_price_dates_from', true );
						$ced_sale_price_dates_to   = get_post_meta( $variation->ID, 'ced_sale_price_dates_to', true );
						$ced_onbuy_ean             = get_post_meta( $variation->ID, 'ced_onbuy_ean', true );
						$ced_onbuy_brand           = get_post_meta( $variation->ID, 'ced_onbuy_brand', true );
						$ced_onbuy_mpn             = get_post_meta( $variation->ID, 'ced_onbuy_mpn', true );
						$ced_onbuy_handling_time   = get_post_meta( $variation->ID, 'ced_onbuy_handling_time', true );
						$ced_onbuy_summary_points1 = get_post_meta( $variation->ID, 'ced_onbuy_summary_points1', true );
						$ced_onbuy_summary_points2 = get_post_meta( $variation->ID, 'ced_onbuy_summary_points2', true );
						$ced_onbuy_summary_points3 = get_post_meta( $variation->ID, 'ced_onbuy_summary_points3', true );
						$ced_onbuy_summary_points4 = get_post_meta( $variation->ID, 'ced_onbuy_summary_points4', true );
						$ced_onbuy_summary_points5 = get_post_meta( $variation->ID, 'ced_onbuy_summary_points5', true );
						$ced_onbuy_rrp             = get_post_meta( $variation->ID, 'ced_onbuy_rrp', true );
						$ced_onbuy_boost_percent   = get_post_meta( $variation->ID, 'ced_onbuy_boost_percent', true );
						$ced_onbuy_title           = get_post_meta( $variation->ID, 'ced_onbuy_title', true );
						$ced_onbuy_description     = get_post_meta( $variation->ID, 'ced_onbuy_description', true );

						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_title]',
								'label'             => __( 'OnBuy Title', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product name or title must not exceed more than 150 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_title,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_description]',
								'label'             => __( 'OnBuy Description', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product description must not exceed more than 50,000 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_description,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_select(
							array(
								'id'          => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_condition]',
								'label'       => __( 'OnBuy Condition', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'    => 'true',
								'description' => __( 'Choose the product condition from the dropdown.', 'onbuy-integration-by-cedcommerce' ),
								'options'     => $options,
								'value'       => $ced_onbuy_condition,
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_price]',
								'label'             => __( 'OnBuy Price', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'desc_tip'          => 'true',
								'description'       => __( 'Enter the price to be uploaded on OnBuy.', 'onbuy-integration-by-cedcommerce' ),
								'value'             => $ced_onbuy_price,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_sale_price]',
								'label'             => __( 'OnBuy Sale Price', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Enter the sale price to be uploaded on OnBuy.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_sale_price,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'          => 'ced_onbuy_var_data[' . $variation->ID . '][ced_sale_price_dates_from]',
								'label'       => __( 'Sale Price Date From', 'onbuy-integration-by-cedcommerce' ),
								'type'        => 'date',
								'desc_tip'    => 'true',
								'description' => __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'onbuy-integration-by-cedcommerce' ),
								'placeholder' => 'From… DD-MM-YYYY',
								'value'       => $ced_sale_price_dates_from,
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'          => 'ced_onbuy_var_data[' . $variation->ID . '][ced_sale_price_dates_to]',
								'label'       => __( 'Sale Price Date To', 'onbuy-integration-by-cedcommerce' ),
								'type'        => 'date',
								'desc_tip'    => 'true',
								'description' => __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'onbuy-integration-by-cedcommerce' ),
								'placeholder' => 'To… DD-MM-YYYY',
								'value'       => $ced_sale_price_dates_to,
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_ean]',
								'label'             => __( 'Product Code', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product code or barcode must be a 13-digits valid code. It should not be GTIN reserved or invalid code. If the products having Brand exemption then no need of product code for the products.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_ean,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_brand]',
								'label'             => __( 'Brand', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'If the products having the Brand exemption for the products then make sure brand name must be same on woocommerce as well as OnBuy. For ex: and or & is treated as different thing while uploading.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_brand,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_mpn]',
								'label'             => __( 'Mpn', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Prodcut Mpn can be sku or product code of the products.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_mpn,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_handling_time]',
								'label'             => __( 'Handling Time', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Enter the handling time of the product.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_handling_time,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_summary_points1]',
								'label'             => __( 'Summary Points 1', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_summary_points1,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_summary_points2]',
								'label'             => __( 'Summary Points 2', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_summary_points2,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_summary_points3]',
								'label'             => __( 'Summary Points 3', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_summary_points3,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_summary_points4]',
								'label'             => __( 'Summary Points 4', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_summary_points4,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_summary_points5]',
								'label'             => __( 'Summary Points 5', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_summary_points5,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_rrp]',
								'label'             => __( 'Retail Price', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Enter the retail price of the product to be uploaded on Onbuy.', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_rrp,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						woocommerce_wp_text_input(
							array(
								'id'                => 'ced_onbuy_var_data[' . $variation->ID . '][ced_onbuy_boost_percent]',
								'label'             => __( 'Boost Percentage', 'onbuy-integration-by-cedcommerce' ),
								'desc_tip'          => 'true',
								'description'       => __( 'Enter the boost percentage to be apply on the products. Eg : 10', 'onbuy-integration-by-cedcommerce' ),
								'type'              => 'text',
								'value'             => $ced_onbuy_boost_percent,
								'custom_attributes' => array(
									'min'  => '1',
									'step' => '1',
								),
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function ced_onbuy_custom_product_tabs( $tab ) {
		$tab['onbuy_product_data'] = array(
			'label'  => __( 'OnBuy Data', 'onbuy-integration-by-cedcommerce' ),
			'target' => 'onbuy_product_fields',
			'class'  => array( 'show_if_simple' ),
		);
		return $tab;
	}

	public function ced_onbuy_custom_panel_tab() {
		global $post;
		?>
		<div id='onbuy_product_fields' class='panel woocommerce_options_panel' style='max-height: 350px;min-height: 350px;
overflow: scroll;'><div class='options_group'>
			<form>
				<?php wp_nonce_field( 'ced_product_settings', 'ced_product_settings_submit' ); ?>
			</form>
			<?php
			$options                   = array(
				'0'                    => '--select condition--',
				'new'                  => 'New',
				'diamond'              => 'Refurbished (Diamond)',
				'platinum'             => 'Refurbished (Platinum)',
				'gold'                 => 'Refurbished (Gold)',
				'silver'               => 'Refurbished (Silver)',
				'bronze'               => 'Refurbished (Bronze)',
				'refurbished-ungraded' => 'Refurbished',
				'excellent'            => 'Excellent',
				'verygood'             => 'Very Good',
				'good'                 => 'Good',
				'average'              => 'Average',
				'belowaverage'         => 'Below Average',
			);
			$ced_onbuy_condition       = get_post_meta( $post->ID, 'ced_onbuy_condition', true );
			$ced_onbuy_price           = get_post_meta( $post->ID, 'ced_onbuy_price', true );
			$ced_onbuy_sale_price      = get_post_meta( $post->ID, 'ced_onbuy_sale_price', true );
			$ced_sale_price_dates_from = get_post_meta( $post->ID, 'ced_sale_price_dates_from', true );
			$ced_sale_price_dates_to   = get_post_meta( $post->ID, 'ced_sale_price_dates_to', true );
			$ced_onbuy_ean             = get_post_meta( $post->ID, 'ced_onbuy_ean', true );
			$ced_onbuy_brand           = get_post_meta( $post->ID, 'ced_onbuy_brand', true );
			$ced_onbuy_mpn             = get_post_meta( $post->ID, 'ced_onbuy_mpn', true );
			$ced_onbuy_handling_time   = get_post_meta( $post->ID, 'ced_onbuy_handling_time', true );
			$ced_onbuy_summary_points1 = get_post_meta( $post->ID, 'ced_onbuy_summary_points1', true );
			$ced_onbuy_summary_points2 = get_post_meta( $post->ID, 'ced_onbuy_summary_points2', true );
			$ced_onbuy_summary_points3 = get_post_meta( $post->ID, 'ced_onbuy_summary_points3', true );
			$ced_onbuy_summary_points4 = get_post_meta( $post->ID, 'ced_onbuy_summary_points4', true );
			$ced_onbuy_summary_points5 = get_post_meta( $post->ID, 'ced_onbuy_summary_points5', true );
			$ced_onbuy_rrp             = get_post_meta( $post->ID, 'ced_onbuy_rrp', true );
			$ced_onbuy_boost_percent   = get_post_meta( $post->ID, 'ced_onbuy_boost_percent', true );
			$ced_onbuy_title           = get_post_meta( $post->ID, 'ced_onbuy_title', true );
			$ced_onbuy_description     = get_post_meta( $post->ID, 'ced_onbuy_description', true );

			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_title',
					'label'             => __( 'OnBuy Title', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product name or title must not exceed more than 150 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_title,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_description',
					'label'             => __( 'OnBuy Description', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product description must not exceed more than 50,000 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_description,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_select(
				array(
					'id'          => 'ced_onbuy_condition',
					'label'       => __( 'OnBuy Condition', 'onbuy-integration-by-cedcommerce' ),
					'options'     => $options, // this is where I am having trouble
					'value'       => $ced_onbuy_condition,
					'desc_tip'    => 'true',
					'description' => __( 'Choose the product condition from the dropdown.', 'onbuy-integration-by-cedcommerce' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_price',
					'label'             => __( 'OnBuy Price', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Enter the price to be uploaded on OnBuy.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_price,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_sale_price',
					'label'             => __( 'OnBuy Sale Price', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'desc_tip'          => 'true',
					'description'       => __( 'Enter the sale price to be uploaded on OnBuy.', 'onbuy-integration-by-cedcommerce' ),
					'value'             => $ced_onbuy_sale_price,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'ced_sale_price_dates_from',
					'label'       => __( 'Sale Price Date From', 'onbuy-integration-by-cedcommerce' ),
					'type'        => 'date',
					'desc_tip'    => 'true',
					'description' => __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'onbuy-integration-by-cedcommerce' ),
					'placeholder' => 'From… DD-MM-YYYY',
					'value'       => $ced_sale_price_dates_from,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'ced_sale_price_dates_to',
					'label'       => __( 'Sale Price Date To', 'onbuy-integration-by-cedcommerce' ),
					'type'        => 'date',
					'desc_tip'    => 'true',
					'description' => __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'onbuy-integration-by-cedcommerce' ),
					'placeholder' => 'To… DD-MM-YYYY',
					'value'       => $ced_sale_price_dates_to,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_ean',
					'label'             => __( 'Product Code', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product code or barcode must be a 13-digits valid code. It should not be GTIN reserved or invalid code. If the products having Brand exemption then no need of product code for the products.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_ean,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_brand',
					'label'             => __( 'Brand', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'If the products having the Brand exemption for the products then make sure brand name must be same on woocommerce as well as OnBuy. For ex: and or & is treated as different thing while uploading.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_brand,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_mpn',
					'label'             => __( 'Mpn', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Prodcut Mpn can be sku or product code of the products.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_mpn,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_handling_time',
					'label'             => __( 'Handling Time', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Enter the handling time of the product.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_handling_time,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_summary_points1',
					'label'             => __( 'Summary Points 1', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_summary_points1,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_summary_points2',
					'label'             => __( 'Summary Points 2', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_summary_points2,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_summary_points3',
					'label'             => __( 'Summary Points 3', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_summary_points3,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_summary_points4',
					'label'             => __( 'Summary Points 4', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_summary_points4,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_summary_points5',
					'label'             => __( 'Summary Points 5', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Product summary points must not exceed more than 500 characters.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_summary_points5,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_rrp',
					'label'             => __( 'Retail Price', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Enter the retail price of the product to be uploaded on Onbuy.', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_rrp,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_onbuy_boost_percent',
					'label'             => __( 'Boost Percentage', 'onbuy-integration-by-cedcommerce' ),
					'desc_tip'          => 'true',
					'description'       => __( 'Enter the boost percentage to be apply on the products. Eg : 10', 'onbuy-integration-by-cedcommerce' ),
					'type'              => 'text',
					'value'             => $ced_onbuy_boost_percent,
					'custom_attributes' => array(
						'min'  => '1',
						'step' => '1',
					),
				)
			);
			?>
		</div>
	</div>
		<?php
	}

	public function ced_onbuy_save_metadata( $post_id = '' ) {
		if ( ! isset( $_POST['ced_product_settings_submit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_product_settings_submit'] ) ), 'ced_product_settings' ) ) {
			return;
		}
		if ( empty( $post_id ) ) {
			return;
		}

		$sanitized_array     = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$ced_onbuy_condition = isset( $_POST['ced_onbuy_condition'] ) ? sanitize_text_field( $_POST['ced_onbuy_condition'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_condition', $ced_onbuy_condition );

		$ced_onbuy_price = isset( $_POST['ced_onbuy_price'] ) ? sanitize_text_field( $_POST['ced_onbuy_price'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_price', $ced_onbuy_price );

		$ced_onbuy_sale_price = isset( $_POST['ced_onbuy_sale_price'] ) ? sanitize_text_field( $_POST['ced_onbuy_sale_price'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_sale_price', $ced_onbuy_sale_price );

		$ced_sale_price_dates_from = isset( $_POST['ced_sale_price_dates_from'] ) ? sanitize_text_field( $_POST['ced_sale_price_dates_from'] ) : '';
		update_post_meta( $post_id, 'ced_sale_price_dates_from', $ced_sale_price_dates_from );

		$ced_sale_price_dates_to = isset( $_POST['ced_sale_price_dates_to'] ) ? sanitize_text_field( $_POST['ced_sale_price_dates_to'] ) : '';
		update_post_meta( $post_id, 'ced_sale_price_dates_to', $ced_sale_price_dates_to );

		$ced_onbuy_brand = isset( $_POST['ced_onbuy_brand'] ) ? sanitize_text_field( $_POST['ced_onbuy_brand'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_brand', $ced_onbuy_brand );

		$ced_onbuy_ean = isset( $_POST['ced_onbuy_ean'] ) ? sanitize_text_field( $_POST['ced_onbuy_ean'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_ean', $ced_onbuy_ean );

		$ced_onbuy_mpn = isset( $_POST['ced_onbuy_mpn'] ) ? sanitize_text_field( $_POST['ced_onbuy_mpn'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_mpn', $ced_onbuy_mpn );

		$ced_onbuy_handling_time = isset( $_POST['ced_onbuy_handling_time'] ) ? sanitize_text_field( $_POST['ced_onbuy_handling_time'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_handling_time', $ced_onbuy_handling_time );

		$ced_onbuy_summary_points1 = isset( $_POST['ced_onbuy_summary_points1'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points1'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points1', $ced_onbuy_summary_points1 );

		$ced_onbuy_summary_points2 = isset( $_POST['ced_onbuy_summary_points2'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points2'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points2', $ced_onbuy_summary_points2 );

		$ced_onbuy_summary_points3 = isset( $_POST['ced_onbuy_summary_points3'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points3'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points3', $ced_onbuy_summary_points3 );

		$ced_onbuy_summary_points4 = isset( $_POST['ced_onbuy_summary_points4'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points4'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points4', $ced_onbuy_summary_points4 );

		$ced_onbuy_summary_points5 = isset( $_POST['ced_onbuy_summary_points5'] ) ? sanitize_text_field( $_POST['ced_onbuy_summary_points5'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_summary_points5', $ced_onbuy_summary_points5 );

		$ced_onbuy_rrp = isset( $_POST['ced_onbuy_rrp'] ) ? sanitize_text_field( $_POST['ced_onbuy_rrp'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_rrp', $ced_onbuy_rrp );

		$ced_onbuy_boost_percent = isset( $_POST['ced_onbuy_boost_percent'] ) ? sanitize_text_field( $_POST['ced_onbuy_boost_percent'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_boost_percent', $ced_onbuy_boost_percent );

		$ced_onbuy_title = isset( $_POST['ced_onbuy_title'] ) ? sanitize_text_field( $_POST['ced_onbuy_title'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_title', $ced_onbuy_title );

		$ced_onbuy_description = isset( $_POST['ced_onbuy_description'] ) ? sanitize_text_field( $_POST['ced_onbuy_description'] ) : '';
		update_post_meta( $post_id, 'ced_onbuy_description', $ced_onbuy_description );

		if ( isset( $_POST['ced_onbuy_var_data'] ) ) {
			foreach ( $sanitized_array['ced_onbuy_var_data'] as $var_id => $var_data ) {
				foreach ( $var_data as $meta_key => $meta_value ) {
					update_post_meta( $var_id, $meta_key, $meta_value );

				}
			}
		}
	}



	public function ced_onbuy_authorise_account() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$consumerKey = isset( $_POST['consumerKey'] ) ? sanitize_text_field( $_POST['consumerKey'] ) : '';
			$secretKey   = isset( $_POST['secretKey'] ) ? sanitize_text_field( $_POST['secretKey'] ) : '';
			$sellerId    = isset( $_POST['sellerId'] ) ? sanitize_text_field( $_POST['sellerId'] ) : '';
			if ( isset( $secretKey ) && ! empty( $secretKey ) && isset( $consumerKey ) && ! empty( $consumerKey ) ) {
				$authorization_response = $this->ced_onbuy_instance->ced_get_validation_account( $consumerKey, $secretKey, $sellerId );
				if ( '200' != $authorization_response ) {
					echo 'Unauthorised Request';
					wp_die();
				} else {
					echo 'Details Saved';
					wp_die();
				}
			} else {
				echo 'Please fill both fields';
				wp_die();
			}
		}
	}

	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_change_account_status.
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_change_account_status() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_onbuy_accounts';
			$id         = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			$status     = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active';
			$wpdb->update( $table_name, array( 'account_status' => $status ), array( 'id' => $id ) );
			echo json_encode( array( 'status' => '200' ) );
			die;
		}
	}

	public function ced_onbuy_get_product_meta_keys_and_attributes() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_name = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
			if ( ! empty( $product_name ) ) {
				$file_metakeys_template = CED_ONBUY_DIRPATH . 'admin/partials/ced-onbuy-product-metakeys-attributes.php';
				if ( file_exists( $file_metakeys_template ) ) {
					include_once $file_metakeys_template;
				}
			}
		}
	}

	public function ced_onbuy_save_metakeys() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_onbuy_selected_metakeys', array() );
				if ( 'save' == $operation ) {
					$added_meta_keys[] = $metakey;
					update_option( 'ced_onbuy_selected_metakeys', $added_meta_keys );
					echo json_encode(
						array(
							'status'  => 200,
							'message' => 'Meatkeys/Attributes added successfully.',
						)
					);
					die();
				} else {
					if ( is_array( $added_meta_keys ) && ! empty( $added_meta_keys ) ) {
						foreach ( $added_meta_keys as $index => $value ) {
							if ( $metakey == $value ) {
								unset( $added_meta_keys[ $index ] );
								break;
							}
						}
						update_option( 'ced_onbuy_selected_metakeys', $added_meta_keys );
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Meatkeys/Attributes removed successfully.',
							)
						);
						die();
					}
				}
			} else {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => 'Unable to process.',
					)
				);
				die();
			}
		}
	}


	/**
	 * Ced_Onbuy_Integration_For_Woocommerce_Admin ced_onbuy_search_categories.
	 *
	 * @since 1.0.0
	 */
	public function ced_onbuy_search_categories() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$keyWord = isset( $_POST['keyWord'] ) ? sanitize_text_field( $_POST['keyWord'] ) : '';
			$shop_id = isset( $_POST['shop_id'] ) ? sanitize_text_field( $_POST['shop_id'] ) : '';
			if ( isset( $keyWord ) && ! empty( $keyWord ) && ! empty( $shop_id ) ) {
				$woo_saved_cat = get_option( 'ced_onbuy_fetched_categories', array() );
				do_action( 'ced_onbuy_refresh_token', $shop_id );
				$categories = $this->ced_onbuy_instance->ced_onbuy_fetch_category_method( $keyWord, $shop_id );
				if ( isset( $categories['results'] ) && ! empty( $categories['results'] ) && is_array( $categories['results'] ) ) {
					$options = '';
					if ( isset( $categories['results'] ) ) {
						foreach ( $categories['results'] as $key => $value ) {
							if ( isset( $woo_saved_cat[ $shop_id ] ) && ! empty( $woo_saved_cat[ $shop_id ] ) ) {
								if ( array_key_exists( $value['category_id'], $woo_saved_cat[ $shop_id ] ) ) {
									$selected = 'selected';
									$options .= '<option selected value="' . $value['category_id'] . '$' . $value['category_tree'] . '->' . $value['name'] . '">' . $value['category_tree'] . '->' . $value['name'] . '</option>';
								} else {
									$selected = '';
									$options .= '<option  value="' . $value['category_id'] . '$' . $value['category_tree'] . '->' . $value['name'] . '">' . $value['category_tree'] . '->' . $value['name'] . '</option>';
								}
							} else {
								$selected = '';
								$options .= '<option  value="' . $value['category_id'] . '$' . $value['category_tree'] . '->' . $value['name'] . '">' . $value['category_tree'] . '->' . $value['name'] . '</option>';
							}
						}
					}
					echo json_encode(
						array(
							'status'  => '200',
							'options' => $options,
						)
					);
					wp_die();
				} elseif ( isset( $categories['results'] ) && empty( $categories['results'] ) ) {
					echo json_encode(
						array(
							'status'  => '200',
							'options' => '<option>No Category Found With This KeyWord</option>',
						)
					);
					wp_die();
				} else {
					echo json_encode(
						array(
							'status'  => '400',
							'message' => $categories['error']['message'],
						)
					);
				}
			}
		}
	}

	public function ced_onbuy_manage_woo_category_dropdown() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id              = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_cat_id         = isset( $_POST['onbuy_cat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_cat_id'] ) ) : '';
			$product_ids          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$woo_selected_cat_ids = $product_ids['woo_selected_cat_ids'];

			$categories = get_option( 'ced_onbuy_mapped_categories', array() );

			$selected_categories   = isset( $categories[ $onbuy_cat_id ] ) ? $categories[ $onbuy_cat_id ] : array();
			$all_mapped_categories = isset( $categories['all_mapped_categories'] ) ? $categories['all_mapped_categories'] : array();

			$woo_cat        = ced_onbuy_get_woo_categories();
			$category_diff1 = array_diff( $all_mapped_categories, $selected_categories );
			$category_diff  = array_diff( $woo_cat, $category_diff1 );

			if ( ! empty( $category_diff ) ) {
				foreach ( $category_diff as $key => $value ) {
					if ( in_array( $value, $selected_categories ) ) {
						$options .= '<option selected value="' . $value . '">' . $key . '</option>';
					} else {
						$options .= '<option value="' . $value . '">' . $key . '</option>';
					}
				}
				echo json_encode(
					array(
						'status'  => '200',
						'options' => $options,
					)
				);
				wp_die();
			}
		}
	}

	public function ced_onbuy_manage_woo_selected_category_dropdown() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id      = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_cat_id = isset( $_POST['onbuy_cat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_cat_id'] ) ) : '';

			$product_ids          = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$woo_selected_cat_ids = ! empty( $product_ids['woo_selected_cat_ids'] ) ? $product_ids['woo_selected_cat_ids'] : array();

			$categories = array();

			$categories = get_option( 'ced_onbuy_mapped_categories', array() );

			$categories[ $onbuy_cat_id ] = $woo_selected_cat_ids;
			if ( ! isset( $categories['all_mapped_categories'] ) ) {
				$categories['all_mapped_categories'] = array();
			}

			if ( ! empty( $categories ) ) {
				$merged_categories = array();
				foreach ( $categories as $key => $value ) {

					if ( empty( $value ) ) {
						$value = array();
					}

					if ( 'all_mapped_categories' !== $key ) {

						$merged_categories = array_merge( $merged_categories, $value );
					}
				}
			}

			$merged_categories = array_unique( $merged_categories );

			$categories['all_mapped_categories'] = $merged_categories;
			update_option( 'ced_onbuy_mapped_categories', $categories );

			$woo_selected_cat_ids                       = array_unique( $woo_selected_cat_ids );
			$woo_saved_cat                              = get_option( 'ced_onbuy_saved_categories', array() );
			$woo_saved_cat[ $shop_id ][ $onbuy_cat_id ] = $woo_selected_cat_ids;
			update_option( 'ced_onbuy_saved_categories', $woo_saved_cat );
			$cat_to_remove = get_option( 'ced_woo_cat_to_remove', array() );
			$cat_to_remove = array_merge( $cat_to_remove, $woo_selected_cat_ids );
			$cat_to_remove = array_unique( $cat_to_remove );
			update_option( 'ced_woo_cat_to_remove', $cat_to_remove );
			$woo_cat              = ced_onbuy_get_woo_categories();
			$cat_to_remove        = get_option( 'ced_woo_cat_to_remove', array() );
			$categories_to_remove = $categories['all_mapped_categories'];
			$category_diff        = array_diff( $woo_cat, $categories_to_remove );
			$options              = '';
			foreach ( $woo_cat as $key => $value ) {
				if ( in_array( $value, $woo_selected_cat_ids ) ) {
					$options .= '<option selected value="' . $value . '">' . $key . '</option>';
				} else {
					$options .= '<option value="' . $value . '">' . $key . '</option>';
				}
			}

			echo json_encode(
				array(
					'status'  => '200',
					'options' => $options,
				)
			);
			wp_die();
		}
	}

	public function ced_onbuy_process_bulk_action() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_onbuy_manager = $this->ced_onbuy_instance;
			$shop_id           = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$is_shop_inactive  = ced_onbuy_inactive_shops( $shop_id );
			if ( $is_shop_inactive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'onbuy-integration-by-cedcommerce'
						),
					)
				);
				die;
			}

			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$operation       = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( wp_unslash( $_POST['operation_to_be_performed'] ) ) : '';
			$product_id      = isset( $sanitized_array['id'] ) ? ( $sanitized_array['id'] ) : '';

			$single_pro_id = $product_id;
			$notice        = '<span><b>Onbuy Response</b></span>';
			if ( 'upload_product' == $operation ) {
				$get_product_detail = $ced_onbuy_manager->ced_prepare_product_html_for_upload( $single_pro_id, $shop_id );

				foreach ( $get_product_detail as $product_name => $message ) {
					$notice .= '<p><i><a>' . $product_name . '</i></a> => <span class="onbuy-error">' . $message . '</span></p>';
				}
				if ( isset( $get_product_detail ) ) {
					echo json_encode(
						array(
							'status'  => 200,
							'message' => $notice,
							'prodid'  => $product_id,
						)
					);
					die;
				}
			} elseif ( 'update_product' == $operation ) {
				$get_product_detail = $ced_onbuy_manager->ced_prepare_product_html_for_update( $product_id, $shop_id );
				foreach ( $get_product_detail as $product_name => $message ) {
					$notice .= '<p><i><a>' . $product_name . '</i></a> => <span class="onbuy-error">' . $message . '</span></p>';
				}
				if ( isset( $get_product_detail ) ) {
					echo json_encode(
						array(
							'status'  => 200,
							'message' => $notice,
							'prodid'  => $product_id,
						)
					);
					die;
				}
			} elseif ( 'remove_product' == $operation ) {
				$get_product_detail = $ced_onbuy_manager->ced_prepare_product_html_for_delete( $single_pro_id, $shop_id );

				foreach ( $get_product_detail as $product_name => $message ) {
					$notice .= '<p><i><a>' . $product_name . '</i></a> => <span class="onbuy-error">' . $message . '</span></p>';
				}
				if ( isset( $get_product_detail ) ) {
					echo json_encode(
						array(
							'status'  => 200,
							'message' => $notice,
							'prodid'  => $product_id,
						)
					);
					die;
				}
			} elseif ( 'update_stock' == $operation ) {
				$get_product_detail = $ced_onbuy_manager->ced_prepare_product_html_for_update_stock( $single_pro_id, $shop_id );

				foreach ( $get_product_detail as $product_name => $message ) {
					$notice .= '<p><i><a>' . $product_name . '</i></a> => <span class="onbuy-error">' . $message . '</span></p>';
				}
				if ( isset( $get_product_detail ) ) {
					echo json_encode(
						array(
							'status'  => 200,
							'message' => $notice,
							'prodid'  => $product_id,
						)
					);
					die;
				}
			} elseif ( 'create_listing' == $operation ) {
				$get_product_detail = $ced_onbuy_manager->ced_prepare_product_html_for_create_listing( $single_pro_id, $shop_id );
				foreach ( $get_product_detail as $product_name => $message ) {
					$notice .= '<p><i><a>' . $product_name . '</i></a> => <span class="onbuy-error">' . $message . '</span></p>';
				}
				if ( isset( $get_product_detail ) ) {
					echo json_encode(
						array(
							'status'  => 200,
							'message' => $notice,
							'prodid'  => $product_id,
						)
					);
					die;
				}
			} elseif ( 'mark_as_not_uploaded' == $operation ) {

				foreach ( $product_id as $key => $value ) {

					$parent_product_id = wp_get_post_parent_id( $value );
					$product           = wc_get_product( $value );
					$type              = $product->get_type();

					if ( 'variable' == $type ) {
						$available_variations = $product->get_available_variations();
						foreach ( $available_variations as $available_variation ) {
							$variation_id = $available_variation['variation_id'];
							delete_post_meta( $variation_id, '_ced_onbuy_listing_id_' . $shop_id );
						}
						delete_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id );

					} else {
						delete_post_meta( $value, '_ced_onbuy_listing_id_' . $shop_id );
					}
					$notice .= '<p><i><a>' . $value . '</i></a> => <span class="onbuy-error">Product deleted</span></p>';

				}
				echo json_encode(
					array(
						'status'  => 200,
						'message' => $notice,

					)
				);
				die;
			}
		}
	}

	public function ced_onbuy_feed_details( $feed_id = '', $shop_id = '' ) {
		global $wpdb;
		$result    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_queue WHERE `id` = %d ', $feed_id ), 'ARRAY_A' );
		$queue_ids = json_decode( $result[0]['queue_id'], true );
		if ( isset( $queue_ids ) && ! empty( $queue_ids ) && is_array( $queue_ids ) ) {
			foreach ( $queue_ids as $pro_id => $queue_id ) {
				do_action( 'ced_onbuy_refresh_token', $shop_id );
				$queue_response[ $pro_id ] = $this->ced_onbuy_instance->ced_onbuy_fetch_queue_status( $queue_id, $shop_id );
			}
		}
		$tableHeader = array( __( 'Import Id', 'ced-umb' ), __( 'Feed Status', 'ced-umb' ), __( 'Product Id', 'ced-umb' ), __( 'Product Sku', 'ced-umb' ), __( 'Response', 'ced-umb' ) );
		if ( is_array( $queue_response ) && ! empty( $queue_response ) ) {
			echo '<br/>';
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead>';
			echo '<tr>';
			foreach ( $tableHeader as $value ) {
				echo '<th class="manage-column">' . esc_attr( $value ) . '</th>';
			}
			echo '</tr>';
			echo '</thead>';
			foreach ( $queue_response as $pro_id => $value ) {
				$_product = wc_get_product( $pro_id );
				$sku      = '';
				if ( ! is_bool( $_product ) ) {
					$type = $_product->get_type();
					$sku  = $_product->get_data()['sku'];
					if ( 'variable' == $type ) {
						update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $value['results']['opc'] );
						if ( $value['results']['variant_opcs'] ) {
							$variant_opcs = $value['results']['variant_opcs'];
							$_product     = wc_get_product( $pro_id );
							$variations   = $_product->get_available_variations();
							foreach ( $variations as $key => $variation ) {
								update_post_meta( $variation['variation_id'], '_ced_onbuy_listing_id_' . $shop_id, $variant_opcs[ $key ] );
							}
						}
					} else {
						update_post_meta( $pro_id, '_ced_onbuy_listing_id_' . $shop_id, $value['results']['opc'] );
					}
				}
				echo '<tbody>';
				echo '<tr>';
				echo '<td class="manage-column">' . esc_attr( $value['results']['queue_id'] ) . '</td>';
				echo '<td class="manage-column">' . esc_attr( $value['results']['status'] ) . '</td>';
				echo '<td class="manage-column">' . esc_attr( $pro_id ) . '</td>';
				echo '<td class="manage-column">' . esc_attr( $sku ) . '</td>';
				if ( isset( $value['results']['error_message'] ) && ! empty( $value['results']['error_message'] ) ) {
					echo '<td class="manage-column">' . esc_attr( $value['results']['error_message'] );
				} elseif ( isset( $value['results']['product_url'] ) ) {
					echo '<td class="manage-column"><a href="' . esc_attr( $value['results']['product_url'] ) . '">' . esc_attr( $value['results']['product_url'] ) . '</a>';
					update_post_meta( $pro_id, 'ced_onbuy_product_url_' . $shop_id, $value['results']['product_url'] );
				} else {
					echo '<td class="manage-column">';
				}
				echo '</td>';
				echo '</tr>';
				echo '</tbody>';
			}
			echo '</table>';
		}

	}

	public function ced_onbuy_get_orders() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';

			$is_shop_inactive = ced_onbuy_inactive_shops( $shop_id );
			if ( $is_shop_inactive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'onbuy-integration-by-cedcommerce'
						),
					)
				);
				die;
			}
			$file_orders = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
			if ( file_exists( $file_orders ) ) {
				include_once $file_orders;
			}
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			$onbuy_orders_instance    = Class_CedOnbuyOrders::get_instance();
			$ced_onbuy_get_the_orders = $onbuy_orders_instance->ced_onbuy_get_the_orders( $shop_id );
		}
	}

	public function ced_onbuy_cancel_order() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id          = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_order_id   = isset( $_POST['onbuy_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_order_id'] ) ) : '';
			$woo_order_id     = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$cancel_reason_id = isset( $_POST['cancel_reason_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cancel_reason_id'] ) ) : '';
			$cancel_info      = isset( $_POST['cancel_info'] ) ? sanitize_text_field( wp_unslash( $_POST['cancel_info'] ) ) : '';
			$file_orders      = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
			if ( file_exists( $file_orders ) ) {
				include_once $file_orders;
			}
			$cancel_data['orders'][] = array(
				'order_id'                     => $onbuy_order_id,
				'order_cancellation_reason_id' => $cancel_reason_id,
				'cancel_order_additional_info' => $cancel_info,
			);
			$cancel_data             = json_encode( $cancel_data );
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			$onbuy_orders_instance = Class_CedOnbuyOrders::get_instance();
			$response              = $onbuy_orders_instance->ced_onbuy_cancel_orders( $cancel_data, $shop_id );
			if ( isset( $response['error'] ) && ! empty( $response['error'] ) ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => $response['error']['message'],
					)
				);
				wp_die();
			} else {
				$order_status = wc_get_order( $woo_order_id );
				$order_status->update_status( 'wc-cancelled' );

				update_post_meta( $woo_order_id, '_onbuy_order_status', 'cancel' );
				update_post_meta( $woo_order_id, '_onbuy_order_status_template', 'cancel' );
				echo json_encode(
					array(
						'status'  => 200,
						'message' => $response['result'][0]['message'],
					)
				);
				wp_die();
			}
		}
	}

	public function ced_onbuy_refund_order() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id          = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_order_id   = isset( $_POST['onbuy_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_order_id'] ) ) : '';
			$woo_order_id     = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$refund_reason_id = isset( $_POST['refund_reason_id'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason_id'] ) ) : '';
			$refund_info      = isset( $_POST['refund_info'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_info'] ) ) : '';
			$file_orders      = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
			if ( file_exists( $file_orders ) ) {
				include_once $file_orders;
			}
			$refund_data['orders'][] = array(
				'order_id'               => $onbuy_order_id,
				'order_refund_reason_id' => $refund_reason_id,
				'delivery'               => $refund_info,
			);
			$refund_data             = json_encode( $refund_data );
			do_action( 'ced_onbuy_refresh_token', $shop_id );

			$onbuy_orders_instance = Class_CedOnbuyOrders::get_instance();
			$response              = $onbuy_orders_instance->ced_onbuy_refund_orders( $refund_data, $shop_id );
			if ( isset( $response['error'] ) && ! empty( $response['error'] ) ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => $response['error']['message'],
					)
				);
				wp_die();
			} else {
				$order_status = wc_get_order( $woo_order_id );
				$order_status->update_status( 'wc-refunded' );

				update_post_meta( $woo_order_id, '_onbuy_order_status', 'refund' );
				update_post_meta( $woo_order_id, '_onbuy_order_status_template', 'refund' );
				echo json_encode(
					array(
						'status'  => 200,
						'message' => $response['result'][0]['message'],
					)
				);
				wp_die();
			}
		}
	}

	public function ced_onbuy_complete_dispatch_order() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id              = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_order_id       = isset( $_POST['onbuy_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_order_id'] ) ) : '';
			$woo_order_id         = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$trackNumber          = isset( $_POST['trackNumber'] ) ? trim( sanitize_text_field( $_POST['trackNumber'] ) ) : false;
			$trackingUrl          = isset( $_POST['tracking_url'] ) ? trim( sanitize_text_field( $_POST['tracking_url'] ) ) : false;
			$shipping_provider_id = isset( $_POST['shipping_provider_id'] ) ? sanitize_text_field( $_POST['shipping_provider_id'] ) : false;
			if ( ! empty( $trackNumber ) && ! empty( $shipping_provider_id ) && ! empty( $onbuy_order_id ) ) {
				$onbuy_order_details = get_post_meta( $woo_order_id, '_onbuy_order_complete_details', true );
				$product_data        = array();
				if ( isset( $onbuy_order_details['products'] ) && ! empty( $onbuy_order_details['products'] ) ) {
					foreach ( $onbuy_order_details['products'] as $key => $value ) {
						$product_data[] = array(
							'onbuy_internal_reference' => $value['onbuy_internal_reference'],
							'quantity'                 => $value['quantity'],
						);
					}
				}
				$file_orders = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
				if ( file_exists( $file_orders ) ) {
					include_once $file_orders;
				}
				$dispatch_data['site_id']  = '2000';
				$dispatch_data['orders'][] = array(
					'order_id' => $onbuy_order_id,
					'tracking' => array(
						'tracking_id' => $shipping_provider_id,
						'number'      => $trackNumber,
						'url'         => trim( $trackingUrl . '?' . $trackNumber ),
					),
					'products' => $product_data,
				);
				$dispatch_data             = json_encode( $dispatch_data );
				do_action( 'ced_onbuy_refresh_token', $shop_id );

				$onbuy_orders_instance = Class_CedOnbuyOrders::get_instance();
				$response              = $onbuy_orders_instance->ced_onbuy_ship_orders( $dispatch_data, $shop_id );
				if ( $response['success'] && ! empty( $response['success'] ) ) {
					if ( isset( $response['results'][ $onbuy_order_id ]['message'] ) && ! empty( $response['results'][ $onbuy_order_id ]['message'] ) ) {
						echo json_encode(
							array(
								'status'  => '200',
								'message' => $response['results'][ $onbuy_order_id ]['message'],
							)
						);
						die;
					} else {
						$order_status = wc_get_order( $woo_order_id );
						$order_status->update_status( 'wc-completed' );

						update_post_meta(
							$woo_order_id,
							'_onbuy_order_details',
							array(
								'trackingNo' => $trackNumber,
								'provider'   => $shipping_provider_id,
							)
						);
						update_post_meta( $woo_order_id, '_onbuy_order_status', 'Complete Dispatched' );
						update_post_meta( $woo_order_id, '_onbuy_order_status_template', 'complete_dispatch' );
						echo json_encode(
							array(
								'status'  => '200',
								'message' => 'OnBuy Order Dispatched Successfully',
							)
						);
						die;
					}
				} elseif ( isset( $response['error'] ) && ! empty( $response['error'] ) ) {
					echo json_encode(
						array(
							'status'  => '402',
							'message' => $response['error']['message'],
						)
					);
					die;
				}
			} else {
				echo json_encode( array( 'message' => 'Please fill in all the details' ) );
				die;
			}
		}
	}


	public function ced_onbuy_partial_dispatch_order() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shop_id              = isset( $_POST['shopid'] ) ? sanitize_text_field( wp_unslash( $_POST['shopid'] ) ) : '';
			$onbuy_order_id       = isset( $_POST['onbuy_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['onbuy_order_id'] ) ) : '';
			$woo_order_id         = isset( $_POST['woo_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_order_id'] ) ) : '';
			$trackNumber          = isset( $_POST['trackNumber'] ) ? trim( sanitize_text_field( $_POST['trackNumber'] ) ) : false;
			$trackingUrl          = isset( $_POST['tracking_url'] ) ? trim( sanitize_text_field( $_POST['tracking_url'] ) ) : false;
			$shipping_provider_id = isset( $_POST['shipping_provider_id'] ) ? sanitize_text_field( $_POST['shipping_provider_id'] ) : false;
			$all_data_array       = isset( $_POST['all_data_array'] ) ? sanitize_text_field( $_POST['all_data_array'] ) : false;
			if ( isset( $all_data_array ) && is_array( $all_data_array ) ) {
				foreach ( $all_data_array as $key => $item ) {
					$data_cancel_to_fruugo = '';
					$find                  = explode( '/', $key );
					$check                 = $find[0];
					$unq_id                = $find[1];
					$product_ids           = explode( 'A', $unq_id );
					$product_id            = $product_ids[0];
					if ( 'sku' == $check ) {
						$all_info[ $product_id ]['sku'] = $item;
					}
					if ( 'qty_shipped' == $check ) {
						$all_info[ $product_id ]['qty_shipped'] = $item;
					}
					if ( 'qty_order' == $check ) {
						$all_info[ $product_id ]['qty_order'] = $item;
					}
					if ( 'pro_id' == $check ) {
						$all_info[ $product_id ]['pro_id'] = $item;
					}
				}
				foreach ( $all_info as $all_info_key => $all_info_valdata ) {
					$products_data_to_ship[] = array(
						'sku'      => $all_info_valdata['sku'],
						'quantity' => $all_info_valdata['qty_shipped'],
						'tracking' => array(
							'tracking_id' => $shipping_provider_id,
							'number'      => $trackNumber,
							'url'         => trim( $trackingUrl . '?' . $trackNumber ),
						),
					);
				}
			}
			if ( ! empty( $trackNumber ) && ! empty( $shipping_provider_id ) && ! empty( $onbuy_order_id ) && ! empty( $products_data_to_ship ) ) {
				$file_orders = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
				if ( file_exists( $file_orders ) ) {
					include_once $file_orders;
				}
				$dispatch_data['site_id']  = '2000';
				$dispatch_data['orders'][] = array(
					'order_id' => $onbuy_order_id,
					'products' => $products_data_to_ship,
				);
				$dispatch_data             = json_encode( $dispatch_data );
				do_action( 'ced_onbuy_refresh_token', $shop_id );

				$onbuy_orders_instance = Class_CedOnbuyOrders::get_instance();
				$response              = $onbuy_orders_instance->ced_onbuy_ship_orders( $dispatch_data, $shop_id );
				if ( $response['success'] && ! empty( $response['success'] ) ) {
					if ( isset( $response['results'][ $onbuy_order_id ]['message'] ) && ! empty( $response['results'][ $onbuy_order_id ]['message'] ) ) {
						echo json_encode(
							array(
								'status'  => '200',
								'message' => $response['results'][ $onbuy_order_id ]['message'],
							)
						);
						die;
					} else {
						update_post_meta(
							$woo_order_id,
							'_onbuy_order_details',
							array(
								'trackingNo' => $trackNumber,
								'provider'   => $shipping_provider_id,
							)
						);
						update_post_meta( $woo_order_id, '_onbuy_partial_order_item_details', $products_data_to_ship );
						update_post_meta( $woo_order_id, '_onbuy_order_status', 'Partially Dispatched' );
						update_post_meta( $woo_order_id, '_onbuy_order_status_template', 'partials_dispatch' );
						echo json_encode(
							array(
								'status'  => '200',
								'message' => 'OnBuy Order Partially Dispatched Successfully',
							)
						);
						die;
					}
				} elseif ( isset( $response['error'] ) && ! empty( $response['error'] ) ) {
					echo json_encode(
						array(
							'status'  => '402',
							'message' => $response['error']['message'],
						)
					);
					die;
				}
			} else {
				echo json_encode( array( 'message' => 'Please fill in all the details' ) );
				die;
			}
		}
	}

	public function ced_onbuy_add_order_metabox() {
		global $post;
		$product = wc_get_product( $post->ID );
		add_meta_box(
			'ced_onbuy_manage_orders_metabox',
			__( 'Manage Marketplace Orders', 'onbuy-integration-by-cedcommerce' ) . wc_help_tip( __( 'Please send shipping confirmation or order cancellation request.', 'onbuy-integration-by-cedcommerce' ) ),
			array( $this, 'ced_onbuy_render_orders_metabox' ),
			'shop_order',
			'advanced',
			'high'
		);
	}

	public function ced_onbuy_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';
		if ( ! is_null( $order_id ) ) {
			$order = wc_get_order( $order_id );

			$order_from  = get_post_meta( $order_id, '_ced_onbuy_order_id', true );
			$marketplace = strtolower( $order_from );

			$template_path = CED_ONBUY_DIRPATH . 'admin/partials/order-template.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
		}
	}

	public function ced_onbuy_auto_product_upload_schedule_manager() {

		$shopid = get_option( 'ced_onbuy_shop_id', '' );

		$products_to_sync = get_option( 'ced_onbuy_auto_upload_chunk_product', array() );

		if ( empty( $products_to_sync ) ) {
			$store_products   = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					'meta_key'     => '_ced_onbuy_listing_id_' . $shopid,
					'meta_compare' => 'NOT EXISTS',
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 25 );
		}

		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			do_action( 'ced_onbuy_refresh_token', $shopid );

			$get_product_detail = $this->ced_onbuy_instance->ced_prepare_product_html_for_upload( $products_to_sync[0], $shopid, true );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_onbuy_auto_upload_chunk_product', $products_to_sync );
		}

	}

	// ------------------------------------------------------------------
	public function ced_onbuy_check_winning_price() {

		$shopid = get_option( 'ced_onbuy_shop_id', '' );

		$skus_to_check_win_price = get_option( 'ced_onbuy_winning_price_chunk_sku', array() );

		if ( empty( $skus_to_check_win_price ) ) {
			$store_products = get_posts(
				array(
					'numberposts'  => -1,
					'post_type'    => 'product',
					'meta_key'     => '_ced_onbuy_listing_id_' . $shopid,
					'meta_compare' => 'EXISTS',
				)
			);
			$store_products = wp_list_pluck( $store_products, 'ID' );

			foreach ( $store_products as $key => $post_id ) {
				$skus[] = get_post_meta( $post_id, '_sku', true );
			}
			$skus_to_check_win_price = array_chunk( $skus, 20 );
		}
		$file_onbuy = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-products.php';
		if ( file_exists( $file_onbuy ) ) {
			include_once $file_onbuy;
		}
		$ced_onbuy_products = new Class_Ced_Onbuy_Products();
		if ( is_array( $skus_to_check_win_price[0] ) && ! empty( $skus_to_check_win_price[0] ) ) {
			$get_product_detail = $ced_onbuy_products->ced_check_winning_price_to_onbuy( $skus_to_check_win_price[0], $shopid );
			unset( $skus_to_check_win_price[0] );
			$skus_to_check_win_price = array_values( $skus_to_check_win_price );
			update_option( 'ced_onbuy_winning_price_chunk_sku', $skus_to_check_win_price );
		}

	}
	// ----------------------------------------------------------------------------------


	public function ced_onbuy_inventory_schedule_manager() {
		$hook           = current_action();
		$shopid         = get_option( $hook );
		$sync_inventory = get_option( 'onbuy_auto_syncing' . $shopid, false );

		$products_to_sync = get_option( 'ced_onbuy_chunk_product', array() );
		if ( empty( $products_to_sync ) ) {
			$store_products   = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'meta_query'  => array(
						array(
							'key'     => '_ced_onbuy_listing_id_' . $shopid,
							'compare' => 'EXISTS',
						),
					),
				)
			);
			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 100 );
		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			do_action( 'ced_onbuy_refresh_token', $shopid );
			$get_product_detail = $this->ced_onbuy_instance->ced_prepare_product_html_for_update_stock( $products_to_sync[0], $shopid, true );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_onbuy_chunk_product', $products_to_sync );
		}

	}

	public function ced_onbuy_order_schedule_manager() {
		$hook   = current_action();
		$shopid = get_option( $hook );

		if ( empty( $shopid ) ) {
			$shopid = get_option( 'ced_onbuy_shop_id' );

		}

		$file_orders = CED_ONBUY_DIRPATH . 'admin/onbuy/partials/class-ced-onbuy-orders.php';
		if ( file_exists( $file_orders ) ) {
			include_once $file_orders;
		}
		do_action( 'ced_onbuy_refresh_token', $shopid );
		$onbuy_orders_instance    = Class_CedOnbuyOrders::get_instance();
		$ced_onbuy_get_the_orders = $onbuy_orders_instance->ced_onbuy_get_the_orders( $shopid, true );
	}

	public function ced_onbuy_product_sync_schedule_manager() {

		$hook           = current_action();
		$shopid         = get_option( $hook );
		$sync_inventory = get_option( 'onbuy_auto_syncing' . $shopid, false );

		$products_to_sync = get_option( 'ced_onbuy_chunk_product_sync', array() );

		if ( empty( $products_to_sync ) ) {
			$store_products = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product',
					'meta_query'  => array(
						array(
							'key'     => '_ced_onbuy_listing_id_' . $shopid,
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);

			$store_products   = wp_list_pluck( $store_products, 'ID' );
			$products_to_sync = array_chunk( $store_products, 20 );
		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			do_action( 'ced_onbuy_refresh_token', $shopid );
			$get_product_detail = $this->ced_onbuy_instance->ced_prepare_product_html_for_product_sync( $products_to_sync[0], $shopid );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_onbuy_chunk_product_sync', $products_to_sync );
		}

	}

	public function ced_onbuy_process_queue_schedule_manager() {
		$hook   = current_action();
		$shopid = get_option( $hook );

		if ( empty( $shopid ) ) {
			$shopid = get_option( 'ced_onbuy_shop_id' );

		}
		$products_to_sync = get_option( 'ced_onbuy_chunk_queue_schedules', array() );
		if ( empty( $products_to_sync ) ) {
			$args           = array(
				'post_type'   => 'product',
				'fields'      => 'ids',
				'numberposts' => -1,
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => '_ced_onbuy_listing_id_' . $shopid,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_ced_onbuy_queue_id_' . $shopid,
						'compare' => 'EXISTS',
					),
				),
			);
			$store_products = get_posts( $args );

			$products_to_sync = array_chunk( $store_products, 50 );
		}
		if ( is_array( $products_to_sync[0] ) && ! empty( $products_to_sync[0] ) ) {
			$get_product_detail = $this->ced_onbuy_instance->ced_onbuy_process_queue( $products_to_sync[0], $shopid );
			unset( $products_to_sync[0] );
			$products_to_sync = array_values( $products_to_sync );
			update_option( 'ced_onbuy_chunk_queue_schedules', $products_to_sync );
		}
	}

	public function ced_onbuy_delete_profile() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
			if ( 'ced_onbuy_delete_profile' == $action ) {
				$profile_id = isset( $_POST['profile_id'] ) ? sanitize_text_field( $_POST['profile_id'] ) : '';
				$shop_id    = isset( $_POST['shop_id'] ) ? sanitize_text_field( $_POST['shop_id'] ) : '';

				$saved_cat = get_option( 'ced_onbuy_selected_cat_to_render_' . $shop_id, array() );

				foreach ( $saved_cat as $key => $value ) {
					if ( $value['catId'] == $profile_id ) {
						unset( $saved_cat[ $key ] );

					}
				}
				global $wpdb;
				$wpdb->delete( $wpdb->prefix . 'termmeta', array( 'meta_value' => $profile_id ) );
				$data = array_values( $saved_cat );
				update_option( 'ced_onbuy_selected_cat_to_render_' . $shop_id, $data );
				echo 'deleted';

			}

			die;
		}
	}

	public function ced_onbuy_search_product_name() {

		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

			$product_list = '';
			if ( ! empty( $keyword ) ) {
				$arguements = array(
					'numberposts' => -1,
					'post_type'   => array( 'product', 'product_variation' ),
					's'           => $keyword,
				);
				$post_data  = get_posts( $arguements );
				if ( ! empty( $post_data ) ) {
					foreach ( $post_data as $key => $data ) {
						$product_list .= '<li class="ced_onbuy_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'onbuy-integration-by-cedcommerce' ) ) . '</li>';
					}
				} else {
					$product_list .= '<li>No products found.</li>';
				}
			} else {
				$product_list .= '<li>No products found.</li>';
			}
			echo json_encode( array( 'html' => $product_list ) );
			wp_die();
		}
	}

	public function ced_onbuy_get_product_metakeys() {
		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once CED_ONBUY_DIRPATH . 'admin/partials/ced-onbuy-metakeys-list.php';
		}
	}


	public function ced_onbuy_update_stock( $meta_id, $product_id, $meta_key, $_meta_value ) {

		$shop_id = get_option( 'ced_onbuy_shop_id' );

		if ( '_stock' == $meta_key ) {

			$prod_data = wc_get_product( $product_id );
			$type      = $prod_data->get_type();
			if ( 'variation' == $type ) {

				$product_id = $prod_data->get_parent_id();

			}
			do_action( 'ced_onbuy_refresh_token', $shop_id );
			$this->ced_onbuy_instance->ced_prepare_product_html_for_update_stock( array( $product_id ), $shop_id );
		}
	}

	// =================================================================================================

	/**
	 * OnBuy Mapping Categories to WooStore
	 *
	 * @since    1.0.0
	 */
	public function ced_onbuy_map_categories_to_store() {

		$check_ajax = check_ajax_referer( 'ced-onbuy-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$onbuy_category_array = isset( $sanitized_array['onbuy_category_array'] ) ? $sanitized_array['onbuy_category_array'] : '';
			$store_category_array = isset( $sanitized_array['store_category_array'] ) ? $sanitized_array['store_category_array'] : '';
			$onbuy_category_name  = isset( $sanitized_array['onbuy_category_name'] ) ? $sanitized_array['onbuy_category_name'] : '';
			$shop_id              = isset( $_POST['shop_id'] ) ? sanitize_text_field( wp_unslash( $_POST['shop_id'] ) ) : '';

			$onbuy_saved_category           = get_option( 'ced_onbuy_saved_category', array() );
			$already_mapped_categories      = array();
			$already_mapped_categories_name = array();
			$onbuy_mapped_categories        = array_combine( $store_category_array, $onbuy_category_array );
			$onbuy_mapped_categories        = array_filter( $onbuy_mapped_categories );
			$already_mapped_categories      = get_option( 'ced_woo_onbuy_mapped_categoriess', array() );
			if ( is_array( $onbuy_mapped_categories ) && ! empty( $onbuy_mapped_categories ) ) {
				foreach ( $onbuy_mapped_categories as $key => $value ) {
					$already_mapped_categories[ $key ] = $value;
				}
			}
			update_option( 'ced_woo_onbuy_mapped_categoriess', $already_mapped_categories );
			$onbuy_mapped_categories_name   = array_combine( $onbuy_category_array, $onbuy_category_name );
			$onbuy_mapped_categories_name   = array_filter( $onbuy_mapped_categories_name );
			$already_mapped_categories_name = get_option( 'ced_woo_onbuy_mapped_categories_names', array() );
			if ( is_array( $onbuy_mapped_categories_name ) && ! empty( $onbuy_mapped_categories_name ) ) {
				foreach ( $onbuy_mapped_categories_name as $key => $value ) {
					$already_mapped_categories_name[ $key ] = $value;
				}
			}

			update_option( 'ced_woo_onbuy_mapped_categories_names', $already_mapped_categories_name );
			$this->ced_onbuy_instance->ced_onbuy_create_auto_profiles( $onbuy_mapped_categories, $onbuy_mapped_categories_name, $shop_id );
			wp_die();
		}

	}
}
