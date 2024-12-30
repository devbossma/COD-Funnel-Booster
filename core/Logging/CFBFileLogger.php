<?php
/**
 * File Logger
 *
 * @version 1.0.0
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core\Logging;

use DevBossMa\CODFunnelBooster\Core\Enums\LogLevel;
use DevBossMa\CODFunnelBooster\Core\Contracts\CFBLoggerInterface;

/**
 * File Logger
 */
class CFBFileLogger implements CFBLoggerInterface {

	/**
	 * Log file path
	 *
	 * @var string
	 */
	private string $logFile;

	/**
	 * Maximum file size
	 *
	 * @var int
	 */
	private int $maxFileSize;

	/**
	 * Maximum number of files
	 *
	 * @var int
	 */
	private int $maxFiles;

	/**
	 * Filesystem manager
	 *
	 * @var mixed
	 */
	private $filesystem;

	/**
	 * Constructor
	 *
	 * @param string $logFile Log file path.
	 * @param int    $maxFileSize Maximum file size.
	 * @param int    $maxFiles Maximum number of files.
	 */
	public function __construct(
		string $logFile = WP_CONTENT_DIR . '/logs/cod-funnel-booster.log',
		int $maxFileSize = 5242880, // 5MB
		int $maxFiles = 5
	) {
		$this->logFile     = $logFile;
		$this->maxFileSize = $maxFileSize;
		$this->maxFiles    = $maxFiles;
		$this->initializeFilesystem();
		$this->ensureLogDirectoryExists();
	}

	/**
	 * Initialize filesystem
	 *
	 * @return void
	 */
	private function initializeFilesystem(): void {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;
		$this->filesystem = $wp_filesystem;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function emergency( string $message, array $context = array() ): void {
		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function alert( string $message, array $context = array() ): void {
		$this->log( LogLevel::ALERT, $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function critical( string $message, array $context = array() ): void {
		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function error( string $message, array $context = array() ): void {
		$this->log( LogLevel::ERROR, $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function warning( string $message, array $context = array() ): void {
		$this->log( LogLevel::WARNING, $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function notice( string $message, array $context = array() ): void {
		$this->log( LogLevel::NOTICE, $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	public function info( string $message, array $context = array() ): void {
		$this->log( LogLevel::INFO, $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message Log message.
	 *
	 * @param array  $context Additional context data.
	 */
	public function debug( string $message, array $context = array() ): void {
		$this->log( LogLevel::DEBUG, $message, $context );
	}

	/**
	 * Log a message
	 *
	 * @param string $level Log level.
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 */
	private function log( string $level, string $message, array $context = array() ): void {
		$this->rotate();

		$logLevel = new LogLevel( $level );

		$logMessage = sprintf(
			"[%s] %s: %s %s\n",
			gmdate( 'Y-m-d H:i:s' ),
			$logLevel->value,
			$message,
			! empty( $context ) ? wp_json_encode( $context ) : ''
		);

		// Read existing content.
		$existing_content = '';
		if ( $this->filesystem->exists( $this->logFile ) ) {
			$existing_content = $this->filesystem->get_contents( $this->logFile );
		}

		// Append new content.
		$this->filesystem->put_contents(
			$this->logFile,
			$existing_content . $logMessage,
			FS_CHMOD_FILE
		);
	}

	/**
	 * Rotate log files
	 *
	 * @return void
	 */
	private function rotate(): void {
		if ( ! file_exists( $this->logFile ) || filesize( $this->logFile ) < $this->maxFileSize ) {
			return;
		}

		for ( $i = $this->maxFiles - 1; $i >= 0; $i-- ) {
			$oldFile = $this->logFile . ( $i > 0 ? '.' . $i : '' );
			$newFile = $this->logFile . '.' . ( $i + 1 );

			if ( file_exists( $oldFile ) ) {
				$this->filesystem->move( $oldFile, $newFile );
			}
		}
	}

	/**
	 * Ensure log directory exists
	 *
	 * @return void
	 */
	private function ensureLogDirectoryExists(): void {
		$logDir = dirname( $this->logFile );
		if ( ! file_exists( $logDir ) ) {
			$this->filesystem->mkdir( $logDir, 0755 );
		}
	}
}
