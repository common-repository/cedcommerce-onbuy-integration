<?php
/**
 * Wordpress-plugin
 * Plugin Name:       OnBuy Integration for WooCommerce
 * Plugin URI:        https://cedcommerce.com
 * Description:       OnBuy Integration for WooCommerce allows merchants to list their products on OnBuy marketplace and manage the orders from the woocommerce store.
 * Version:           2.0.0
 * Author:            CedCommerce
 * Author URI:        https://cedcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       onbuy-integration-for-woocommerce
 * Domain Path:       /languages
 *
 * Woo: 8513563:c7606e110b42848a494f949012d9761a
 * WC requires at least: 3.0
 * WC tested up to: 6.6.1
 *
 * @package  Woocommmerce_OnBuy_Integration
 * @version  1.0.1
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ONBUY_INTEGRATION_FOR_WOOCOMMERCE_VERSION', '2.0.0' );
define( 'CED_ONBUY_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_onbuy_log_directory' );
define( 'CED_ONBUY_VERSION', '1.0.1' );
define( 'CED_ONBUY_PREFIX', 'ced_onbuy' );
define( 'CED_ONBUY_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_ONBUY_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_ONBUY_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
define( 'CED_ONBUY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-onbuy-core-functions.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-onbuy-integration-for-woocommerce-activator.php
 */
function ced_onbuy_activate_onbuy_integration_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-onbuy-integration-for-woocommerce-activator.php';
	Onbuy_Integration_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-onbuy-integration-for-woocommerce-deactivator.php
 */
function ced_onbuy_deactivate_onbuy_integration_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-onbuy-integration-for-woocommerce-deactivator.php';
	Onbuy_Integration_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ced_onbuy_activate_onbuy_integration_for_woocommerce' );
register_deactivation_hook( __FILE__, 'ced_onbuy_deactivate_onbuy_integration_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-onbuy-integration-by-cedcommerce.php';


function ced_admin_notice_example_activation_hook_ced_onbuy() {
	set_transient( 'ced-onbuy-admin-notice', true, 5 );
}

/**
 * Ced_vidaxl_admin_notice_activation.
 *
 * @since 1.0.0
 */
function ced_onbuy_admin_notice_activation() {
	if ( get_transient( 'ced-onbuy-admin-notice' ) ) {?>
	<div class="updated notice is-dismissible">
	<p>Welcome to WooCommerce OnBuy Integration. Start listing, syncing, managing, & automating your WooCommerce and OnBuy store to boost sales.</p>
		<a href="admin.php?page=ced_onbuy" class ="ced_configuration_plugin_main">Connect to OnBuy</a>
	</div>
		<?php
		delete_transient( 'ced-onbuy-admin-notice' );
	}
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_onbuy_integration_for_woocommerce() {

	$plugin = new Onbuy_Integration_For_Woocommerce();
	$plugin->run();

}
if ( ced_onbuy_check_woocommerce_active() ) {
	run_onbuy_integration_for_woocommerce();
	register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_onbuy' );
	add_action( 'admin_notices', 'ced_onbuy_admin_notice_activation' );
} else {
	add_action( 'admin_init', 'deactivate_ced_onbuy_woo_missing' );
}
