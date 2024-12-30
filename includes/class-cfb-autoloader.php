<?php
/**
 * Custom autoloader for the COD Funnel Booster plugin classes.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CFB_Autoloaderclass
 */
class CFB_Autoloader {
	/**
	 * Cached file paths to improve performance.
	 *
	 * @var array
	 */
	private static $file_path_cache = array();

	/**
	 * Initialize the autoloader.
	 */
	public static function init() {

		// Ensure Composer's autoloader is included first.
		self::include_composer_autoloader();

		// Register the custom autoloader.
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Include Composer's autoloader.
	 */
	private static function include_composer_autoloader() {
		$composer_autoload = CFB_PLUGIN_DIR . 'vendor/autoload_packages.php';

		if ( ! is_readable( $composer_autoload ) ) {
			self::failed_autoloading();
			return false;
		}

		if ( ! file_exists( $composer_autoload ) ) {
			self::failed_autoloading();
			return false;
		}

		require_once $composer_autoload;
	}

	/**
	 * Custom autoloader for WordPress-style classes.
	 *
	 * @param string $_class The fully qualified _class name.
	 */
	private static function autoload( $_class ) {

		if ( isset( self::$file_path_cache[ $_class ] ) ) {

			require_once self::$file_path_cache[ $_class ];

			return true;
		}

		if ( strpos( $_class, 'DevBossMa\\CODFunnelBooster\\' ) !== 0 ) {
			return false;
		}

		$relative_class = str_replace( 'DevBossMa\\CODFunnelBooster\\', '', $_class );
		$relative_class = str_replace( '\\', '/', strtolower( $relative_class ) );
		$relative_class = str_replace( '_', '-', strtolower( $relative_class ) );
		$path_parts     = explode( '/', $relative_class );
		$class_name     = array_pop( $path_parts );
		$file_name      = 'class-' . $class_name . '.php';

		// Define the base directory for your plugin classes.
		$base_dir  = __DIR__ . '/';
		$file_path = $base_dir . implode( '/', $path_parts ) . '/' . $file_name;

		if ( file_exists( $file_path ) ) {
			self::$file_path_cache[ $_class ] = $file_path;
			require_once $file_path;

			// Stop further processing if the _class was found and loaded.
			return true;
		}

		// Let Composer's autoloader or other autoloaders take over if the file wasn't found.
		return false;
	}
	/**
	 * Handling the Autoloader File's missing
	 *
	 * @return void
	 */
	protected static function failed_autoloading() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// This message is not translated as at this point it's too early to load translations.
			error_log(  // phpcs:ignore
				esc_html(
					'Your installation of COD Funnel Booster is incomplete. If you installed COD Funnel Booster from GitHub, please refer to this document to set up your development environment: http://localhost/cod-funnel-booster'
				)
			);
		}

		add_action(
			'admin_notices',
			function () {
				?>
				<div _class="notice notice-error">
					<p>
						<?php
						printf(
							/* translators: 1: is a link to a support document. 2: closing link */
							esc_html__( 'Your installation of COD Funnel Booster is incomplete. If you installed COD Funnel Booster from GitHub, %1$splease refer to this document%2$s to set up your development environment.', 'cod-funnel-booster' ),
							'<a href="' . esc_url( 'http://localhost/cod-funnel-booster' ) . '" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
						?>
					</p>
				</div>
					<?php
			}
		);
	}
}
