<?php
/**
 * Setup Wizard for plugin dependencies installation
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Admin\Dashboard;

use WP_REST_Server;
use WP_REST_Response;
use DevBossMa\CODFunnelBooster\Core\Exceptions\GeoServiceException;
use DevBossMa\CODFunnelBooster\Core\Services\Providers\GeoServiceProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Admin Dashboard class.
 */
class CFB_Admin_Dashboard {

	/**
	 * Initialize the setup wizard
	 */
	public function init(): void {
		add_action(
			'admin_init',
			function () {
				if ( isset( $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					&& 'cod-funnel-dashboard' === $_GET['page'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					) {
					remove_all_actions( 'admin_notices' );
					remove_all_actions( 'all_admin_notices' );
				}
			}
		);
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Check if user has admin permissions
	 *
	 * @return bool
	 */
	public function check_admin_permissions(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Add admin menu pages for the Admin Dashboard.
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'Dashboard', 'cod-funnel-booster' ),
			__( 'Dashboard', 'cod-funnel-booster' ),
			'manage_options',
			'cod-funnel-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-buddicons-forums',
			10
		);
	}

	/**
	 * Enqueue necessary scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ): void {
		if ( 'toplevel_page_cod-funnel-dashboard' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'cod-funnel-dashboard',
			plugins_url( 'assets/build/css/dashboard/dashboard.css', CFB_PLUGIN_FILE ),
			array(),
			CFB_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'cod-funnel-dashboard',
			plugins_url( 'assets/build/js/dashboard/dashboard.js', CFB_PLUGIN_FILE ),
			array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
			CFB_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Render the Admin Dashboard page container.
	 */
	public function render_dashboard_page(): void {
		if ( ! $this->check_admin_permissions() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'cod-funnel-booster' ) );
		}

		$container            = cfb_container();
		$geo_service_provider = $container::resolve( GeoServiceProvider::class );

		try {
			// Get current settings first.
			$current_type      = get_option( 'woocommerce_allowed_countries', 'all' );
			$current_countries = get_option( 'woocommerce_specific_allowed_countries', array() );

			echo '<h2>Current Settings:</h2>';
			echo '<pre>';
			var_dump(
				array(
					'current_type'      => $current_type,
					'current_countries' => $current_countries,
				)
			);
			echo '</pre>';

			// Try to update.
			$is_updated = $geo_service_provider->update_allowed_countries( 'specific', array( 'US' ) );

			echo '<h2>Update Result:</h2>';
			echo '<pre>';
			var_dump(
				array(
					'success'       => $is_updated,
					'new_type'      => get_option( 'woocommerce_allowed_countries' ),
					'new_countries' => get_option( 'woocommerce_specific_allowed_countries' ),
				)
			);
			echo '</pre>';

		} catch ( GeoServiceException $e ) {
			echo '<div class="error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
		}

		echo '<div id="cod-funnel-dashboard-root"></div>';
	}
}
