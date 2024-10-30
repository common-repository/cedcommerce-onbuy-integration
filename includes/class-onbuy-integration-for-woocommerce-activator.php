<?php

/**
 * Fired during plugin activation
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Onbuy_Integration_By_CedCommerce
 * @subpackage Onbuy_Integration_By_CedCommerce/includes
 */
class Onbuy_Integration_For_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table_name = $wpdb->prefix . 'ced_onbuy_accounts';

		$create_accounts_table =
		"CREATE TABLE $table_name (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		shop_id BIGINT(20) DEFAULT NULL,
		consumer_key VARCHAR(255) DEFAULT NULL,
		consumer_secret VARCHAR(255) DEFAULT NULL,
		account_status VARCHAR(255) NOT NULL,
		seller_data LONGTEXT NOT NULL,
		seller_deliveries LONGTEXT DEFAULT NULL,
		PRIMARY KEY (id)
		);";
		dbDelta( $create_accounts_table );

		$table_name        = $wpdb->prefix . 'ced_onbuy_queue';
		$createFileTracker =
		"CREATE TABLE  $table_name (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		shop_id BIGINT(20) DEFAULT NULL,
		queue_id LONGTEXT NOT NULL DEFAULT '',
		post_time VARCHAR(255),
		queue_type VARCHAR(255),
		PRIMARY KEY  (id)
		);";
		dbDelta( $createFileTracker );

		$tableName = $wpdb->prefix . 'ced_onbuy_profiles';

		$create_profile_table =
		"CREATE TABLE $tableName (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		profile_name VARCHAR(255) NOT NULL,
		profile_status VARCHAR(255) NOT NULL,
		shop_id VARCHAR(255) DEFAULT NULL,
		profile_data TEXT DEFAULT NULL,
		woo_categories TEXT DEFAULT NULL,
		PRIMARY KEY (id)
		);";
		dbDelta( $create_profile_table );

	}
}
