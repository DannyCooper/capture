<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/yourusername/capture
 * @since      1.0.0
 *
 * @package    Capture
 * @subpackage Capture/includes
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    Capture
 */
class Core {
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
		$this->load_dependencies();
		$this->encryption_service = new Encryption(); // Instantiate your Encryption class.
		$this->register_ems_services();
		$this->define_admin_hooks();

		// Initialize unsubscribe functionality.
		new Unsubscribe();

		// Initialize data retention functionality.
		new Data_Retention();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		// Load database and subscriber classes.
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-database.php';
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-subscriber.php';

		// // Load EMS service interfaces and implementations.
		require_once CAPTURE_PLUGIN_DIR . 'includes/ems/interface-ems-service.php';
		require_once CAPTURE_PLUGIN_DIR . 'includes/ems/class-mailchimp-service.php';
		require_once CAPTURE_PLUGIN_DIR . 'includes/ems/class-convertkit-service.php';

		// // Load your custom Encryption class.
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-encryption.php';

		// Load block registration.
		require_once CAPTURE_PLUGIN_DIR . 'includes/block-registration.php';

		// Load REST API handlers.
		require_once CAPTURE_PLUGIN_DIR . 'includes/rest-api-handlers.php';
		// Load frontend AJAX handlers.
		require_once CAPTURE_PLUGIN_DIR . 'includes/frontend-ajax-handlers.php';

		// Load post type registration.
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-post-types.php';

		// Load unsubscribe functionality.
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-unsubscribe.php';

		// Load data retention functionality.
		require_once CAPTURE_PLUGIN_DIR . 'includes/class-data-retention.php';

		// Load admin classes.
		require_once CAPTURE_PLUGIN_DIR . 'includes/admin/class-admin.php';
	}

	/**
	 * Register the EMS services.
	 */
	private function register_ems_services() {
		$this->ems_services = array(
			'mailchimp'  => new Mailchimp_Service(),
			'convertkit' => new ConvertKit_Service(),
		);

		// Allow other plugins/themes to register their own EMS services.
		$this->ems_services = apply_filters( 'capture_register_ems_services', $this->ems_services );
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
					'key'  => $key,
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
		$plugin_admin = new Admin( $this );

		// Add menu item.
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_admin_menu' ) );

		// Add Settings link to the plugin.
		add_filter(
			'plugin_action_links_' . plugin_basename( CAPTURE_PLUGIN_DIR . 'capture.php' ),
			array( $plugin_admin, 'add_action_links' )
		);
	}
}
