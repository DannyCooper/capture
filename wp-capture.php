<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/yourusername/wp-capture
 * @since             1.0.0
 * @package           WP_Capture
 *
 * @wordpress-plugin
 * Plugin Name:       WP Capture
 * Plugin URI:        https://github.com/yourusername/wp-capture
 * Description:       A WordPress plugin for capturing email subscriptions with EMS integration and local storage options.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       capture
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'WP_CAPTURE_VERSION', '1.0.0' );
define( 'WP_CAPTURE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_CAPTURE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_capture() {
	// Load database class.
	require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-database.php';

	// Create subscribers table.
	WP_Capture_Database::create_subscribers_table();

	// Set default options if needed.
	$default_options = array(
		'ems_connections' => array(),
		'enable_local_storage' => true,
		'default_success_message' => __( 'Thank you for subscribing!', 'capture' ),
		'notify_admin_new_subscriber' => false,
		'send_subscriber_confirmation' => false,
		'subscriber_email_subject' => __( 'Welcome! Subscription Confirmed', 'capture' ),
		'enable_unsubscribe_links' => true,
		'data_retention_enabled' => false,
		'data_retention_period' => 365,
	);

	// Only add options if they don't exist.
	if ( ! get_option( 'wp_capture_options' ) ) {
		add_option( 'wp_capture_options', $default_options );
	}
}
register_activation_hook( __FILE__, 'activate_wp_capture' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_capture() {
	// Unschedule data retention cleanup.
	wp_clear_scheduled_hook( 'wp_capture_data_retention_cleanup' );

	// Keep the options and data in case the plugin is reactivated.
}
register_deactivation_hook( __FILE__, 'deactivate_wp_capture' );

/**
 * Begins execution of the plugin.
 */
function run_wp_capture() {
	// Load plugin dependencies.
	require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture.php';

	// Initialize the plugin.
	$plugin = new WP_Capture();
	// Make the instance globally accessible.
	$GLOBALS['wp_capture_instance'] = $plugin;
}
run_wp_capture();
