<?php
/**
 * Manage (Install & Activate) the requiered Plugins for COD Funnel Booster Plugin.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Managers;

use WP_REST_Response;
use WP_REST_Server;
use DevBossMa\CODFunnelBooster\Services\Dependency\Dependency_Checker_Service;
use DevBossMa\CODFunnelBooster\Interface\Plugin_Dependency_Api_Interface;
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
		add_action( 'wp_ajax_install_required_plugin', array( $this, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_activate_required_plugin', array( $this, 'ajax_activate_plugin' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
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
				'nonce'         => wp_create_nonce( 'cod_funnel_setup' ),
				'plugins'       => $this->dependency_checker->get_plugins_status(),
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'restUrl'       => get_rest_url( null, 'cod-funnel-booster/v1' ),
				'dashboard_url' => admin_url( 'admin.php?page=cod-funnel-dashboard' ),
			)
		);
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'cod-funnel-booster/v1',
			'/plugin-status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_plugin_status' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			)
		);
	}


	/**
	 * Get plugin status
	 *
	 * @return WP_REST_Response
	 */
	public function get_plugin_status(): WP_REST_Response {

		$status = $this->$dependency_checker->get_plugins_status();

		return rest_ensure_response( $status );
	}


	/**
	 * Check if user has admin permissions
	 *
	 * @return bool
	 */
	public function check_admin_permissions(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle plugin installation via AJAX.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function ajax_install_plugin(): void {
		check_ajax_referer( 'cod_funnel_setup', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$plugin_slug = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		if ( ! isset( $this->dependency_checker->get_plugins_status()[ $plugin_slug ] ) ) {
			wp_send_json_error( 'Invalid plugin' );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin_slug,
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'homepage'          => false,
					'donate_link'       => false,
					'author_profile'    => false,
					'author'            => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		wp_send_json_success();
	}

	/**
	 * Handle plugin activation via AJAX.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function ajax_activate_plugin(): void {
		check_ajax_referer( 'cod_funnel_setup', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$plugin_file = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		if ( ! isset( $this->dependency_checker->get_plugins_status()[ $plugin_file ] ) ) {
			wp_send_json_error( 'Invalid plugin' );
		}

		$result = activate_plugin( $this->required_plugin_api->get_plugin_info( $plugin_file )['file'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success();
	}
}
