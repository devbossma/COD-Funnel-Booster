<?php
/**
 * GeoServiceException class file
 *
 * @package DevBossMa\CODFunnelBooster
 * @subpackage Core\Exceptions
 */

namespace DevBossMa\CODFunnelBooster\Core\Exceptions;

use Exception;

/**
 * GeoServiceException for handling geographic service related errors
 */
class GeoServiceException extends Exception {
	/**
	 * Error codes
	 */
	public const ERROR_WOOCOMMERCE_NOT_INITIALIZED = 100;
	public const ERROR_INVALID_COUNTRY_CODE        = 101;
	public const ERROR_INVALID_STATE_CODE          = 102;
	public const ERROR_INITIALIZATION_FAILED       = 103;
	public const ERROR_DATA_RETRIEVAL_FAILED       = 104;
	public const ERROR_CACHE_FAILED                = 105;

	/**
	 * Error messages
	 *
	 * @var array<int, string>
	 */
	protected static array $messages = array(
		self::ERROR_WOOCOMMERCE_NOT_INITIALIZED => 'WooCommerce is not initialized',
		self::ERROR_INVALID_COUNTRY_CODE        => 'Invalid country code: %s',
		self::ERROR_INVALID_STATE_CODE          => 'Invalid state code: %s',
		self::ERROR_INITIALIZATION_FAILED       => 'Failed to initialize geo service: %s',
		self::ERROR_DATA_RETRIEVAL_FAILED       => 'Failed to retrieve geographic data: %s',
		self::ERROR_CACHE_FAILED                => 'Failed to cache geographic data: %s',
	);

	/**
	 * Create a new exception for WooCommerce not initialized
	 *
	 * @return self
	 */
	public static function wooCommerceNotInitialized(): self {
		return new self(
			self::$messages[ self::ERROR_WOOCOMMERCE_NOT_INITIALIZED ],
			self::ERROR_WOOCOMMERCE_NOT_INITIALIZED
		);
	}

	/**
	 * Create a new exception for invalid country code
	 *
	 * @param string $countryCode The invalid country code.
	 * @return self
	 */
	public static function invalidCountryCode( string $countryCode ): self {
		return new self(
			sprintf( self::$messages[ self::ERROR_INVALID_COUNTRY_CODE ], $countryCode ),
			self::ERROR_INVALID_COUNTRY_CODE
		);
	}

	/**
	 * Create a new exception for invalid state code
	 *
	 * @param string $stateCode The invalid state code.
	 * @return self
	 */
	public static function invalidStateCode( string $stateCode ): self {
		return new self(
			sprintf( self::$messages[ self::ERROR_INVALID_STATE_CODE ], $stateCode ),
			self::ERROR_INVALID_STATE_CODE
		);
	}

	/**
	 * Create a new exception for invalid state code for specific country
	 *
	 * @param string $countryCode The country code.
	 * @param string $stateCode The invalid state code.
	 * @return self
	 */
	public static function invalidStateCodeForCountry( string $countryCode, string $stateCode ): self {
		return new self(
			sprintf( 'State code %s is not valid for country %s', $stateCode, $countryCode ),
			self::ERROR_INVALID_STATE_CODE
		);
	}

	/**
	 * Create a new exception for initialization failure
	 *
	 * @param string $reason The reason for failure.
	 * @return self
	 */
	public static function initializationFailed( string $reason ): self {
		return new self(
			sprintf( self::$messages[ self::ERROR_INITIALIZATION_FAILED ], $reason ),
			self::ERROR_INITIALIZATION_FAILED
		);
	}

	/**
	 * Create a new exception for data retrieval failure
	 *
	 * @param string $reason The reason for failure.
	 * @return self
	 */
	public static function dataRetrievalFailed( string $reason ): self {
		return new self(
			sprintf( self::$messages[ self::ERROR_DATA_RETRIEVAL_FAILED ], $reason ),
			self::ERROR_DATA_RETRIEVAL_FAILED
		);
	}

	/**
	 * Create a new exception for cache failure
	 *
	 * @param string $reason The reason for failure.
	 * @return self
	 */
	public static function cacheFailed( string $reason ): self {
		return new self(
			sprintf( self::$messages[ self::ERROR_CACHE_FAILED ], $reason ),
			self::ERROR_CACHE_FAILED
		);
	}

	/**
	 * Get all available error codes
	 *
	 * @return array<int>
	 */
	public static function getErrorCodes(): array {
		return array_keys( self::$messages );
	}

	/**
	 * Check if an error code is valid
	 *
	 * @param int $code The error code to check.
	 * @return bool
	 */
	public static function isValidErrorCode( int $code ): bool {
		return isset( self::$messages[ $code ] );
	}
}
