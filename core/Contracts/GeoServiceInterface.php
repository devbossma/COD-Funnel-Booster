<?php
/**
 * GeoServiceInterface
 *
 * @version 1.0.0
 * @package CODFUnnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Contracts;

/**
 * Geographic Service Interface
 */
interface GeoServiceInterface {
	/**
	 * Get all countries
	 *
	 * @return array<string, string>
	 */
	public function get_countries(): array;

	/**
	 * Get states for a country
	 *
	 * @param string $country_code The Country Code EX: 'MA'.
	 * @return array<string, string>
	 */
	public function get_states_by_country_code( string $country_code ): array;

	/**
	 * Validate a geographic location.
	 *
	 * @param string      $country_code The Country Code EX: 'MA'.
	 * @param string|null $state_code The State Code EX: 'mague'.
	 * @return bool
	 */
	public function validate_location( string $country_code, ?string $state_code = null ): bool;

	/**
	 * Get the base country code from WooCommerce settings.
	 *
	 * @return string
	 */
	public function get_base_country(): string;

	/**
	 * Get the base state code from WooCommerce settings
	 *
	 * @return string
	 */
	public function get_base_state(): string;

	/**
	 * Get country name by country code
	 *
	 * @param string $country_code The country code.
	 * @return string
	 */
	public function get_country_name( string $country_code ): string;

	/**
	 * Get state name by country and state code
	 *
	 * @param string $country_code The country code.
	 * @param string $state_code The state code.
	 * @return string
	 */
	public function get_state_name( string $country_code, string $state_code ): string;

	/**
	 * Get allowed countries
	 *
	 * @return array<string, string>
	 */
	public function get_allowed_countries(): array;

	/**
	 * Get all states
	 *
	 * @return array<string, string>
	 */
	public function get_all_states(): array;
}
