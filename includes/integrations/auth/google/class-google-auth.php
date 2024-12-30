<?php
/**
 * Google Platform Authentication class.
 *
 * @package CODFunnelBooster
 * @subpackage Authentication
 * @since 1.0.0
 */

namespace DevBossMa\CODFunnelBooster\Includes\integrations\auth\google;

use Exception;
use Google_Client;
use Google\Service;
use Google\Service\Drive;
use Google_Service_Drive;
use Google\Service\Sheets;
use Google_Service_Sheets;
use DevBossMa\CODFunnelBooster\Includes\abstract\CFB_Platform_Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class responsible for handling Google platform authentication.
 *
 * @since 1.0.0
 */
class Google_Auth extends CFB_Platform_Auth {

	/**
	 * Google Client instance
	 *
	 * @since 1.0.0
	 * @var Google_Client|null
	 */
	private ?Google_Client $client = null;

	/**
	 * Required Google API scopes
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $scopes = array(
		Google_Service_Drive::DRIVE_READONLY,
		Google_Service_Sheets::SPREADSHEETS_READONLY,
	);

	/**
	 * Constructor for Google platform authentication.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'google' );

		// Additional Google-specific initialization if needed.
		if ( ! class_exists( 'Google_Client' ) ) {
			require_once CFB_PLUGIN_DIR . 'vendor/autoload.php';
		}
	}

	/**
	 * Validates Google credentials format and content.
	 *
	 * @since 1.0.0
	 * @param string $credentials_data JSON string containing Google credentials.
	 * @return bool Whether the credentials are valid.
	 * @throws Exception Google credentials validation error.
	 */
	protected function validate_credentials( string $credentials_data ): bool {
		try {
			$credentials = json_decode( $credentials_data, true, 512, JSON_THROW_ON_ERROR );

			// Check for required credential fields.
			$required_fields = array(
				'web',
				'web.client_id',
				'web.project_id',
				'web.auth_uri',
				'web.token_uri',
				'web.client_secret',
			);

			foreach ( $required_fields as $field ) {
				$keys  = explode( '.', $field );
				$value = $credentials;
				foreach ( $keys as $key ) {
					if ( ! isset( $value[ $key ] ) ) {
						return false;
					}
					$value = $value[ $key ];
				}
			}

			// Validate credential format.
			return $this->validate_credential_format( $credentials['web'] );

		} catch ( Exception $e ) {

			throw new Exception( esc_textarea( 'Google credentials validation error: ' . $e->getMessage() ), 1 );

		}
	}

	/**
	 * Validates Google OAuth token format and content.
	 *
	 * @since 1.0.0
	 * @param string $token_data JSON string containing Google token.
	 * @return bool Whether the token is valid.
	 * @throws Exception Google token validation error.
	 * */
	protected function validate_token( string $token_data ): bool {
		try {
			$token = json_decode( $token_data, true, 512, JSON_THROW_ON_ERROR );

			// Check for required token fields.
			$required_fields = array(
				'access_token',
				'refresh_token',
				'scope',
				'token_type',
				'expires_in',
			);

			foreach ( $required_fields as $field ) {
				if ( ! isset( $token[ $field ] ) ) {
					return false;
				}
			}

			// Validate token format.
			return $this->validate_token_format( $token );

		} catch ( Exception $e ) {
			throw new Exception( esc_textarea( 'Google token validation error: ' . $e->getMessage() ), 1 );

		}
	}

	/**
	 * Retrieves required authorization scopes for Google API.
	 *
	 * @since 1.0.0
	 * @return array List of required Google API scopes.
	 */
	protected function get_required_scopes(): array {
		return $this->scopes;
	}

	/**
	 * Initializes and configures Google API client.
	 *
	 * @since 1.0.0
	 * @return Google_Client|null Configured Google client or null on failure.
	 * @throws Exception Google credentials not found.
	 */
	public function initialize_client() {
		try {
			if ( null !== $this->client ) {
				return $this->client;
			}

			$this->client = new Google_Client();
			$this->client->setApplicationName( 'COD Funnel Booster' );
			$this->client->setScopes( $this->get_required_scopes() );
			$this->client->setAccessType( 'offline' );
			$this->client->setPrompt( 'consent' );

			// Load credentials.
			$credentials = $this->get_credentials();
			if ( ! $credentials ) {
				throw new Exception( 'Google credentials not found' );
			}

			$this->client->setAuthConfig( json_decode( $credentials, true ) );

			// Load and set access token if available.
			$token = $this->get_token();
			if ( $token ) {
				$this->client->setAccessToken( json_decode( $token, true ) );

				// Refresh token if expired.
				if ( $this->client->isAccessTokenExpired() ) {
					$this->refresh_token();
				}
			}

			return $this->client;

		} catch ( Exception $e ) {
			throw new Exception( 'Google client initialization error' );

		}
	}

	/**
	 * Validates the format of Google OAuth credentials.
	 *
	 * @since 1.0.0
	 * @param array $credentials Decoded credentials array.
	 * @return bool Whether the credential format is valid.
	 */
	private function validate_credential_format( array $credentials ): bool {
		// Validate client ID format.
		if ( ! preg_match( '/^\d+-[a-z0-9]+\.apps\.googleusercontent\.com$/', $credentials['client_id'] ) ) {
			return false;
		}

		// Validate project ID format.
		if ( ! preg_match( '/^[a-z][-a-z0-9]{4,28}[a-z0-9]$/', $credentials['project_id'] ) ) {
			return false;
		}

		// Validate auth URI.
		if ( ! filter_var( $credentials['auth_uri'], FILTER_VALIDATE_URL ) ) {
			return false;
		}

		// Validate token URI.
		if ( ! filter_var( $credentials['token_uri'], FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validates the format of Google OAuth token.
	 *
	 * @since 1.0.0
	 * @param array $token Decoded token array.
	 * @return bool Whether the token format is valid.
	 */
	private function validate_token_format( array $token ): bool {
		// Validate access token format.
		if ( ! preg_match( '/^[a-zA-Z0-9._-]+$/', $token['access_token'] ) ) {
			return false;
		}

		// Validate refresh token format.
		if ( ! preg_match( '/^[a-zA-Z0-9._-]+$/', $token['refresh_token'] ) ) {
			return false;
		}

		// Validate token type.
		if ( 'Bearer' !== $token['token_type'] ) {
			return false;
		}

		// Validate expires_in value.
		if ( ! is_int( $token['expires_in'] ) || $token['expires_in'] <= 0 ) {
			return false;
		}

		// Validate scope format.
		if ( ! is_string( $token['scope'] ) || empty( $token['scope'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Refreshes the Google OAuth token.
	 *
	 * @since 1.0.0
	 * @return bool Whether the token refresh was successful.
	 * @throws Exception Google client not initialized.
	 */
	private function refresh_token(): bool {
		try {
			if ( ! $this->client ) {
				throw new Exception( 'Google client not initialized' );
			}

			if ( ! $this->client->getRefreshToken() ) {
				throw new Exception( 'Refresh token not available' );
			}

			$this->client->fetchAccessTokenWithRefreshToken( $this->client->getRefreshToken() );

			// Save the new token.
			$new_token = wp_json_encode( $this->client->getAccessToken(), JSON_PRETTY_PRINT );
			return $this->save_credentials( $this->get_credentials(), $new_token );

		} catch ( Exception $e ) {
			throw new Exception( 'Google token refresh error: ' . esc_textarea( $e->getMessage() ) );
		}
	}

	/**
	 * Revokes Google OAuth access and cleans up stored credentials.
	 *
	 * @since 1.0.0
	 * @return bool Whether the revocation was successful.
	 * @throws Exception Google access revocation error.
	 */
	public function revoke_access(): bool {
		try {
			if ( $this->client && $this->client->getAccessToken() ) {
				$this->client->revokeToken();
			}

			// Remove stored credentials and token.
			$platform_dir = $this->get_platform_directory();
			array_map( 'unlink', glob( "$platform_dir/*" ) );

			// Update platform status.
			$this->update_platform_status( false );

			return true;

		} catch ( Exception $e ) {
			throw new Exception( 'Google access revocation error: ' . esc_textarea( $e->getMessage() ) );
		}
	}
}
