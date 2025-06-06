<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file contains all the ajax handler functions used on the frontend.
 *
 * @link       https://github.com/yourusername/capture
 * @since      1.0.0
 *
 * @package    Capture
 * @subpackage Capture/includes
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Register frontend script enqueue hooks.
\add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_frontend_scripts' );

// Register AJAX hooks for form submission.
\add_action( 'wp_ajax_capture_submit_form', __NAMESPACE__ . '\ajax_submit_form' );
\add_action( 'wp_ajax_nopriv_capture_submit_form', __NAMESPACE__ . '\ajax_submit_form' );

/**
 * Enqueue frontend scripts and localize AJAX data.
 */
function enqueue_frontend_scripts() {
	wp_register_script(
		'capture-form-frontend',
		CAPTURE_PLUGIN_URL . 'assets/js/capture-form-frontend.js',
		array(),
		CAPTURE_VERSION,
		true
	);

	wp_localize_script(
		'capture-form-frontend',
		'captureFrontend',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'capture_nonce' ),
			'strings'  => array(
				'emptyEmail'      => esc_html__( 'Email address cannot be empty.', 'capture' ),
				'invalidEmail'    => esc_html__( 'Please enter a valid email address.', 'capture' ),
				'successMessage'  => esc_html__( 'Thank you for subscribing!', 'capture' ),
				'errorMessage'    => esc_html__( 'An error occurred. Please try again.', 'capture' ),
				'fetchError'      => esc_html__( 'A network error occurred. Please try again.', 'capture' ),
				'configError'     => esc_html__( 'Form configuration error. Please contact site admin.', 'capture' ),
				'emsNotSelected'  => esc_html__( 'EMS Provider not selected. Please check form configuration.', 'capture' ),
				'listNotSelected' => esc_html__( 'List not selected. Please check form configuration.', 'capture' ),
			),
		)
	);
}

/**
 * Handle AJAX form submission.
 */
function ajax_submit_form() {
	// Verify nonce for security.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'capture_nonce' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please refresh the page and try again.', 'capture' ) ) );
		return;
	}

	// Validate email.
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Please enter a valid email address.', 'capture' ) ) );
		return;
	}

	// Retrieve form data.
	$ems_connection_id = isset( $_POST['ems_connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ems_connection_id'] ) ) : '';
	$list_id           = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';
	$post_id           = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$form_id           = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : 'capture-form';
	$first_name        = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';

	// Get plugin options.
	$options              = get_option( 'capture_options', array() );
	$connections          = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();
	$enable_local_storage = isset( $options['enable_local_storage'] ) ? $options['enable_local_storage'] : true;

	// Determine submission path: EMS or Local Storage.
	$has_ems_connection = ! empty( $ems_connection_id ) && ! empty( $list_id ) && isset( $connections[ $ems_connection_id ] );

	if ( $has_ems_connection ) {
		// Path 1: Submit to EMS.
		$result = submit_to_ems( $email, $ems_connection_id, $list_id, $post_id, $form_id, $first_name );
	} elseif ( $enable_local_storage ) {
		// Path 2: Store locally.
		$result = store_locally( $email, $form_id, $first_name, $post_id );
	} else {
		// No EMS and local storage disabled.
		wp_send_json_error( array( 'message' => esc_html__( 'Form submission is not properly configured. Please contact the site administrator.', 'capture' ) ) );
		return;
	}

	// Handle the result.
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	} else {
		// Record analytics for successful submission.
		record_analytics( $form_id, $post_id );

		// Get success message from options or use default.
		$success_message = isset( $options['default_success_message'] ) ? $options['default_success_message'] : esc_html__( 'Thank you for subscribing!', 'capture' );

		wp_send_json_success( array( 'message' => $success_message ) );
	}
}

/**
 * Submit email to EMS provider.
 *
 * @param string $email Email address.
 * @param string $ems_connection_id EMS connection ID.
 * @param string $list_id List ID.
 * @param int    $post_id Post ID.
 * @param string $form_id Form ID.
 * @param string $first_name First name.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function submit_to_ems( $email, $ems_connection_id, $list_id, $post_id, $form_id, $first_name ) {
	// Get plugin options and connections.
	$options     = get_option( 'capture_options', array() );
	$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();

	$connection_details       = $connections[ $ems_connection_id ];
	$provider_slug            = $connection_details['provider'];
	$stored_encrypted_api_key = isset( $connection_details['api_key'] ) ? $connection_details['api_key'] : '';

	if ( empty( $stored_encrypted_api_key ) ) {
		return new \WP_Error( 'missing_api_key', \esc_html__( 'API key is missing for this connection. Please contact the site administrator.', 'capture' ) );
	}

	// Access the global plugin instance.
	if ( ! isset( $GLOBALS['capture_instance'] ) ) {
		return new \WP_Error( 'plugin_error', \esc_html__( 'Plugin core error. Please contact the site administrator.', 'capture' ) );
	}

	$plugin_instance = $GLOBALS['capture_instance'];

	// Decrypt the API key using the encryption service.
	$decrypted_api_key = \Capture\Encryption::decrypt( $stored_encrypted_api_key );

	// Check for potential decryption issues.
	if ( $decrypted_api_key === $stored_encrypted_api_key && ! empty( $stored_encrypted_api_key ) && \extension_loaded( 'openssl' ) && \Capture\Encryption::is_properly_configured() ) {
		return new \WP_Error( 'decryption_failed', \esc_html__( 'API credential retrieval failed. Please contact the site administrator.', 'capture' ) );
	}

	// Get the EMS service.
	$service       = $plugin_instance->get_service( $provider_slug );
	$provider_name = 'EMS';

	if ( $service && \method_exists( $service, 'get_provider_name' ) ) {
		$provider_name = $service->get_provider_name();
	}

	if ( ! $service ) {
		/* translators: %s: The EMS provider slug */
		return new \WP_Error( 'service_not_found', \sprintf( \esc_html__( 'EMS Provider "%s" service not found.', 'capture' ), \esc_html( $provider_slug ) ) );
	}

	// Check if service has required methods.
	if ( ! \method_exists( $service, 'subscribe_email' ) || ! \method_exists( $service, 'get_provider_name' ) ) {
		/* translators: %s: The EMS provider slug */
		return new \WP_Error( 'service_misconfigured', \sprintf( \esc_html__( 'The email service (%s) is not correctly configured.', 'capture' ), \esc_html( $provider_slug ) ) );
	}

	$credentials = array( 'api_key' => $decrypted_api_key );

	// Prepare subscription data.
	$subscription_data = array(
		'first_name' => $first_name,
	);

	// Subscribe to the EMS provider.
	try {
		$subscription_result = $service->subscribe_email( $credentials, $email, $list_id, $subscription_data );

		if ( $subscription_result ) {
			return true;
		} else {
			/* translators: %s: The EMS provider name */
			return new \WP_Error( 'ems_subscription_failed', \sprintf( \esc_html__( 'Could not subscribe with %s. Please try again later.', 'capture' ), \esc_html( $provider_name ) ) );
		}
	} catch ( \Exception $e ) {
		\error_log( 'WP Capture EMS Submission Error: ' . $e->getMessage() );
		/* translators: %s: The EMS provider name */
		return new \WP_Error( 'ems_exception', \sprintf( \esc_html__( 'An unexpected error occurred with %s. Please try again later.', 'capture' ), \esc_html( $provider_name ) ) );
	}
}

/**
 * Store email locally in database.
 *
 * @param string $email Email address.
 * @param string $form_id Form ID.
 * @param string $first_name First name.
 * @param int    $post_id Post ID.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function store_locally( $email, $form_id, $first_name, $post_id ) {
	// Ensure subscriber table exists.
	if ( ! Database::subscribers_table_exists() ) {
		Database::create_subscribers_table();
	}

	// Create subscriber object.
	$subscriber_data = array(
		'email'      => $email,
		'name'       => $first_name,
		'form_id'    => $form_id,
		'post_id'    => $post_id,
		'source_url' => wp_get_referer() ? wp_get_referer() : get_permalink( $post_id ),
		'ip_address' => get_user_ip(),
		'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'status'     => 'subscribed',
	);

	$subscriber = new Subscriber( $subscriber_data );
	$result     = $subscriber->save();

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	// Send admin notification if enabled.
	send_admin_notification( $subscriber_data, $result );

	// Send subscriber confirmation email if enabled.
	send_subscriber_confirmation( $subscriber_data, $result );

	return true;
}

/**
 * Record analytics for form submission.
 *
 * @param string $form_id Form ID.
 * @param int    $post_id Post ID.
 */
function record_analytics( $form_id, $post_id ) {

	if ( empty( $form_id ) || empty( $post_id ) ) {
		return;
	}

	$shape = array(
		'post_id'                   => $post_id,
		'form_id'                   => $form_id,
		'count'                     => 1,
		'last_submission_timestamp' => time(),
	);

	$key  = 'capture_analytics';
	$data = get_option( $key, array() );

	if ( ! isset( $data[ $form_id ] ) ) {
		$data[ $form_id ] = $shape;
	} else {
		++$data[ $form_id ]['count'];
		$data[ $form_id ]['last_submission_timestamp'] = time();
	}

	// Update the analytics data.
	update_option( $key, $data );
}

/**
 * Get user IP address safely.
 *
 * @return string The user's IP address.
 */
function get_user_ip() {
	$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );

	foreach ( $ip_keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			// Handle comma-separated IPs (for forwarded headers).
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = trim( explode( ',', $ip )[0] );
			}
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return $ip;
			}
		}
	}

	return '127.0.0.1'; // Fallback.
}

/**
 * Send admin notification email.
 *
 * @param array $subscriber_data The subscriber data.
 * @param int   $subscriber_id   The subscriber ID.
 */
function send_admin_notification( $subscriber_data, $subscriber_id ) {
	$options = get_option( 'capture_options', array() );

	// Check if admin notifications are enabled.
	if ( empty( $options['notify_admin_new_subscriber'] ) ) {
		return;
	}

	$admin_email = isset( $options['admin_notification_email'] ) ? $options['admin_notification_email'] : get_option( 'admin_email' );

	if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
		return;
	}

	$site_name = get_bloginfo( 'name' );
	$site_url  = home_url( '/wp-admin/admin.php?page=capture-subscribers' );

	/* translators: %s: Site name */
	$subject = \sprintf( \__( '[%s] New Subscriber', 'capture' ), $site_name );

	/* translators: 1: Email address, 2: Name, 3: Form ID, 4: Source URL, 5: Date, 6: Admin URL */
	$message = \sprintf(
		\__( "A new subscriber has been added to your website:\n\nEmail: %1\$s\nName: %2\$s\nForm ID: %3\$s\nSource: %4\$s\nDate: %5\$s\n\nView all subscribers: %6\$s", 'capture' ),
		$subscriber_data['email'],
		! empty( $subscriber_data['name'] ) ? $subscriber_data['name'] : \__( 'Not provided', 'capture' ),
		$subscriber_data['form_id'],
		! empty( $subscriber_data['source_url'] ) ? $subscriber_data['source_url'] : \__( 'Direct access', 'capture' ),
		current_time( 'mysql' ),
		$site_url
	);

	// Add unsubscribe link if enabled.
	if ( ! empty( $options['enable_unsubscribe_links'] ) && class_exists( 'Capture\Unsubscribe' ) ) {
		$unsubscribe_url = Unsubscribe::generate_unsubscribe_url( $subscriber_id, $subscriber_data['email'] );
		/* translators: %s: Unsubscribe URL */
		$message .= \sprintf( \__( "\n\nUnsubscribe link: %s", 'capture' ), $unsubscribe_url );
	}

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	\wp_mail( $admin_email, $subject, $message, $headers );
}

/**
 * Send subscriber confirmation email.
 *
 * @param array $subscriber_data The subscriber data.
 * @param int   $subscriber_id   The subscriber ID.
 */
function send_subscriber_confirmation( $subscriber_data, $subscriber_id ) {
	$options = get_option( 'capture_options', array() );

	// Check if subscriber confirmation emails are enabled.
	if ( empty( $options['send_subscriber_confirmation'] ) ) {
		return;
	}

	$subscriber_email = $subscriber_data['email'];

	if ( empty( $subscriber_email ) || ! is_email( $subscriber_email ) ) {
		return;
	}

	// Get email settings with defaults.
	$from_name  = isset( $options['subscriber_email_from_name'] ) ? $options['subscriber_email_from_name'] : get_bloginfo( 'name' );
	$from_email = isset( $options['subscriber_email_from_email'] ) ? $options['subscriber_email_from_email'] : get_option( 'admin_email' );
	$subject    = isset( $options['subscriber_email_subject'] ) ? $options['subscriber_email_subject'] : \__( 'Welcome! Subscription Confirmed', 'capture' );

	// Get email template.
	$default_template = "Hello {name},\n\nThank you for subscribing to our updates!\n\nWe're excited to have you as part of our community. You'll receive our latest news, updates, and exclusive content directly in your inbox.\n\nSubscription Details:\n• Email: {email}\n• Date: {date}\n• Website: {site_name}\n\nIf you ever want to unsubscribe, you can do so at any time using this link:\n{unsubscribe_url}\n\nBest regards,\nThe {site_name} Team";
	$template         = isset( $options['subscriber_email_template'] ) ? $options['subscriber_email_template'] : $default_template;

	// Process template placeholders.
	$message = process_email_template( $template, $subscriber_data, $subscriber_id );

	// Set up headers.
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
	);

	// Send the email.
	\wp_mail( $subscriber_email, $subject, $message, $headers );
}

/**
 * Process email template with placeholders.
 *
 * @param string $template       The email template.
 * @param array  $subscriber_data The subscriber data.
 * @param int    $subscriber_id   The subscriber ID.
 * @return string The processed email content.
 */
function process_email_template( $template, $subscriber_data, $subscriber_id ) {
	$site_name = get_bloginfo( 'name' );
	$site_url  = home_url( '/' );

	// Generate unsubscribe URL.
	$unsubscribe_url = '';
	if ( class_exists( 'Capture\Unsubscribe' ) ) {
		$unsubscribe_url = Unsubscribe::generate_unsubscribe_url( $subscriber_id, $subscriber_data['email'] );
	}

	// Prepare replacement values.
	$replacements = array(
		'{name}'            => ! empty( $subscriber_data['name'] ) ? $subscriber_data['name'] : \__( 'Subscriber', 'capture' ),
		'{email}'           => $subscriber_data['email'],
		'{date}'            => current_time( get_option( 'date_format' ) ),
		'{site_name}'       => $site_name,
		'{site_url}'        => $site_url,
		'{unsubscribe_url}' => $unsubscribe_url,
	);

	// Replace placeholders.
	return str_replace( array_keys( $replacements ), array_values( $replacements ), $template );
}
