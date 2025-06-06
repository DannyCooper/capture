<?php
/**
 * Handles the settings page and related functionality for WP Capture.
 *
 * @package    Capture
 * @subpackage Capture/includes/admin
 */

namespace Capture;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin_Settings class.
 */
class Admin_Settings {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		// Register hooks.
		add_action( 'admin_init', array( $this, 'register_settings_init' ) );
	}

	/**
	 * Display the settings page.
	 * This method will be called by the main admin class.
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Plugin Settings', 'capture' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'capture_options_group' );
				do_settings_sections( 'capture-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register settings, sections, and fields.
	 * Hooked to 'admin_init'.
	 */
	public function register_settings_init() {
		register_setting(
			'capture_options_group',
			'capture_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'show_in_rest' => true,
			)
		);

		// Main Settings Section.
		add_settings_section(
			'capture_main_settings_section',
			__( 'Global Settings', 'capture' ),
			array( $this, 'main_settings_section_callback' ),
			'capture-settings'
		);

		// Local Subscriber Storage Section.
		add_settings_section(
			'capture_local_storage_section',
			__( 'Local Subscriber Storage', 'capture' ),
			array( $this, 'local_storage_section_callback' ),
			'capture-settings'
		);

		// Privacy & GDPR Section.
		add_settings_section(
			'capture_privacy_section',
			__( 'Privacy & GDPR Compliance', 'capture' ),
			array( $this, 'privacy_section_callback' ),
			'capture-settings'
		);

		// Email Notifications Section.
		add_settings_section(
			'capture_notifications_section',
			__( 'Email Notifications', 'capture' ),
			array( $this, 'notifications_section_callback' ),
			'capture-settings'
		);

		// Subscriber Emails Section.
		add_settings_section(
			'capture_subscriber_emails_section',
			__( 'Subscriber Confirmation Emails', 'capture' ),
			array( $this, 'subscriber_emails_section_callback' ),
			'capture-settings'
		);

		// Add settings fields.
		$this->add_settings_fields();
	}

	/**
	 * Add all settings fields.
	 */
	private function add_settings_fields() {
		// Local Storage Settings.
		add_settings_field(
			'enable_local_storage',
			__( 'Enable Local Storage', 'capture' ),
			array( $this, 'enable_local_storage_callback' ),
			'capture-settings',
			'capture_local_storage_section'
		);

		add_settings_field(
			'default_success_message',
			__( 'Default Success Message', 'capture' ),
			array( $this, 'default_success_message_callback' ),
			'capture-settings',
			'capture_local_storage_section'
		);

		add_settings_field(
			'privacy_policy_text',
			__( 'Privacy Policy Text', 'capture' ),
			array( $this, 'privacy_policy_text_callback' ),
			'capture-settings',
			'capture_privacy_section'
		);

		add_settings_field(
			'data_retention_days',
			__( 'Data Retention Period', 'capture' ),
			array( $this, 'data_retention_days_callback' ),
			'capture-settings',
			'capture_privacy_section'
		);

		add_settings_field(
			'notify_admin_new_subscriber',
			__( 'Admin Notifications', 'capture' ),
			array( $this, 'notify_admin_new_subscriber_callback' ),
			'capture-settings',
			'capture_notifications_section'
		);

		add_settings_field(
			'admin_notification_email',
			__( 'Notification Email Address', 'capture' ),
			array( $this, 'admin_notification_email_callback' ),
			'capture-settings',
			'capture_notifications_section'
		);

		add_settings_field(
			'send_subscriber_confirmation',
			__( 'Send Confirmation Emails', 'capture' ),
			array( $this, 'send_subscriber_confirmation_callback' ),
			'capture-settings',
			'capture_subscriber_emails_section'
		);

		add_settings_field(
			'subscriber_email_from_name',
			__( 'From Name', 'capture' ),
			array( $this, 'subscriber_email_from_name_callback' ),
			'capture-settings',
			'capture_subscriber_emails_section'
		);

		add_settings_field(
			'subscriber_email_from_email',
			__( 'From Email', 'capture' ),
			array( $this, 'subscriber_email_from_email_callback' ),
			'capture-settings',
			'capture_subscriber_emails_section'
		);

		add_settings_field(
			'subscriber_email_subject',
			__( 'Email Subject', 'capture' ),
			array( $this, 'subscriber_email_subject_callback' ),
			'capture-settings',
			'capture_subscriber_emails_section'
		);

		add_settings_field(
			'subscriber_email_template',
			__( 'Email Template', 'capture' ),
			array( $this, 'subscriber_email_template_callback' ),
			'capture-settings',
			'capture_subscriber_emails_section'
		);
	}

	/**
	 * Callback for the main settings section description.
	 */
	public function main_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure global settings for the WP Capture plugin.', 'capture' ) . '</p>';
	}

	/**
	 * Callback for the local storage section description.
	 */
	public function local_storage_section_callback() {
		echo '<p>' . esc_html__( 'Configure how subscribers are stored locally when no EMS provider is connected.', 'capture' ) . '</p>';
	}

	/**
	 * Callback for the privacy section description.
	 */
	public function privacy_section_callback() {
		echo '<p>' . esc_html__( 'Configure privacy and GDPR compliance settings for local subscriber data.', 'capture' ) . '</p>';
	}

	/**
	 * Callback for the notifications section description.
	 */
	public function notifications_section_callback() {
		echo '<p>' . esc_html__( 'Configure email notifications for new local subscribers.', 'capture' ) . '</p>';
	}

	/**
	 * Callback for the subscriber emails section description.
	 */
	public function subscriber_emails_section_callback() {
		echo '<p>' . esc_html__( 'Configure confirmation emails sent to subscribers when they sign up locally.', 'capture' ) . '</p>';
	}

	/**
	 * Enable Local Storage field callback.
	 */
	public function enable_local_storage_callback() {
		$options = get_option( 'capture_options', array() );
		$enabled = isset( $options['enable_local_storage'] ) ? $options['enable_local_storage'] : true;

		echo '<input type="checkbox" id="enable_local_storage" name="capture_options[enable_local_storage]" value="1" ' . checked( 1, $enabled, false ) . ' />';
		echo '<label for="enable_local_storage">' . esc_html__( 'Store subscribers locally when no EMS provider is connected', 'capture' ) . '</label>';
		echo '<p class="description">' . esc_html__( 'When disabled, forms without EMS connections will show an error.', 'capture' ) . '</p>';
	}

	/**
	 * Default Success Message field callback.
	 */
	public function default_success_message_callback() {
		$options = get_option( 'capture_options', array() );
		$message = isset( $options['default_success_message'] ) ? $options['default_success_message'] : __( 'Thank you for subscribing!', 'capture' );

		echo '<input type="text" id="default_success_message" name="capture_options[default_success_message]" value="' . esc_attr( $message ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Message displayed to users after successful subscription.', 'capture' ) . '</p>';
	}

	/**
	 * Privacy Policy Text field callback.
	 */
	public function privacy_policy_text_callback() {
		$options = get_option( 'capture_options', array() );
		$text    = isset( $options['privacy_policy_text'] ) ? $options['privacy_policy_text'] : '';

		echo '<textarea id="privacy_policy_text" name="capture_options[privacy_policy_text]" rows="4" cols="50" class="large-text">' . esc_textarea( $text ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Privacy notice text to display with forms (optional). HTML allowed.', 'capture' ) . '</p>';
	}

	/**
	 * Data Retention Days field callback.
	 */
	public function data_retention_days_callback() {
		$options = get_option( 'capture_options', array() );
		$days    = isset( $options['data_retention_days'] ) ? $options['data_retention_days'] : 0;

		echo '<input type="number" id="data_retention_days" name="capture_options[data_retention_days]" value="' . esc_attr( $days ) . '" min="0" max="3650" />';
		echo '<label for="data_retention_days">' . esc_html__( ' days', 'capture' ) . '</label>';
		echo '<p class="description">' . esc_html__( 'Automatically delete subscriber data after this many days. Set to 0 to keep data indefinitely.', 'capture' ) . '</p>';
	}

	/**
	 * Admin Notification field callback.
	 */
	public function notify_admin_new_subscriber_callback() {
		$options = get_option( 'capture_options', array() );
		$enabled = isset( $options['notify_admin_new_subscriber'] ) ? $options['notify_admin_new_subscriber'] : false;

		echo '<input type="checkbox" id="notify_admin_new_subscriber" name="capture_options[notify_admin_new_subscriber]" value="1" ' . checked( 1, $enabled, false ) . ' />';
		echo '<label for="notify_admin_new_subscriber">' . esc_html__( 'Send email notification when new subscribers are added locally', 'capture' ) . '</label>';
	}

	/**
	 * Admin Notification Email field callback.
	 */
	public function admin_notification_email_callback() {
		$options = get_option( 'capture_options', array() );
		$email   = isset( $options['admin_notification_email'] ) ? $options['admin_notification_email'] : get_option( 'admin_email' );

		echo '<input type="email" id="admin_notification_email" name="capture_options[admin_notification_email]" value="' . esc_attr( $email ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Email address to receive new subscriber notifications.', 'capture' ) . '</p>';
	}

	/**
	 * Send Confirmation Emails field callback.
	 */
	public function send_subscriber_confirmation_callback() {
		$options = get_option( 'capture_options', array() );
		$enabled = isset( $options['send_subscriber_confirmation'] ) ? $options['send_subscriber_confirmation'] : true;

		echo '<input type="checkbox" id="send_subscriber_confirmation" name="capture_options[send_subscriber_confirmation]" value="1" ' . checked( 1, $enabled, false ) . ' disabled/>';
		echo '<label for="send_subscriber_confirmation">' . esc_html__( 'Send welcome email to subscribers when they sign up locally', 'capture' ) . '</label>';
		echo '<p class="description">' . esc_html__( 'Subscribers will receive a confirmation email with an unsubscribe link.', 'capture' ) . '</p>';
	}

	/**
	 * From Name field callback.
	 */
	public function subscriber_email_from_name_callback() {
		$options   = get_option( 'capture_options', array() );
		$from_name = isset( $options['subscriber_email_from_name'] ) ? $options['subscriber_email_from_name'] : get_bloginfo( 'name' );

		echo '<input type="text" id="subscriber_email_from_name" name="capture_options[subscriber_email_from_name]" value="' . esc_attr( $from_name ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Name that appears in the "From" field of subscriber emails.', 'capture' ) . '</p>';
	}

	/**
	 * From Email field callback.
	 */
	public function subscriber_email_from_email_callback() {
		$options    = get_option( 'capture_options', array() );
		$from_email = isset( $options['subscriber_email_from_email'] ) ? $options['subscriber_email_from_email'] : get_option( 'admin_email' );

		echo '<input type="email" id="subscriber_email_from_email" name="capture_options[subscriber_email_from_email]" value="' . esc_attr( $from_email ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Email address that subscriber emails will be sent from.', 'capture' ) . '</p>';
	}

	/**
	 * Email Subject field callback
	 */
	public function subscriber_email_subject_callback() {
		$options = get_option( 'capture_options', array() );
		$subject = isset( $options['subscriber_email_subject'] ) ? $options['subscriber_email_subject'] : __( 'Welcome! Subscription Confirmed', 'capture' );

		echo '<input type="text" id="subscriber_email_subject" name="capture_options[subscriber_email_subject]" value="' . esc_attr( $subject ) . '" class="regular-text" disabled/>';
		echo '<p class="description">' . esc_html__( 'Subject line for subscriber confirmation emails.', 'capture' ) . '</p>';
	}

	/**
	 * Email Template field callback
	 */
	public function subscriber_email_template_callback() {
		$options          = get_option( 'capture_options', array() );
		$default_template = "Hello {name},\n\nThank you for subscribing to our updates!\n\nWe're excited to have you as part of our community. You'll receive our latest news, updates, and exclusive content directly in your inbox.\n\nSubscription Details:\n• Email: {email}\n• Date: {date}\n• Website: {site_name}\n\nIf you ever want to unsubscribe, you can do so at any time using this link:\n{unsubscribe_url}\n\nBest regards,\nThe {site_name} Team";

		$template = isset( $options['subscriber_email_template'] ) ? $options['subscriber_email_template'] : $default_template;

		echo '<textarea disabled id="subscriber_email_template" name="capture_options[subscriber_email_template]" rows="12" cols="70" class="large-text code">' . esc_textarea( $template ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Email template sent to subscribers. Available placeholders: {name}, {email}, {date}, {site_name}, {site_url}, {unsubscribe_url}', 'capture' ) . '</p>';
	}

	/**
	 * Sanitize each setting field as needed.
	 *
	 * @param array $input The input array of settings.
	 * @return array The sanitized settings.
	 */
	public function sanitize_options( $input ) {
		$sanitized = array();

		// Sanitize enable_local_storage.
		$sanitized['enable_local_storage'] = isset( $input['enable_local_storage'] ) ? (bool) $input['enable_local_storage'] : false;

		// Sanitize default_success_message.
		if ( isset( $input['default_success_message'] ) ) {
			$sanitized['default_success_message'] = sanitize_text_field( $input['default_success_message'] );
		}

		// Sanitize privacy_policy_text.
		if ( isset( $input['privacy_policy_text'] ) ) {
			$sanitized['privacy_policy_text'] = wp_kses_post( $input['privacy_policy_text'] );
		}

		// Sanitize data_retention_days.
		if ( isset( $input['data_retention_days'] ) ) {
			$sanitized['data_retention_days'] = max( 0, intval( $input['data_retention_days'] ) );
		}

		// Sanitize notify_admin_new_subscriber.
		$sanitized['notify_admin_new_subscriber'] = isset( $input['notify_admin_new_subscriber'] ) ? (bool) $input['notify_admin_new_subscriber'] : false;

		// Sanitize admin_notification_email.
		if ( isset( $input['admin_notification_email'] ) ) {
			$sanitized['admin_notification_email'] = sanitize_email( $input['admin_notification_email'] );
		}

		// Sanitize subscriber email settings.
		$sanitized['send_subscriber_confirmation'] = isset( $input['send_subscriber_confirmation'] ) ? (bool) $input['send_subscriber_confirmation'] : false;

		if ( isset( $input['subscriber_email_from_name'] ) ) {
			$sanitized['subscriber_email_from_name'] = sanitize_text_field( $input['subscriber_email_from_name'] );
		}

		if ( isset( $input['subscriber_email_from_email'] ) ) {
			$sanitized['subscriber_email_from_email'] = sanitize_email( $input['subscriber_email_from_email'] );
		}

		if ( isset( $input['subscriber_email_subject'] ) ) {
			$sanitized['subscriber_email_subject'] = sanitize_text_field( $input['subscriber_email_subject'] );
		}

		if ( isset( $input['subscriber_email_template'] ) ) {
			$sanitized['subscriber_email_template'] = sanitize_textarea_field( $input['subscriber_email_template'] );
		}

		if ( isset( $input['ems_connections'] ) ) {
			$sanitized['ems_connections'] = $input['ems_connections'];
		}

		$existing_options = get_option( 'capture_options', array() );
		return array_merge( $existing_options, $sanitized );
	}
}
