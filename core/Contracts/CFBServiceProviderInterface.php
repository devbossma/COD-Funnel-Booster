<?php
/**
 * Geographic Service Interface
 *
 * @version 1.0.0
 * @package CODFUnnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Contracts;

/**
 * Undocumented interface
 */
interface CFBServiceProviderInterface {
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void;

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot(): void;
}
