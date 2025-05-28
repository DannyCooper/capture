<?php
/**
 * Test script for WP Capture subscriber email functionality
 * 
 * This file can be run to test email template processing
 * Run from WordPress admin or via WP-CLI
 */

// Ensure this is run in WordPress context
if ( ! defined( 'ABSPATH' ) ) {
	echo "This script must be run within WordPress context.\n";
	exit;
}

// Test data
$test_subscriber_data = array(
	'email' => 'test@example.com',
	'name' => 'John Doe',
	'form_id' => 'test-form-123',
	'user_agent' => 'Test User Agent',
	'status' => 'active',
	'source_url' => 'https://example.com/test-page',
);

$test_subscriber_id = 999; // Fake ID for testing

echo "=== WP Capture Email Template Test ===\n\n";

// Test template processing
$test_template = "Hello {name},\n\nThank you for subscribing!\n\nDetails:\n• Email: {email}\n• Date: {date}\n• Site: {site_name}\n\nUnsubscribe: {unsubscribe_url}";

echo "Template:\n" . $test_template . "\n\n";

// Process template (function should exist after plugin loads)
if ( function_exists( 'wp_capture_process_email_template' ) ) {
	$processed = wp_capture_process_email_template( $test_template, $test_subscriber_data, $test_subscriber_id );
	echo "Processed Result:\n" . $processed . "\n\n";
	echo "✅ Template processing successful!\n";
} else {
	echo "❌ wp_capture_process_email_template function not found. Is the plugin active?\n";
}

// Test settings
$options = get_option( 'wp_capture_options', array() );
echo "\nCurrent Settings:\n";
echo "- Send confirmation emails: " . ( isset( $options['send_subscriber_confirmation'] ) && $options['send_subscriber_confirmation'] ? 'Yes' : 'No' ) . "\n";
echo "- From name: " . ( isset( $options['subscriber_email_from_name'] ) ? $options['subscriber_email_from_name'] : 'Not set' ) . "\n";
echo "- From email: " . ( isset( $options['subscriber_email_from_email'] ) ? $options['subscriber_email_from_email'] : 'Not set' ) . "\n";
echo "- Subject: " . ( isset( $options['subscriber_email_subject'] ) ? $options['subscriber_email_subject'] : 'Not set' ) . "\n";

echo "\n=== Test Complete ===\n"; 