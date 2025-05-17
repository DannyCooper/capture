<?php
/**
 * Handles frontend script enqueueing and AJAX form submissions for the WP Capture plugin.
 *
 * @package WP_Capture
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enqueue frontend scripts and styles for the WP Capture Form block.
 *
 * @since 1.0.0
 */
function wp_capture_enqueue_frontend_scripts() {
	// Only enqueue if we're on a singular page and the block is present.
	wp_register_script(
		'wp-capture-form-frontend',
		WP_CAPTURE_PLUGIN_URL . 'assets/js/wp-capture-form-frontend.js',
		array(), // Dependencies.
		WP_CAPTURE_VERSION,
		true // Load in footer.
	);

	wp_localize_script(
		'wp-capture-form-frontend',
		'wpCaptureFrontend',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'capture_submit_nonce' ),
			'i18n'    => array(
				'emptyEmail'      => esc_html__( 'Email address cannot be empty.', 'wp-capture' ),
				'invalidEmail'    => esc_html__( 'Please enter a valid email address.', 'wp-capture' ),
				'successMessage'  => esc_html__( 'Thank you for subscribing!', 'wp-capture' ),
				'errorMessage'    => esc_html__( 'An error occurred. Please try again.', 'wp-capture' ),
				'fetchError'      => esc_html__( 'A network error occurred. Please try again.', 'wp-capture' ),
				'configError'     => esc_html__( 'Form configuration error. Please contact site admin.', 'wp-capture' ),
				'emsNotSelected'  => esc_html__( 'EMS Provider not selected. Please check form configuration.', 'wp-capture' ),
				'listNotSelected' => esc_html__( 'List not selected. Please check form configuration.', 'wp-capture' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wp_capture_enqueue_frontend_scripts' );

/**
 * AJAX handler for frontend form submission.
 *
 * @since 1.0.0
 */
function wp_capture_ajax_submit_form() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'capture_submit_nonce' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'wp-capture' ) ), 403 );
		return;
	}

	// Sanitize and retrieve email.
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Invalid email address provided.', 'wp-capture' ) ) );
		return;
	}

	// Retrieve EMS connection ID and List ID.
	$ems_connection_id = isset( $_POST['ems_connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ems_connection_id'] ) ) : '';
	$list_id           = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';
	$post_id           = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$form_id           = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : ''; // This is now the block's clientId.

	if ( empty( $ems_connection_id ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'EMS Connection ID is missing.', 'wp-capture' ) ) );
		return;
	}

	if ( empty( $list_id ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'List ID is missing.', 'wp-capture' ) ) );
		return;
	}

	$options     = get_option( 'wp_capture_options' );
	$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();

	if ( ! isset( $connections[ $ems_connection_id ] ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Selected EMS connection not found.', 'wp-capture' ) ) );
		return;
	}

	$connection_details       = $connections[ $ems_connection_id ];
	$provider_slug            = $connection_details['provider'];
	$stored_encrypted_api_key = isset( $connection_details['api_key'] ) ? $connection_details['api_key'] : '';

	if ( empty( $stored_encrypted_api_key ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Could not process subscription: API configuration error (key missing). Contact admin.', 'wp-capture' ) ) );
		return;
	}

	// Access the global plugin instance and encryption service.
	if ( ! isset( $GLOBALS['wp_capture_instance'] ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Could not process subscription: Plugin core error.', 'wp-capture' ) ) );
		return;
	}

	if ( ! class_exists( 'Encryption' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Could not process subscription: Security setup incomplete. Please contact site admin.', 'wp-capture' ) ) );
		return;
	}

	$decrypted_api_key = Encryption::decrypt( $stored_encrypted_api_key );

	// If decryption returned the original value (potential issue if OpenSSL active & keys configured).
	if ( $decrypted_api_key === $stored_encrypted_api_key && ! empty( $stored_encrypted_api_key ) && extension_loaded( 'openssl' ) && Encryption::is_properly_configured() ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Could not process subscription: API credential retrieval failed. Contact admin.', 'wp-capture' ) ) );
		return;
	}

	if ( empty( $decrypted_api_key ) && ! empty( $stored_encrypted_api_key ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Could not process subscription: API configuration issue. Contact admin.', 'wp-capture' ) ) );
		return;
	}

	// If $decrypted_api_key is empty AND $stored_encrypted_api_key was also empty, the first check for empty stored key would have hit.
	// If OpenSSL is off or keys are bad, decrypt returns original value. If that original value was empty, then this is correct.
	// The scenario for this specific error condition is an actual encrypted key that decrypts to an empty string, which should not happen.

	$credentials = array( 'api_key' => $decrypted_api_key );

	$service = $GLOBALS['wp_capture_instance']->get_service( $provider_slug );

	if ( ! $service ) {
		/* translators: %s: Provider slug. */
		wp_send_json_error( array( 'message' => sprintf( esc_html__( 'EMS Provider "%s" service not found.', 'wp-capture' ), esc_html( $provider_slug ) ) ) );
		return;
	}

	// Generic handling for any EMS provider that implements EmsServiceInterface.
	if ( ! method_exists( $service, 'subscribe_email' ) || ! method_exists( $service, 'get_provider_name' ) ) {
		$actual_provider_slug = isset( $provider_slug ) ? $provider_slug : 'unknown';
		/* translators: %s: Provider slug. */
		wp_send_json_error( array( 'message' => sprintf( esc_html__( 'The email service (%s) is not correctly configured.', 'wp-capture' ), esc_html( $actual_provider_slug ) ) ) );
		return;
	}

	$provider_name = $service->get_provider_name();

	$form_data_for_service = array(
		'post_id' => $post_id,
		'form_id' => $form_id,
		// Add any other relevant data from $formData if the service expects/supports it.
	);

	try {
		$subscribed = $service->subscribe_email( $credentials, $email, $list_id, $form_data_for_service );

		if ( $subscribed ) {
			// Step 25: Analytics - Record successful submission.
			if ( ! empty( $form_id ) ) {
				$analytics_data = get_option( 'wp_capture_analytics_data', array() );

				if ( ! isset( $analytics_data[ $form_id ] ) ) {
					$analytics_data[ $form_id ] = array(
						'count'                => 0,
						'post_id'              => null,
						'post_title'           => 'N/A', // Default title.
						'first_seen_timestamp' => time(),
					);
				}
				++$analytics_data[ $form_id ]['count'];
				$analytics_data[ $form_id ]['last_submission_timestamp'] = time();

				if ( $post_id > 0 ) {
					$analytics_data[ $form_id ]['post_id'] = $post_id;
					$post_title                            = get_the_title( $post_id );
					if ( ! empty( $post_title ) ) {
						$analytics_data[ $form_id ]['post_title'] = $post_title;
					} elseif ( empty( $analytics_data[ $form_id ]['post_title'] ) ) {
						// If title is empty and we don't have one stored, keep N/A or a placeholder.
						$analytics_data[ $form_id ]['post_title'] = 'Post ID: ' . $post_id;
					}
				}
				update_option( 'wp_capture_analytics_data', $analytics_data );
			}

			wp_send_json_success( array( 'message' => esc_html__( 'Successfully subscribed!', 'wp-capture' ) ) );
		} else {
			// The service method should ideally log specific API errors from the EMS provider itself.
			// This error is for when the service method itself returns false without throwing an exception.
			/* translators: %s: Provider name. */
			wp_send_json_error( array( 'message' => sprintf( esc_html__( 'Could not subscribe with %s. Please try again later.', 'wp-capture' ), esc_html( $provider_name ) ) ) );
		}
	} catch ( Exception $e ) {
		/* translators: %s: Provider name. */
		wp_send_json_error( array( 'message' => sprintf( esc_html__( 'An unexpected error occurred with %s. Please try again later.', 'wp-capture' ), esc_html( $provider_name ) ) ) );
	}
}
add_action( 'wp_ajax_capture_submit', 'wp_capture_ajax_submit_form' );
add_action( 'wp_ajax_nopriv_capture_submit', 'wp_capture_ajax_submit_form' );
