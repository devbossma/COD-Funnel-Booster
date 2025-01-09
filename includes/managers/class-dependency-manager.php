<?php
/**
 * Manage (Install & Activate) the requiered Plugins for COD Funnel Booster Plugin.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Managers;

use DevBossMa\CODFunnelBooster\Services\Dependency\Dependency_Checker_Service;
use DevBossMa\CODFunnelBooster\Interfaces\Plugin_Dependency_Api_Interface;
use DevBossMa\CODFunnelBooster\Services\Dependency\Plugin_Dependency_Api_Service_V1;

/**
 * Dependency_Manager class.
 */
class Dependency_Manager {

	/**
	 * The "Dependency_Checker_Service" variable.
	 *
	 * @var Dependency_Checker_Service
	 */
	private Dependency_Checker_Service $dependency_checker;

	/**
	 * The "Plugin_Dependency_Api_Service" Interface.
	 *
	 * @var Plugin_Dependency_Api_Interface
	 */
	private Plugin_Dependency_Api_Interface $required_plugin_api;

	/**
	 * Dependency Manager constructor function.
	 *
	 * @param Plugin_Dependency_Api_Interface $required_plugin_api Plugin_Dependency_Api_Interface implementation.
	 * @param Dependency_Checker_Service      $dependency_checker Dependency checker service instance.
	 * @since 1.0.0
	 */
	public function __construct(
		Plugin_Dependency_Api_Interface $required_plugin_api,
		Dependency_Checker_Service $dependency_checker
	) {
		$this->required_plugin_api = $required_plugin_api;
		$this->dependency_checker  = $dependency_checker;
	}

	/**
	 * Initialize the Dependency Manager.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wp_ajax_install_plugin', 'wp_ajax_install_plugin' );
		add_action( 'wp_ajax_wp_ajax_activate_plugin', 'wp_ajax_activate_plugin' );
	}
	/**
	 * Enqueue necessary scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ): void {
		if ( 'toplevel_page_cod-funnel-setup' !== $hook ) {
			return;
		}

		wp_localize_script(
			'cod-funnel-wizard',
			'codFunnelDependencyManager',
			array(
				'nonce'         => wp_create_nonce( 'updates' ),
				'plugins'       => $this->dependency_checker->get_plugins_status(),
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'dashboard_url' => admin_url( 'admin.php?page=cod-funnel-dashboard' ),
			)
		);
	}
}
