<?php
/**
 * Test email template processing functionality
 *
 * @package Capture
 */

// Try to load WordPress if not already loaded
if ( ! function_exists( 'get_option' ) ) {
	// Look for wp-config.php going up directories
	$wp_config_path = null;
	$current_dir = dirname( __FILE__ );
	
	for ( $i = 0; $i < 10; $i++ ) {
		if ( file_exists( $current_dir . '/wp-config.php' ) ) {
			$wp_config_path = $current_dir . '/wp-config.php';
			break;
		}
		$current_dir = dirname( $current_dir );
	}
	
	if ( $wp_config_path ) {
		// Load WordPress
		define( 'WP_USE_THEMES', false );
		require_once $wp_config_path;
		require_once dirname( $wp_config_path ) . '/wp-load.php';
	}
}

require_once 'wp-capture.php';

// Test data.
$test_subscriber_data = array(
	'name'  => 'John Doe',
	'email' => 'john@example.com',
);
$test_subscriber_id   = 123;

// Test template.
$test_template = "Hello {name},\n\nThank you for subscribing!\n\nUnsubscribe: {unsubscribe_url}";

// Helper function for HTML escaping
function safe_html( $text ) {
	if ( function_exists( 'esc_html' ) ) {
		return esc_html( $text );
	}
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

echo '<h1>Test Email Template</h1>';
echo '<h2>Original Template:</h2>';
echo '<pre>' . safe_html( $test_template ) . '</pre>';

// Check if function exists before calling
if ( function_exists( '\Capture\process_email_template' ) ) {
	// Process template.
	$processed = \Capture\process_email_template( $test_template, $test_subscriber_data, $test_subscriber_id );

	echo '<h2>Processed Template:</h2>';
	echo '<pre>' . safe_html( $processed ) . '</pre>';
} else {
	echo '<h2>Error:</h2>';
	echo '<p>process_email_template function not found. Make sure WordPress and the Capture plugin are properly loaded.</p>';
}

// Test current settings if WordPress is loaded
if ( function_exists( 'get_option' ) ) {
	$options = get_option( 'capture_options', array() );
	echo '<h2>Current Email Settings:</h2>';
	echo '<p>From Name: ' . safe_html( isset( $options['subscriber_email_from_name'] ) ? $options['subscriber_email_from_name'] : 'Not set' ) . '</p>';
	echo '<p>From Email: ' . safe_html( isset( $options['subscriber_email_from_email'] ) ? $options['subscriber_email_from_email'] : 'Not set' ) . '</p>';
	echo '<p>Subject: ' . safe_html( isset( $options['subscriber_email_subject'] ) ? $options['subscriber_email_subject'] : 'Not set' ) . '</p>';
} else {
	echo '<h2>WordPress Not Loaded</h2>';
	echo '<p>WordPress functions are not available. Cannot retrieve plugin settings.</p>';
}

