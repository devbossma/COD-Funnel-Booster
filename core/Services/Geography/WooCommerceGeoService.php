<?php
/**
 * WooCommerceGeoService class
 *
 * @package DevBossMa\CODFunnelBooster\Core\Services\Geography
 */

namespace DevBossMa\CODFunnelBooster\Core\Services\Geography;

use Exception;
use WC_Cache_Helper;
use DevBossMa\CODFunnelBooster\Core\Contracts\CFBLoggerInterface;
use DevBossMa\CODFunnelBooster\Core\Contracts\GeoServiceInterface;
use DevBossMa\CODFunnelBooster\Core\Exceptions\GeoServiceException;

/**
 * WooCommerceGeoService  class is an implimentation of the GeoServiceInterface.
 */
class WooCommerceGeoService implements GeoServiceInterface {
	/**
	 * Woocommerce Geo Handler.
	 *
	 * @var \WC_Countries $geo_service Woocommerce Geo Handler instance.
	 */
	private \WC_Countries $geo_service;

	/**
	 * Logger instance.
	 *
	 * @var CFBLoggerInterface|null
	 */
	private ?CFBLoggerInterface $logger;

	/**
	 * Class Constructor.
	 *
	 * @param CFBLoggerInterface|null $logger Logger instance.
	 * @throws GeoServiceException If WooCommerce is not initialized or fails to initialize.
	 * @return void
	 * @since 1.0.0
	 * @version 1.0.0
	 * @throws GeoServiceException When there is an error retrieving countries.
	 */
	public function __construct( CFBLoggerInterface $logger = null ) {
		$this->logger = $logger;
	}

	/**
	 * Get WooCommerce Countries instance
	 *
	 * @throws GeoServiceException If WooCommerce is not initialized.
	 * @return \WC_Countries
	 */
	private function get_geo_service(): \WC_Countries {
		if ( ! isset( $this->geo_service ) ) {
			// Wait for woocommerce_init hook to be fired.
			if ( ! did_action( 'woocommerce_init' ) ) {
				throw GeoServiceException::wooCommerceNotInitialized();
			}

			if ( ! function_exists( 'WC' ) || ! WC() ) {
				throw GeoServiceException::wooCommerceNotInitialized();
			}

			$this->geo_service = WC()->countries;
		}
		return $this->geo_service;
	}

	/**
	 * Get all countries
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array<string, string>
	 * @throws GeoServiceException When there is an error retrieving countries.
	 */
	public function get_countries(): array {
		try {
			return $this->get_geo_service()->get_countries();
		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Failed to get countries: ' . esc_html( $e->getMessage() ) );
			}
			throw GeoServiceException::dataRetrievalFailed( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Get allowed countries
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array<string, string>
	 */
	public function get_allowed_countries(): array {
		return $this->get_geo_service()->get_allowed_countries();
	}

	/**
	 * Get states for a country
	 *
	 * @param string|null $country_code The Country Code EX: 'MA'.
	 * @return array<string, string>
	 * @throws GeoServiceException When there is an error retrieving states.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function get_states_by_country_code( ?string $country_code ): array {
		if ( ! $this->is_valid_country_code( $country_code ) ) {
			if ( $this->logger ) {
				$this->logger->error( 'Invalid country code provided: ' . esc_html( $country_code ) );
			}
			throw GeoServiceException::invalidCountryCode( esc_html( $country_code ) );
		}

		try {
			$states = $this->get_geo_service()->get_states( $country_code );
			return $states ? $states : array();
		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Failed to get states for country: ' . esc_html( $country_code ) . '. Error: ' . esc_html( $e->getMessage() ) );
			}
			throw GeoServiceException::dataRetrievalFailed( 'Failed to get states for country: ' . esc_html( $country_code ) );
		}
	}

	/**
	 * Validate a geographic location.
	 *
	 * @param string      $country_code The Country Code EX: 'MA'.
	 * @param string|null $state_code The State Code EX: 'mague'.
	 * @since 1.0.0
	 * @version 1.0.0
	 * @throws GeoServiceException When there is an error validating the location.
	 * @return bool
	 */
	public function validate_location( string $country_code, ?string $state_code = null ): bool {
		try {
			if ( ! isset( $this->get_countries()[ $country_code ] ) ) {
				return false;
			}

			if ( null !== $state_code ) {
				$states = $this->get_states_by_country_code( $country_code );
				return isset( $states[ $state_code ] );
			}

			return true;
		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Location validation failed: ' . esc_html( $e->getMessage() ) );
			}
			return false;
		}
	}

	/**
	 * Get the base country code from WooCommerce settings
	 *
	 * @return string
	 */
	public function get_base_country(): string {
		return $this->get_geo_service()->get_base_country();
	}

	/**
	 * Get the base state code from WooCommerce settings
	 *
	 * @return string
	 */
	public function get_base_state(): string {
		return $this->get_geo_service()->get_base_state();
	}

	/**
	 * Get country name by country code
	 *
	 * @param string $country_code The country code.
	 * @return string
	 * @throws GeoServiceException When there is an error retrieving country name.
	 */
	public function get_country_name( string $country_code ): string {
		if ( ! $this->is_valid_country_code( $country_code ) ) {
			if ( $this->logger ) {
				$this->logger->error( 'Invalid country code: ' . esc_html( $country_code ) );
			}
			throw GeoServiceException::invalidCountryCode( esc_html( $country_code ) );
		}

		try {
			$countries = $this->get_countries();
			return $countries[ $country_code ];
		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Failed to get country name for code: ' . esc_html( $country_code ) . '. Error: ' . esc_html( $e->getMessage() ) );
			}
			throw GeoServiceException::dataRetrievalFailed( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Get state name by country and state code
	 *
	 * @param string $country_code The country code.
	 * @param string $state_code The state code.
	 * @return string
	 * @throws GeoServiceException When country code is invalid or state code doesn't exist.
	 */
	public function get_state_name( string $country_code, string $state_code ): string {
		if ( ! $this->is_valid_country_code( $country_code ) ) {
			throw GeoServiceException::invalidCountryCode( esc_html( $country_code ) );
		}

		if ( ! $this->is_valid_state_code( $state_code ) ) {
			throw GeoServiceException::invalidStateCode( esc_html( $state_code ) );
		}

		$states = $this->get_states_by_country_code( $country_code );
		if ( ! isset( $states[ $state_code ] ) ) {
			throw GeoServiceException::invalidStateCodeForCountry( esc_html( $country_code ), esc_html( $state_code ) );
		}

		return $states[ $state_code ];
	}

	/**
	 * Get all states
	 *
	 * @return array<string, array<string, string>>
	 */
	public function get_all_states(): array {
		$states = array();
		foreach ( $this->get_countries() as $country_code => $country ) {
			$states[ $country_code ] = $this->get_states_by_country_code( $country_code );
		}
		return $states;
	}

	/**
	 * Validate state code.
	 *
	 * @param string $state_code The state code.
	 * @return bool
	 */
	private function is_valid_state_code( string $state_code ): bool {
		foreach ( $this->get_countries() as $country_code => $country ) {
			$states = $this->get_states_by_country_code( $country_code );
			if ( isset( $states[ $state_code ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validate country code.
	 *
	 * @param string $country_code The country code.
	 * @return bool
	 */
	private function is_valid_country_code( string $country_code ): bool {
		return isset( $this->get_countries()[ $country_code ] );
	}

	/**
	 * Update base country and state
	 *
	 * @param string $country_code Country code.
	 * @param string $state_code State code.
	 * @return bool
	 * @throws GeoServiceException When invalid country or state code provided.
	 */
	public function update_base_location( string $country_code, string $state_code = '' ): bool {
		if ( ! $this->is_valid_country_code( $country_code ) ) {
			throw GeoServiceException::invalidCountryCode( esc_html( $country_code ) );
		}

		if ( $state_code && ! isset( $this->get_states_by_country_code( $country_code )[ $state_code ] ) ) {
			throw GeoServiceException::invalidStateCodeForCountry( esc_html( $country_code ), esc_html( $state_code ) );
		}

		$location = array(
			'country' => $country_code,
			'state'   => $state_code,
		);

		// Use WooCommerce's filter to update base location.
		return (bool) update_option( 'woocommerce_default_country', esc_attr( $country_code . ':' . $state_code ) );
	}

	/**
	 * Update allowed selling countries
	 *
	 * @param string $allowed_type 'all'|'specific'|'all_except'.
	 * @param array  $country_codes Array of country codes when type is 'specific' or 'all_except'.
	 * @return bool
	 * @throws GeoServiceException When invalid country codes provided.
	 */
	public function update_allowed_countries( string $allowed_type, array $country_codes = array() ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( $this->logger ) {
				$this->logger->error( 'Insufficient permissions to update WooCommerce settings' );
			}
			return false;
		}

		try {
			if ( ! in_array( $allowed_type, array( 'all', 'specific', 'all_except' ), true ) ) {
				throw new GeoServiceException( esc_html__( 'Invalid allowed countries type', 'cod-funnel-booster' ) );
			}

			// Validate country codes.
			if ( ! empty( $country_codes ) ) {
				foreach ( $country_codes as $code ) {
					if ( ! $this->is_valid_country_code( $code ) ) {
						throw GeoServiceException::invalidCountryCode( esc_html( $code ) );
					}
				}
			}

			// Get current allowed countries.
			$current_allowed_type       = get_option( 'woocommerce_allowed_countries', 'all' );
			$current_specific_countries = get_option( 'woocommerce_specific_allowed_countries', array() );

			// Log current state.
			if ( $this->logger ) {
				$this->logger->debug(
					sprintf(
						'Updating allowed countries. Current: type=%s, countries=%s',
						$current_allowed_type,
						wp_json_encode( $current_specific_countries )
					)
				);
			}

			$success = true;

			// Only update type if it's different.
			if ( $current_allowed_type !== $allowed_type ) {
				$success = update_option( 'woocommerce_allowed_countries', $allowed_type );
			}

			// Update countries if specific type and countries have changed.
			if ( $success && 'specific' === $allowed_type ) {
				$current_countries_serialized = wp_json_encode( $current_specific_countries );
				$new_countries_serialized     = wp_json_encode( $country_codes );

				if ( $current_countries_serialized !== $new_countries_serialized ) {
					$success = update_option( 'woocommerce_specific_allowed_countries', $country_codes );
				}
			}

			// Log result.
			if ( $this->logger ) {
				$this->logger->debug(
					sprintf(
						'Update result: success=%s, current_type=%s, new_type=%s, countries_changed=%s',
						$success ? 'true' : 'false',
						$current_allowed_type,
						$allowed_type,
						$current_countries_serialized !== $new_countries_serialized ? 'true' : 'false'
					)
				);
			}

			if ( $success ) {
				$this->clear_woocommerce_cache();
			}

			return $success;

		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Failed to update allowed countries: ' . $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Update shipping countries
	 *
	 * @param string $ship_type 'disabled'|'all'|'specific'|'all_allowed'.
	 * @param array  $country_codes Array of country codes when type is 'specific'.
	 * @return bool
	 * @throws GeoServiceException When invalid country codes provided.
	 */
	public function update_shipping_countries( string $ship_type, array $country_codes = array() ): bool {
		if ( ! in_array( $ship_type, array( 'disabled', 'all', 'specific', 'all_allowed' ), true ) ) {
			throw new GeoServiceException( esc_html__( 'Invalid shipping countries type', 'cod-funnel-booster' ) );
		}

		// Validate country codes for specific countries.
		if ( 'specific' === $ship_type && ! empty( $country_codes ) ) {
			foreach ( $country_codes as $code ) {
				if ( ! $this->is_valid_country_code( $code ) ) {
					throw GeoServiceException::invalidCountryCode( esc_html( $code ) );
				}
			}
		}

		$success = update_option( 'woocommerce_ship_to_countries', $ship_type );

		if ( $success && 'specific' === $ship_type ) {
			$success = $success && update_option( 'woocommerce_specific_ship_to_countries', $country_codes );
		}

		// Clear WooCommerce cache safely.
		if ( $success ) {
			$this->clear_woocommerce_cache();
		}

		return $success;
	}

	/**
	 * Clear WooCommerce cache.
	 */
	private function clear_woocommerce_cache(): void {
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::get_transient_version( 'shipping', true );
		}
	}
}