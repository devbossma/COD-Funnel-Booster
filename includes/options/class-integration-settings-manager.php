<?php
/**
 * Manages Integrated platforms and Apis settings.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Optioins;

use DevBossMa\CODFunnelBooster\Abstract\CFB_Option_Manager;

/**
 * Integration_Settings_Manager class.
 */
class Integration_Settings_Manager extends CFB_Option_Manager {

	/**
	 * Option name variable and the option name in  the Database wp_options Table.
	 *
	 * @var string
	 */
	protected static string $option_name = 'cfb_integration_settings';

	/**
	 * Get Default Options function.
	 *
	 * @return array
	 */
	protected static function get_default_options(): array {
		return array(
			'woocommerce_sync'        => true,
			'elementor_compatibility' => true,
			'third_party_tracking'    => array(
				'facebook_pixel'   => array(
					'enabled'  => false,
					'pixel_id' => '',
				),
				'google_analytics' => array(
					'enabled'     => false,
					'tracking_id' => '',
				),
			),
		);
	}
}
