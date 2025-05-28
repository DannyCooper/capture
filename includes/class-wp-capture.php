<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/yourusername/wp-capture
 * @since      1.0.0
 *
 * @package    WP_Capture
 * @subpackage WP_Capture/includes
 */

/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */
class WP_Capture {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name = 'capture';

	/**
	 * The encryption service instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Encryption    $encryption_service    The encryption service instance.
	 */
	private $encryption_service;

	/**
	 * The EMS services array.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $ems_services    The registered EMS services.
	 */
	private $ems_services = array();

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->load_textdomain();
		$this->load_dependencies();
		$this->encryption_service = new Encryption(); // Instantiate your Encryption class.
		$this->register_ems_services();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load plugin textdomain for translations.
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'capture',
			false,
			dirname( plugin_basename( WP_CAPTURE_PLUGIN_DIR . 'wp-capture.php' ) ) . '/languages'
		);
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		// Load database and subscriber classes.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-database.php';
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-subscriber.php';

		// Load EMS service interfaces and implementations.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/interface-ems-service.php';
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/class-mailchimp-service.php';
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/ems/class-convertkit-service.php';

		// Load your custom Encryption class.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-encryption.php';

		// Load block registration.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/block-registration.php';

		// Load REST API handlers.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/rest-api-handlers.php';
		// Load frontend AJAX handlers.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/frontend-ajax-handlers.php';

		// Load post type registration.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-post-types.php';

		// Load unsubscribe functionality.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-unsubscribe.php';

		// Load data retention functionality.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/class-wp-capture-data-retention.php';

		// Load admin and public classes.
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/admin/class-wp-capture-admin.php';
		require_once WP_CAPTURE_PLUGIN_DIR . 'includes/public/class-wp-capture-public.php';
	}

	/**
	 * Register the EMS services.
	 */
	private function register_ems_services() {
		$this->ems_services = array(
			'mailchimp' => new Mailchimp_Service(),
			'convertkit' => new ConvertKit_Service(),
		);

		// Allow other plugins/themes to register their own EMS services.
		$this->ems_services = apply_filters( 'wp_capture_register_ems_services', $this->ems_services );
	}

	/**
	 * Get a specific EMS service by provider key.
	 *
	 * @param string $provider_key The provider key.
	 * @return object|null The EMS service object or null if not found.
	 */
	public function get_service( $provider_key ) {
		return isset( $this->ems_services[ $provider_key ] ) ? $this->ems_services[ $provider_key ] : null;
	}

	/**
	 * Get all registered EMS services.
	 *
	 * @return array The array of registered EMS services.
	 */
	public function get_registered_services() {
		$services = array();
		foreach ( $this->ems_services as $key => $service ) {
			if ( method_exists( $service, 'get_provider_name' ) ) {
				$services[ $key ] = array(
					'name' => $service->get_provider_name(),
					'key' => $key,
				);
			}
		}
		return $services;
	}

	/**
	 * Get the encryption service instance.
	 *
	 * @return Encryption The encryption service instance.
	 */
	public function get_encryption_service() {
		return $this->encryption_service;
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new WP_Capture_Admin( $this );

		// Add menu item.
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_admin_menu' ) );

		// Add Settings link to the plugin.
		add_filter(
			'plugin_action_links_' . plugin_basename( WP_CAPTURE_PLUGIN_DIR . 'wp-capture.php' ),
			array( $plugin_admin, 'add_action_links' )
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 */
	private function define_public_hooks() {
		$plugin_public = new WP_Capture_Public( $this );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles' ) );

		// Initialize unsubscribe functionality.
		new WP_Capture_Unsubscribe();

		// Initialize data retention functionality.
		new WP_Capture_Data_Retention();
	}
}
