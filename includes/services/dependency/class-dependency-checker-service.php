<?php
/**
 * The "Dependency_Checker_Service" Class
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Services\Dependency;

use DevBossMa\CODFunnelBooster\Interface\Plugin_Dependency_Api_Interface;

/**
 * Dependency_Checker_Service Definition class.
 */
class Dependency_Checker_Service {

	/**
	 * The reqired plugins Status.
	 *
	 * @var array $plugins_status
	 */
	private $plugins_status = array();


	private const REQUIRED_PLUGINS = array( 'woocommerce', 'elementor' );

	/**
	 * The "Plugin_Dependency_Api_Service" Interface.
	 *
	 * @var Plugin_Dependency_Api_Interface
	 */
	private Plugin_Dependency_Api_Interface $required_plugin_api;

	/**
	 * The "Dependency_Checker_Service" constructor.
	 *
	 * @param Plugin_Dependency_Api_Interface $required_plugin_api The required plugin interface.
	 */
	public function __construct( Plugin_Dependency_Api_Interface $required_plugin_api ) {
		$this->required_plugin_api = $required_plugin_api;
	}

	/**
	 * Get The requier plugins status function.
	 *
	 * @return array
	 */
	public function get_plugins_status(): array {

		foreach ( self::REQUIRED_PLUGINS as $slug ) {
			$this->plugins_status[ $slug ] = array(
				'installed'          => $this->is_plugin_installed( $this->required_plugin_api->get_plugin_file( $slug ) ),
				'activated'          => $this->is_plugin_active( $this->required_plugin_api->get_plugin_file( $slug ) ),
				'version_compatible' => $this->is_plugin_version_compatible(
					$this->required_plugin_api->get_plugin_file( $slug ),
					$this->required_plugin_api->get_plugin_info( $slug )
				),
				'min_version'        => $this->required_plugin_api->get_plugin_min_version( $slug ),
				'name'               => $this->required_plugin_api->get_plugin_info( $slug )['name'],
			);
		}

		return $this->plugins_status;
	}

	/**
	 * Check if a plugin is installed
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return bool
	 */
	private function is_plugin_installed( string $plugin_file ): bool {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		return file_exists( $plugin_path );
	}

	/**
	 * Check if a plugin is active
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return bool
	 */
	private function is_plugin_active( string $plugin_file ): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin_file );
	}

	/**
	 * Get plugin version
	 *
	 * @param string $plugin_file Plugin file path.
	 * @param array  $requirements Requirements.
	 * @return boolean
	 */
	private function is_plugin_version_compatible( string $plugin_file, array $requirements ): bool {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( $this->is_plugin_installed( $plugin_file ) ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			$plugin_data = get_plugin_data( $plugin_path );
			return version_compare( $plugin_data['Version'], $requirements['min_version'], '<' );
		}
		return false;
	}
}
