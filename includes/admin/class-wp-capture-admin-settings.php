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
    }
    
    /**
     * Callback for the main settings section description.
     */
    public function main_settings_section_callback() {
        echo '<p>' . esc_html__('Configure global settings for the WP Capture plugin.', 'wp-capture') . '</p>';
    }

    /**
     * Sanitize each setting field as needed.
     * This will also handle 'ems_connections' for now, as it's part of the same option.
     * Consider further refactoring if `ems_connections` sanitization becomes too complex here.
     */
    public function sanitize_options($input) {        
        return $input;
    }
} 