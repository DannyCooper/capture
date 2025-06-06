<?php
/**
 * Handles data retention functionality for WP Capture.
 *
 * @package    Capture
 * @subpackage Capture/includes
 */

namespace Capture;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.

}

/**
 * Class Data_Retention
 *
 * Manages automatic deletion of subscriber data based on retention settings.
 */
class Data_Retention {

	/**
	 * Initialize the data retention functionality.
	 */
	public function __construct() {
		add_action( 'capture_data_retention_cleanup', array( $this, 'cleanup_expired_data' ) );
		add_action( 'init', array( $this, 'schedule_cleanup_event' ) );

		// Hook into settings save to reschedule if retention period changes.
		add_action( 'update_option_capture_options', array( $this, 'handle_settings_update' ), 10, 2 );
	}

	/**
	 * Schedule the cleanup event if not already scheduled.
	 */
	public function schedule_cleanup_event() {
		if ( ! wp_next_scheduled( 'capture_data_retention_cleanup' ) ) {
			// Schedule daily cleanup at 3 AM.
			wp_schedule_event( strtotime( 'tomorrow 3:00 AM' ), 'daily', 'capture_data_retention_cleanup' );
		}
	}

	/**
	 * Handle settings update to reschedule cleanup if needed.
	 *
	 * @param array $old_value Old option value.
	 * @param array $new_value New option value.
	 */
	public function handle_settings_update( $old_value, $new_value ) {
		$old_retention = isset( $old_value['data_retention_days'] ) ? $old_value['data_retention_days'] : 0;
		$new_retention = isset( $new_value['data_retention_days'] ) ? $new_value['data_retention_days'] : 0;

		// If retention setting changed, clear and reschedule.
		if ( $old_retention !== $new_retention ) {
			wp_clear_scheduled_hook( 'capture_data_retention_cleanup' );
			$this->schedule_cleanup_event();
		}
	}

	/**
	 * Clean up expired subscriber data based on retention settings.
	 */
	public function cleanup_expired_data() {
		$options        = get_option( 'capture_options', array() );
		$retention_days = isset( $options['data_retention_days'] ) ? intval( $options['data_retention_days'] ) : 0;

		// If retention is 0, keep data indefinitely.
		if ( $retention_days <= 0 ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'capture_subscribers';

		// Calculate cutoff date.
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		// Delete subscribers older than retention period using prepared query.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is properly escaped, data is prepared
		$deleted_count = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . esc_sql( $table_name ) . '` WHERE date_subscribed < %s AND status = %s',
				$cutoff_date,
				'unsubscribed'
			)
		);

		// Handle database error.
		if ( false === $deleted_count ) {
			error_log( 'WP Capture Data Retention: Database error during cleanup - ' . $wpdb->last_error );
			return;
		}

		// Log cleanup activity.
		if ( $deleted_count > 0 ) {
			error_log( sprintf( 'WP Capture Data Retention: Deleted %d subscriber records older than %d days.', $deleted_count, $retention_days ) );
		}

		// Optional: Send admin notification about cleanup.
		$this->maybe_send_cleanup_notification( $deleted_count, $retention_days );
	}

	/**
	 * Send admin notification about data cleanup if configured.
	 *
	 * @param int $deleted_count Number of records deleted.
	 * @param int $retention_days Retention period in days.
	 */
	private function maybe_send_cleanup_notification( $deleted_count, $retention_days ) {
		// Only send notification if records were deleted and admin notifications are enabled.
		if ( $deleted_count <= 0 ) {
			return;
		}

		$options = get_option( 'capture_options', array() );

		$admin_email = isset( $options['admin_notification_email'] ) ? $options['admin_notification_email'] : get_option( 'admin_email' );

		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return;
		}

		$site_name = get_bloginfo( 'name' );
		/* translators: %s: Site name */
		$subject = sprintf( __( '[%s] Subscriber Data Cleanup Report', 'capture' ), $site_name );

		/* translators: 1: Number of deleted records, 2: Retention period in days, 3: Current date */
		$message = sprintf(
			__( "Automated data retention cleanup has been completed.\n\nDeleted Records: %1\$d\nRetention Period: %2\$d days\nDate: %3\$s\n\nThis is an automated process based on your data retention settings.", 'capture' ),
			$deleted_count,
			$retention_days,
			current_time( 'mysql' )
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		wp_mail( $admin_email, $subject, $message, $headers );
	}

	/**
	 * Manually trigger data cleanup (for testing or admin action).
	 *
	 * @return array Results of the cleanup operation.
	 */
	public function manual_cleanup() {
		$options        = get_option( 'capture_options', array() );
		$retention_days = isset( $options['data_retention_days'] ) ? intval( $options['data_retention_days'] ) : 0;

		if ( $retention_days <= 0 ) {
			return array(
				'success' => false,
				'message' => __( 'Data retention is set to indefinite. No cleanup performed.', 'capture' ),
			);
		}

		// Run cleanup.
		$this->cleanup_expired_data();

		return array(
			'success' => true,
			/* translators: %d: Number of days */
			'message' => sprintf( __( 'Data cleanup completed for records older than %d days.', 'capture' ), $retention_days ),
		);
	}

	/**
	 * Get retention statistics.
	 *
	 * @return array Statistics about data that would be affected by retention policy.
	 */
	public function get_retention_stats() {
		$options        = get_option( 'capture_options', array() );
		$retention_days = isset( $options['data_retention_days'] ) ? intval( $options['data_retention_days'] ) : 0;

		global $wpdb;
		$table_name = $wpdb->prefix . 'capture_subscribers';

		$stats = array(
			'retention_days'    => $retention_days,
			'total_subscribers' => 0,
			'expired_count'     => 0,
			'next_cleanup'      => wp_next_scheduled( 'capture_data_retention_cleanup' ),
		);

		// Check if table exists.
		if ( ! Database::subscribers_table_exists() ) {
			return $stats;
		}

		// Get total subscriber count using properly constructed query.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is properly escaped
		$stats['total_subscribers'] = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . esc_sql( $table_name ) . '`' );

		// Get count of expired records if retention is enabled.
		if ( $retention_days > 0 ) {
			$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is properly escaped, data is prepared
			$stats['expired_count'] = $wpdb->get_var(
				$wpdb->prepare( 'SELECT COUNT(*) FROM `' . esc_sql( $table_name ) . '` WHERE date_subscribed < %s', $cutoff_date )
			);
		}

		return $stats;
	}
}
