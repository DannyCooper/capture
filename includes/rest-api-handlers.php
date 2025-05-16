<?php
/**
 * Handles REST API endpoint registration and callbacks for the WP Capture plugin.
 *
 * @package WP_Capture
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register a REST API endpoint to fetch EMS lists.
 *
 * @since 1.0.0
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'wp-capture/v1',
			'/get-ems-lists/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'wp_capture_get_ems_lists_callback',
				'args'                => array(
					'ems_id' => array(
						'required'          => true,
						'type'              => 'string',
						'description'       => __( 'The ID of the EMS connection.', 'wp-capture' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'wp-capture/v1',
			'/get-ems-providers/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'wp_capture_get_ems_providers_callback',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
);

/**
 * Callback function for the /get-ems-providers REST API endpoint.
 *
 * @since 1.0.0
 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
 */
function wp_capture_get_ems_providers_callback() {
	$options             = get_option( 'wp_capture_options' );
	$connections         = isset( $options['ems_connections'] ) && is_array( $options['ems_connections'] ) ? $options['ems_connections'] : array();
	$formatted_providers = array();

	if ( empty( $connections ) ) {
		return new WP_REST_Response(
			array(
				'success'   => true,
				'message'   => esc_html__( 'No EMS providers configured yet.', 'wp-capture' ),
				'providers' => array(),
			),
			200
		);
	}

	foreach ( $connections as $connection_id => $details ) {
		$label = isset( $details['name'] ) && ! empty( $details['name'] ) ? $details['name'] : $connection_id;
		if ( isset( $details['provider'] ) ) {
			$label .= ' (' . ucfirst( $details['provider'] ) . ')';
		}

		$formatted_providers[] = array(
			'label' => $label,
			'value' => $connection_id,
		);
	}

	return new WP_REST_Response(
		array(
			'success'   => true,
			'providers' => $formatted_providers,
		),
		200
	);
}

/**
 * Callback function for the /get-ems-lists REST API endpoint.
 *
 * @since 1.0.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
 */
function wp_capture_get_ems_lists_callback( WP_REST_Request $request ) {
	$ems_id  = $request->get_param( 'ems_id' );
	$options = get_option( 'wp_capture_options' );

	if ( empty( $ems_id ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => esc_html__( 'EMS connection ID is required.', 'wp-capture' ),
				'lists'   => array(),
			),
			400
		); // Bad Request.
	}

	$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();
	if ( ! isset( $connections[ $ems_id ] ) ) {
		/* translators: %s: EMS Connection ID. */
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => sprintf( esc_html__( 'EMS connection "%s" not found.', 'wp-capture' ), esc_html( $ems_id ) ),
				'lists'   => array(),
			),
			404
		); // Not Found.
	}

	$connection_details = $connections[ $ems_id ];
	$provider_slug      = $connection_details['provider'];

	// Access the global plugin instance.
	if ( ! isset( $GLOBALS['wp_capture_instance'] ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => esc_html__( 'WP Capture main instance not available.', 'wp-capture' ),
				'lists'   => array(),
			),
			500
		);
	}
	// $wp_capture_instance = $GLOBALS['wp_capture_instance']; // Instance no longer needed for encryption.
	// $encryption_service = $wp_capture_instance->get_encryption_service(); // Service is static.

	if ( ! class_exists( 'Encryption' ) ) {
		// error_log( 'WP Capture REST API: Encryption class not available.' ); // Removed error_log.
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => esc_html__( 'Encryption class not available for fetching lists.', 'wp-capture' ),
				'lists'   => array(),
			),
			500
		);
	}

	$service = $GLOBALS['wp_capture_instance']->get_service( $provider_slug );

	if ( ! $service ) {
		/* translators: %s: Provider slug. */
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => sprintf( esc_html__( 'EMS Provider "%s" service not found.', 'wp-capture' ), esc_html( $provider_slug ) ),
				'lists'   => array(),
			),
			500
		);
	}

	try {
		$stored_encrypted_api_key = isset( $connection_details['api_key'] ) ? $connection_details['api_key'] : '';

		if ( empty( $stored_encrypted_api_key ) ) {
			/* translators: %s: EMS Connection ID. */
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => sprintf( esc_html__( 'API key for the EMS connection "%s" is missing or not configured.', 'wp-capture' ), esc_html( $ems_id ) ),
					'lists'   => array(),
				),
				200 // Or 400/500 depending on desired client handling.
			);
		}

		$decrypted_api_key = Encryption::decrypt( $stored_encrypted_api_key );

		// Check if decryption returned the original value (potential issue if OpenSSL active & keys configured).
		if ( $decrypted_api_key === $stored_encrypted_api_key && ! empty( $stored_encrypted_api_key ) && extension_loaded( 'openssl' ) && Encryption::is_properly_configured() ) {
			// error_log( 'WP Capture REST API: API Key decryption failed for ' . esc_html( $ems_id ) . ' or returned original encrypted value unexpectedly.' ); // Removed error_log.
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Could not securely retrieve API key for fetching lists.', 'wp-capture' ),
					'lists'   => array(),
				),
				500
			);
		}

		if ( empty( $decrypted_api_key ) && ! empty( $stored_encrypted_api_key ) ) {
			// This means decryption failed and returned an empty string, or it was stored empty AND OpenSSL is off / keys bad.
			// If stored_encrypted_api_key was indeed empty, the first check would have caught it.
			// So this implies a failure to decrypt an actual key, or it was stored as empty string post-encryption (unlikely).
			// error_log( 'WP Capture REST API: Decrypted API key is empty for ' . esc_html( $ems_id ) . ' (Provider: ' . esc_html( $provider_slug ) . ')' ); // Removed error_log.
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Failed to decrypt API key for the selected EMS connection.', 'wp-capture' ),
					'lists'   => array(),
				),
				500
			);
		}

		$credentials = array( 'api_key' => $decrypted_api_key );
		$lists       = $service->get_lists( $credentials );

		if ( false === $lists || is_wp_error( $lists ) ) {
			$error_message = is_wp_error( $lists ) ? $lists->get_error_message() : esc_html__( 'Could not retrieve lists from the EMS provider.', 'wp-capture' );
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $error_message, // Already escaped or a system message.
					'lists'   => array(),
				),
				200
			);
		}

		// Format for SelectControl: array of { label: string, value: string }.
		$formatted_lists = array();
		foreach ( $lists as $list_id => $list_name ) {
			if ( is_array( $list_name ) && isset( $list_name['id'] ) && isset( $list_name['name'] ) ) { // For services returning array of objects.
				$formatted_lists[] = array(
					'label' => $list_name['name'],
					'value' => $list_name['id'],
				);
			} elseif ( is_string( $list_name ) ) { // For services returning associative array id => name.
				$formatted_lists[] = array(
					'label' => $list_name,
					'value' => $list_id,
				);
			}
		}

		if ( empty( $formatted_lists ) ) {
			return new WP_REST_Response(
				array(
					'success' => true, // Success, but no lists found.
					'message' => esc_html__( 'No lists found for the configured EMS provider.', 'wp-capture' ),
					'lists'   => array(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'lists'   => $formatted_lists,
			),
			200
		);
	} catch ( Exception $e ) {
		error_log( 'WP Capture API Error (get-ems-lists): ' . $e->getMessage() );
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => esc_html__( 'An unexpected error occurred while fetching lists.', 'wp-capture' ),
				'lists'   => array(),
			),
			500
		);
	}
}
