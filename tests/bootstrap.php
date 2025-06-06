<?php
/**
 * PHPUnit bootstrap file for Capture plugin tests.
 *
 * @package Capture
 */

// Define WordPress constants that the plugin expects.
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Define plugin constants.
define( 'CAPTURE_VERSION', '1.0.0' );
define( 'CAPTURE_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'CAPTURE_PLUGIN_URL', 'http://example.com/wp-content/plugins/capture/' );

// Mock WordPress functions that the Encryption class might use.
if ( ! function_exists( 'error_log' ) ) {
	/**
	 * Mock error_log function for testing.
	 *
	 * @param string $message The error message.
	 * @return bool Always returns true.
	 */
	function error_log( $message ) {
		// In tests, we might want to capture these or ignore them.
		return true;
	}
}

// Load the plugin's main classes.
require_once CAPTURE_PLUGIN_DIR . 'includes/class-encryption.php';

// Set up test environment.
echo "Bootstrap complete. Running tests...\n"; 