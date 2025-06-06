<?php
/**
 * Mailchimp EMS service implementation.
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
 * Mailchimp EMS service implementation.
 *
 * @package Capture
 * @since   1.0.0
 */

/**
 * Mailchimp EMS service implementation.
 *
 * @since      1.0.0
 * @package    Capture
 */
class Mailchimp_Service implements Ems_Service_Interface {
	/**
	 * The Mailchimp API endpoint.
	 */
	private const API_ENDPOINT = 'https://{dc}.api.mailchimp.com/3.0/';

	/**
	 * Validate the Mailchimp API credentials.
	 *
	 * @param array $credentials The Mailchimp API credentials.
	 * @return bool True if credentials are valid, false otherwise.
	 */
	public function validate_credentials( array $credentials ): bool {
		if ( empty( $credentials['api_key'] ) ) {
			return false;
		}

		$dc = $this->get_data_center( $credentials['api_key'] );
		if ( ! $dc ) {
			return false;
		}

		$endpoint = str_replace( '{dc}', $dc, self::API_ENDPOINT );
		$response = wp_remote_get(
			$endpoint . 'ping',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $credentials['api_key'] ),
				),
			)
		);

		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Get available lists from Mailchimp.
	 *
	 * @param array $credentials The Mailchimp API credentials.
	 * @return array Array of lists with their IDs and names.
	 */
	public function get_lists( array $credentials ): array {
		if ( empty( $credentials['api_key'] ) ) {
			return array();
		}

		$dc = $this->get_data_center( $credentials['api_key'] );
		if ( ! $dc ) {
			return array();
		}

		$endpoint = str_replace( '{dc}', $dc, self::API_ENDPOINT );
		$response = wp_remote_get(
			$endpoint . 'lists',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $credentials['api_key'] ),
				),
			)
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array();
		}

		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$lists = array();

		if ( ! empty( $body['lists'] ) ) {
			foreach ( $body['lists'] as $list ) {
				$lists[] = array(
					'id'   => $list['id'],
					'name' => $list['name'],
				);
			}
		}

		return $lists;
	}

	/**
	 * Subscribe an email to a Mailchimp list.
	 *
	 * @param array  $credentials The Mailchimp API credentials.
	 * @param string $email The email address to subscribe.
	 * @param string $list_id The ID of the list to subscribe to.
	 * @param array  $form_data Additional form data (optional).
	 * @return bool True if subscription was successful, false otherwise.
	 */
	public function subscribe_email( array $credentials, string $email, string $list_id, array $form_data = array() ): bool {
		if ( empty( $credentials['api_key'] ) || empty( $email ) || empty( $list_id ) ) {
			return false;
		}

		$dc = $this->get_data_center( $credentials['api_key'] );
		if ( ! $dc ) {
			return false;
		}

		$endpoint = str_replace( '{dc}', $dc, self::API_ENDPOINT );
		$response = wp_remote_post(
			$endpoint . 'lists/' . $list_id . '/members',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $credentials['api_key'] ),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'email_address' => $email,
						'status'        => 'subscribed',
						'merge_fields'  => array(
							'FNAME' => ! empty( $form_data['first_name'] ) ? $form_data['first_name'] : '',
						),
					)
				),
			)
		);

		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Get the name of the EMS provider.
	 *
	 * @return string The name of the EMS provider.
	 */
	public function get_provider_name(): string {
		return 'Mailchimp';
	}

	/**
	 * Extract the data center from a Mailchimp API key.
	 *
	 * @param string $api_key The Mailchimp API key.
	 * @return string|false The data center or false if invalid.
	 */
	private function get_data_center( string $api_key ): string|false {
		$parts = explode( '-', $api_key );
		return count( $parts ) === 2 ? $parts[1] : false;
	}
}
