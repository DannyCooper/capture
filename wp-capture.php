<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/dannycooper/capture
 * @since             1.0.0
 * @package           Capture
 *
 * @wordpress-plugin
 * Plugin Name:       Capture
 * Plugin URI:        https://wpcature.com
 * Description:       A WordPress plugin for capturing email subscriptions with EMS integration and local storage options.
 * Version:           1.0.0
 * Author:            DannyCooper
 * Author URI:        https://wpcature.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       capture
 * Domain Path:       /languages
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'CAPTURE_VERSION', '1.0.0' );
define( 'CAPTURE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAPTURE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_capture() {
	// Load database class.
	require_once CAPTURE_PLUGIN_DIR . 'includes/class-database.php';

	// Create subscribers table.
	Database::create_subscribers_table();

	// Set default options if needed.
	$default_options = array(
		'ems_connections'              => array(),
		'enable_local_storage'         => true,
		'default_success_message'      => \__( 'Thank you for subscribing!', 'capture' ),
		'notify_admin_new_subscriber'  => false,
		'send_subscriber_confirmation' => false,
		'subscriber_email_subject'     => \__( 'Welcome! Subscription Confirmed', 'capture' ),
		'enable_unsubscribe_links'     => true,
		'data_retention_enabled'       => false,
		'data_retention_period'        => 365,
	);

	// Only add options if they don't exist.
	if ( ! \get_option( 'capture_options' ) ) {
		\add_option( 'capture_options', $default_options );
	}
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_capture' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_capture() {
	// Unschedule data retention cleanup.
	\wp_clear_scheduled_hook( 'capture_data_retention_cleanup' );

	// Keep the options and data in case the plugin is reactivated.
}
\register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate_capture' );

/**
 * Begins execution of the plugin.
 */
function run_capture() {
	// Load plugin dependencies.
	require_once CAPTURE_PLUGIN_DIR . 'includes/class-core.php';

	// Initialize the plugin.
	$plugin = new Core();
	// Make the instance globally accessible.
	$GLOBALS['capture_instance'] = $plugin;
}
run_capture();
