<?php
/**
 * Header of the extensiom
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
update_option( 'ced_onbuy_shop_id', $shop_id );
global $wpdb;
$shop_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ced_onbuy_accounts WHERE `shop_id` = %d ', $shop_id ), 'ARRAY_A' );
$shop_details = $shop_details[0];
if ( isset( $_GET['section'] ) ) {
	$section = sanitize_text_field( wp_unslash( $_GET['section'] ) );
}

?>
<div class="ced_onbuy_loader">
	<img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/loading.gif' ); ?>" width="50px" height="50px" class="ced_onbuy_loading_img" >
</div>
<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper">
	<?php esc_attr( ced_onbuy_cedcommerce_logo() ); ?>
	<ul class="navigation">
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=settings-view&shop_id=' . $shop_id ) ); ?>" class="
				<?php
				if ( 'settings-view' == $section ) {
					echo 'active';
				}
				?>
				"><?php esc_html_e( 'Global Settings', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=category-mapping-view&shop_id=' . $shop_id ) ); ?>" class="
			<?php
			if ( 'category-mapping-view' == $section ) {
				echo 'active';
			}
			?>
			"><?php esc_html_e( 'Category Search', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=ced-onbuy-profile-listing&shop_id=' . $shop_id ) ); ?>" class="
				<?php
				if ( 'ced-onbuy-profile-listing' == $section ) {
					echo 'active';
				}
				?>
				"><?php esc_html_e( 'Profile', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'class-onbuylistproducts' == $section ) {
				echo 'active';
			}
			?>
			" href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=class-onbuylistproducts&shop_id=' . $shop_id ) ); ?>"><?php esc_html_e( 'Products', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'class-onbuyQueueManagement' == $section ) {
				echo 'active';
			}
			?>
			" href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=class-onbuyQueueManagement&shop_id=' . $shop_id ) ); ?>"><?php esc_html_e( 'Queue Management', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'class-ced-onbuy-list-orders' == $section ) {
				echo 'active';
			}
			?>
			" href="<?php echo esc_url( admin_url( 'admin.php?page=ced_onbuy&section=class-ced-onbuy-list-orders&shop_id=' . $shop_id ) ); ?>"><?php esc_html_e( 'Orders', 'onbuy-integration-by-cedcommerce' ); ?></a>
		</li>
		<li>
			<?php
			$url = admin_url( 'admin.php?page=ced_onbuy&section=ced-onbuy-timeline&shop_id=' . $shop_id );
			?>
			<a class="
			<?php
			if ( 'ced-onbuy-timeline' == $section ) {
				echo 'active'; }
			?>
				" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Timeline', 'woocommerce-onbuy-integration' ); ?></a>
		</li>
	</ul>
	<div class="ced_onbuy_document"><span><a href="https://woocommerce.com/document/onbuy-integration-for-woocommerce/" target="_blank" class="ced_onbuy_document_link" name="" value="">View documentation</a></span></div>
	<?php
	$active = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

	?>
</div>

<div class="ced_contact_menu_wrap">
	<input type="checkbox" href="#" class="ced_menu_open" name="menu-open" id="menu-open" />
	<label class="ced_menu_button" for="menu-open">
		<img src="<?php echo esc_url( CED_ONBUY_URL . 'admin/images/icon.png' ); ?>" alt="" title="Click to Chat">
	</label>
	<a href="https://join.skype.com/rzxfe8JrHbao" class="ced_menu_content ced_skype" target="_blank"> <i class="fa fa-skype" aria-hidden="true"></i> </a>
	<a href="https://chat.whatsapp.com/GgYqefNlVeJH0KcXZyOrkp" class="ced_menu_content ced_whatsapp" target="_blank"> <i class="fa fa-whatsapp" aria-hidden="true"></i> </a>
</div>

