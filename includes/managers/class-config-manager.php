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
		// Only register REST routes after WooCommerce is initialized.
		add_action(
			'woocommerce_init',
			function () {
				add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
			}
		);

		// Add script localization.
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
						'required' => true,
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

			$config = array(
				'buisinessInfo' => array(
					'buisinessName'     => get_option( 'blogname' ),
					'buisinessEmail'    => get_option( 'admin_email' ),
					'buisinessCountry'  => $this->geo_service_provider->get_base_country(),
					'buisinessState'    => $this->geo_service_provider->get_base_state(),
					'buisinessCity'     => get_option( 'woocommerce_store_city' ),
					'buisinessAdress'   => get_option( 'woocommerce_store_address' ),
					'buisinessCurrency' => get_woocommerce_currency(),
				),
				'geoConfig'     => array(
					'allCountries'      => $this->geo_service_provider->get_countries(),
					'states'            => $this->geo_service_provider->get_states_by_country_code( $this->geo_service_provider->get_base_country() ),
					'sellOption'        => get_option( 'woocommerce_allowed_countries', 'all' ),
					'specificCountries' => get_option( 'woocommerce_specific_allowed_countries', array() ),
					'excludedCountries' => get_option( 'woocommerce_excluded_countries', array() ),
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

			// Update WordPress and WooCommerce settings.
			update_option( 'blogname', sanitize_text_field( $request['buisinessName'] ) );
			update_option( 'admin_email', sanitize_email( $request['buisinessEmail'] ) );
			update_option( 'woocommerce_store_address', sanitize_text_field( $request['buisinessAddress'] ) );
			update_option( 'woocommerce_store_city', sanitize_text_field( $request['buisinessCity'] ) );
			update_option( 'woocommerce_default_country', sanitize_text_field( $request['buisinessCountry'] ) . ':' . sanitize_text_field( $request['buisinessState'] ) );
			update_option( 'woocommerce_currency', sanitize_text_field( $request['buisinessCurrency'] ) );

			update_option( 'woocommerce_default_country', $country . ':' . $state );

			// Set allowed countries.
			$this->geo_service_provider->update_allowed_countries( 'specific', array( $country ) );

			// Return updated config.
			return new WP_REST_Response(
				array(
					'message' => 'Store settings updated successfully',
					'data'    => $this->get_store_config(),
				),
				200
			);

		} catch ( \Exception $e ) {
			error_log( 'Store settings update failed: ' . $e->getMessage() ); // phpcs:ignore
			return new WP_REST_Response(
				array( 'message' => $e->getMessage() ),
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
}
