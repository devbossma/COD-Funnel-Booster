<?php
/**
 *  Plugin Name: COD Funnel Booster
 * Description: An optimized cash-on-delivery sales funnel builder, with WooCommerce integration and Google Sheets syncing for effortless tracking and improved conversions.
 * Version: 1.0.0
 * Author: Y.SABER | devboss.ma
 * Author URI: https://www.linkedin.com/in/yassine-saber-84b919275/
 * Plugin URI:        http://localhost/cod-funnel-booster
 * Requires at least: 6.7.1
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        http://localhost/cod-funnel-booster
 * Text Domain:       cod-funnel-booster
 * Domain Path:       /languages
 *
 * @package CODFunnelBooster
 */

use DevBossMa\CODFunnelBooster\CFB_Autoloader;
use DevBossMa\CODFunnelBooster\COD_Funnel_Booster;
use DevBossMa\CODFunnelBooster\Core\CFB_Container;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CFB_PLUGIN_FILE' ) ) {
	define( 'CFB_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'CFB_PLUGIN_DIR' ) ) {
	define( 'CFB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CFB_PLUGIN_BASENAME' ) ) {
	define( 'CFB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

// Include the custom autoloader.
require_once CFB_PLUGIN_DIR . 'includes/class-cfb-autoloader.php';

// Initialize the autoloader.
CFB_Autoloader::init();

// Include the main plugin class.
require_once CFB_PLUGIN_DIR . 'includes/class-cod-funnel-booster.php';

// Initialize dependency injection.
$GLOBALS['cfb_container'] = new CFB_Container();


/**
 * Returns the main instance of COD_Funnel_Booster class.
 *
 * @since  1.0.0
 * @return COD_Funnel_Booster
 */
function CFB_Plugin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return COD_Funnel_Booster::instance();
}

/**
 * Returns the COD_Funnel_Booster container's instance.
 * Code in the `includes` directory should use the '\DevBossMa\CODFunnelBooster\Core\CFB_Container' to get instances of classes in the `core` directory.
 *
 * @since  1.0.0
 * @return DevBossMa\CODFunnelBooster\Core\CFB_Container The COD_Funnel_Booster container's instance.
 */
function cfb_container() {
	return $GLOBALS['cfb_container'];
}

// Global for backwards compatibility.
$GLOBALS['cfb_plugin'] = CFB_Plugin();

register_activation_hook( CFB_PLUGIN_FILE, array( COD_Funnel_Booster::instance(), 'activate' ) );
register_deactivation_hook( CFB_PLUGIN_FILE, array( COD_Funnel_Booster::instance(), 'deactivate' ) );
