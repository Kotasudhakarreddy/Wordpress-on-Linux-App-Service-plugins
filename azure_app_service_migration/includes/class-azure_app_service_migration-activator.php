<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/plugins/azure-app-service-migration/
 * @since      1.0.0
 *
 * @package    Azure_app_service_migration
 * @subpackage Azure_app_service_migration/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Azure_app_service_migration
 * @subpackage Azure_app_service_migration/includes
 * @author     Microsoft <wordpressdev@microsoft.com>
 */
class Azure_app_service_migration_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Add status option in Database
		$status_option_value = array( 'type' => 'done', 'title' => 'initialize', 'message' => 'Initial Migration option' );
		Azure_app_service_migration_Custom_Logger::update_migration_status($status_option_value);
	}

}
