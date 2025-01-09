<?php
/**
 * The Dependency Checker Service.
 *
 * This class handles checking dependencies for the COD Funnel Booster plugin.
 *
 * @package CODFunnelBooster
 * @since   1.0.0
 */

namespace DevBossMa\CODFunnelBooster\Services\Dependency;

use DevBossMa\CODFunnelBooster\Interfaces\Plugin_Dependency_Api_Interface;

/**
 * Class Dependency_Checker_Service
 *
 * Handles checking plugin dependencies and their compatibility.
 */
class Dependency_Checker_Service {

	/**
	 * The required plugins status array.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $plugins_status = array();

	/**
	 * Array of required plugin slugs.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private const REQUIRED_PLUGINS = array( 'woocommerce', 'elementor' );

	/**
	 * Plugin Dependency API Interface instance.
	 *
	 * @since 1.0.0
	 * @var   Plugin_Dependency_Api_Interface
	 */
	private Plugin_Dependency_Api_Interface $required_plugin_api;

	/**
	 * Initialize the dependency checker service.
	 *
	 * @since 1.0.0
	 * @param Plugin_Dependency_Api_Interface $required_plugin_api The required plugin API interface.
	 */
	public function __construct( Plugin_Dependency_Api_Interface $required_plugin_api ) {
		$this->required_plugin_api = $required_plugin_api;
	}

	/**
	 * Get the status of required plugins.
	 *
	 * @since  1.0.0
	 * @return array Array containing status information for required plugins.
	 */
	public function get_plugins_status() {
		foreach ( self::REQUIRED_PLUGINS as $slug ) {
			$plugin_file = $this->required_plugin_api->get_plugin_file( $slug );
			$plugin_info = $this->required_plugin_api->get_plugin_info( $slug );

			$this->plugins_status[ $slug ] = array(
				'installed'          => $this->is_plugin_installed( $plugin_file ),
				'activated'          => $this->is_plugin_active( $plugin_file ),
				'version_compatible' => $this->is_plugin_version_compatible( $plugin_file, $plugin_info ),
				'min_version'        => $this->required_plugin_api->get_plugin_min_version( $slug ),
				'name'               => $plugin_info['name'],
				'file'               => $plugin_file,
			);
		}

		return $this->plugins_status;
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_file The plugin file path.
	 * @return bool Whether the plugin is installed.
	 */
	private function is_plugin_installed( $plugin_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		return file_exists( $plugin_path );
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_file The plugin file path.
	 * @return bool Whether the plugin is active.
	 */
	private function is_plugin_active( $plugin_file ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}

	/**
	 * Check if a plugin version is compatible.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_file   The plugin file path.
	 * @param  array  $requirements  The plugin requirements array.
	 * @return bool Whether the plugin version is compatible.
	 */
	private function is_plugin_version_compatible( $plugin_file, $requirements ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( $this->is_plugin_installed( $plugin_file ) ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			$plugin_data = get_plugin_data( $plugin_path );
			return version_compare( $plugin_data['Version'], $requirements['min_version'], '>=' );
		}

		return false;
	}
}
