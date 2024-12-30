<?php
/**
 * Manages checkout form featured  settings.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Optioins;

use DevBossMa\CODFunnelBooster\Abstract\CFB_Option_Manager;


/**
 * Checkout_Settings_Manager class.
 */
class Checkout_Settings_Manager extends CFB_Option_Manager {

	/**
	 * Option name variable and the option name in  the Database wp_options Table.
	 *
	 * @var string
	 */
	protected static string $option_name = 'cfb_checkout_settings';

	/**
	 * Get Default Options function.
	 *
	 * @return array
	 */
	protected static function get_default_options(): array {
		return array(
			'cod_enabled'              => true,
			'custom_fields'            => array(
				'phone'               => array(
					'enabled'         => true,
					'required'        => true,
					'label'           => 'Phone Number',
					'placeholder'     => 'Enter your phone number',
					'validation_type' => 'phone',
				),
				'alternative_contact' => array(
					'enabled'     => false,
					'required'    => false,
					'label'       => 'Alternative Contact',
					'placeholder' => 'Alternative phone number',
				),
			),
			'order_notes_enabled'      => true,
			'order_notes_default_text' => 'Additional order instructions',
		);
	}
}
