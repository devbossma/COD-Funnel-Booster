<?php
/**
 * CFBLoggerInterface
 *
 * @version 1.0.0
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Contracts;

interface CFBLoggerInterface {
	/**
	 * System is unusable.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function emergency( string $message, array $context = array() ): void;

	/**
	 * Action must be taken immediately.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function alert( string $message, array $context = array() ): void;

	/**
	 * Critical conditions.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function critical( string $message, array $context = array() ): void;

	/**
	 * Runtime errors that do not require immediate action.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function error( string $message, array $context = array() ): void;

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function warning( string $message, array $context = array() ): void;

	/**
	 * Normal but significant events.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function notice( string $message, array $context = array() ): void;

	/**
	 * Interesting events.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function info( string $message, array $context = array() ): void;

	/**
	 * Detailed debug information.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function debug( string $message, array $context = array() ): void;
}
