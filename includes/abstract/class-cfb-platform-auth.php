<?php
/**
 * Abstract Platform Authentication class.
 *
 * @abstract
 * @version 1.0.0
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Abstract;

use Exception;
use RunTimeExeption;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract  CFB Platform Auth Class
 *
 * Implemented by platform classes using the same credentioals based authentication process.
 *
 * @version  1.0.0
 * @package  CODFunnelBooster
 */
abstract class CFB_Platform_Auth {

	/**
	 * Name of the platform being authenticated
	 *
	 * @var string $platform_name Unique identifier for the authentication platform
	 */
	protected string $platform_name;

	/**
	 * Encryption key for file contents
	 *
	 * @var string
	 */
	private string $encryption_key;

	/**
	 * List of required files for platform authentication
	 *
	 * @var string[] $required_files Configuration and token file names
	 */
	protected array $required_files = array( 'credentials', 'token' );

	/**
	 * Application settings retrieved from WordPress options
	 *
	 * @var array<string, mixed> $settings Stored configuration settings
	 */
	protected array $settings;

	/**
	 * Base directory path for storing authentication files
	 *
	 * @var string $base_path Encrypted base directory path
	 */
	protected string $base_path;

	/**
	 * Constructor initializes platform-specific authentication setup
	 *
	 * @param string $platform_name Name of the authentication platform.
	 * @throws Exception When critical security requirements are not met.
	 **/
	public function __construct( string $platform_name ) {

		if ( ! defined( 'AUTH_KEY' ) || ! defined( 'SECURE_AUTH_KEY' ) ) {
			throw new Exception( 'WordPress security keys are not properly configured.' );
		}

		$this->platform_name = $platform_name;
		$this->settings      = get_option( 'cfb_settings' ) ? get_option( 'cfb_settings' ) : array();
		$this->base_path     = $this->decrypt_path( $this->settings['paths']['auth_dir'] ?? '' );

		// Ensure secure initialization.
		$this->validate_environment();
		$this->init_platform_directory();
	}

	/**
	 * Retrieves the platform-specific authentication directory
	 *
	 * @return string Full path to platform authentication directory
	 */
	protected function get_platform_directory(): string {
		return $this->base_path . '/' . $this->platform_name;
	}

	/**
	 * Validates the security environment
	 *
	 * @throws Exception If security requirements are not met.
	 */
	private function validate_environment(): void {
		// Check for secure connection.
		if ( ! is_ssl() && ! WP_DEBUG ) {
			throw new Exception( 'Secure connection required for authentication handling.' );
		}

		// Validate base directory.
		if ( empty( $this->base_path ) || ! wp_is_writable( $this->base_path ) ) {
			throw new Exception( 'Authentication directory is not properly configured or writable.' );
		}
	}

	/**
	 * Generates a unique encryption key for file content encryption
	 *
	 * @return string
	 */
	private function generate_encryption_key(): string {
		return hash_hmac(
			'sha256',
			AUTH_KEY . SECURE_AUTH_KEY . $this->platform_name,
			wp_salt( 'auth' )
		);
	}

	/**
	 * Securely writes data to a file with encryption
	 *
	 * @param string $type File type ('credentials' or 'token').
	 * @param string $data Data to write.
	 * @return bool Success status.
	 * @throws Exception On file operation failures.
	 */
	protected function write_secure_file( string $type, string $data ): bool {
		global $wp_filesystem;

		// Initialize WordPress Filesystem.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			throw new Exception( 'Failed to initialize WordPress filesystem' );
		}

		$file_path      = $this->get_file_path( $type );
		$encrypted_data = $this->encrypt_data( $data );

		// Create a temporary file first.
		$temp_file = $file_path . '.tmp';
		$success   = $wp_filesystem->put_contents(
			$temp_file,
			$encrypted_data,
			FS_CHMOD_FILE
		);

		if ( ! $success ) {
			throw new Exception( esc_textarea( "Failed to write temporary {$type} file" ) );
		}

		// Atomically rename the temporary file.
		if ( ! $wp_filesystem->move( $temp_file, $file_path ) ) {
			wp_delete_file( $temp_file );
			throw new Exception( esc_textarea( "Failed to save {$type} file" ) );
		}

		// Set secure permissions.
		$wp_filesystem->chmod( $file_path, 0640 );

		return true;
	}

	/**
	 * Securely reads and decrypts file contents
	 *
	 * @param string $type File type ('credentials' or 'token').
	 * @return string|null Decrypted contents or null on failure.
	 */
	protected function read_secure_file( string $type ): ?string {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			return null;
		}

		$file_path = $this->get_file_path( $type );

		if ( ! $wp_filesystem->exists( $file_path ) ) {
			return null;
		}

		$encrypted_content = $wp_filesystem->get_contents( $file_path );

		if ( false === $encrypted_content ) {
			return null;
		}

		return $this->decrypt_data( $encrypted_content );
	}

	/**
	 * Encrypts data using authenticated encryption
	 *
	 * @param string $data Raw data to encrypt.
	 * @return string Encrypted data.
	 */
	protected function encrypt_data( string $data ): string {
		$method = 'aes-256-gcm';
		$iv     = random_bytes( 16 );
		$tag    = '';

		$encrypted = sodium_crypto_aead_aes256gcm_encrypt(
			$data,
			$iv,
			$iv,
			$this->encryption_key
		);

		return base64_encode( $iv . $encrypted ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypts data using authenticated encryption
	 *
	 * @param string $encrypted_data Encrypted data to decrypt.
	 * @return string|null Decrypted data or null on failure.
	 * @throws Exception Decryption failed for exeption.
	 */
	protected function decrypt_data( string $encrypted_data ): ?string {
		try {
			$decoded = base64_decode( $encrypted_data, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			if ( false === $decoded ) {
				return null;
			}

			$iv         = substr( $decoded, 0, 16 );
			$ciphertext = substr( $decoded, 16 );

			$decrypted = sodium_crypto_aead_aes256gcm_decrypt(
				$ciphertext,
				$iv,
				$iv,
				$this->encryption_key
			);

			return ( false !== $decrypted ) ? $decrypted : null;
		} catch ( Exception $e ) {
			throw new Exception( esc_textarea( "Decryption failed for {$this->platform_name}: " . $e->getMessage() ), 1 );
		}
	}

	/**
	 * Initializes and secures platform-specific authentication directory
	 *
	 * Creates directory if not exists and applies security measures
	 *
	 * @return void
	 */
	protected function init_platform_directory(): void {
		$dir = $this->get_platform_directory();
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			$this->secure_directory( $dir );
		}
	}

	/**
	 * Applies security measures to authentication directory
	 *
	 * Adds index.php and .htaccess to prevent directory listing
	 * and restrict access to sensitive files
	 *
	 * @param string $dir Directory path to secure.
	 * @return void
	 * @throws Exception Failed to initialize WordPress filesystem.
	 */
	protected function secure_directory( string $dir ): void {
		global $wp_filesystem;

		// Initialize WordPress Filesystem.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			throw new Exception( 'Failed to initialize WordPress filesystem' );
		}

		// Create index.php.
		$wp_filesystem->put_contents(
			$dir . '/index.php',
			'<?php // Silence is golden'
		);

		// Create .htaccess with security rules.
		$htaccess_content  = "Options -Indexes\n\n";
		$htaccess_content .= "<Files ~ \"\\.(json)$\">\n";
		$htaccess_content .= "Order Deny,Allow\n";
		$htaccess_content .= "Deny from all\n";
		$htaccess_content .= "</Files>\n\n";
		$htaccess_content .= "<IfModule mod_headers.c>\n";
		$htaccess_content .= "Header set X-Content-Type-Options \"nosniff\"\n";
		$htaccess_content .= "Header set X-Frame-Options \"DENY\"\n";
		$htaccess_content .= '</IfModule>';

		$wp_filesystem->put_contents( $dir . '/.htaccess', $htaccess_content );
		$wp_filesystem->chmod( $dir, 0750 );
	}

	/**
	 * Encrypts a given path using AES-256-CBC encryption.
	 *
	 * @param string $path The path to encrypt.
	 * @return string The encrypted path, encoded in base64 format.
	 */
	protected function encrypt_path( string $path ): string {
		if ( ! defined( 'AUTH_KEY' ) ) {
			return base64_encode( $path ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		$method = 'aes-256-cbc';
		$salt   = AUTH_KEY . $this->platform_name;
		$key    = substr( hash( 'sha256', $salt ), 0, 32 );
		$iv     = openssl_random_pseudo_bytes( openssl_cipher_iv_length( $method ) );

		$encrypted = openssl_encrypt( $path, $method, $key, 0, $iv );
		return base64_encode( $iv . $encrypted ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypts an encrypted path using AES-256-CBC decryption.
	 *
	 * @param string $encrypted_path The encrypted path in base64 format.
	 * @return string The decrypted path.
	 */
	protected function decrypt_path( string $encrypted_path ): string {
		if ( ! defined( 'AUTH_KEY' ) ) {
			return base64_decode( $encrypted_path ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		}

		$encrypted_data = base64_decode( $encrypted_path ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$method         = 'aes-256-cbc';
		$salt           = AUTH_KEY . $this->platform_name;
		$key            = substr( hash( 'sha256', $salt ), 0, 32 );
		$iv_length      = openssl_cipher_iv_length( $method );

		$iv        = substr( $encrypted_data, 0, $iv_length );
		$encrypted = substr( $encrypted_data, $iv_length );

		return openssl_decrypt( $encrypted, $method, $key, 0, $iv );
	}

	/**
	 * Saves credentials and optionally a token for the platform.
	 *
	 * @param string      $credentials_data The raw credentials data to save.
	 * @param string|null $token_data Optional token data to save.
	 * @return bool True if the credentials and token were saved successfully, false otherwise.
	 * @throws Exception Thtowing invalid credentials exeption format for.
	 * @throws \RunTimeException Error saving exceptionx.
	 */
	public function save_credentials( string $credentials_data, ?string $token_data = null ): bool {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			return null;
		}
		try {
			// Validate credentials.
			if ( ! $this->validate_credentials( $credentials_data ) ) {
				throw new Exception( "Invalid credentials format for {$this->platform_name}" );
			}

			// Save credentials file.
			$cred_path = $this->get_file_path( 'credentials' );
			$wp_filesystemput_contents( $cred_path, $credentials_data );
			$wp_filesystem->chmod( $cred_path, 0640 );

			// Save token if provided.
			if ( null !== $token_data ) {
				if ( ! $this->validate_token( $token_data ) ) {
					throw new Exception( "Invalid token format for {$this->platform_name}" );
				}
				$token_path = $this->get_file_path( 'token' );
				$wp_filesystem_put_contents( $token_path, $token_data );
				$wp_filesystem->chmod( $token_path, 0640 );
			}

			// Update platform status.
			$this->update_platform_status( true );
			return true;

		} catch ( Exception $e ) {
			throw new \RunTimeException( esc_textarea( "Error saving {$this->platform_name} credentials: " . $e->getMessage() ) );
		}
	}

	/**
	 * Gets the file path for a given file type (e.g., credentials or token).
	 *
	 * @param string $type The type of file (e.g., 'credentials', 'token').
	 * @return string The full path to the file.
	 */
	protected function get_file_path( string $type ): string {
		$base_dir = $this->get_platform_directory();
		$filename = $this->get_file_name( $type );
		return $base_dir . '/' . $filename;
	}

	/**
	 * Gets the file name for a given type of file.
	 *
	 * @param string $type The type of file (e.g., 'credentials', 'token').
	 * @return string|null The file name, or null if the type is not recognized.
	 */
	protected function get_file_name( string $type ): ?string {

		/**
		 * File names for credentials.
		 *
		 * @var array $file_names.
		 */
		$file_names = array(
			'credentials' => 'credentials.json',
			'token'       => 'token.json',
		);
		return $file_names[ $type ] ?? null;
	}

		/**
		 * Updates the platform's status in the settings.
		 *
		 * @param bool $configured Indicates whether the platform is configured. Defaults to true.
		 * @return void
		 */
	protected function update_platform_status( bool $configured = true ): void {
		$settings                                      = get_option( 'cfb_settings' );
		$settings['platforms'][ $this->platform_name ] = array(
			'configured'   => $configured,
			'last_updated' => current_time( 'mysql' ),
			'active'       => $configured,
		);
		update_option( 'cfb_settings', $settings );
	}

		/**
		 * Checks if the platform is configured.
		 *
		 * @return bool True if the platform is configured, otherwise false.
		 */
	public function is_configured(): bool {
		$settings = get_option( 'cfb_settings' );
		return isset( $settings['platforms'][ $this->platform_name ]['configured'] )
		&& $settings['platforms'][ $this->platform_name ]['configured'];
	}

		/**
		 * Retrieves the credentials from the credentials file.
		 *
		 * @return string|null The contents of the credentials file, or null if the file does not exist.
		 */
	public function get_credentials(): ?string {
		$path = $this->get_file_path( 'credentials' );
		return file_exists( $path ) ? wp_remote_get( $path ) : null;
	}

		/**
		 * Retrieves the token from the token file.
		 *
		 * @return string|null The contents of the token file, or null if the file does not exist.
		 */
	public function get_token(): ?string {
		/**
			 * Path to the token,json file.
			 *
			 * @var string $path.
			 */
			$path = $this->get_file_path( 'token' );

			return file_exists( $path ) ? wp_remote_get( $path ) : null;
	}

		/**
		 * Validates platform-specific credentials
		 *
		 * @param string $credentials_data Raw credentials data to validate.
		 * @return bool Indicates whether credentials are valid.
		 * @abstract
		 */
	abstract protected function validate_credentials( string $credentials_data ): bool;

		/**
		 * Validates platform-specific access token
		 *
		 * @param string $token_data Raw token data to validate.
		 * @return bool Indicates whether token is valid.
		 * @abstract
		 */
	abstract protected function validate_token( string $token_data ): bool;

		/**
		 * Retrieves required authorization scopes for the platform
		 *
		 * @return string[] List of required authorization scopes
		 * @abstract
		 */
	abstract protected function get_required_scopes(): array;

		/**
		 * Initializes platform-specific client for authentication
		 *
		 * @return mixed Configured authentication client
		 * @abstract
		 */
	abstract public function initialize_client();
}
