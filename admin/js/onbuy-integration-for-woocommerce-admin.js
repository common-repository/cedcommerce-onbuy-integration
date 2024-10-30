(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 var parsed_response;
	 var ajaxUrl   = ced_onbuy_admin_obj.ajax_url;
	 var ajaxNonce = ced_onbuy_admin_obj.ajax_nonce;
	 var shop_id   = ced_onbuy_admin_obj.shop_id;

	 // ----------------------------------------------------------------
	$( document ).on(
		'click',
		'#ced_onbuy_account_details',
		function(){
			window.open( "https://seller.onbuy.com/inventory/integrations/onbuy-api/" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_category_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=category-search-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_global_settings_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=metakeys-and-attributes-list-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_product_data_settings_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=product-data-setting-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_product_settings_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=product-management-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_upload_product_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=uploading-the-products-on-onbuy-2" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_update_product_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=updating-the-products-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_update_stock_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=updating-the-stock-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_remove_listing_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=removing-products-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_create_listing_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=creating-list-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_mark_not_uploaded_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=mark-products-as-not-uploaded-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_queue_manage_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=managing-queue-on-onbuy" );
		}
	);

	$( document ).on(
		'click',
		'#ced_onbuy_orders_link',
		function(){
			window.open( "https://docs.cedcommerce.com/woocommerce/onbuy-integration-woocommerce/?section=order-management-on-onbuy" );
		}
	);
	// ----------------------------------------------------------------------------------------------

	$( document ).on(
		'click',
		'.ced_onbuy_add_account_button',
		function(){

			$( document ).find( '.ced_onbuy_add_account_popup_main_wrapper' ).addClass( 'show' );

		}
	);
	$( document ).on(
		'click',
		'.ced_onbuy_add_account_popup_close',
		function(){

			$( document ).find( '.ced_onbuy_add_account_popup_main_wrapper' ).removeClass( 'show' );

		}
	);
	$( document ).on(
		'click',
		"#ced_onbuy_update_account_status",
		function(){

			var status = $( "#ced_onbuy_account_status" ).val();
			var id     = $( this ).attr( "data-id" );
			var url    = window.location.href;
			$( '.ced_onbuy_loader' ).show();
			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_onbuy_change_account_status',
						status : status,
						id : id
					},
					type : 'POST',
					success: function(response)
				{
						var response         = jQuery.parseJSON( response );
						window.location.href = url;
					}
				}
			);

		}
	);

	 // --------------------------------------------
	$( document ).on(
		'click',
		'.ced_onbuy_render_meta_keys_toggle',
		function(){
			$( '.ced_onbuy_render_meta_keys_content' ).slideToggle();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_render_product_setting_toggle',
		function(){
			$( '.ced_onbuy_render_product_setting_content' ).slideToggle();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_render_order_setting_toggle',
		function(){
			$( '.ced_onbuy_render_order_setting_content' ).slideToggle();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_render_scheduler_setting_toggle',
		function(){
			$( '.ced_onbuy_render_scheduler_setting_content' ).slideToggle();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_instructions_toggle',
		function(){
			$( '.ced_onbuy_instructions_content' ).slideToggle();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_render_buybox_setting_toggle',
		function(){
			$( '.ced_onbuy_render_buybox_setting_content' ).slideToggle();
		}
	);
	// ------------------------------------------------

	$( document ).on(
		'click',
		'.ced_onbuy_render_meta_key_search_box_toggle',
		function(){
			$( '.ced_onbuy_render_meta_key_search_box' ).slideToggle();
		}
	);

	$( document ).on(
		'change',
		'.ced_onbuy_meta_key',
		function(){
			$( '.ced_onbuy_loader' ).show();
			var metakey   = $( this ).val();
			var operation = '';
			if ( $( this ).is( ':checked' ) ) {
				operation = 'save';
			} else {
				operation = 'remove';
			}
			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_onbuy_save_metakeys',
						metakey : metakey ,
						operation : operation
					},
					type : 'POST',
					success: function(response)
				{
						$( '.ced_onbuy_loader' ).hide();

					}
				}
			);
		}
	);

		$( document ).on(
			'keyup' ,
			'#ced_onbuy_search_product_name' ,
			function() {
				var keyword = $( this ).val();
				if ( keyword.length < 3 ) {
					var html = '';
					html    += '<li>Please enter 3 or more characters.</li>';
					$( document ).find( '.ced-onbuy-search-product-list' ).html( html );
					$( document ).find( '.ced-onbuy-search-product-list' ).show();
					return;
				}

				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							keyword : keyword,
							action : 'ced_onbuy_search_product_name',
						},
						type:'POST',
						success : function( response ) {

							parsed_response = jQuery.parseJSON( response );
							$( document ).find( '.ced-onbuy-search-product-list' ).html( parsed_response.html );
							$( document ).find( '.ced-onbuy-search-product-list' ).show();
						},
						error : function( error ) {
						}
					}
				);

			}
		);

		$( document ).on(
			'click' ,
			'.ced_onbuy_searched_product' ,
			function() {
				$( '.ced_onbuy_loader' ).show();
				var post_id = $( this ).data( 'post-id' );

				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							post_id : post_id,
							action : 'ced_onbuy_get_product_metakeys',
						},
						type:'POST',
						success : function( response ) {
							$( '.ced_onbuy_loader' ).hide();
							parsed_response = jQuery.parseJSON( response );
							$( document ).find( '.ced-onbuy-search-product-list' ).hide();
							$( ".ced_onbuy_render_meta_keys_content" ).removeClass( 'hide' );
							$( ".ced_onbuy_render_meta_keys_content1" ).html( parsed_response.html );
							$( ".ced_onbuy_render_meta_keys_content1" ).show();
						}
					}
				);
			}
		);

		$( document ).ready(
			function(){
				$( '.ced_onbuy_categories' ).hide();
				$( '.ced_onbuy_loader' ).hide();
				$( '.ced_onbuy_notification_messages' ).hide();
			}
		);

		$( document ).on(
			'click' ,
			'.ced_onbuy_save_metakey_button' ,
			function(){
				location.reload( true );
			}
		);

		$( document ).on(
			'click',
			'#ced_onbuy_authorise_account_button',
			function(){
				var consumerKey = $( "#ced_onbuy_consumer_key" ).val();
				var secretKey   = $( "#ced_onbuy_secret_key" ).val();
				var sellerId    = $( "#ced_onbuy_seller_id" ).val();
				var execute     = true;
				$( ".ced_onbuy_api_input" ).css( 'border','1px solid grey' );
				$( ".ced_onbuy_api_input" ).each(
					function() {
						if ( $( this ).val() == "" || $( this ).val() == null ) {
							$( this ).css( 'border','1px solid red' );
							execute = false;
						}
					}
				);

				if ( ! execute ) {
					return false;
				}
				$( '.ced_onbuy_loader' ).show();

				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_onbuy_authorise_account',
							consumerKey : consumerKey,
							secretKey : secretKey,
							sellerId : sellerId,
						},
						type : 'POST',
						success: function(response)
					{
							location.reload( true );
						}
					}
				);

			}
		);

		$( document ).on(
			'change' ,
			'.ced_woo_categories_select' ,
			function(){
				var onbuy_cat_id      = $( this ).parents( "div" ).prev().prev().attr( "data-id" ); // cat get disappear resolved
				var selected_category = $( this ).val();
				console.log( selected_category );
				$( '.ced_onbuy_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_onbuy_manage_woo_selected_category_dropdown',
							onbuy_cat_id : onbuy_cat_id,
							woo_selected_cat_ids : selected_category,
							shopid : shop_id,
						},
						type : 'POST',
						success: function(response)
					{

								$( '.ced_onbuy_loader' ).hide();
						}
					}
				);
			}
		)

		$( document ).on(
			'click',
			'.ced_onbuy_render_category_wrapper label',
			function(){

				if ($( this ).find( '.ced_onbuy_instruction_icon' ).hasClass( "dashicons-arrow-down-alt2" )) {
					$( this ).find( '.ced_onbuy_instruction_icon' ).removeClass( "dashicons-arrow-down-alt2" );
					$( this ).find( '.ced_onbuy_instruction_icon' ).addClass( "dashicons-arrow-up-alt2" );
				} else if ($( this ).find( '.ced_onbuy_instruction_icon' ).hasClass( "dashicons-arrow-up-alt2" )) {
					$( this ).find( '.ced_onbuy_instruction_icon' ).addClass( "dashicons-arrow-down-alt2" );
					$( this ).find( '.ced_onbuy_instruction_icon' ).removeClass( "dashicons-arrow-up-alt2" );
				}
				var onbuy_cat_id = $( this ).attr( "data-id" );
				$( '.ced_onbuy_render_category_' + onbuy_cat_id ).slideToggle();
				var selected_category = $( '.ced_onbuy_render_category_' + onbuy_cat_id ).find( '.ced_woo_categories_select' ).val();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_onbuy_manage_woo_category_dropdown',
							onbuy_cat_id : onbuy_cat_id,
							woo_selected_cat_ids : selected_category,
							shopid : shop_id,
						},
						type : 'POST',
						success: function(response)
					{
							var response = jQuery.parseJSON( response );
							if (response.status == '200') {
								$( '.ced_onbuy_render_category_' + onbuy_cat_id ).find( '.ced_woo_categories_select' ).html( "<option></option>" );
								$( '.ced_onbuy_render_category_' + onbuy_cat_id ).find( '.ced_woo_categories_select' ).append( response.options );

							}
							$( '.ced_onbuy_loader' ).hide();
						}
					}
				);
			}
		);

		$( document ).on(
			'keyup',
			'#ced_onbuy_category_search',
			function(){
				var keyWord           = $( "#ced_onbuy_category_search" ).val();
				var length            = $( "#ced_onbuy_category_search" ).val().length;
				var store_category_id = $( this ).attr( 'data-categoryID' );
				if (length >= 2) {
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_search_categories',
								keyWord : keyWord,
								store_category_id : store_category_id,
								shop_id : shop_id,
							},
							type : 'POST',
							success: function(response)
						{
								var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									$( document ).find( '.ced_onbuy_categories_' ).show();
									$( document ).find( '.ced_onbuy_level1_category' ).html( "<option>--Select OnBuy Category--</option>" );
									$( document ).find( '.ced_onbuy_level1_category' ).append( response.options );

								} else {
									alert( response.message );
								}
								$( '.ced_onbuy_loader' ).hide();
							}
						}
					);
				}
			}
		);

		$( document ).on(
			'click',
			'.ced_onbuy_parent_element',
			function(){
				if ($( this ).find( '.ced_onbuy_instruction_icon' ).hasClass( "dashicons-arrow-down-alt2" )) {
					$( this ).find( '.ced_onbuy_instruction_icon' ).removeClass( "dashicons-arrow-down-alt2" );
					$( this ).find( '.ced_onbuy_instruction_icon' ).addClass( "dashicons-arrow-up-alt2" );
				} else if ($( this ).find( '.ced_onbuy_instruction_icon' ).hasClass( "dashicons-arrow-up-alt2" )) {
					$( this ).find( '.ced_onbuy_instruction_icon' ).addClass( "dashicons-arrow-down-alt2" );
					$( this ).find( '.ced_onbuy_instruction_icon' ).removeClass( "dashicons-arrow-up-alt2" );
				}
				$( this ).next( '.ced_onbuy_child_element' ).slideToggle();
			}
		);

		$( document ).on(
			'click' ,
			'.ced_onbuy_navigation' ,
			function() {
				$( '.ced_onbuy_loader' ).show();
				var page_no = $( this ).data( 'page' );
				$( '.ced_onbuy_metakey_body' ).hide();
				window.setTimeout( function() {$( '.ced_onbuy_loader' ).hide()},500 );
				$( document ).find( '.ced_onbuy_metakey_list_' + page_no ).show();
			}
		);

		$( document ).on(
			'change',
			'#ced_onbuy_scheduler_info',
			function(){

				if (this.checked) {
					$( ".ced_onbuy_scheduler_info" ).css( 'display','contents' );
				} else {
					$( ".ced_onbuy_scheduler_info" ).css( 'display','none' );
				}
			}
		);

		$( document ).on(
			'change',
			'#ced_buybox_type',
			function(){
				var is_sync_enabled = $( this ).val();
				is_sync_enabled     = jQuery.trim( is_sync_enabled );
				if (is_sync_enabled == 'Fixed_Decreased' || is_sync_enabled == 'Percentage_Decreased') {
					$( ".ced_onbuy_buybox_decreased_price" ).css( 'display','table-row' );
				} else {
					$( ".ced_onbuy_buybox_decreased_price" ).css( 'display','none' );
				}
			}
		);

		$( document ).on(
			'change',
			'#ced_update_decreased_price',
			function(){
				var is_sync_enabled = $( this ).val();
				is_sync_enabled     = jQuery.trim( is_sync_enabled );
				if (is_sync_enabled == 'yes') {
					$( ".ced_onbuy_enable_buybox_setting" ).css( 'display','table-row' );
				} else {
					$( ".ced_onbuy_enable_buybox_setting" ).css( 'display','none' );
					$( ".ced_onbuy_buybox_decreased_price" ).css( 'display','none' );
				}
			}
		);

		$( document ).on(
			'click',
			'.ced_onbuy_order_template_sbutton',
			function(){
				var div = $( this ).attr( 'data-id' );
				if (div) {
					if (div == 'ced_onbuy_complete_dispatch_template') {
						$( "#" + div ).show();
						$( "#ced_onbuy_partials_dispatch_template" ).hide();
						$( "#ced_onbuy_cancel_template" ).hide();
						$( "#ced_onbuy_refund_template" ).hide();
					}
					if (div == 'ced_onbuy_partials_dispatch_template') {
						$( "#" + div ).show();
						$( "#ced_onbuy_complete_dispatch_template" ).hide();
						$( "#ced_onbuy_cancel_template" ).hide();
						$( "#ced_onbuy_refund_template" ).hide();
					}
					if (div == 'ced_onbuy_cancel_template') {
						$( "#" + div ).show();
						$( "#ced_onbuy_complete_dispatch_template" ).hide();
						$( "#ced_onbuy_partials_dispatch_template" ).hide();
						$( "#ced_onbuy_refund_template" ).hide();
					}
					if (div == 'ced_onbuy_refund_template') {
						$( "#" + div ).show();
						$( "#ced_onbuy_complete_dispatch_template" ).hide();
						$( "#ced_onbuy_partials_dispatch_template" ).hide();
						$( "#ced_onbuy_cancel_template" ).hide();
					}
				} else {
					$( "#" + div ).hide();
				}
			}
		);

		/*---------------------------------Bulk Actions in Manage Products-------------------------------------------------*/

		$( document ).on(
			'click',
			'#ced_onbuy_bulk_operation',
			function(e){
				$( '.success-admin-notices' ).show();
				e.preventDefault();
				var operation = $( this ).data( 'operation' );
				if (operation <= 0 ) {
					var notice = "";
					notice    += "<div class='notice notice-error'>Please Select Operation To Be Performed</div>";
					$( ".success-admin-notices" ).append( notice );
				} else {
					var onbuy_products_id = new Array();
					$( '.onbuy_products_id:checked' ).each(
						function(){
							onbuy_products_id.push( $( this ).val() );
						}
					);
					if (onbuy_products_id == "") {
						var notice = "";
						notice    += "<div class='notice notice-error'>No Products Selected</div>";
						$( ".success-admin-notices" ).append( notice );
					} else {
						performBulkAction( onbuy_products_id,operation );
					}
				}

			}
		);

	function performBulkAction(onbuy_products_id,operation)
		{
		if (onbuy_products_id == "") {
			var notice = "";
			notice    += "<div class='notice notice-error'>No Products Selected</div>";
			$( ".success-admin-notices" ).append( notice );
		}

		var onbuy_product_id = onbuy_products_id;
		$( '.ced_onbuy_loader' ).show();
		$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_onbuy_process_bulk_action',
					operation_to_be_performed : operation,
					id : onbuy_product_id,
					shopid:shop_id
				},
				type : 'POST',
				success: function(response)
				{
					$( '.ced_onbuy_loader' ).hide();
					var response2 = jQuery.parseJSON( response );
					var notice    = "";
					$( ".success-admin-notices" ).html();
					if ( response2.status == 200) {
						notice += "<div class='notice notice'>" + response2.message + "</div>";
						$( ".success-admin-notices" ).html( notice );
					} else {
						notice += "<div class='notice notice'>" + response2.message + "</div>";
						$( ".success-admin-notices" ).html( notice );
					}
				}
				}
		);
	}

		$( document ).on(
			'click',
			'#ced_onbuy_fetch_orders',
			function(event)
			{
				event.preventDefault();
				var store_id = $( this ).attr( 'data-id' );
				$( '.ced_onbuy_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_onbuy_get_orders',
							shopid:store_id
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_onbuy_loader' ).hide();
							var response  = jQuery.parseJSON( response );
							var response1 = jQuery.trim( response.message );
							if (response1 == "Shop is Not Active") {
								var notice = "";
								notice    += "<div class='notice notice-error'>Currently Shop is not Active . Please activate your Shop in order to fetch orders.</div>";
								$( ".success-admin-notices" ).append( notice );
								return;
							} else {
								location.reload( true );
							}

						}
					}
				);
			}
		);

		jQuery( document ).on(
			'click',
			'#ced_onbuy_shipment_submit',
			function(){
				var type           = $( this ).attr( 'data-order-type' );
				var all_data_array = {};
				var unique_ids     = {};
				var i              = 0;
				var order_id       = jQuery( '#onbuy_orderid' ).val();
				var wo_order_id    = jQuery( '#post_ID' ).val();
				var shop_id        = jQuery( '#onbuy_shop_id' ).val();
				if (type != 'complete') {
					var shipping_provider_id = jQuery( '#onbuy_shipping_providers_partial' ).val();
					var tracking_url         = jQuery( '#onbuy_shipping_providers_partial' ).find( ':selected' ).attr( 'data-url' );
					var trackNumber          = jQuery( '#onbuy_onbuy_tracking_number_partial' ).val();
					jQuery( '#onbuy_order_line_items tr' ).each(
						function(){

							var tr          = jQuery( this ).attr( 'id' );
							unique_ids[i]   = tr;
							var sku         = jQuery( '#sku' + tr ).val();
							var qty_order   = jQuery( '#qty_order' + tr ).val();
							var qty_shipped = jQuery( '#qty_shipped' + tr ).val();
							var pro_id      = jQuery( '#sku' + tr ).attr( 'data-p-id' );

							all_data_array['sku/' + tr]         = sku;
							all_data_array['qty_order/' + tr]   = qty_order;
							all_data_array['qty_shipped/' + tr] = qty_shipped;
							all_data_array['pro_id/' + tr]      = pro_id;
						}
					);
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_partial_dispatch_order',
								onbuy_order_id : order_id,
								woo_order_id: wo_order_id,
								trackNumber : trackNumber,
								shipping_provider_id : shipping_provider_id,
								tracking_url : tracking_url,
								all_data_array : all_data_array,
								shopid : shop_id,
							},
							type : 'POST',
							success: function(response)
						{
									$( '.ced_onbuy_loader' ).hide();
									var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
									window.location.reload();
								} else {
									alert( response.message );
								}
							}
						}
					);

				} else {
					var shipping_provider_id = jQuery( '#onbuy_shipping_providers_complete' ).val();
					var tracking_url         = jQuery( '#onbuy_shipping_providers_complete' ).find( ':selected' ).attr( 'data-url' );
					var trackNumber          = jQuery( '#onbuy_onbuy_tracking_number_complete' ).val();
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_complete_dispatch_order',
								onbuy_order_id : order_id,
								woo_order_id: wo_order_id,
								trackNumber : trackNumber,
								shipping_provider_id : shipping_provider_id,
								tracking_url : tracking_url,
								shopid : shop_id,
							},
							type : 'POST',
							success: function(response)
						{
								$( '.ced_onbuy_loader' ).hide();
								var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
									window.location.reload();
								} else {
									alert( response.message );
								}
							}
						}
					);

				}
			}
		);

		$( document ).on(
			'click',
			'#ced_onbuy_cancel_submit' ,
			function(){
				var cancel_info      = $( '#cancel_info' ).val();
				var cancel_reason_id = $( '#cancel_reason_id' ).find( ':selected' ).val();
				var order_id         = jQuery( '#onbuy_orderid' ).val();
				var shop_id          = jQuery( '#onbuy_shop_id' ).val();
				var wo_order_id      = jQuery( '#post_ID' ).val();
				if (cancel_reason_id != "" ) {
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_cancel_order',
								cancel_info : cancel_info,
								cancel_reason_id : cancel_reason_id,
								onbuy_order_id : order_id,
								shopid : shop_id,
								woo_order_id : wo_order_id,
							},
							type : 'POST',
							success: function(response)
						{
								$( '.ced_onbuy_loader' ).hide();
								var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
								} else {
									alert( response.message );
								}
							}
						}
					);

				} else {
					alert( "Cancellation Reason Must Be Selected" );
				}
			}
		);

		$( document ).on(
			'click',
			'#ced_onbuy_refund_submit' ,
			function(){
				var refund_info      = $( '#refund_info' ).val();
				var refund_reason_id = $( '#refund_reason_id' ).find( ':selected' ).val();
				var order_id         = jQuery( '#onbuy_orderid' ).val();
				var shop_id          = jQuery( '#onbuy_shop_id' ).val();
				var wo_order_id      = jQuery( '#post_ID' ).val();
				if (refund_reason_id != "" ) {
					$( '.ced_onbuy_loader' ).show();
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_refund_order',
								refund_info : refund_info,
								refund_reason_id : refund_reason_id,
								onbuy_order_id : order_id,
								shopid : shop_id,
								woo_order_id : wo_order_id,
							},
							type : 'POST',
							success: function(response)
						{
								$( '.ced_onbuy_loader' ).hide();
								var response = jQuery.parseJSON( response );
								if (response.status == '200') {
									alert( response.message );
								} else {
									alert( response.message );
								}
							}
						}
					);

				} else {
					alert( "Refund Reason Must Be Selected" );
				}
			}
		);

		$( document ).on(
			'click',
			'.delete_profile',
			function(event){
				event.preventDefault();
				var profile_id = $( this ).attr( 'id' );
				var shop_id    = $( this ).attr( 'shop_id' );

				if (profile_id != "") {
					$.ajax(
						{
							url : ajaxUrl,
							data : {
								ajax_nonce : ajaxNonce,
								action : 'ced_onbuy_delete_profile',
								profile_id : profile_id,
								shop_id : shop_id,

							},
							type : 'POST',
							success : function(response)
						{
								if (response == "deleted") {
									location.reload();
								}
							}
						}
					);
				}

			}
		);

	$( document ).on(
		'click',
		'.ced_onbuy_technical_feature_screen_show',
		function(e){
			e.preventDefault();
			$( '.ced_onbuy_technical_detail_section' ).slideToggle();
		}
	);

	$( document ).on(
		'mouseover',
		'.ced_onbuy_view_error',
		function(e){
			$( this ).next().show();
		}
	);
	$( document ).on(
		'mouseout',
		'.ced_onbuy_view_error',
		function(e){
			$( ".ced_onbuy_error_message" ).hide();
		}
	);

	// ==============================================================================
	$( document ).on(
		'change',
		'.ced_onbuy_select_store_category_checkbox',
		function(){
			var store_category_id = $( this ).attr( 'data-categoryID' );

			if ( $( this ).is( ':checked' ) ) {
				$( '#ced_onbuy_categories_' + store_category_id ).show( 'slow' );

			} else {
				$( '#ced_onbuy_categories_' + store_category_id ).hide( 'slow' );
			}
		}
	);

	$( document ).on(
		'keyup' ,
		'#ced_onbuy_category_search' ,
		function() {
			var keyword = $( this ).val();
			if ( keyword.length > 0 ) {
				$( '#check_select' ).show( 'slow' );
				$( '.ced_onbuy_select_store_category_checkbox' ).show( 'slow' );
			} else {
				$( '#check_select' ).hide( 'slow' );
				$( '.ced_onbuy_select_store_category_checkbox' ).hide( 'slow' );
			}
		}
	)

	$( document ).on(
		'click',
		'#ced_onbuy_save_category_button',
		function(){

			var  onbuy_category_array = [];
			var  store_category_array = [];
			var  onbuy_category_name  = [];
			var  category_value       = '';
			var  shop_id              = $( this ).attr( 'data-shop-id' );
			jQuery( '.ced_onbuy_select_store_category_checkbox' ).each(
				function(key) {

					if ( jQuery( this ).is( ':checked' ) ) {
						var store_category_id = $( this ).attr( 'data-categoryid' );
						var cat_level         = $( '#ced_onbuy_categories_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel' );

						var selected_onbuy_category_id = $( '#ced_onbuy_categories_' + store_category_id ).find( '.ced_onbuy_level' + cat_level + '_category' ).val();

						if ( selected_onbuy_category_id == '' || selected_onbuy_category_id == null ) {
							selected_onbuy_category_id = $( '#ced_onbuy_categories_' + store_category_id ).find( '.ced_onbuy_level' + (parseInt( cat_level ) - 1) + '_category' ).val();
						}

						 selected_onbuy_category_id = selected_onbuy_category_id.split( '$' );
						 selected_onbuy_category_id = selected_onbuy_category_id[0];

						var category_name = '';
						$( '#ced_onbuy_categories_' + store_category_id ).find( 'select' ).each(
							function(key1){
								category_name  = $( this ).find( "option:selected" ).text();
								category_value = category_name;

							}
						);
						var name_len = 0;
						if ( selected_onbuy_category_id != '' && selected_onbuy_category_id != null ) {
							onbuy_category_array.push( selected_onbuy_category_id );
							store_category_array.push( store_category_id );

							onbuy_category_name.push( category_name );
						}
					}
				}
			);

			var n = category_value.search( '--Select OnBuy Category--' );
			$( '.ced_onbuy_loader' ).show();
			if (n < 0) {
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_onbuy_map_categories_to_store',
							onbuy_category_array : onbuy_category_array,
							store_category_array : store_category_array,
							onbuy_category_name : onbuy_category_name,
							shop_id : shop_id
						},
						type : 'POST',
						success: function(response)
					{
							$( '.ced_onbuy_loader' ).hide();
							var html = "<div class='notice notice-success'>Profile Created Successfully</div>";
							$( "#profile_create_message" ).html( html );
							$( 'html, body' ).animate(
								{
									scrollTop: parseInt( $( "body" ).offset().top )
									},
								2000
							);
							window.setTimeout( function(){window.location.reload()}, 2000 );

						}
					}
				);
			} else {
				$( '.ced_onbuy_loader' ).hide();
				var html = "<div class='notice notice-success'>Please select Category</div>";
				$( "#profile_create_message" ).html( html );
				$( 'html, body' ).animate(
					{
						scrollTop: parseInt( $( "body" ).offset().top )
					},
					2000
				);
			}

		}
	);
	// ===============================================================================

	// ===================== TIMELINE LOGS PART ========================================
	$( document ).on(
		'click',
		'.log_details',
		function(e){
			e.preventDefault();
			$( '.log_message' ).hide();
			$( document ).find( '.ced_onbuy_add_account_popup_main_wrapper' ).addClass( 'show' );
			$( this ).next().addClass( 'show' );
			$( this ).next().slideToggle();
		}
	);

	$( document ).on(
		'click',
		'#ced_close_log_message',
		function (e) {
			e.preventDefault();
			$( '.log_message' ).hide();
		}
	);

	$( document ).on(
		'click',
		'.ced_onbuy_load_more',
		function () {
			$( '.ced_onbuy_loader' ).show();
			var parent  = $( document ).find( this ).attr( 'data-parent' );
			var offset  = $( document ).find( this ).attr( 'data-offset' );
			var total   = $( this ).data( 'total' );
			var element = this;
			console.log( this );
			$.ajax(
				{
					url: ajaxUrl,
					data: {
						ajax_nonce: ajaxNonce,
						parent: parent,
						offset: offset,
						total: total,
						action: 'ced_onbuy_load_more_logs',
					},
					type: 'POST',
					success: function (response) {

						parsed_response = jQuery.parseJSON( response );
						if ( parsed_response.html !== "" ) {
							$( element ).attr( 'data-offset', parseInt( parsed_response.offset ) );
							setTimeout(
								function () {
									$( '.ced_onbuy_loader' ).hide();
									$( '.' + parent ).find( '.ced_onbuy_log_rows' ).last().after( parsed_response.html );

								},
								1000
							);

							if (parsed_response.is_disable == "yes" ) {
								$( element ).hide();
							}

						}
					}
				}
			);
		}
	);
	// ==========================================================================================
})( jQuery );
