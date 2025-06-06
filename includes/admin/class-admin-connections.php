<?php
/**
 * Handles the connections page and related AJAX functionality for WP Capture.
 *
 * @package    Capture
 * @subpackage Capture/includes/admin
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin_Connections class.
 */
class Admin_Connections {

	/**
	 * The plugin instance.
	 *
	 * @var Core
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Core $plugin The plugin instance.
	 */
	public function __construct( Core $plugin ) {
		$this->plugin = $plugin;

		// Register AJAX hooks.
		add_action( 'wp_ajax_capture_save_test_connection', array( $this, 'ajax_save_test_connection' ) );
		add_action( 'wp_ajax_capture_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_capture_remove_connection', array( $this, 'ajax_remove_connection' ) );
		add_action( 'wp_ajax_capture_update_connection', array( $this, 'ajax_update_connection' ) );
	}

	/**
	 * Render the Connections page.
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'EMS Connections', 'capture' ); ?></h1>
			<p><?php esc_html_e( 'Manage your connections to Email Marketing Services. Add new connections and test their validity.', 'capture' ); ?></p>
			<?php $this->ems_connections_ui_callback(); ?>
		</div>
		<?php
	}

	/**
	 * Callback for EMS Connections UI field.
	 * This renders the main interface for managing connections.
	 */
	public function ems_connections_ui_callback() {
		$options     = get_option( 'capture_options', array() );
		$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();

		echo '<div id="capture-connections-wrapper">';
		if ( ! empty( $connections ) ) {
			foreach ( $connections as $connection_id => $connection ) {
				$connection_name_display = isset( $connection['name'] ) && ! empty( $connection['name'] )
					? esc_html( $connection['name'] )
					: ( isset( $connection['provider'] ) ? esc_html( $connection['provider'] ) : __( 'Unnamed Connection', 'capture' ) );
				$provider_display        = isset( $connection['provider'] ) ? esc_html( $connection['provider'] ) : __( 'Unknown Provider', 'capture' );

				echo '<div class="capture-connection-item" data-id="' . esc_attr( $connection_id ) . '">';
				echo '<h4>' . esc_html( $connection_name_display ) . ' (' . esc_html( $provider_display ) . ')</h4>';

				echo '<p><label for="capture-name-' . esc_attr( $connection_id ) . '">' . esc_html__( 'Connection Name (Optional)', 'capture' ) . ':</label><br/>';
				$current_name_val = isset( $connection['name'] ) ? esc_attr( $connection['name'] ) : '';
				echo '<input placeholder="' . esc_attr( $connection_id ) . '" id="capture-name-' . esc_attr( $connection_id ) . '" type="text" class="capture-connection-name-input" name="capture_options[ems_connections][' . esc_attr( $connection_id ) . '][name]" value="' . esc_attr( $current_name_val ) . '" /></p>';

				if ( isset( $connection['provider'] ) ) {
					echo '<input type="hidden" class="capture-provider-value" name="capture_options[ems_connections][' . esc_attr( $connection_id ) . '][provider]" value="' . esc_attr( $connection['provider'] ) . '" />';
				}

				$stored_encrypted_key = isset( $connection['api_key'] ) ? $connection['api_key'] : '';
				$placeholder_text     = __( 'Enter API key', 'capture' );
				echo '<p><label for="capture-api-key-' . esc_attr( $connection_id ) . '">' . esc_html__( 'API Key', 'capture' ) . ':</label>';
				$key_display_html = '';

				if ( ! empty( $stored_encrypted_key ) ) {
					$encryption_service        = $this->plugin->get_encryption_service();
					$decrypted_key_for_display = '';

					if ( $encryption_service ) {
						$decrypted_key_for_display = $encryption_service->decrypt( $stored_encrypted_key );
					}

					if ( ! empty( $decrypted_key_for_display ) ) {
						// If OpenSSL is off, decrypt returns original, so this will mask the stored plaintext.
						// If OpenSSL is on and decryption worked, this will mask the decrypted plaintext.
						// If OpenSSL is on but decryption failed and returned original (encrypted) string, this will mask the encrypted string.
						// We rely on the decrypt method's behavior to either give plaintext or the original if issues.
						$masked_key_preview = '••••••••••••' . substr( esc_html( $decrypted_key_for_display ), -4 );
						$key_display_html   = '<span style="margin-top: 0; margin-bottom: 5px; display: block;"><em>' . esc_html__( 'Current key:', 'capture' ) . ' ' . $masked_key_preview . '</em></span>';
						$placeholder_text   = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					} elseif ( $encryption_service ) {
						// Stored key was not empty, but decrypted display key is empty.
						// This implies an issue with decryption if OpenSSL was active, or empty key was stored post-encryption (unlikely).
						$key_display_html = '<span style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'Current key: Set (preview unavailable)', 'capture' ) . '</em></span>';
						$placeholder_text = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					} else {
						// Encryption service itself is not available.
						$key_display_html = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'Current key: Set (encryption service unavailable)', 'capture' ) . '</em></p>';
						$placeholder_text = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					}
				} else {
					// If no key is set.
					$key_display_html = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'No API key set.', 'capture' ) . '</em></p>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contains safe HTML from above.
				echo $key_display_html;

				echo '<input id="capture-api-key-' . esc_attr( $connection_id ) . '" type="text" class="capture-api-key-input" name="capture_options[ems_connections][' . esc_attr( $connection_id ) . '][api_key]" value="" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off" style="width: 100%;" />';
				echo '</p>';

				echo '<div class="capture-connection-actions">';
				echo '<button type="button" class="button button-primary capture-update-connection" data-id="' . esc_attr( $connection_id ) . '">' . esc_html__( 'Update', 'capture' ) . '</button> ';
				echo '<button type="button" class="button capture-test-connection" data-id="' . esc_attr( $connection_id ) . '" data-provider="' . esc_attr( isset( $connection['provider'] ) ? $connection['provider'] : '' ) . '">' . esc_html__( 'Test Connection', 'capture' ) . '</button> ';
				echo '<button type="button" class="button capture-remove-connection" data-id="' . esc_attr( $connection_id ) . '">' . esc_html__( 'Remove', 'capture' ) . '</button>';
				echo '</div>';

				echo '<div class="capture-connection-status"></div>';
				echo '</div>';
			}
		}
		echo '</div>';

		echo '<button type="button" class="button button-secondary" id="capture-add-new-connection">' . esc_html__( 'Add New Connection', 'capture' ) . '</button>';

		echo '<script type="text/html" id="capture-connection-template">';
		echo '<div class="capture-connection-item is-new" data-id="NEW_KEY_PLACEHOLDER">';
		echo '<h4>' . esc_html__( 'New Connection', 'capture' ) . '</h4>';

		echo '<p><label for="capture-provider-NEW_KEY_PLACEHOLDER">' . esc_html__( 'Provider', 'capture' ) . ':</label><br/>';
		echo '<select id="capture-provider-NEW_KEY_PLACEHOLDER" name="capture_options[ems_connections][NEW_KEY_PLACEHOLDER][provider]" class="capture-provider-select">';
		echo '<option value="">' . esc_html__( '-- Select Provider --', 'capture' ) . '</option>';

		$available_providers = $this->plugin->get_registered_services();
		foreach ( $available_providers as $providor ) {
			echo '<option value="' . esc_attr( $providor['key'] ) . '">' . esc_html( $providor['name'] ) . '</option>';
		}
		echo '</select></p>';
		echo '<p><label for="capture-name-NEW_KEY_PLACEHOLDER">' . esc_html__( 'Connection Name (Optional)', 'capture' ) . ':</label><br/>';
		echo '<input id="capture-name-NEW_KEY_PLACEHOLDER" type="text" name="capture_options[ems_connections][NEW_KEY_PLACEHOLDER][name]" placeholder="' . esc_attr__( 'e.g., Newsletter Opt-ins', 'capture' ) . '" /></p>';
		echo '<p><label for="capture-api-key-NEW_KEY_PLACEHOLDER">' . esc_html__( 'API Key', 'capture' ) . ':</label><br/>';
		echo '<input id="capture-api-key-NEW_KEY_PLACEHOLDER" type="text" class="capture-api-key-input" name="capture_options[ems_connections][NEW_KEY_PLACEHOLDER][api_key]" /></p>';
		echo '<div class="capture-connection-actions">';
		echo '<button type="button" class="button capture-save-test-connection" data-id="NEW_KEY_PLACEHOLDER">' . esc_html__( 'Save & Test', 'capture' ) . '</button> ';
		echo '<button type="button" class="button capture-remove-connection" data-id="NEW_KEY_PLACEHOLDER">' . esc_html__( 'Remove', 'capture' ) . '</button>';
		echo '</div>';

		echo '<div class="capture-connection-status"></div>';
		echo '</div>';
		echo '</script>';
	}

	/**
	 * AJAX handler to save and test a single EMS connection.
	 */
	public function ajax_save_test_connection() {
		\check_ajax_referer( 'capture_admin_nonce', 'nonce' );

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( array( 'message' => \__( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		$provider      = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- API keys should not be sanitized as they may contain special characters.
		$raw_api_key = isset( $_POST['api_key'] ) ? trim( wp_unslash( $_POST['api_key'] ) ) : '';
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( empty( $provider ) || empty( $raw_api_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider and API key are required.', 'capture' ) ) );
			return;
		}

		$service = $this->plugin->get_service( $provider );
		if ( ! $service ) {
			/* translators: %s: Provider name */
			wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
			return;
		}

		// Use the raw API key for validation.
		$credentials_for_validation = array( 'api_key' => $raw_api_key );
		$valid                      = $service->validate_credentials( $credentials_for_validation );

		if ( ! $valid ) {
			wp_send_json_error( array( 'message' => __( 'Invalid API credentials. Please check your API key and try again.', 'capture' ) ) );
			return;
		}

		// Encrypt the API key before saving.
		$encryption_service = $this->plugin->get_encryption_service();
		if ( ! $encryption_service ) {
			// This case should ideally be handled by an admin notice, but good to have a fallback.
			wp_send_json_error( array( 'message' => __( 'Encryption service is not available. Cannot save connection.', 'capture' ) ) );
			return;
		}
		$api_key_to_save = $encryption_service->encrypt( $raw_api_key );
		if ( $api_key_to_save === $raw_api_key && extension_loaded( 'openssl' ) && \Capture\Encryption::is_properly_configured() ) {
			// If encryption returned the same value and OpenSSL is loaded and keys are configured,
			// it implies an encryption failure for some other reason, or the key was empty.
			// We already check for empty raw_api_key, so this is an unexpected state.
			error_log( 'WP Capture: API Key encryption failed unexpectedly for new connection.' );
			wp_send_json_error( array( 'message' => __( 'Could not securely save the API key. Encryption failed.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'capture_options', array() );
		if ( ! isset( $options['ems_connections'] ) ) {
			$options['ems_connections'] = array();
		}

		if ( strpos( $connection_id, 'new_' ) === 0 || empty( $connection_id ) ) {
			$connection_id = 'c_' . time();
		}

		$options['ems_connections'][ $connection_id ] = array(
			'provider' => $provider,
			'api_key'  => $api_key_to_save,
			'name'     => $name,
		);

		$update_result = update_option( 'capture_options', $options );

		if ( $update_result ) {
			wp_send_json_success(
				array(
					'message'       => __( 'Connection saved and tested successfully!', 'capture' ),
					'connection_id' => $connection_id,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Connection was validated but failed to save to database.', 'capture' ),
				)
			);
		}
	}

	/**
	 * AJAX handler to test an existing EMS connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing connection ID.', 'capture' ) ) );
			return;
		}

		$options     = get_option( 'capture_options', array() );
		$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();

		if ( ! isset( $connections[ $connection_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Connection not found.', 'capture' ) ) );
			return;
		}

		$connection     = $connections[ $connection_id ];
		$provider       = isset( $connection['provider'] ) ? $connection['provider'] : '';
		$stored_api_key = isset( $connection['api_key'] ) ? $connection['api_key'] : '';

		if ( empty( $provider ) || empty( $stored_api_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection data. Provider or API key is missing.', 'capture' ) ) );
			return;
		}

		$service = $this->plugin->get_service( $provider );
		if ( ! $service ) {
			/* translators: %s: Provider name */
			wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
			return;
		}

		// Decrypt the stored API key for validation.
		$encryption_service = $this->plugin->get_encryption_service();
		if ( ! $encryption_service ) {
			wp_send_json_error( array( 'message' => __( 'Encryption service is not available. Cannot test connection.', 'capture' ) ) );
			return;
		}
		$decrypted_api_key = $encryption_service->decrypt( $stored_api_key );
		// If decryption returns the same as stored and openssl is on and keys are configured, it might mean it was stored as plaintext
		// or decryption failed silently (though our modified decrypt logs errors).
		// We proceed with the decrypted_api_key regardless, as per user's preference for encrypt/decrypt behavior.

		$credentials = array( 'api_key' => $decrypted_api_key );
		$valid       = $service->validate_credentials( $credentials );

		if ( $valid ) {
			wp_send_json_success(
				array(
					'message'       => __( 'Connection successfully tested!', 'capture' ),
					'connection_id' => $connection_id,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message'       => __( 'Connection test failed. Please check your API key and try again.', 'capture' ),
					'connection_id' => $connection_id,
				)
			);
		}
	}

	/**
	 * AJAX handler to remove an EMS connection.
	 */
	public function ajax_remove_connection() {
		check_ajax_referer( 'capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';

		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection ID.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'capture_options', array() );

		if ( isset( $options['ems_connections'][ $connection_id ] ) ) {
			unset( $options['ems_connections'][ $connection_id ] );

			$update_result = update_option( 'capture_options', $options );

			if ( $update_result ) {
				wp_send_json_success(
					array(
						'message' => __( 'Connection removed successfully.', 'capture' ),
					)
				);
			} else {
				wp_send_json_error(
					array(
						'message' => __( 'Connection was removed from memory but failed to save to database.', 'capture' ),
					)
				);
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Connection not found.', 'capture' ) ) );
		}
	}

	/**
	 * AJAX handler to update an existing EMS connection.
	 */
	public function ajax_update_connection() {
		check_ajax_referer( 'capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- API keys should not be sanitized as they may contain special characters.
		$submitted_raw_api_key = isset( $_POST['api_key'] ) ? trim( wp_unslash( $_POST['api_key'] ) ) : '';
		$connection_id         = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		$submitted_name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection ID.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'capture_options', array() );
		if ( ! isset( $options['ems_connections'] ) || ! isset( $options['ems_connections'][ $connection_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Connection not found.', 'capture' ) ) );
			return;
		}

		$current_connection = $options['ems_connections'][ $connection_id ];
		$provider           = $current_connection['provider'];
		$api_key_to_save    = $current_connection['api_key']; // Keep current encrypted key by default.

		if ( ! empty( $submitted_raw_api_key ) ) {
			$service = $this->plugin->get_service( $provider );
			if ( ! $service ) {
				/* translators: %s: Provider name */
				wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
				return;
			}

			// Use the raw submitted API key for validation.
			$credentials_for_validation = array( 'api_key' => $submitted_raw_api_key );
			$valid                      = $service->validate_credentials( $credentials_for_validation );

			if ( ! $valid ) {
				wp_send_json_error( array( 'message' => __( 'Invalid API credentials for the new API key. Please check and try again.', 'capture' ) ) );
				return;
			}

			// Encrypt the new API key before saving.
			$encryption_service = $this->plugin->get_encryption_service();
			if ( ! $encryption_service ) {
				wp_send_json_error( array( 'message' => __( 'Encryption service is not available. Cannot update connection.', 'capture' ) ) );
				return;
			}
			$api_key_to_save = $encryption_service->encrypt( $submitted_raw_api_key );
			if ( $api_key_to_save === $submitted_raw_api_key && ! empty( $submitted_raw_api_key ) && extension_loaded( 'openssl' ) && \Capture\Encryption::is_properly_configured() ) {
				error_log( 'WP Capture: API Key encryption failed unexpectedly during update.' );
				wp_send_json_error( array( 'message' => __( 'Could not securely save the new API key. Encryption failed.', 'capture' ) ) );
				return;
			}
		}

		$options['ems_connections'][ $connection_id ]['name']    = $submitted_name;
		$options['ems_connections'][ $connection_id ]['api_key'] = $api_key_to_save;

		if ( $current_connection['name'] === $submitted_name && $current_connection['api_key'] === $api_key_to_save ) {
			wp_send_json_error( array( 'message' => __( 'No changes were made to the connection.', 'capture' ) ) );
			return;
		}

		$update_result = update_option( 'capture_options', $options );

		if ( $update_result ) {
			wp_send_json_success(
				array(
					'message'       => __( 'Connection updated successfully!', 'capture' ),
					'connection_id' => $connection_id,
					'new_name'      => $submitted_name,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Connection data was valid but failed to save to database.', 'capture' ),
				)
			);
		}
	}
}
