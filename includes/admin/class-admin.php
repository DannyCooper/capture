<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Capture
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Require handler classes.
require_once plugin_dir_path( __FILE__ ) . 'class-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-connections.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-analytics.php';
require_once plugin_dir_path( __FILE__ ) . 'class-admin-subscribers.php';

/**
 * Admin class.
 */
class Admin {
	/**
	 * The main plugin instance.
	 *
	 * @var Core
	 */
	private $plugin;

	/**
	 * Store hooks for plugin admin pages.
	 *
	 * @var array
	 */
	private $admin_page_hooks = array();

	/**
	 * Settings page handler.
	 *
	 * @var Admin_Settings
	 */
	private $settings_handler;

	/**
	 * Connections page handler.
	 *
	 * @var Admin_Connections
	 */
	private $connections_handler;

	/**
	 * Analytics page handler.
	 *
	 * @var Admin_Analytics
	 */
	private $analytics_handler;

	/**
	 * Subscribers page handler.
	 *
	 * @var Admin_Subscribers
	 */
	private $subscribers_handler;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param Core $plugin The main plugin instance.
	 */
	public function __construct( Core $plugin ) {
		$this->plugin = $plugin;

		$this->settings_handler    = new Admin_Settings();
		$this->connections_handler = new Admin_Connections( $this->plugin );
		$this->analytics_handler   = new Admin_Analytics();
		$this->subscribers_handler = new Admin_Subscribers();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'encryption_status_notice' ) );
	}

	/**
	 * Enqueue admin-specific JavaScript and CSS.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Ensure admin_page_hooks is populated if needed by other logic.
		// For now, we collect all unique hooks from the menu registration.
		if ( ! in_array( $hook_suffix, array_unique( $this->admin_page_hooks ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'capture-admin',
			CAPTURE_PLUGIN_URL . 'assets/js/capture-admin.js',
			array( 'jquery' ),
			CAPTURE_VERSION,
			true
		);

		$available_providers = $this->plugin->get_registered_services();

		wp_localize_script(
			'capture-admin',
			'captureAdmin',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'capture_admin_nonce' ),
				'providers' => $available_providers,
				'text'      => array(
					'removeConnection' => __( 'Are you sure you want to remove this connection?', 'capture' ),
					'apiKey'           => __( 'API Key', 'capture' ),
					'connectionName'   => __( 'Connection Name (Optional)', 'capture' ),
					'remove'           => __( 'Remove', 'capture' ),
					'provider'         => __( 'Provider', 'capture' ),
					'selectProvider'   => __( 'Please select a provider.', 'capture' ),
					'apiKeyRequired'   => __( 'Please enter an API key.', 'capture' ),
					'testing'          => __( 'Testing connection...', 'capture' ),
					'errorOccurred'    => __( 'An error occurred. Please try again.', 'capture' ),
					'saved'            => __( 'Connection saved successfully!', 'capture' ),
					'removing'         => __( 'Removing connection...', 'capture' ),
					'removedSuccess'   => __( 'Connection removed successfully.', 'capture' ),
					'removeError'      => __( 'Could not remove connection. Please try again.', 'capture' ),
					'updating'         => __( 'Updating connection...', 'capture' ),
					'updatedSuccess'   => __( 'Connection updated successfully!', 'capture' ),
				),
			)
		);

		wp_enqueue_style( 'capture-admin-css', CAPTURE_PLUGIN_URL . 'assets/css/admin.css', array(), CAPTURE_VERSION );
	}

	/**
	 * Add menu items to the admin menu.
	 */
	public function add_plugin_admin_menu() {
		$this->admin_page_hooks[] = add_menu_page(
			__( 'Capture', 'capture' ),
			__( 'Capture', 'capture' ),
			'manage_options',
			'capture',
			array( $this->connections_handler, 'display_page' ), // Default to Connections page.
			'dashicons-email-alt',
			30
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Connections', 'capture' ),
			__( 'Connections', 'capture' ),
			'manage_options',
			'capture', // Slug same as parent for default.
			array( $this->connections_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Subscribers', 'capture' ),
			__( 'Subscribers', 'capture' ),
			'manage_options',
			'capture-subscribers',
			array( $this->subscribers_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Settings', 'capture' ),
			__( 'Settings', 'capture' ),
			'manage_options',
			'capture-settings',
			array( $this->settings_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Analytics', 'capture' ),
			__( 'Analytics', 'capture' ),
			'manage_options',
			'capture-analytics',
			array( $this->analytics_handler, 'display_page' )
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param array $links The current array of action links.
	 * @return array The modified array of action links.
	 */
	public function add_action_links( $links ) {
		$settings_link   = array(
			'<a href="' . admin_url( 'admin.php?page=capture' ) . '">' . __( 'Connections', 'capture' ) . '</a>',
		);
		$settings_link[] = '<a href="' . admin_url( 'admin.php?page=capture-settings' ) . '">' . __( 'Settings', 'capture' ) . '</a>';
		return array_merge( $settings_link, $links );
	}

	/**
	 * Getter for the admin page hooks array if needed by handlers.
	 *
	 * @return array
	 */
	public function get_admin_page_hooks() {
		return $this->admin_page_hooks;
	}

	/**
	 * Getter for the plugin instance.
	 *
	 * @return Core
	 */
	public function get_plugin() {
		return $this->plugin;
	}

	/**
	 * Show admin notice about encryption status.
	 */
	public function encryption_status_notice() {
		// Only show on plugin admin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'capture' ) === false ) {
			return;
		}

		if ( ! \Capture\Encryption::is_properly_configured() ) {
			$notice_message = __(
				'WP Capture: For enhanced security, please define CAPTURE_API_ENCRYPTION_KEY and CAPTURE_API_ENCRYPTION_SALT in your wp-config.php file.',
				'capture'
			);

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html( $notice_message )
			);
		}
	}
}
