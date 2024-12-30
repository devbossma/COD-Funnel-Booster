<?php
/**
 * Main Plugin Class "CODFunnelBooster"
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster;

use DevBossMa\CODFunnelBooster\Managers\Dependencies_Checker;
use DevBossMa\CODFunnelBooster\Managers\Setup_Wizard;
use DevBossMa\CODFunnelBooster\Admin\Dashboard\CFB_Admin_Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class CODFunnelBooster's defenition
 */
final class COD_Funnel_Booster {


	/**
	 * Plugin version  variable
	 *
	 * @var string
	 */
	protected string $plugin_version = '1.0.0';

	/**
	 *  Variable Holde the  single instance of the "CODFunnelBooster" class.
	 *
	 * @var COD_Funnel_Booster
	 */
	protected static $instance = null;




	/**
	 * Function return the uniq instance of the main Plugin Class "CODFunnelBooster".
	 *
	 * @return COD_Funnel_Booster
	 */
	public static function instance(): COD_Funnel_Booster {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Undocumented function
	 */
	private function __construct() {
		add_filter( 'plugin_action_links_' . CFB_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Define constants() function that define the constants of this plugin.
	 *
	 * @return void
	 */
	private function define_constants() {
		// Define the rest of the plugin constants.
		define( 'CFB_PLUGIN_VERSION', $this->plugin_version );
		define( 'CFB_PLUGIN_ASSETS_DIR', CFB_PLUGIN_DIR . 'assets/' );
		define( 'CFB_PLUGIN_LANGUAGE_DIR', CFB_PLUGIN_DIR . 'languages/' );
	}

	/**
	 * Initialize plugin hooks
	 */
	protected function init_hooks(): void {
		$container = cfb_container();

		// Initialize Setup Wizard immediately - no WooCommerce dependency.
		$setup_wizard = $container::resolve( Setup_Wizard::class );
		$setup_wizard->init();

		$dashboard = new CFB_Admin_Dashboard();
		$dashboard->init();

		add_action( 'admin_init', array( $this, 'activate_plugin_redirect' ) );
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'cod-funnel-booster',
			false,
			CFB_PLUGIN_LANGUAGE_DIR
		);
	}

	/**
	 * Initialize plugin components
	 */
	private function init_components(): void {
		// Initialize option managers.
		General_Settings_Manager::get_option();
		Funnel_Settings_Manager::get_option();
		Checkout_Settings_Manager::get_option();
		Integration_Settings_Manager::get_option();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function activate() {
		add_option( 'cod_funnel_do_activation_redirect', true );
		add_option( 'cod_funnel_setup_redirect_complated', false );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links default WordPress links.
	 * @return array
	 */
	public function add_plugin_action_links( array $links ): array {
		$custom_links = array(
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=cod-funnel-setup' ),
				esc_html__( 'Settings', 'cod-funnel-booster' )
			),
		);

		return array_merge( $custom_links, $links );
	}

	/**
	 * The deactivate function.
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @throws \Exception Unserialization singleton exception.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}


	/**
	 * If the required plugins are not installed and activated this function will
	 * redirect thhe user to the setup's step where he should install and activate them.
	 *
	 * @return void
	 */
	public function activate_plugin_redirect(): void {
		// Check if the required plugins was just activated.
		if ( get_option( 'cod_funnel_do_activation_redirect', false ) ) {
			// Delete the redirect option to prevent infinite redirects.
			delete_option( 'cod_funnel_do_activation_redirect' );

			// Perform the redirect.
			wp_safe_redirect( admin_url( 'admin.php?page=cod-funnel-setup' ) );
			exit();
		}
	}

	/**
	 * Enqueue scripts for the setup wizard.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'cod-funnel-setup-wizard',
			plugin_dir_url( CFB_PLUGIN_FILE ) . 'assets/build/js/setup-wizard/setup-wizard.js',
			array( 'wp-element', 'react', 'react-dom' ),
			CFB_PLUGIN_VERSION,
			true
		);

		// Add this line to set script as module.
		add_filter(
			'script_loader_tag',
			function ( $tag, $handle, $src ) {
				if ( 'cod-funnel-setup-wizard' === $handle ) {
					return '<script type="module" src="' . esc_url( $src ) . '"></script>';
				}
				return $tag;
			},
			10,
			3
		);
	}
}
