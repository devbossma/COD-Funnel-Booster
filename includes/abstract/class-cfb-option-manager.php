<?php
/**
 * Abstract base class for the plugin option management.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Abstract;

/**
 * Abstract CFB_Option_Manager class
 */
abstract class CFB_Option_Manager {

	/**
	 * Option name variable and the option name in  the Database wp_options Table.
	 *
	 * @var string
	 */
	protected static string $option_name;

	/**
	 * Get option value(s)
	 *
	 * @param string|null $key Specific option key using dot notation.
	 * @return mixed Option value or full options array.
	 */
	public static function get_option( ?string $key = null ): mixed {
		$options = get_option( static::$option_name );

		if ( false === $options ) {
			$options = static::get_default_options();
			update_option( static::$option_name, $options );
		}

		if ( null === $key ) {
			return $options;
		}

		// Support dot notation for nested options.
		$keys  = explode( '.', $key );
		$value = $options;

		foreach ( $keys as $nested_key ) {
			if ( ! isset( $value[ $nested_key ] ) ) {
				return null;
			}
			$value = $value[ $nested_key ];
		}

		return $value;
	}

	/**
	 * Update specific option
	 *
	 * @param string $key Option key using dot notation.
	 * @param mixed  $value New value.
	 * @return bool Update success.
	 */
	public static function update_option( string $key, mixed $value ): bool {
		$options = static::get_option();

		// Support dot notation for nested options.
		$keys   = explode( '.', $key );
		$target = &$options;

		foreach ( $keys as $i => $nested_key ) {
			if ( count( $keys ) - 1 === $i ) {
				$target[ $nested_key ] = $value;
			} else {
				if ( ! isset( $target[ $nested_key ] ) ) {
					$target[ $nested_key ] = array();
				}
				$target = &$target[ $nested_key ];
			}
		}

		return update_option( static::$option_name, $options );
	}

	/**
	 * Reset options to defaults
	 */
	public static function reset_options(): bool {
		return update_option( static::$option_name, static::get_default_options() );
	}

	/**
	 * Get default options
	 */
	abstract protected static function get_default_options(): array;
}
