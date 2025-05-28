<?php
/**
 * Handles unsubscribe functionality for WP Capture subscribers.
 *
 * @package    WP_Capture
 * @subpackage WP_Capture/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_Capture_Unsubscribe {

	/**
	 * Initialize the unsubscribe functionality.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'handle_unsubscribe_request' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_unsubscribe_styles' ) );
	}

	/**
	 * Handle unsubscribe requests via URL parameters.
	 */
	public function handle_unsubscribe_request() {
		// Check if this is an unsubscribe request
		if ( ! isset( $_GET['wp_capture_unsubscribe'] ) || ! isset( $_GET['token'] ) ) {
			return;
		}

		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
		$result = $this->process_unsubscribe( $token );

		if ( $result['success'] ) {
			$this->render_unsubscribe_page( 'success', $result['message'] );
		} else {
			$this->render_unsubscribe_page( 'error', $result['message'] );
		}
	}

	/**
	 * Process the unsubscribe request.
	 *
	 * @param string $token The unsubscribe token.
	 * @return array Result with success status and message.
	 */
	private function process_unsubscribe( $token ) {
		// Decode the token to get subscriber ID and email
		$decoded = $this->decode_unsubscribe_token( $token );
		
		if ( ! $decoded ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid unsubscribe link. Please contact the site administrator.', 'capture' )
			);
		}

		// Load the subscriber
		$subscriber = new WP_Capture_Subscriber();
		$subscriber_data = null;
		
		// If subscriber_id is available, try to get by ID first
		if ( ! empty( $decoded['subscriber_id'] ) ) {
			$subscriber_data = $subscriber->get_by_id( $decoded['subscriber_id'] );
		}
		
		// If not found by ID or ID was null, try to find by email
		if ( ! $subscriber_data ) {
			global $wpdb;
			$table_name = WP_Capture_Database::get_subscribers_table_name();
			$result = $wpdb->get_row( 
				$wpdb->prepare( 
					'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE email = %s LIMIT 1', 
					$decoded['email'] 
				), 
				ARRAY_A 
			);
			
			if ( $result ) {
				$subscriber_data = new WP_Capture_Subscriber( $result );
			}
		}

		if ( ! $subscriber_data ) {
			return array(
				'success' => false,
				'message' => __( 'Subscriber not found. You may already be unsubscribed.', 'capture' )
			);
		}

		// Verify the email matches
		if ( $subscriber_data->email !== $decoded['email'] ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid unsubscribe link. Please contact the site administrator.', 'capture' )
			);
		}

		// Check if already unsubscribed
		if ( $subscriber_data->status === 'unsubscribed' ) {
			return array(
				'success' => true,
				'message' => __( 'You are already unsubscribed from our mailing list.', 'capture' )
			);
		}

		// Update subscriber status to unsubscribed
		$update_data = array(
			'email' => $subscriber_data->email,
			'name' => $subscriber_data->name,
			'form_id' => $subscriber_data->form_id,
			'user_agent' => $subscriber_data->user_agent,
			'status' => 'unsubscribed',
			'source_url' => $subscriber_data->source_url,
		);

		$updated_subscriber = new WP_Capture_Subscriber( $update_data );
		$updated_subscriber->id = $subscriber_data->id;
		$result = $updated_subscriber->save();

		if ( ! is_wp_error( $result ) ) {
			return array(
				'success' => true,
				'message' => __( 'You have been successfully unsubscribed from our mailing list.', 'capture' )
			);
		} else {
			return array(
				'success' => false,
				'message' => __( 'There was an error processing your request. Please try again or contact the site administrator.', 'capture' )
			);
		}
	}

	/**
	 * Generate an unsubscribe token for a subscriber.
	 *
	 * @param int    $subscriber_id The subscriber ID.
	 * @param string $email The subscriber email.
	 * @return string The encoded unsubscribe token.
	 */
	public static function generate_unsubscribe_token( $subscriber_id, $email ) {
		// Validate input parameters
		if ( empty( $subscriber_id ) || ! is_numeric( $subscriber_id ) || intval( $subscriber_id ) <= 0 ) {
			error_log( 'WP Capture: Invalid subscriber_id provided to generate_unsubscribe_token: ' . var_export( $subscriber_id, true ) );
			return false;
		}
		
		if ( empty( $email ) || ! is_email( $email ) ) {
			error_log( 'WP Capture: Invalid email provided to generate_unsubscribe_token: ' . var_export( $email, true ) );
			return false;
		}

		$data = array(
			'subscriber_id' => intval( $subscriber_id ),
			'email' => $email,
			'timestamp' => time(),
		);

		// Use WordPress built-in auth mechanism for security
		$token = base64_encode( json_encode( $data ) );
		$hash = wp_hash( $token . $subscriber_id . $email );
		
		return base64_encode( $token . '|' . $hash );
	}

	/**
	 * Decode and verify an unsubscribe token.
	 *
	 * @param string $token The encoded token.
	 * @return array|false Decoded data or false if invalid.
	 */
	private function decode_unsubscribe_token( $token ) {
		$decoded_token = base64_decode( $token );
		if ( ! $decoded_token ) {
			return false;
		}

		$parts = explode( '|', $decoded_token );
		if ( count( $parts ) !== 2 ) {
			return false;
		}

		list( $data_token, $provided_hash ) = $parts;
		$data = json_decode( base64_decode( $data_token ), true );

		if ( ! $data || ! isset( $data['email'], $data['timestamp'] ) ) {
			return false;
		}
		
		// Handle backwards compatibility for misspelled key
		if ( isset( $data['subscribar_id'] ) && ! isset( $data['subscriber_id'] ) ) {
			$data['subscriber_id'] = $data['subscribar_id'];
		}
		
		if ( ! isset( $data['subscriber_id'] ) || empty( $data['subscriber_id'] ) ) {
			return false;
		}

		// Verify the hash
		$expected_hash = wp_hash( $data_token . $data['subscriber_id'] . $data['email'] );
		if ( ! hash_equals( $expected_hash, $provided_hash ) ) {
			return false;
		}

		// Check if token is not older than 30 days (optional security measure)
		if ( time() - $data['timestamp'] > ( 30 * 24 * 60 * 60 ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Generate an unsubscribe URL for a subscriber.
	 *
	 * @param int    $subscriber_id The subscriber ID.
	 * @param string $email The subscriber email.
	 * @return string The complete unsubscribe URL.
	 */
	public static function generate_unsubscribe_url( $subscriber_id, $email ) {
		$token = self::generate_unsubscribe_token( $subscriber_id, $email );
		
		// If token generation failed, return empty string
		if ( false === $token ) {
			error_log( 'WP Capture: Failed to generate unsubscribe token for subscriber_id: ' . $subscriber_id . ', email: ' . $email );
			return '';
		}

		$args = array(
			'wp_capture_unsubscribe' => '1',
			'token' => $token,
		);

		return add_query_arg( $args, home_url( '/' ) );
	}

	/**
	 * Render the unsubscribe page.
	 *
	 * @param string $type The message type ('success' or 'error').
	 * @param string $message The message to display.
	 */
	private function render_unsubscribe_page( $type, $message ) {
		$page_title = __( 'Unsubscribe', 'capture' );
		
		// Prevent any other output
		if ( ! headers_sent() ) {
			header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		}

		// Get site info
		$site_name = get_bloginfo( 'name' );
		$site_url = home_url( '/' );

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $page_title . ' - ' . $site_name ); ?></title>
			<?php wp_head(); ?>
		</head>
		<body class="wp-capture-unsubscribe-page">
			<div class="wp-capture-unsubscribe-container">
				<div class="wp-capture-unsubscribe-header">
					<h1><?php echo esc_html( $site_name ); ?></h1>
					<h2><?php echo esc_html( $page_title ); ?></h2>
				</div>
				
				<div class="wp-capture-unsubscribe-content">
					<div class="wp-capture-message wp-capture-message-<?php echo esc_attr( $type ); ?>">
						<?php if ( $type === 'success' ): ?>
							<span class="wp-capture-icon">✓</span>
						<?php else: ?>
							<span class="wp-capture-icon">⚠</span>
						<?php endif; ?>
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					
					<div class="wp-capture-actions">
						<a href="<?php echo esc_url( $site_url ); ?>" class="wp-capture-button">
							<?php _e( 'Return to Website', 'capture' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Enqueue styles for the unsubscribe page.
	 */
	public function enqueue_unsubscribe_styles() {
		if ( isset( $_GET['wp_capture_unsubscribe'] ) ) {
			$this->output_unsubscribe_styles();
		}
	}

	/**
	 * Output inline styles for the unsubscribe page.
	 */
	private function output_unsubscribe_styles() {
		?>
		<style type="text/css">
		.wp-capture-unsubscribe-page {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			line-height: 1.6;
			color: #333;
			background-color: #f8f9fa;
			margin: 0;
			padding: 0;
		}

		.wp-capture-unsubscribe-container {
			max-width: 600px;
			margin: 50px auto;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			overflow: hidden;
		}

		.wp-capture-unsubscribe-header {
			background: #0073aa;
			color: #fff;
			padding: 30px;
			text-align: center;
		}

		.wp-capture-unsubscribe-header h1 {
			margin: 0 0 10px 0;
			font-size: 24px;
			font-weight: 600;
		}

		.wp-capture-unsubscribe-header h2 {
			margin: 0;
			font-size: 18px;
			font-weight: 400;
			opacity: 0.9;
		}

		.wp-capture-unsubscribe-content {
			padding: 40px 30px;
		}

		.wp-capture-message {
			display: flex;
			align-items: flex-start;
			padding: 20px;
			border-radius: 6px;
			margin-bottom: 30px;
		}

		.wp-capture-message-success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}

		.wp-capture-message-error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}

		.wp-capture-icon {
			font-size: 20px;
			margin-right: 12px;
			margin-top: 2px;
		}

		.wp-capture-message p {
			margin: 0;
			font-size: 16px;
		}

		.wp-capture-actions {
			text-align: center;
		}

		.wp-capture-button {
			display: inline-block;
			background: #0073aa;
			color: #fff;
			padding: 12px 24px;
			text-decoration: none;
			border-radius: 4px;
			font-size: 16px;
			transition: background-color 0.2s ease;
		}

		.wp-capture-button:hover {
			background: #005a87;
			color: #fff;
		}

		@media (max-width: 640px) {
			.wp-capture-unsubscribe-container {
				margin: 20px;
				max-width: none;
			}
			
			.wp-capture-unsubscribe-header,
			.wp-capture-unsubscribe-content {
				padding: 20px;
			}
		}
		</style>
		<?php
	}
} 