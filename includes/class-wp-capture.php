<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

class WP_Capture {
    /**
     * The registered EMS services.
     *
     * @var array
     */
    private $ems_services = array();

    /**
     * The encryption service instance.
     *
     * @since 1.0.0 // Update if version changes
     * @var Encryption|null
     */
    private $encryption_service = null;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->load_dependencies();
        $this->encryption_service = new Encryption(); // Instantiate your Encryption class
        $this->register_ems_services();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Load EMS service interfaces and implementations
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/interface-ems-service.php';
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/class-mailchimp-service.php';
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/class-convertkit-service.php';

        // Load your custom Encryption class
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-encryption.php';
        
        // Load admin and public classes
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/admin/class-wp-capture-admin.php';
        require_once WP_CAPTURE_PLUGIN_DIR . 'includes/public/class-wp-capture-public.php';
    }

    /**
     * Register available EMS services.
     */
    private function register_ems_services() {
        $mailchimp_service = new MailchimpService();
        $convertkit_service = new ConvertKitService();

        $this->ems_services = array(
            'mailchimp' => $mailchimp_service,
            'convertkit' => $convertkit_service,
        );

        // Allow other plugins/themes to register their own EMS services
        $this->ems_services = apply_filters('wp_capture_register_ems_services', $this->ems_services);
    }

    /**
     * Get a registered EMS service by provider key.
     *
     * @param string $provider_key The provider key.
     * @return EmsServiceInterface|null The EMS service implementation or null if not found.
     */
    public function get_service($provider_key) {
        return isset($this->ems_services[$provider_key]) ? $this->ems_services[$provider_key] : null;
    }
    
    /**
     * Get all registered EMS services with their display names.
     *
     * @return array Associative array of provider keys and display names.
     */
    public function get_registered_services() {
        $providers = array();
        
        foreach ($this->ems_services as $key => $service) {
            $providers[$key] = $service->getProviderName();
        }
        
        return $providers;
    }

    /**
     * Get the encryption service instance.
     *
     * @since 1.0.0 // Update if version changes
     * @return Encryption|null The encryption service instance.
     */
    public function get_encryption_service() {
        return $this->encryption_service;
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Capture_Admin($this);
        
        // Add menu item
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        
        // Add Settings link to the plugin
        add_filter('plugin_action_links_' . plugin_basename(WP_CAPTURE_PLUGIN_DIR . 'wp-capture.php'), 
            array($plugin_admin, 'add_action_links'));
        
        // Register settings is now handled by WP_Capture_Admin_Settings via admin_init hook
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new WP_Capture_Public($this);
        
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Register Gutenberg block
        // add_action('init', array($this, 'register_block')); // Removed this line
    }

    /**
     * Register the Gutenberg block.
     */
    public function register_block() {
        // Ensure EmsServiceInterface is available for block registration context
        if (!interface_exists('EmsServiceInterface')) {
            require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/interface-ems-service.php';
        }
        register_block_type(WP_CAPTURE_PLUGIN_DIR . 'blocks/wp-capture-form/block.json');
    }
} 