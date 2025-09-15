<?php
/**
 * Mailerlite EMS service implementation.
 *
 * @package Capture
 * @since   1.0.0
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Mailerlite EMS service implementation.
 *
 * @since      1.0.0
 * @package    Capture
 */
class Mailerlite_Service implements Ems_Service_Interface {
	/**
	 * The Mailerlite API endpoint.
	 */
	const API_ENDPOINT = 'https://connect.mailerlite.com/api';

	/**
	 * Validate the Mailerlite API credentials.
	 *
	 * @param array $credentials The Mailerlite API credentials.
	 * @return bool True if credentials are valid, false otherwise.
	 */
	public function validate_credentials( array $credentials ): bool {
		if ( empty( $credentials['api_key'] ) ) {
			return false;
		}

		$response = wp_remote_get(
			self::API_ENDPOINT,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $credentials['api_key'],
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'User-Agent'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				),
			)
		);

		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Get available lists from Mailerlite.
	 *
	 * @param array $credentials The Mailerlite API credentials.
	 * @return array Array of lists with their IDs and names.
	 */
	public function get_lists( array $credentials ): array {
		if ( empty( $credentials['api_key'] ) ) {
			return array();
		}

		$cache_key = 'capture_mailerlite_lists_' . md5( $credentials['api_key'] );
		$cached    = get_transient( $cache_key );
		
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			self::API_ENDPOINT . '/groups',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $credentials['api_key'],
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'User-Agent'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				),
			)
		);
	
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array();
		}

		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$lists = array();

		if ( ! empty( $body['data'] ) ) {
			foreach ( $body['data'] as $list ) {
				$lists[] = array(
					'id'   => $list['id'],
					'name' => $list['name'] ?? '',
				);
			}
		}

		// Cache for 5 minutes.
		set_transient( $cache_key, $lists, 300 );

		return $lists;
	}

	/**
	 * Subscribe an email to a Mailerlite list.
	 *
	 * @param array  $credentials The Mailerlite API credentials.
	 * @param string $email The email address to subscribe.
	 * @param string $list_id The ID of the list to subscribe to.
	 * @param array  $form_data Additional form data (optional).
	 * @return bool True if subscription was successful, false otherwise.
	 */
	public function subscribe_email( array $credentials, string $email, string $list_id, array $form_data = array() ): bool {
		if ( empty( $credentials['api_key'] ) || empty( $email ) || empty( $list_id ) ) {
			return false;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $credentials['api_key'],
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'User-Agent'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			),
		);
		
		$body = array(
			'email'  => $email,
			'groups' => array(
				$list_id,
			),
		);

		if ( ! empty( $form_data['first_name'] ) ) {
			$body['fields']['name'] = $form_data['first_name'];
		}

		$args['body'] = wp_json_encode( $body );

		$response = wp_remote_post(
			self::API_ENDPOINT . '/subscribers',
			$args
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		return ! is_wp_error( $response ) && ( $response_code === 201 || $response_code === 200 );
	}

	/**
	 * Get the name of the EMS provider.
	 *
	 * @return string The name of the EMS provider.
	 */
	public function get_provider_name(): string {
		return 'Mailerlite';
	}
}
