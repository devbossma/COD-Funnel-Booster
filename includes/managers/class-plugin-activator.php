<?php
/**
 * Plugin activation manager
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Managers;

use DevBossMa\CODFunnelBooster\Options\Funnel_Settings_Manager;
use DevBossMa\CODFunnelBooster\Options\General_Settings_Manager;
use DevBossMa\CODFunnelBooster\Options\Checkout_Settings_Manager;
use DevBossMa\CODFunnelBooster\Options\Integration_Settings_Manager;

/**
 * The Plugin Activator class.
 */
class Plugin_Activator {

	/**
	 * Activate plugin and initialize all option managers
	 */
	public static function activate(): void {
		$managers = array(
			General_Settings_Manager::class,
			Funnel_Settings_Manager::class,
			Checkout_Settings_Manager::class,
			Integration_Settings_Manager::class,
		);

		foreach ( $managers as $manager ) {
			$manager::get_option();
		}

		self::create_database_tables();
		self::set_capabilities();

		$upload_dir     = wp_upload_dir();
		$cfb_upload_dir = $upload_dir['basedir'] . '/cfb-uploads';

		// Create COD FUNNEL BOOSTER upload's directory if it doesn't exist.
		if ( ! file_exists( $cfb_upload_dir ) ) {
			wp_mkdir_p( $cfb_upload_dir );
		}
	}

	/**
	 * Create necessary database tables
	 */
	private static function create_database_tables(): void {}

	/**
	 * Set initial capabilities
	 */
	private static function set_capabilities(): void {
		$admin_role = get_role( 'administrator' );

		$capabilities = array(
			'manage_sales_funnels',
			'create_sales_funnels',
			'edit_sales_funnels',
			'delete_sales_funnels',
			'view_sales_funnel_reports',
		);

		foreach ( $capabilities as $cap ) {
			$admin_role->add_cap( $cap );
		}
	}
}
