<?php
/**
 * LogLevel class as an enum alternative for PHP < 8.1
 *
 * @package CodFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Enums;

/**
 * LogLevel class
 */
class LogLevel {
	public const EMERGENCY = 'emergency';
	public const ALERT     = 'alert';
	public const CRITICAL  = 'critical';
	public const ERROR     = 'error';
	public const WARNING   = 'warning';
	public const NOTICE    = 'notice';
	public const INFO      = 'info';
	public const DEBUG     = 'debug';

	/**
	 * The log level.
	 *
	 * @var string
	 */
	public string $value;

	/**
	 * Constructor
	 *
	 * @param string $level The log level.
	 */
	public function __construct( string $level ) {
		$this->value = $level;
	}

	/**
	 * Create emergency level
	 *
	 * @return self
	 */
	public static function EMERGENCY(): self { // phpcs:ignore
		return new self( self::EMERGENCY );
	}

	/**
	 * Create alert level
	 *
	 * @return self
	 */
	public static function ALERT(): self { // phpcs:ignore
		return new self( self::ALERT );
	}

	/**
	 * Create critical level
	 *
	 * @return self
	 */
	public static function CRITICAL(): self {// phpcs:ignore
		return new self( self::CRITICAL );
	}

	/**
	 * Create error level
	 *
	 * @return self
	 */
	public static function ERROR(): self { // phpcs:ignore
		return new self( self::ERROR );
	}

	/**
	 * Create warning level
	 *
	 * @return self
	 */
	public static function WARNING(): self { // phpcs:ignore
		return new self( self::WARNING );
	}

	/**
	 * Create notice level
	 *
	 * @return self
	 */
	public static function NOTICE(): self { // phpcs:ignore
		return new self( self::NOTICE );
	}

	/**
	 * Create info level
	 *
	 * @return self
	 */
	public static function INFO(): self { // phpcs:ignore
		return new self( self::INFO );
	}

	/**
	 * Create debug level
	 *
	 * @return self
	 */
	public static function DEBUG(): self { // phpcs:ignore
		return new self( self::DEBUG );
	}
}
