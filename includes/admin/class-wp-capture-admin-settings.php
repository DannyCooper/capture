<?php
/**
 * Handles the settings page and related functionality for WP Capture.
 *
 * @package    WP_Capture
 * @subpackage WP_Capture/includes/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_Capture_Admin_Settings {

    private $plugin;
    private $main_admin; // Reference to WP_Capture_Admin

    public function __construct(WP_Capture $plugin, WP_Capture_Admin $main_admin) {
        $this->plugin = $plugin;
        $this->main_admin = $main_admin;

        // Register hooks
        add_action('admin_init', array($this, 'register_settings_init'));
    }

    /**
     * Display the settings page.
     * This method will be called by the main admin class.
     */
    public function display_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Plugin Settings', 'wp-capture'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_capture_options_group');
                do_settings_sections('wp-capture-settings');
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
            'wp_capture_options_group',
            'wp_capture_options'
        );

        add_settings_section(
            'wp_capture_main_settings_section',
            __('Global Settings', 'wp-capture'),
            array($this, 'main_settings_section_callback'),
            'wp-capture-settings'
        );

        add_settings_field(
            'global_default_ems',
            __('Global Default EMS', 'wp-capture'),
            array($this, 'global_default_ems_field_callback'),
            'wp-capture-settings',
            'wp_capture_main_settings_section'
        );
    }
    
    /**
     * Callback for the main settings section description.
     */
    public function main_settings_section_callback() {
        echo '<p>' . esc_html__('Configure global settings for the WP Capture plugin, such as the default Email Marketing Service for forms.', 'wp-capture') . '</p>';
    }

    /**
     * Sanitize each setting field as needed.
     * This will also handle 'ems_connections' for now, as it's part of the same option.
     * Consider further refactoring if `ems_connections` sanitization becomes too complex here.
     */
    public function sanitize_options($input) {        
        return $input;
    }

    /**
     * Callback for rendering the Global Default EMS field.
     */
    public function global_default_ems_field_callback() {
        $options = get_option('wp_capture_options');
        $global_default_ems = isset($options['global_default_ems']) ? $options['global_default_ems'] : '';
        // Connections are needed for the dropdown. These are still part of 'wp_capture_options'.
        $connections = isset($options['ems_connections']) && is_array($options['ems_connections']) ? $options['ems_connections'] : array();
        
        echo '<select id="global_default_ems" name="wp_capture_options[global_default_ems]">';
        echo '<option value="">' . esc_html__('-- Select Default EMS --', 'wp-capture') . '</option>';

        if (!empty($connections)) {
            foreach ($connections as $key => $connection) {
                if (!empty($connection['provider']) && (isset($connection['api_key']) /*&& !empty($connection['api_key'])*/ )) {
                    // Use $this->plugin->get_service to get provider name
                    $service_instance = $this->plugin->get_service($connection['provider']);
                    $provider_name_label = $service_instance ? $service_instance->getProviderName() : $connection['provider'];
                    
                    $connection_label = '';
                    if (!empty($connection['name'])) {
                        $connection_label = esc_html($connection['name']) . ' (' . esc_html($provider_name_label) . ')';
                    } else {
                        $connection_label = esc_html($provider_name_label) . ' (' . esc_html(substr($key,0,8)). '...)';
                    }
                    echo '<option value="' . esc_attr($key) . '" ' . selected($global_default_ems, $key, false) . '>' . esc_html($connection_label) . '</option>';
                }
            }
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the default Email Marketing Service for all forms. Connections are managed on the "Connections" page.', 'wp-capture') . '</p>';
    }
} 