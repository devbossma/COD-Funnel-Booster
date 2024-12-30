<?php
/**
 * Manages general featured  settings.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Optioins;

use DevBossMa\CODFunnelBooster\Abstract\CFB_Option_Manager;


/**
 * CRF_General_Settings_Manager class.
 */
class General_Settings_Manager extends CFB_Option_Manager {

	/**
	 * Option name variable and the option name in  the Database wp_options Table.
	 *
	 * @var string
	 */
	protected static string $option_name = 'cfb_general_settings';

	/**
	 * Get Default Options function.
	 *
	 * @return array
	 */
	protected static function get_default_options(): array {
		return array(
			'plugin_version'  => CFB_PLUGIN_VERSION,
			'plugin_enabled'  => true,
			'debug_mode'      => false,
			'setup_complited' => false,
		);
	}
}
