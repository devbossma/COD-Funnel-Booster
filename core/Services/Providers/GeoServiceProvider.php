<?php
/**
 * Geographic Service Provider
 *
 * @var 1.0.0
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Services\Providers;

use DevBossMa\CODFunnelBooster\Core\Contracts\GeoServiceInterface;
use DevBossMa\CODFunnelBooster\Core\Contracts\CFBServiceProviderInterface;
use DevBossMa\CODFunnelBooster\Core\Services\Geography\WooCommerceGeoService;


/**
 * Geographic Service Provider
 */
class GeoServiceProvider implements CFBServiceProviderInterface {
	/**
	 * Geo service instance.
	 *
	 * @var GeoServiceInterface $geo_service Geo service instance.
	 */
	private GeoServiceInterface $geo_service;

	/**
	 * Constructor
	 *
	 * @param GeoServiceInterface $geo_service Geo service instance.
	 */
	public function __construct( GeoServiceInterface $geo_service ) {
		$this->geo_service = $geo_service;
	}

	/**
	 * Register the service provider.
	 */
	public function register(): void {
		// Registration logic if needed.
	}

	/**
	 * Boot the service provider.
	 */
	public function boot(): void {
		// Boot logic if needed.
	}

	/**
	 * Delegate method calls to the geo service
	 *
	 * @param string $name Method name.
	 * @param array  $arguments Method arguments.
	 * @return mixed
	 */
	public function __call( string $name, array $arguments ) {
		return $this->geo_service->$name( ...$arguments );
	}
}
