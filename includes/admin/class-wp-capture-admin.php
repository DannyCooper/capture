<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require handler classes
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-connections.php';
require_once plugin_dir_path( __FILE__ ) . 'class-wp-capture-admin-analytics.php';

class WP_Capture_Admin {
    /**
     * The main plugin instance.
     *
     * @var WP_Capture
     */
    private $plugin;

    /**
     * Store hooks for plugin admin pages.
     * @var array
     */
    private $admin_page_hooks = array();

    /**
     * Settings page handler.
     * @var WP_Capture_Admin_Settings
     */
    private $settings_handler;

    /**
     * Connections page handler.
     * @var WP_Capture_Admin_Connections
     */
    private $connections_handler;

    /**
     * Analytics page handler.
     * @var WP_Capture_Admin_Analytics
     */
    private $analytics_handler;

    /**
     * Initialize the class and set its properties.
     *
     * @param WP_Capture $plugin The main plugin instance.
     */
    public function __construct(WP_Capture $plugin) {
        $this->plugin = $plugin;

        // Instantiate handlers
        $this->settings_handler = new WP_Capture_Admin_Settings($this->plugin, $this);
        $this->connections_handler = new WP_Capture_Admin_Connections($this->plugin, $this);
        $this->analytics_handler = new WP_Capture_Admin_Analytics($this->plugin, $this);

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // AJAX actions are now registered within their respective handler classes (Connections)
        // Settings registration (register_setting) is now handled by WP_Capture_Admin_Settings via admin_init hook

        // Removed encryption notice hook
        // add_action( 'admin_notices', array( $this, 'encryption_unavailable_notice' ) );
    }

    /**
     * Enqueue admin-specific JavaScript and CSS.
     */
    public function enqueue_admin_scripts($hook_suffix) {
        // Ensure admin_page_hooks is populated if needed by other logic
        // For now, we collect all unique hooks from the menu registration.
        if (!in_array($hook_suffix, array_unique($this->admin_page_hooks))) {
            return;
        }

        wp_enqueue_script(
            'wp-capture-admin',
            WP_CAPTURE_PLUGIN_URL . 'assets/js/wp-capture-admin.js',
            array('jquery'),
            WP_CAPTURE_VERSION,
            true
        );
        
        $available_providers = $this->plugin->get_registered_services();
        
        wp_localize_script('wp-capture-admin', 'wpCaptureAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_capture_admin_nonce'),
            'providers' => $available_providers,
            'text' => array(
                'removeConnection' => __('Are you sure you want to remove this connection?', 'wp-capture'),
                'apiKey' => __('API Key', 'wp-capture'),
                'connectionName' => __('Connection Name (Optional)', 'wp-capture'),
                'remove' => __('Remove', 'wp-capture'),
                'provider' => __('Provider', 'wp-capture'),
                'selectProvider' => __('Please select a provider.', 'wp-capture'),
                'apiKeyRequired' => __('Please enter an API key.', 'wp-capture'),
                'testing' => __('Testing connection...', 'wp-capture'),
                'errorOccurred' => __('An error occurred. Please try again.', 'wp-capture'),
                'saved' => __('Connection saved successfully!', 'wp-capture'),
                'removing' => __('Removing connection...', 'wp-capture'),
                'removedSuccess' => __('Connection removed successfully.', 'wp-capture'),
                'removeError' => __('Could not remove connection. Please try again.', 'wp-capture'),
                'updating' => __('Updating connection...', 'wp-capture'),
                'updatedSuccess' => __('Connection updated successfully!', 'wp-capture')
            )
        ));
        
        wp_enqueue_style('wp-capture-admin-css', WP_CAPTURE_PLUGIN_URL . 'assets/css/wp-capture-admin.css', array(), WP_CAPTURE_VERSION);
    }

    /**
     * Add menu items to the admin menu.
     */
    public function add_plugin_admin_menu() {
        $this->admin_page_hooks[] = add_menu_page(
            __('WP Capture', 'wp-capture'),
            __('WP Capture', 'wp-capture'),
            'manage_options',
            'wp-capture',
            array($this->connections_handler, 'display_page'), // Default to Connections page
            'dashicons-email-alt',
            30
        );

        $this->admin_page_hooks[] = add_submenu_page(
            'wp-capture',
            __('Connections', 'wp-capture'),
            __('Connections', 'wp-capture'),
            'manage_options',
            'wp-capture', // Slug same as parent for default
            array($this->connections_handler, 'display_page')
        );

        $this->admin_page_hooks[] = add_submenu_page(
            'wp-capture',
            __('Settings', 'wp-capture'),
            __('Settings', 'wp-capture'),
            'manage_options',
            'wp-capture-settings',
            array($this->settings_handler, 'display_page')
        );

        $this->admin_page_hooks[] = add_submenu_page(
            'wp-capture',
            __('Analytics', 'wp-capture'),
            __('Analytics', 'wp-capture'),
            'manage_options',
            'wp-capture-analytics',
            array($this->analytics_handler, 'display_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     */
    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=wp-capture') . '">' . __('Connections', 'wp-capture') . '</a>', 
        );
        $settings_link[] = '<a href="' . admin_url('admin.php?page=wp-capture-settings') . '">' . __('Settings', 'wp-capture') . '</a>';
        return array_merge($settings_link, $links);
    }

    /**
     * Getter for the admin page hooks array if needed by handlers.
     */
    public function get_admin_page_hooks() {
        return $this->admin_page_hooks;
    }

    /**
     * Getter for the plugin instance if needed by handlers (though passed in constructor).
     */
    public function get_plugin() {
        return $this->plugin;
    }

} 