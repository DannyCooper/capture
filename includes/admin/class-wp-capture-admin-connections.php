<?php
/**
 * Handles the connections page and related AJAX functionality for WP Capture.
 *
 * @package    WP_Capture
 * @subpackage WP_Capture/includes/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_Capture_Admin_Connections {

	private $plugin;
	private $main_admin; // Reference to WP_Capture_Admin

	public function __construct( WP_Capture $plugin, WP_Capture_Admin $main_admin ) {
		$this->plugin     = $plugin;
		$this->main_admin = $main_admin;

		// Register AJAX hooks
		add_action( 'wp_ajax_wp_capture_save_test_connection', array( $this, 'ajax_save_test_connection' ) );
		add_action( 'wp_ajax_wp_capture_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_wp_capture_remove_connection', array( $this, 'ajax_remove_connection' ) );
		add_action( 'wp_ajax_wp_capture_update_connection', array( $this, 'ajax_update_connection' ) );
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
		$options     = get_option( 'wp_capture_options', array() );
		$connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();

		echo '<div id="wp-capture-connections-wrapper">';
		if ( ! empty( $connections ) ) {
			foreach ( $connections as $connection_id => $connection ) {
				$connection_name_display = isset( $connection['name'] ) && ! empty( $connection['name'] )
					? esc_html( $connection['name'] )
					: ( isset( $connection['provider'] ) ? esc_html( $connection['provider'] ) : __( 'Unnamed Connection', 'capture' ) );
				$provider_display        = isset( $connection['provider'] ) ? esc_html( $connection['provider'] ) : __( 'Unknown Provider', 'capture' );

				echo '<div class="wp-capture-connection-item" data-id="' . esc_attr( $connection_id ) . '">';
				echo '<h4>' . esc_html( $connection_name_display ) . ' (' . esc_html( $provider_display ) . ')</h4>';

				echo '<p><label for="wp-capture-name-' . esc_attr( $connection_id ) . '">' . __( 'Connection Name (Optional)', 'capture' ) . ':</label><br/>';
				$current_name_val = isset( $connection['name'] ) ? esc_attr( $connection['name'] ) : '';
				echo '<input id="wp-capture-name-' . esc_attr( $connection_id ) . '" type="text" class="wp-capture-connection-name-input" name="wp_capture_options[ems_connections][' . esc_attr( $connection_id ) . '][name]" value="' . $current_name_val . '" /></p>';

				if ( isset( $connection['provider'] ) ) {
					echo '<input type="hidden" class="wp-capture-provider-value" name="wp_capture_options[ems_connections][' . esc_attr( $connection_id ) . '][provider]" value="' . esc_attr( $connection['provider'] ) . '" />';
				}

				echo '<p><label for="wp-capture-api-key-' . esc_attr( $connection_id ) . '">' . __( 'API Key', 'capture' ) . ':</label><br/>';

				$stored_encrypted_key = isset( $connection['api_key'] ) ? $connection['api_key'] : '';
				$placeholder_text     = __( 'Enter API key', 'capture' );
				$key_display_html     = '';

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
						$key_display_html   = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'Current key:', 'capture' ) . ' ' . $masked_key_preview . '</em></p>';
						$placeholder_text   = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					} elseif ( $encryption_service ) {
						// Stored key was not empty, but decrypted display key is empty.
						// This implies an issue with decryption if OpenSSL was active, or empty key was stored post-encryption (unlikely).
						$key_display_html = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'Current key: Set (preview unavailable)', 'capture' ) . '</em></p>';
						$placeholder_text = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					} else {
						// Encryption service itself is not available.
						$key_display_html = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'Current key: Set (encryption service unavailable)', 'capture' ) . '</em></p>';
						$placeholder_text = __( 'Enter new key to change, or leave empty to keep current', 'capture' );
					}
				} else {
					// If no key is set
					$key_display_html = '<p style="margin-top: 0; margin-bottom: 5px;"><em>' . esc_html__( 'No API key set.', 'capture' ) . '</em></p>';
				}

				echo $key_display_html; // Output the determined key display HTML

				echo '<input id="wp-capture-api-key-' . esc_attr( $connection_id ) . '" type="text" class="wp-capture-api-key-input" name="wp_capture_options[ems_connections][' . esc_attr( $connection_id ) . '][api_key]" value="" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off" style="width: 100%;" />';

				echo '<div class="wp-capture-connection-actions">';
				echo '<button type="button" class="button button-primary wp-capture-update-connection" data-id="' . esc_attr( $connection_id ) . '">' . __( 'Update', 'capture' ) . '</button> ';
				echo '<button type="button" class="button wp-capture-test-connection" data-id="' . esc_attr( $connection_id ) . '" data-provider="' . esc_attr( isset( $connection['provider'] ) ? $connection['provider'] : '' ) . '">' . __( 'Test Connection', 'capture' ) . '</button> ';
				echo '<button type="button" class="button wp-capture-remove-connection" data-id="' . esc_attr( $connection_id ) . '">' . __( 'Remove', 'capture' ) . '</button>';
				echo '</div>';

				echo '<div class="wp-capture-connection-status"></div>';
				echo '</div>';
			}
		}
		echo '</div>';

		echo '<button type="button" class="button button-secondary" id="wp-capture-add-new-connection">' . __( 'Add New Connection', 'capture' ) . '</button>';

		echo '<script type="text/html" id="wp-capture-connection-template">';
		echo '<div class="wp-capture-connection-item is-new" data-id="NEW_KEY_PLACEHOLDER">';
		echo '<h4>' . __( 'New Connection', 'capture' ) . '</h4>';

		echo '<p><label for="wp-capture-provider-NEW_KEY_PLACEHOLDER">' . __( 'Provider', 'capture' ) . ':</label><br/>';
		echo '<select id="wp-capture-provider-NEW_KEY_PLACEHOLDER" name="wp_capture_options[ems_connections][NEW_KEY_PLACEHOLDER][provider]" class="wp-capture-provider-select">';
		echo '<option value="">' . __( '-- Select Provider --', 'capture' ) . '</option>';

		$available_providers = $this->plugin->get_registered_services();
		foreach ( $available_providers as $provider_key => $provider_label ) {
			echo '<option value="' . esc_attr( $provider_key ) . '">' . esc_html( $provider_label ) . '</option>';
		}
		echo '</select></p>';
		echo '<p><label for="wp-capture-name-NEW_KEY_PLACEHOLDER">' . __( 'Connection Name (Optional)', 'capture' ) . ':</label><br/>';
		echo '<input id="wp-capture-name-NEW_KEY_PLACEHOLDER" type="text" name="wp_capture_options[ems_connections][NEW_KEY_PLACEHOLDER][name]" placeholder="' . __( 'e.g., Newsletter Opt-ins', 'capture' ) . '" /></p>';
		echo '<p><label for="wp-capture-api-key-NEW_KEY_PLACEHOLDER">' . __( 'API Key', 'capture' ) . ':</label><br/>';
		echo '<input id="wp-capture-api-key-NEW_KEY_PLACEHOLDER" type="text" class="wp-capture-api-key-input" name="wp_capture_options[ems_connections][NEW_KEY_PLACEHOLDER][api_key]" /></p>';
		echo '<div class="wp-capture-connection-actions">';
		echo '<button type="button" class="button wp-capture-save-test-connection" data-id="NEW_KEY_PLACEHOLDER">' . __( 'Save & Test', 'capture' ) . '</button> ';
		echo '<button type="button" class="button wp-capture-remove-connection" data-id="NEW_KEY_PLACEHOLDER">' . __( 'Remove', 'capture' ) . '</button>';
		echo '</div>';

		echo '<div class="wp-capture-connection-status"></div>';
		echo '</div>';
		echo '</script>';
	}

	/**
	 * AJAX handler to save and test a single EMS connection.
	 */
	public function ajax_save_test_connection() {
		check_ajax_referer( 'wp_capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		$provider      = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$raw_api_key   = isset( $_POST['api_key'] ) ? trim( wp_unslash( $_POST['api_key'] ) ) : '';
		$name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( empty( $provider ) || empty( $raw_api_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider and API key are required.', 'capture' ) ) );
			return;
		}

		$service = $this->plugin->get_service( $provider );
		if ( ! $service ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
			return;
		}

		// Use the raw API key for validation
		$credentials_for_validation = array( 'api_key' => $raw_api_key );
		$valid                      = $service->validate_credentials( $credentials_for_validation );

		if ( ! $valid ) {
			wp_send_json_error( array( 'message' => __( 'Invalid API credentials. Please check your API key and try again.', 'capture' ) ) );
			return;
		}

		// Encrypt the API key before saving
		$encryption_service = $this->plugin->get_encryption_service();
		if ( ! $encryption_service ) {
			// This case should ideally be handled by an admin notice, but good to have a fallback
			wp_send_json_error( array( 'message' => __( 'Encryption service is not available. Cannot save connection.', 'capture' ) ) );
			return;
		}
		$api_key_to_save = $encryption_service->encrypt( $raw_api_key );
		if ( $api_key_to_save === $raw_api_key && extension_loaded( 'openssl' ) && Encryption::is_properly_configured() ) {
			// If encryption returned the same value and OpenSSL is loaded and keys are configured,
			// it implies an encryption failure for some other reason, or the key was empty.
			// We already check for empty raw_api_key, so this is an unexpected state.
			error_log( 'WP Capture: API Key encryption failed unexpectedly for new connection.' );
			wp_send_json_error( array( 'message' => __( 'Could not securely save the API key. Encryption failed.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'wp_capture_options', array() );
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

		update_option( 'wp_capture_options', $options );

		wp_send_json_success(
			array(
				'message'       => __( 'Connection saved and tested successfully!', 'capture' ),
				'connection_id' => $connection_id,
			)
		);
	}

	/**
	 * AJAX handler to test an existing EMS connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'wp_capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing connection ID.', 'capture' ) ) );
			return;
		}

		$options     = get_option( 'wp_capture_options', array() );
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
			wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
			return;
		}

		// Decrypt the stored API key for validation
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
		check_ajax_referer( 'wp_capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$connection_id = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';

		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection ID.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'wp_capture_options', array() );

		if ( isset( $options['ems_connections'][ $connection_id ] ) ) {
			unset( $options['ems_connections'][ $connection_id ] );

			update_option( 'wp_capture_options', $options );
			wp_send_json_success( array( 'message' => __( 'Connection removed successfully.', 'capture' ) ) );
		} else {
			$available_keys = ! empty( $options['ems_connections'] ) ? array_keys( $options['ems_connections'] ) : array();
			$debug_message  = sprintf(
				__( 'Connection with ID "%1$s" not found. Available connection IDs: %2$s.', 'capture' ),
				$connection_id,
				implode( ', ', $available_keys )
			);
			wp_send_json_error(
				array(
					'message' => $debug_message,
				)
			);
		}
	}

	/**
	 * AJAX handler to update an existing EMS connection.
	 */
	public function ajax_update_connection() {
		check_ajax_referer( 'wp_capture_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'capture' ) ) );
			return;
		}

		$submitted_raw_api_key = isset( $_POST['api_key'] ) ? trim( wp_unslash( $_POST['api_key'] ) ) : '';
		$connection_id         = isset( $_POST['connection_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_id'] ) ) : '';
		$submitted_name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( empty( $connection_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid connection ID.', 'capture' ) ) );
			return;
		}

		$options = get_option( 'wp_capture_options', array() );
		if ( ! isset( $options['ems_connections'] ) || ! isset( $options['ems_connections'][ $connection_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Connection not found.', 'capture' ) ) );
			return;
		}

		$current_connection = $options['ems_connections'][ $connection_id ];
		$provider           = $current_connection['provider'];
		$api_key_to_save    = $current_connection['api_key']; // Keep current encrypted key by default

		if ( ! empty( $submitted_raw_api_key ) ) {
			$service = $this->plugin->get_service( $provider );
			if ( ! $service ) {
				wp_send_json_error( array( 'message' => sprintf( __( 'Provider "%s" is not supported.', 'capture' ), esc_html( $provider ) ) ) );
				return;
			}

			// Use the raw submitted API key for validation
			$credentials_for_validation = array( 'api_key' => $submitted_raw_api_key );
			$valid                      = $service->validate_credentials( $credentials_for_validation );

			if ( ! $valid ) {
				wp_send_json_error( array( 'message' => __( 'Invalid API credentials for the new API key. Please check and try again.', 'capture' ) ) );
				return;
			}

			// Encrypt the new API key before saving
			$encryption_service = $this->plugin->get_encryption_service();
			if ( ! $encryption_service ) {
				wp_send_json_error( array( 'message' => __( 'Encryption service is not available. Cannot update connection.', 'capture' ) ) );
				return;
			}
			$api_key_to_save = $encryption_service->encrypt( $submitted_raw_api_key );
			if ( $api_key_to_save === $submitted_raw_api_key && ! empty( $submitted_raw_api_key ) && extension_loaded( 'openssl' ) && Encryption::is_properly_configured() ) {
				error_log( 'WP Capture: API Key encryption failed unexpectedly during update.' );
				wp_send_json_error( array( 'message' => __( 'Could not securely save the new API key. Encryption failed.', 'capture' ) ) );
				return;
			}
		}

		$options['ems_connections'][ $connection_id ]['name']    = $submitted_name;
		$options['ems_connections'][ $connection_id ]['api_key'] = $api_key_to_save;

		update_option( 'wp_capture_options', $options );

		wp_send_json_success(
			array(
				'message'       => __( 'Connection updated successfully!', 'capture' ),
				'connection_id' => $connection_id,
				'new_name'      => $submitted_name,
			)
		);
	}
}
