<?php
/**
 * Main Plugin Class "CODFunnelBooster"
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Interface;

interface Plugin_Dependency_Api_Interface {

	/**
	 * Get plugin file path.
	 *
	 * @param string $slug The slug of the plugin.
	 * @return string
	 */
	public function get_plugin_file( string $slug ): string;

	/**
	 * Get plugin version
	 *
	 * @param string $slug The slug of the plugin.
	 * @return string
	 */
	public function get_plugin_min_version( string $slug ): string;

	/**
	 * Check if the passed plugin slug is a part of the requiered plugins plugin version.
	 *
	 * @param string $slug The slug of the plugin.
	 * @return bool
	 */
	public function has_plugin( string $slug ): bool;

	/**
	 * Get the required plugun  info function.
	 *
	 * @param string $slug The slug of the plugin.
	 * @return array
	 */
	public function get_plugin_info( string $slug ): array;
}
