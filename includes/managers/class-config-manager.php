<?php
/**
 * Config_Manager class
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Managers;

use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;
use DevBossMa\CODFunnelBooster\Core\Services\Providers\GeoServiceProvider;

/**
 * Config_Manager class
 */
class Config_Manager {

	/**
	 * The "GeoServiceProvider" variable.
	 *
	 * @var GeoServiceProvider
	 */
	private GeoServiceProvider $geo_service_provider;

	/**
	 * Config_Manager constructor function.
	 *
	 * @param GeoServiceProvider $geo_service_provider GeoServiceProvider instance.
	 */
	public function __construct( GeoServiceProvider $geo_service_provider ) {
		$this->geo_service_provider = $geo_service_provider;
	}

	/**
	 * Initialize the Config Manager.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'cod-funnel-booster/v1',
			'/store-settings',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_store_settings' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'buisinessName'     => array(
						'required' => true,
						'type'     => 'string',
					),
					'buisinessEmail'    => array(
						'required' => true,
						'type'     => 'string',
					),
					'buisinessCountry'  => array(
						'required' => true,
						'type'     => 'string',
					),
					'buisinessState'    => array(
						'required' => false,
						'type'     => 'string',
					),
					'buisinessCity'     => array(
						'required' => true,
						'type'     => 'string',
					),
					'buisinessAddress'  => array(
						'required' => true,
						'type'     => 'string',
					),
					'buisinessCurrency' => array(
						'required' => false,
						'type'     => 'string',
					),
					'sellOption'        => array(
						'required' => true,
						'type'     => 'string',
					),
					'specificCountries' => array(
						'required' => false,
						'type'     => 'array',
					),
					'excludedCountries' => array(
						'required' => false,
						'type'     => 'array',
					),
				),
			),
		);

		register_rest_route(
			'cod-funnel-booster/v1',
			'/states/(?P<country>[A-Z]{2})',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_states_for_country' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
				'args'                => array(
					'country' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => function ( $param ) {
							return strlen( $param ) === 2;
						},
					),
				),
			)
		);
	}

	/**
	 * Get store configuration
	 *
	 * @throws \Exception If an error occurs.
	 * @return array
	 */
	public function get_store_config(): array {
		try {
			// Check if WooCommerce is loaded.
			if ( ! function_exists( 'WC' ) || ! WC() ) {
				throw new \Exception( 'WooCommerce must be loaded before accessing store configuration' );
			}

			// Get custom states.
			$custom_states   = get_option( 'cod_funnel_custom_states', array() );
			$current_country = $this->geo_service_provider->get_base_country();
			$custom_state    = isset( $custom_states[ $current_country ] ) ? $custom_states[ $current_country ] : '';

			// If the country has no predefined states but has a custom state, use it.
			$states = $this->geo_service_provider->get_states_by_country_code( $current_country );
			$state  = empty( $states ) ? $custom_state : $this->geo_service_provider->get_base_state();

			$config = array(
				'buisinessInfo' => array(
					'buisinessName'     => get_option( 'blogname' ),
					'buisinessEmail'    => get_option( 'admin_email' ),
					'buisinessCountry'  => $this->geo_service_provider->get_base_country(),
					'buisinessState'    => $state,
					'buisinessCity'     => get_option( 'woocommerce_store_city' ),
					'buisinessAdress'   => get_option( 'woocommerce_store_address' ),
					'buisinessCurrency' => get_woocommerce_currency(),
				),
				'geoConfig'     => array(
					'allCountries'      => $this->geo_service_provider->get_countries(),
					'states'            => $this->geo_service_provider->get_states_by_country_code( $this->geo_service_provider->get_base_country() ),
					'sellOption'        => get_option( 'woocommerce_allowed_countries', 'all' ),
					'specificCountries' => get_option( 'woocommerce_specific_allowed_countries', array() ),
					'excludedCountries' => get_option( 'woocommerce_all_except_countries', array() ),
					'customStates'      => $custom_states,
				),
			);

			return $config;

		} catch ( \Exception $e ) {
			error_log( 'Config Manager Error: ' . $e->getMessage() ); // phpcs:ignore
			throw $e;
		}
	}

	/**
	 * Check if the current user has admin permissions
	 *
	 * @return bool
	 */
	public function check_admin_permissions(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Save store settings
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return WP_REST_Response
	 * @throws \Exception If an error occurs.
	 */
	public function save_store_settings( WP_REST_Request $request ): WP_REST_Response {
		try {
			if ( ! function_exists( 'WC' ) || ! WC() ) {
				throw new \Exception( 'WooCommerce must be loaded' );
			}

			$country = sanitize_text_field( $request['buisinessCountry'] );
			$state   = sanitize_text_field( $request['buisinessState'] );

			// Validate country first.
			if ( ! $this->geo_service_provider->is_valid_country_code( $country ) ) {
				throw new \Exception( 'Invalid country code provided' );
			}

			// Get available states for the country.
			$available_states = $this->geo_service_provider->get_states_by_country_code( $country );

			// Handle custom states storage.
			$custom_states = get_option( 'cod_funnel_custom_states', array() );

			// For countries with no states, accept any non-empty string as state.
			if ( empty( $available_states ) ) {
				// If country doesn't have predefined states, just sanitize the input.
				$state = ! empty( $state ) ? sanitize_text_field( $state ) : '';
				// If country doesn't have predefined states, store in our custom option.
				if ( ! empty( $state ) ) {
					$custom_states[ $country ] = $state;
					update_option( 'cod_funnel_custom_states', $custom_states );
				}
			} elseif ( empty( $state ) || ! isset( $available_states[ $state ] ) ) {
					throw new \Exception( 'Invalid state code provided for country with predefined states' );
			} elseif ( isset( $custom_states[ $country ] ) ) {
					unset( $custom_states[ $country ] );
					update_option( 'cod_funnel_custom_states', $custom_states );
			}

			// Update settings.
			update_option( 'woocommerce_default_country', $country . ':' . $state );

			// Update WordPress and WooCommerce settings.
			update_option( 'blogname', sanitize_text_field( $request['buisinessName'] ) );
			update_option( 'admin_email', sanitize_email( $request['buisinessEmail'] ) );
			update_option( 'woocommerce_store_address', sanitize_text_field( $request['buisinessAddress'] ) );
			update_option( 'woocommerce_store_city', sanitize_text_field( $request['buisinessCity'] ) );
			update_option( 'woocommerce_default_country', $country . ':' . $state );

			if ( ! empty( $request['buisinessCurrency'] ) ) {
				update_option( 'woocommerce_currency', sanitize_text_field( $request['buisinessCurrency'] ) );
			}

			$sell_option        = sanitize_text_field( $request['sellOption'] );
			$selected_countries = array();

			// Ensure we're working with arrays and sanitize each country code.
			if ( 'specific' === $sell_option && isset( $request['specificCountries'] ) ) {
				$selected_countries = array_map( 'sanitize_text_field', (array) $request['specificCountries'] );
			} elseif ( 'all_except' === $sell_option && isset( $request['excludedCountries'] ) ) {
				$selected_countries = array_map( 'sanitize_text_field', (array) $request['excludedCountries'] );
			}

			// Validate all country codes before updating.
			foreach ( $selected_countries as $code ) {
				if ( ! array_key_exists( $code, $this->geo_service_provider->get_countries() ) ) {
					throw new \Exception( sprintf( 'Invalid country code provided: %s', $code ) );
				}
			}

			// Set allowed countries.
			$result = $this->geo_service_provider->update_allowed_countries( $sell_option, $selected_countries );

			if ( ! $result ) {
				throw new \Exception( 'Failed to update allowed countries' );
			}

			// Clear WooCommerce cache.
			if ( class_exists( 'WC_Cache_Helper' ) ) {
				\WC_Cache_Helper::get_transient_version( 'shipping', true );
			}

			return new WP_REST_Response(
				array(
					'message' => 'Store settings updated successfully',
					'data'    => $this->get_store_config(),
				),
				200
			);
		} catch ( \Exception $e ) {
			error_log( // phpcs:ignore
				'Store settings update failed: ' . print_r( // phpcs:ignore
					array(
						'country' => $country ?? 'none',
						'state'   => $state ?? 'none',
						'error'   => $e->getMessage(),
					),
					true
				)
			);

			return new WP_REST_Response(
				array(
					'message' => $e->getMessage(),
					'code'    => 'store_settings_error',
					'debug'   => array(
						'country' => $country ?? 'none',
						'state'   => $state ?? 'none',
					),
				),
				500
			);
		}
	}

	/**
	 * Enqueue necessary scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ): void {
		if ( 'toplevel_page_cod-funnel-setup' !== $hook ) {
			return;
		}

		wp_localize_script(
			'cod-funnel-wizard',
			'codFunnelConfigManager',
			array(
				'nonce'       => wp_create_nonce( 'wp_rest' ),  // Changed to wp_rest nonce.
				'storeConfig' => $this->get_safe_store_config(),
				'restUrl'     => get_rest_url( null, 'cod-funnel-booster/v1' ),
			)
		);
	}

	/**
	 * Get store configuration safely (without throwing errors)
	 *
	 * @return array
	 */
	private function get_safe_store_config(): array {
		try {
			if ( ! function_exists( 'WC' ) || ! WC() || ! did_action( 'woocommerce_init' ) ) {
				return array(
					'isWooCommerceReady' => false,
					'message'            => 'WooCommerce is not initialized yet',
					'data'               => array(
						'buisinessInfo' => array(
							'storeName'  => get_option( 'blogname' ),
							'storeEmail' => get_option( 'admin_email' ),
						),
						'geoConfig'     => array(
							'countries' => array(),
							'states'    => array(),
						),
					),
				);
			}

			return array(
				'isWooCommerceReady' => true,
				'data'               => $this->get_store_config(),
			);
		} catch ( \Exception $e ) {
			return array(
				'isWooCommerceReady' => false,
				'error'              => $e->getMessage(),
				'data'               => array(
					'buisinessInfo' => array(
						'storeName'  => get_option( 'blogname' ),
						'storeEmail' => get_option( 'admin_email' ),
					),
					'geoConfig'     => array(
						'countries' => array(),
						'states'    => array(),
					),
				),
			);
		}
	}

	/**
	 * Get states for a specific country
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 * @throws  \Exception If an error occurs.
	 */
	public function get_states_for_country( WP_REST_Request $request ): WP_REST_Response {
		try {
			$country_code = $request->get_param( 'country' );

			if ( empty( $country_code ) ) {
				throw new \Exception( 'Country code is required' );
			}

			// Get states for the country.
			$states = $this->geo_service_provider->get_states_by_country_code( $country_code );

			// If no states found, return empty array (some countries don't have states).
			if ( empty( $states ) ) {
				$states = array();
			}

			return new WP_REST_Response(
				array(
					'success' => true,
					'states'  => $states,
					'country' => $country_code, // Include country code for debugging.
				)
			);
		} catch ( \Exception $e ) {
			error_log( 'Error fetching states: ' . $e->getMessage() ); // phpcs:ignore
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $e->getMessage(),
					'country' => $country_code ?? 'unknown',
				),
				500
			);
		}
	}
}
