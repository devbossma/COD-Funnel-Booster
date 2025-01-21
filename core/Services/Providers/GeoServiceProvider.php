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

	/**
	 * Check if a country code is valid
	 *
	 * @param string $country_code The country code to validate.
	 * @return bool
	 */
	public function is_valid_country_code( string $country_code ): bool {
		return $this->geo_service->is_valid_country_code( $country_code );
	}

	/**
	 * Check if a state code is valid
	 *
	 * @param string $state_code The state code to validate.
	 * @return bool
	 */
	public function is_valid_state_code( string $state_code ): bool {
		return $this->geo_service->is_valid_state_code( $state_code );
	}
}
