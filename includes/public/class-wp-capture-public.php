<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

class WP_Capture_Public {
    /**
     * The main plugin instance.
     *
     * @var WP_Capture
     */
    private $plugin;

    /**
     * Initialize the class and set its properties.
     *
     * @param WP_Capture $plugin The main plugin instance.
     */
    public function __construct(WP_Capture $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wp-capture',
            WP_CAPTURE_PLUGIN_URL . 'assets/css/wp-capture-public.css',
            array(),
            WP_CAPTURE_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-capture',
            WP_CAPTURE_PLUGIN_URL . 'assets/js/wp-capture-public.js',
            array('jquery'),
            WP_CAPTURE_VERSION,
            true
        );

        // Localize the script with new data
        wp_localize_script('wp-capture', 'wpCapture', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_capture_form_submission'), // Changed nonce name for clarity
        ));
    }
    
    /**
     * Handle the AJAX form submission.
     */
    public function handle_form_submission() {
        // Verify nonce
        check_ajax_referer('wp_capture_form_submission', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : null;
        $list_id = isset($_POST['list_id']) ? sanitize_text_field($_POST['list_id']) : null;
        // $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : null; // Will be used later
        // $block_id = isset($_POST['block_id']) ? sanitize_text_field($_POST['block_id']) : null; // Will be used later

        if (empty($email) || !is_email($email) || empty($list_id)) {
            wp_send_json_error(array('message' => __('Invalid input. Please check your email and selected list.', 'wp-capture')));
            return;
        }

        $options = get_option('wp_capture_options');
        $global_default_ems_key = isset($options['global_default_ems']) ? $options['global_default_ems'] : null;
        $ems_connections = isset($options['ems_connections']) ? $options['ems_connections'] : array();

        if (empty($global_default_ems_key) || !isset($ems_connections[$global_default_ems_key])) {
            wp_send_json_error(array('message' => __('Global default EMS not configured or connection not found.', 'wp-capture')));
            return;
        }
        
        $active_connection_details = $ems_connections[$global_default_ems_key];
        $provider_key = $active_connection_details['provider'] ?? null;
        $credentials = $active_connection_details; // Pass all connection details as credentials

        if (empty($provider_key)) {
            wp_send_json_error(array('message' => __('EMS provider not found in connection details.', 'wp-capture')));
            return;
        }
        
        $ems_service = $this->plugin->get_ems_service($provider_key);

        if (!$ems_service) {
            wp_send_json_error(array('message' => __('EMS service not available.', 'wp-capture')));
            return;
        }

        try {
            $subscribed = $ems_service->subscribeEmail($credentials, $email, $list_id);
            if ($subscribed) {
                // Analytics increment will go here in a later step
                wp_send_json_success(array('message' => __('Successfully subscribed!', 'wp-capture')));
            } else {
                wp_send_json_error(array('message' => __('Could not subscribe. Please try again.', 'wp-capture')));
            }
        } catch (Exception $e) {
            // Log the exception message for admin
            error_log('WP Capture EMS Subscription Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => __('An unexpected error occurred. Please try again later.', 'wp-capture')));
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }
} 