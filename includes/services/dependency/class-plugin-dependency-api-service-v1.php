<?php
/**
 * The "Plugin_Dependency_Api_Service" Interface
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Services\Dependency;

use DevBossMa\CODFunnelBooster\Interface\Plugin_Dependency_Api_Interface;

/**
 * Plugin_Dependency_Api_Service Definition class.
 */
class Plugin_Dependency_Api_Service_V1 implements Plugin_Dependency_Api_Interface {

	private const REQUIRED_PLUGINS = array(
		'woocommerce' => array(
			'name'        => 'WooCommerce',
			'file'        => 'woocommerce/woocommerce.php',
			'min_version' => '9.4.3',
			'required'    => true,
			'url'         => 'https://wordpress.org/plugins/woocommerce/',
		),
		'elementor'   => array(
			'name'        => 'Elementor',
			'file'        => 'elementor/elementor.php',
			'min_version' => '3.25.11',
			'required'    => true,
			'url'         => 'https://wordpress.org/plugins/elementor/',
		),
	);

	/**
	 * Get plugin file path.
	 *
	 * @param string $slug The slug of the plugin.
	 * @return string
	 */
	public function get_plugin_file( string $slug ): string {
		if ( $this->has_plugin( $slug ) ) {
			return isset( self::REQUIRED_PLUGINS[ $slug ]['file'] )
			? self::REQUIRED_PLUGINS[ $slug ]['file']
			: '';
		}
	}

	/**
	 * Get plugin version
	 *
	 * @param string $slug The slug of the plugin.
	 * @return string
	 */
	public function get_plugin_min_version( string $slug ): string {
		if ( $this->has_plugin( $slug ) ) {
			return isset( self::REQUIRED_PLUGINS[ $slug ]['min_version'] )
			? self::REQUIRED_PLUGINS[ $slug ]['min_version']
			: '';
		}
	}

	/**
	 * Check if the passed plugin slug is a part of the requiered plugins plugin version
	 *
	 * @param string $slug The slug of the plugin.
	 * @return bool
	 */
	public function has_plugin( string $slug ): bool {

		return isset( self::REQUIRED_PLUGINS[ $slug ] );
	}

	/**
	 * Get the required plugun  info function.
	 *
	 * @param string $slug The slug of the plugin.
	 * @return array
	 */
	public function get_plugin_info( string $slug ): array {
		if ( $this->has_plugin( $slug ) ) {
			return self::REQUIRED_PLUGINS[ $slug ];
		}
	}
}
