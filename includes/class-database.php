<?php
/**
 * Database management for WP Capture plugin.
 *
 * This file contains the Database class which handles all database operations
 * for the WP Capture plugin including table creation and management.
 *
 * @since      1.0.0
 * @package    Capture
 * @subpackage Capture/includes
 */

namespace Capture;

/**
 * Database management for WP Capture plugin.
 *
 * @since      1.0.0
 * @package    Capture
 */
class Database {

	/**
	 * Create the subscribers table.
	 *
	 * @since 1.0.0
	 */
	public static function create_subscribers_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'capture_subscribers';

		$charset_collate = $wpdb->get_charset_collate();

		// Use dbDelta-friendly SQL syntax (separate PRIMARY KEY definition).
		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(255) NOT NULL,
			name VARCHAR(255) DEFAULT NULL,
			form_id VARCHAR(255) DEFAULT NULL,
			date_subscribed DATETIME DEFAULT CURRENT_TIMESTAMP,
			status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
			source_url TEXT DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY unique_email_form (email, form_id),
			KEY idx_email (email),
			KEY idx_form_id (form_id),
			KEY idx_date_subscribed (date_subscribed),
			KEY idx_status (status)
		) $charset_collate;";

		require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
		
		// Capture any output from dbDelta to prevent it from being sent to browser.
		ob_start();
		\dbDelta( $sql );
		$output = ob_get_clean();
		
		// Log any dbDelta output for debugging.
		if ( ! empty( $output ) ) {
			error_log( 'WP Capture dbDelta output: ' . $output );
		}

		// Store the database version for future migrations.
		\add_option( 'capture_db_version', '1.1.0' );
	}

	/**
	 * Check if the subscribers table exists.
	 *
	 * @since 1.0.0
	 * @return bool True if table exists, false otherwise.
	 */
	public static function subscribers_table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'capture_subscribers';
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
	}

	/**
	 * Get the subscribers table name with proper prefix.
	 *
	 * @since 1.0.0
	 * @return string The full table name.
	 */
	public static function get_subscribers_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'capture_subscribers';
	}

	/**
	 * Drop the subscribers table (for uninstall).
	 *
	 * @since 1.0.0
	 */
	public static function drop_subscribers_table() {
		global $wpdb;
		$table_name = self::get_subscribers_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
		\delete_option( 'capture_db_version' );
	}
}
