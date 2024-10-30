<?php
/**
 * Instructions of the extensiom
 *
 * @package  Onbuy_Integration_By_CedCommerce
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( isset( $_GET['section'] ) ) {
	$section = sanitize_text_field( wp_unslash( $_GET['section'] ) );
}

?>
	
<?php
if ( 'accounts-view' == $section ) {
	?>
	 
		
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
					<li>* After <b>Account Configuration</b>, now click on <b>Global Settings</b> to map the settings</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>
  
<?php
if ( 'category-mapping-view' == $section ) {
	$activeShop  = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
	$profile_url = admin_url( 'admin.php?page=ced_onbuy&section=ced-onbuy-profile-listing&shop_id=' . $activeShop );
	?>
	 
		
	<div class="ced_onbuy_heading">

		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
					<li>* In <b>Search OnBuy category</b>, the admin has to enter the “KEYWORD” to search the category for that product. Once you enter the keyword the checkboxes get enabled.</li>
					<li>* In this section, you will need to map the woocommerce store categories to the OnBuy categories.</li>
					<li>* You need to select the woocommerce category using the checkbox on the left side and list of Onbuy categories will appear in dropdown. Select the Onbuy category in which you want to list the products of the selected woocmmerce category on OnBuy.</li>
					<li>* Click <b>Save mapping</b> option at the bottom.'</li>
					<li>* Once you map the categories profiles will automatically be created and you can use the <a href="<?php echo esc_url( $profile_url ); ?>" target="_blank">Profiles</a> in order to override the <a>Product Data Settigs</a> at category level.</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>
<?php
if ( 'settings-view' == $section ) {
	?>
	 
		
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
				
				<li>* In this section all the configuration related to product and order sync are provided.</li>
				<li>* The <a>Metakeys and Attributes List</a> section will help you to choose the required metakey or attribute on which the product information is stored. These metakeys or attributes will furthur be used in <a>Product Export Settings</a> for listing products on OnBuy from woocommerce.</li>
				<li>* For selecting the required metakey or attribute expand the <a>Metakeys and Attributes List</a> section enter the product name/keywords and list will be displayed under that. Select the metakey or attribute as per requirement and save settings.</li>
				<li>* Configure the order related settings in <a>Order Setting</a>.</li>
				<li>* To automate the process related to inventory, and order, enable the features as per requirement in <a>Scheduler Setting</a>.</li>
				<li>* Configure the Buybox related settings in <a>Buy Box Setting</a>.</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>

<?php
if ( 'ced-onbuy-profile-listing' == $section ) {
	?>
	 
		
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
				
				<li>* In this section you will see all the profiles created after category mapping.</li>
				<li>* You can use the <a>Profiles</a> in order to override the settings of <a>Product Export Settigs</a> at category level.</li>
				<li>* For overriding the details edit the required profile using the edit option under profile name.</li>
				<li>* Also there are category specific attributes which you can fill.</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>
		 
<?php
if ( 'class-onbuylistproducts' == $section ) {
	?>
	 
		
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
					<li>* This section lets you perform multiple operation such as <a>Upload/Update</a> product from woocommerce to OnBuy.</li>
					<li>* You can also filter out the product on the basis of Product, Category, Type, Post, Per page, and Stock status.</li>
					<li>* The <a>Search Product </a>option lets you find product using product name/keywords.</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>
 
<?php
if ( 'class-onbuyQueueManagement' == $section ) {
	?>
		 
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
					<li>* In <b>Queue Management </b>section, where the admin can view the OnBuy Queue of the products.
<!-- 						<b><a href="javascript:void(0);" id="ced_onbuy_queue_manage_link" ><?php esc_html_e( 'For more details', 'onbuy-integration-by-cedcommerce' ); ?></a></b> -->
					</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>

<?php
if ( 'class-ced-onbuy-list-orders' == $section ) {
	$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( $_GET['shop_id'] ) : '';
	?>
	 
	<div class="ced_onbuy_heading">
		<?php esc_html_e( get_onbuy_instuctions_html( 'INSTRUCTIONS', 'onbuy-integration-by-cedcommerce' ) ); ?>
		<div class="ced_onbuy_child_element default_modal">

			<div class="ced_onbuy_render_meta_key_search_box_wrapper ced_onbuy_global_wrap">
				<ul>
					<li>* In <b>Order Section</b>, OnBuy orders will be displayed here.</li>
					<li>* You can fetch the onbuy orders manually by clicking the <a>Fetch Orders</a> button or also you can enable the auto fetch order feature in Schedulers <a href="<?php admin_url( 'admin.php?page=ced_onbuy&section=settings-view&shop_id=' . $shop_id ); ?>">here</a>.</li>
					<li>* Make sure you have the correct skus present in all your products/variations for order syncing. If not so, orders will not create in woocommerce.</li>
					<li>* You can also submit the tracking details from woocommerce to OnBuy. You need to go in the order edit section using <a>Edit</a> option in the order table below. Once you go in order edit section you will find the section at the bottom where you can enter tracking info and update them on OnBuy.</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}
?>
