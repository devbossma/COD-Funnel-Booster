<?php
/**
 * Setup Wizard for plugin dependencies installation
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Managers;

use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;
use DevBossMa\CODFunnelBooster\Managers\Config_Manager;
use DevBossMa\CODFunnelBooster\Managers\Dependency_Manager;


/**
 * The Setup Wizard class.
 */
class Setup_Wizard {

	/**
	 * The "Dependency_Checker_Service" variable.
	 *
	 * @var Dependency_Checker_Service
	 */
	private Dependency_Manager $dependency_manager;

	/**
	 * The "Config_Manager" variable.
	 *
	 * @var Config_Manager
	 */
	private Config_Manager $config_manager;

	/**
	 * Setup Constractor.
	 *
	 * @param Dependency_Manager $dependency_manager "Dependency_Manager" instance, that will handel the required Dependencies.
	 * @param Config_Manager     $config_manager     "Config_Manager" instance, that will handel the required Configurations.
	 */
	public function __construct( Dependency_Manager $dependency_manager, Config_Manager $config_manager ) {

			$this->dependency_manager = $dependency_manager;
			$this->config_manager     = $config_manager;
	}


	/**
	 * Initialize the setup wizard
	 */
	public function init(): void {
		add_action(
			'admin_init',
			function () {
				if ( isset( $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					&& 'cod-funnel-setup' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					remove_all_actions( 'admin_notices' );
					remove_all_actions( 'all_admin_notices' );
				}
			}
		);
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Initialize managers.
		$this->dependency_manager->init();
		$this->config_manager->init();
	}

	/**
	 * Add admin menu page for the setup wizard
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'Setup Wizard', 'cod-funnel-booster' ),
			__( 'Setup Wizard', 'cod-funnel-booster' ),
			'manage_options',
			'cod-funnel-setup',
			array( $this, 'render_wizard_page' ),
			'dashicons-admin-generic',
			20
		);
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

		wp_enqueue_style(
			'cod-funnel-wizard',
			plugins_url( 'assets/build/css/setup-wizard/setup-wizard.css', CFB_PLUGIN_FILE ),
			array(),
			CFB_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'cod-funnel-wizard',
			plugins_url( 'assets/build/js/setup-wizard/setup-wizard.js', CFB_PLUGIN_FILE ),
			array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ),
			CFB_PLUGIN_VERSION,
			true
		);
	}

	/**  * Render the wizard page container  */
	public function render_wizard_page(): void {
		echo '<div id="cod-funnel-wizard-root"></div>';
	}
}
