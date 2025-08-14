<?php
/**
 * Plugin Name:       Capture
 * Plugin URI:        https://wpcature.com
 * Description:       A WordPress plugin for capturing email subscriptions with EMS integration and local storage options.
 * Version:           1.0.2
 * Author:            DannyCooper
 * Author URI:        https://dannycooper.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       capture
 * Domain Path:       /languages
 * 
 * @package Capture
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'CAPTURE_VERSION', '1.0.2' );
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
		'notify_admin_new_subscriber'  => true,
		'send_subscriber_confirmation' => true,
		'subscriber_email_subject'     => \__( 'Welcome! Subscription Confirmed', 'capture' ),
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
