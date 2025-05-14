<?php
/**
 * Plugin Name: Email Forms for WordPress (WP Capture)
 * Plugin URI: https://github.com/yourusername/wp-capture
 * Description: A simple and intuitive way to create email capture forms and integrate them with popular Email Marketing Services.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-capture
 * Domain Path: /languages
 *
 * @package WP_Capture
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_CAPTURE_VERSION', '1.0.0');
define('WP_CAPTURE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_CAPTURE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wp_capture() {
    // Set default options if needed
    $default_options = array(
        'global_default_ems' => '',
        'ems_connections' => array(),
    );
    
    // Only add options if they don't exist
    if (!get_option('wp_capture_options')) {
        add_option('wp_capture_options', $default_options);
    }
}
register_activation_hook(__FILE__, 'activate_wp_capture');

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_capture() {
    // Clean up if necessary
    // For V1, we'll keep the options in case the plugin is reactivated
}
register_deactivation_hook(__FILE__, 'deactivate_wp_capture');

/**
 * Begin execution of the plugin.
 */
function run_wp_capture() {
    // Load plugin dependencies
    require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture.php';
    
    // Initialize the plugin
    $plugin = new WP_Capture();
}
run_wp_capture();

if ( ! function_exists( 'wp_capture_register_blocks' ) ) {
    /**
     * Register Gutenberg blocks
     */
    function wp_capture_register_blocks() {
        register_block_type( __DIR__ . '/blocks/wp-capture-form/block.json' );
    }
    add_action( 'init', 'wp_capture_register_blocks' );
}

/**
 * Register a REST API endpoint to fetch EMS lists.
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'wp-capture/v1', '/get-ems-lists/', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'wp_capture_get_ems_lists_callback',
        'permission_callback' => function () {
            return current_user_can( 'edit_posts' );
        }
    ) );
} );

/**
 * Callback function for the /get-ems-lists REST API endpoint.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
 */
function wp_capture_get_ems_lists_callback( WP_REST_Request $request ) {
    $options = get_option( 'wp_capture_options' );
    $default_ems_id = isset( $options['global_default_ems'] ) ? $options['global_default_ems'] : null;

    if ( empty( $default_ems_id ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => __( 'No global default EMS configured.', 'wp-capture' ),
            'lists'   => array()
        ), 200 ); // Return 200 with error message for client to handle
    }

    $connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();
    if ( ! isset( $connections[$default_ems_id] ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => __( 'Default EMS connection not found.', 'wp-capture' ),
            'lists'   => array()
        ), 200 );
    }

    $connection_details = $connections[$default_ems_id];
    $provider_slug = $connection_details['provider'];
    // Ensure the main plugin class is available to get the service
    // This is a bit of a shortcut; ideally, we'd access this through a well-defined global or service locator.
    global $wp_capture_instance;
    if ( ! $wp_capture_instance && class_exists('WP_Capture') ) { // Fallback if not globally set
        $wp_capture_instance = new WP_Capture();
    }

    if ( ! $wp_capture_instance || ! method_exists( $wp_capture_instance, 'get_service' ) ) {
         return new WP_REST_Response( array(
            'success' => false,
            'message' => __( 'Could not access WP Capture service manager.', 'wp-capture' ),
            'lists'   => array()
        ), 500 );
    }

    $service = $wp_capture_instance->get_service( $provider_slug );

    if ( ! $service ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => sprintf( __( 'EMS Provider "%s" service not found.', 'wp-capture' ), esc_html( $provider_slug ) ),
            'lists'   => array()
        ), 500 );
    }

    try {
        $stored_api_key = $connection_details['api_key'] ?? ''; // API key is now plain text

        if ( empty( $stored_api_key ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => __( 'API key for the default EMS is missing.', 'wp-capture' ),
                'lists'   => array()
            ), 200 ); // Return 200 as client expects this for config issues
        }

        $credentials = array( 'api_key' => $stored_api_key ); // Use plain text API key
        $lists = $service->getLists( $credentials );

        if ( $lists === false || is_wp_error( $lists ) ) {
            $error_message = is_wp_error( $lists ) ? $lists->get_error_message() : __( 'Could not retrieve lists from the EMS provider.', 'wp-capture' );
             return new WP_REST_Response( array(
                'success' => false,
                'message' => $error_message,
                'lists'   => array()
            ), 200 );
        }

        // Format for SelectControl: array of { label: string, value: string }
        $formatted_lists = array();
        foreach ( $lists as $list_id => $list_name ) {
            if (is_array($list_name) && isset($list_name['id']) && isset($list_name['name'])) { // For services returning array of objects
                 $formatted_lists[] = array( 'label' => $list_name['name'], 'value' => $list_name['id'] );
            } else if (is_string($list_name)) { // For services returning associative array id => name
                 $formatted_lists[] = array( 'label' => $list_name, 'value' => $list_id );
            }
        }
        
        if (empty($formatted_lists)) {
             return new WP_REST_Response( array(
                'success' => true, // Success, but no lists found
                'message' => __( 'No lists found for the configured EMS provider.', 'wp-capture' ),
                'lists'   => array()
            ), 200 );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'lists'   => $formatted_lists
        ), 200 );

    } catch ( Exception $e ) {
        error_log('WP Capture API Error (get-ems-lists): ' . $e->getMessage());
        return new WP_REST_Response( array(
            'success' => false,
            'message' => __( 'An unexpected error occurred while fetching lists.', 'wp-capture' ),
            'lists'   => array()
        ), 500 );
    }
} 

/**
 * Enqueue frontend scripts and styles for the WP Capture Form block.
 */
function wp_capture_enqueue_frontend_scripts() {
    // Only enqueue if we're on a singular page and the block is present.
    wp_register_script(
        'wp-capture-form-frontend',
        WP_CAPTURE_PLUGIN_URL . 'assets/js/wp-capture-form-frontend.js',
        array(), // Dependencies
        WP_CAPTURE_VERSION,
        true // Load in footer
    );

    wp_localize_script(
        'wp-capture-form-frontend',
        'wpCaptureFrontend',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wp_capture_submit_nonce' ),
            'i18n'    => array(
                'emptyEmail'     => __( 'Email address cannot be empty.', 'wp-capture' ),
                'invalidEmail'   => __( 'Please enter a valid email address.', 'wp-capture' ),
                'successMessage' => __( 'Thank you for subscribing!', 'wp-capture' ),
                'errorMessage'   => __( 'An error occurred. Please try again.', 'wp-capture' ),
                'fetchError'     => __( 'A network error occurred. Please try again.', 'wp-capture' ),
                'configError'    => __( 'Form configuration error. Please contact site admin.', 'wp-capture' ),
            ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'wp_capture_enqueue_frontend_scripts' );

/**
 * AJAX handler for frontend form submission.
 */
function wp_capture_ajax_submit_form() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wp_capture_submit_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'wp-capture' ) ), 403 );
        return;
    }

    // Sanitize and retrieve email
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    if ( empty( $email ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid email address provided.', 'wp-capture' ) ) );
        return;
    }

    // Retrieve other data (will be used in later steps)
    $list_id = isset( $_POST['list_id'] ) ? sanitize_text_field( $_POST['list_id'] ) : '';
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : ''; // This is now the block's clientId

    if ( empty( $list_id ) ) {
        wp_send_json_error( array( 'message' => __( 'List ID is missing.', 'wp-capture' ) ) );
        return;
    }

    $options = get_option( 'wp_capture_options' );
    $default_ems_id = isset( $options['global_default_ems'] ) ? $options['global_default_ems'] : null;

    if ( empty( $default_ems_id ) ) {
        wp_send_json_error( array( 'message' => __( 'No global default EMS configured.', 'wp-capture' ) ) );
        return;
    }

    $connections = isset( $options['ems_connections'] ) ? $options['ems_connections'] : array();
    if ( ! isset( $connections[$default_ems_id] ) ) {
        wp_send_json_error( array( 'message' => __( 'Default EMS connection not found.', 'wp-capture' ) ) );
        return;
    }

    $connection_details = $connections[$default_ems_id];
    $provider_slug = $connection_details['provider'];
    $stored_api_key = $connection_details['api_key'] ?? ''; // API key is now plain text

    if ( empty( $stored_api_key ) ) {
        error_log('WP Capture: API key for ' . $provider_slug . ' is empty in options.'); // Changed log message slightly
        // User-facing message should be generic for security.
        wp_send_json_error( array( 'message' => __( 'Could not process subscription: API configuration error.', 'wp-capture' ) ) );
        return;
    }

    // Decrypt the API key
    if ( ! class_exists('WP_Capture_Encryption') || ! WP_Capture_Encryption::is_encryption_available() ) {
        error_log('WP Capture: Encryption services not available during form submission for ' . $provider_slug . '.');
        wp_send_json_error( array( 'message' => __( 'Could not process subscription: Security setup incomplete. Please contact site admin.', 'wp-capture' ) ) );
        return;
    }

    $decrypted_api_key = WP_Capture_Encryption::decrypt( $encrypted_api_key );

    if ( false === $decrypted_api_key ) {
        error_log('WP Capture: Failed to decrypt API key for ' . $provider_slug . ' during form submission.');
        // Admin should be notified about this separately if it persists.
        wp_send_json_error( array( 'message' => __( 'Could not process subscription: API credential retrieval failed. Please contact site admin.', 'wp-capture' ) ) );
        return;
    }
    
    if ( empty( $decrypted_api_key ) ) {
        error_log('WP Capture: Decrypted API key is empty for ' . $provider_slug . ' during form submission.');
        wp_send_json_error( array( 'message' => __( 'Could not process subscription: API configuration issue. Please contact site admin.', 'wp-capture' ) ) );
        return;
    }

    $credentials = array( 'api_key' => $decrypted_api_key );

    global $wp_capture_instance; // Access the global instance set up in run_wp_capture()
    if ( ! $wp_capture_instance && class_exists('WP_Capture') ) {
        $wp_capture_instance = new WP_Capture(); // Fallback initialization
    }

    if ( ! $wp_capture_instance || ! method_exists( $wp_capture_instance, 'get_service' ) ) {
        error_log('WP Capture: Could not access WP Capture service manager in AJAX handler.');
        wp_send_json_error( array( 'message' => __( 'Service manager not available.', 'wp-capture' ) ) );
        return;
    }

    $service = $wp_capture_instance->get_service( $provider_slug );

    if ( ! $service ) {
        error_log('WP Capture: EMS Service for ' . esc_html( $provider_slug ) . ' not found in AJAX handler.');
        wp_send_json_error( array( 'message' => sprintf( __( 'EMS Provider "%s" service not found.', 'wp-capture' ), esc_html( $provider_slug ) ) ) );
        return;
    }

    // Generic handling for any EMS provider that implements EmsServiceInterface
    if ( ! method_exists( $service, 'subscribeEmail' ) || ! method_exists( $service, 'getProviderName' ) ) {
        $actual_provider_slug = $provider_slug ?? 'unknown';
        error_log( "WP Capture: Service for {$actual_provider_slug} is invalid or missing required methods (subscribeEmail, getProviderName)." );
        wp_send_json_error( array( 'message' => sprintf( __( 'The email service (%s) is not correctly configured.', 'wp-capture' ), esc_html( $actual_provider_slug ) ) ) );
        return;
    }

    $provider_name = $service->getProviderName();

    $form_data_for_service = array(
        'post_id' => $post_id,
        'form_id' => $form_id,
        // Add any other relevant data from $formData if the service expects/supports it
    );

    try {
        $subscribed = $service->subscribeEmail( $credentials, $email, $list_id, $form_data_for_service );

        if ( $subscribed ) {
            // Step 25: Analytics - Record successful submission
            if ( ! empty( $form_id ) ) {
                $analytics_data = get_option( 'wp_capture_analytics_data', array() );

                if ( ! isset( $analytics_data[ $form_id ] ) ) {
                    $analytics_data[ $form_id ] = array(
                        'count' => 0,
                        'post_id' => null,
                        'post_title' => 'N/A', // Default title
                        'first_seen_timestamp' => current_time( 'timestamp' ),
                    );
                }
                $analytics_data[ $form_id ]['count']++;
                $analytics_data[ $form_id ]['last_submission_timestamp'] = current_time( 'timestamp' );

                if ( $post_id > 0 ) {
                    $analytics_data[ $form_id ]['post_id'] = $post_id;
                    $post_title = get_the_title( $post_id );
                    if ( ! empty( $post_title ) ) {
                        $analytics_data[ $form_id ]['post_title'] = $post_title;
                    } elseif (empty($analytics_data[ $form_id ]['post_title'])) {
                        // If title is empty and we don't have one stored, keep N/A or a placeholder
                        $analytics_data[ $form_id ]['post_title'] = 'Post ID: ' . $post_id; 
                    }
                }
                update_option( 'wp_capture_analytics_data', $analytics_data );
            }

            wp_send_json_success( array( 'message' => __( 'Successfully subscribed!', 'wp-capture' ) ) );
        } else {
            // The service method should ideally log specific API errors from the EMS provider itself.
            // This error is for when the service method itself returns false without throwing an exception.
            error_log( "WP Capture: {$provider_name}Service->subscribeEmail returned false for email: {$email}, list/form ID: {$list_id}." );
            wp_send_json_error( array( 'message' => sprintf( __( 'Could not subscribe with %s. Please try again later.', 'wp-capture' ), esc_html( $provider_name ) ) ) );
        }
    } catch ( Exception $e ) {
        error_log( "WP Capture: Exception during {$provider_name} subscription - " . $e->getMessage() );
        wp_send_json_error( array( 'message' => sprintf( __( 'An unexpected error occurred with %s. Please try again later.', 'wp-capture' ), esc_html( $provider_name ) ) ) );
    }
}
add_action( 'wp_ajax_wp_capture_submit', 'wp_capture_ajax_submit_form' );
add_action( 'wp_ajax_nopriv_wp_capture_submit', 'wp_capture_ajax_submit_form' );

