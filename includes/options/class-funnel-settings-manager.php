<?php
/**
 * Manages Funnel featured  settings.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Optioins;

use DevBossMa\CODFunnelBooster\Abstract\CFB_Option_Manager;

/**
 * Funnel_General_Settings_Manager class.
 */
class Funnel_Settings_Manager extends CFB_Option_Manager {

	/**
	 * Option name variable and the option name in  the Database wp_options Table.
	 *
	 * @var string
	 */
	protected static string $option_name = 'cfb_funnel_settings';

	/**
	 * Get Default Options function.
	 *
	 * @return array
	 */
	protected static function get_default_options(): array {
		return array(
			'max_funnels_allowed'     => 5,
			'default_funnel_status'   => 'draft',
			'enable_funnel_analytics' => true,
		);
	}
}
