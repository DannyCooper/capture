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
    // Make the instance globally accessible
    $GLOBALS['wp_capture_instance'] = $plugin;
}
run_wp_capture();