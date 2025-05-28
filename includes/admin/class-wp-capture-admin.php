<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require handler classes
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-connections.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-analytics.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-subscribers.php';

class WP_Capture_Admin {
	/**
	 * The main plugin instance.
	 *
	 * @var WP_Capture
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
	 * @var WP_Capture_Admin_Settings
	 */
	private $settings_handler;

	/**
	 * Connections page handler.
	 *
	 * @var WP_Capture_Admin_Connections
	 */
	private $connections_handler;

	/**
	 * Analytics page handler.
	 *
	 * @var WP_Capture_Admin_Analytics
	 */
	private $analytics_handler;

	/**
	 * Subscribers page handler.
	 *
	 * @var WP_Capture_Admin_Subscribers
	 */
	private $subscribers_handler;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param WP_Capture $plugin The main plugin instance.
	 */
	public function __construct( WP_Capture $plugin ) {
		$this->plugin = $plugin;

		// Instantiate handlers
		$this->settings_handler    = new WP_Capture_Admin_Settings( $this->plugin, $this );
		$this->connections_handler = new WP_Capture_Admin_Connections( $this->plugin, $this );
		$this->analytics_handler   = new WP_Capture_Admin_Analytics( $this->plugin, $this );
		$this->subscribers_handler = new WP_Capture_Admin_Subscribers( $this->plugin, $this );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		
		add_action( 'admin_notices', array( $this, 'encryption_status_notice' ) );
	}

	/**
	 * Enqueue admin-specific JavaScript and CSS.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Ensure admin_page_hooks is populated if needed by other logic
		// For now, we collect all unique hooks from the menu registration.
		if ( ! in_array( $hook_suffix, array_unique( $this->admin_page_hooks ) ) ) {
			return;
		}

		wp_enqueue_script(
			'wp-capture-admin',
			WP_CAPTURE_PLUGIN_URL . 'assets/js/wp-capture-admin.js',
			array( 'jquery' ),
			WP_CAPTURE_VERSION,
			true
		);

		$available_providers = $this->plugin->get_registered_services();

		wp_localize_script(
			'wp-capture-admin',
			'wpCaptureAdmin',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'wp_capture_admin_nonce' ),
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

		wp_enqueue_style( 'wp-capture-admin-css', WP_CAPTURE_PLUGIN_URL . 'assets/css/wp-capture-admin.css', array(), WP_CAPTURE_VERSION );
	}

	/**
	 * Add menu items to the admin menu.
	 */
	public function add_plugin_admin_menu() {
		$this->admin_page_hooks[] = add_menu_page(
			__( 'WP Capture', 'capture' ),
			__( 'WP Capture', 'capture' ),
			'manage_options',
			'capture',
			array( $this->connections_handler, 'display_page' ), // Default to Connections page
			'dashicons-email-alt',
			30
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Connections', 'capture' ),
			__( 'Connections', 'capture' ),
			'manage_options',
			'capture', // Slug same as parent for default
			array( $this->connections_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Subscribers', 'capture' ),
			__( 'Subscribers', 'capture' ),
			'manage_options',
			'wp-capture-subscribers',
			array( $this->subscribers_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Settings', 'capture' ),
			__( 'Settings', 'capture' ),
			'manage_options',
			'wp-capture-settings',
			array( $this->settings_handler, 'display_page' )
		);

		$this->admin_page_hooks[] = add_submenu_page(
			'capture',
			__( 'Analytics', 'capture' ),
			__( 'Analytics', 'capture' ),
			'manage_options',
			'wp-capture-analytics',
			array( $this->analytics_handler, 'display_page' )
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {
		$settings_link   = array(
			'<a href="' . admin_url( 'admin.php?page=wp-capture' ) . '">' . __( 'Connections', 'capture' ) . '</a>',
		);
		$settings_link[] = '<a href="' . admin_url( 'admin.php?page=wp-capture-settings' ) . '">' . __( 'Settings', 'capture' ) . '</a>';
		return array_merge( $settings_link, $links );
	}

	/**
	 * Getter for the admin page hooks array if needed by handlers.
	 */
	public function get_admin_page_hooks() {
		return $this->admin_page_hooks;
	}

	/**
	 * Getter for the plugin instance if needed by handlers (though passed in constructor).
	 */
	public function get_plugin() {
		return $this->plugin;
	}

	/**
	 * Displays an admin notice regarding the encryption setup status.
	 *
	 * This notice checks if OpenSSL is available and if custom encryption keys
	 * are defined. It guides the user to set them in wp-config.php if they are not.
	 *
	 * @since 1.0.0 // Or your current version
	 */
	public function encryption_status_notice() {
		// Ensure Encryption class is available
		if ( ! class_exists( 'Encryption' ) ) {
			// This should not happen if dependencies are loaded correctly
			echo '<div class="notice notice-error"><p>' . esc_html__( 'WP Capture: Encryption class not found. Please check plugin integrity.', 'capture' ) . '</p></div>';
			return;
		}

		$notice_message         = '';
		$is_securely_configured = Encryption::is_properly_configured();
		$openssl_loaded         = extension_loaded( 'openssl' );

		if ( ! $openssl_loaded ) {
			$notice_message .= '<p><strong>' . esc_html__( 'OpenSSL Not Available:', 'capture' ) . '</strong> ' .
								esc_html__( 'The OpenSSL PHP extension is not loaded on your server. API keys will be stored and handled in plaintext. This is a significant security risk if you handle sensitive API keys.', 'capture' ) .
								'</p>';
		}

		if ( ! $is_securely_configured ) {
			if ( $openssl_loaded ) { // Only show this part if OpenSSL is loaded but keys are fallback
				$notice_message .= '<p><strong>' . esc_html__( 'Insecure Encryption Key/Salt:', 'capture' ) . '</strong> ' .
									sprintf(
										/* translators: %1$s: CAPTURE_API_ENCRYPTION_KEY, %2$s: CAPTURE_API_ENCRYPTION_SALT, %3$s: wp-config.php */
										esc_html__( 'WP Capture is using fallback encryption keys/salts. For enhanced security, please define %1$s and %2$s in your %3$s file. Until then, encryption may be less secure.', 'capture' ),
										'<code>CAPTURE_API_ENCRYPTION_KEY</code>',
										'<code>CAPTURE_API_ENCRYPTION_SALT</code>',
										'<code>wp-config.php</code>'
									) . '</p>';
			} else { // If OpenSSL is not loaded, the key configuration is secondary to plaintext storage.
				$notice_message .= '<p><strong>' . esc_html__( 'Encryption Configuration:', 'capture' ) . '</strong> ' .
									sprintf(
										/* translators: %1$s: CAPTURE_API_ENCRYPTION_KEY, %2$s: CAPTURE_API_ENCRYPTION_SALT, %3$s: wp-config.php */
										esc_html__( 'Additionally, for when OpenSSL is available, ensure you define %1$s and %2$s in your %3$s file for secure encryption.', 'capture' ),
										'<code>CAPTURE_API_ENCRYPTION_KEY</code>',
										'<code>CAPTURE_API_ENCRYPTION_SALT</code>',
										'<code>wp-config.php</code>'
									) . '</p>';
			}
		} elseif ( $openssl_loaded && $is_securely_configured ) {
			// Optionally, show a success message or nothing if everything is fine.
			// For now, we'll only show notices if there's an issue.
			// echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'WP Capture encryption is configured securely.', 'capture' ) . '</p></div>';
			return; // Everything is good, no notice needed unless you uncomment the above.
		}

		if ( ! empty( $notice_message ) ) {
			echo '<div class="notice notice-warning is-dismissible">' . $notice_message . '</div>';
		}
	}
}
