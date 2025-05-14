<?php
/**
 * ConvertKit EMS service implementation.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

class ConvertKitService implements EmsServiceInterface {
    /**
     * The ConvertKit API endpoint.
     */
    private const API_ENDPOINT = 'https://api.convertkit.com/v3/';

    /**
     * Validate the ConvertKit API credentials.
     *
     * @param array $credentials The ConvertKit API credentials.
     * @return bool True if credentials are valid, false otherwise.
     */
    public function validateCredentials(array $credentials): bool {
        if (empty($credentials['api_key'])) {
            return false;
        }

        $response = wp_remote_get(self::API_ENDPOINT . 'forms?api_key=' . $credentials['api_key']);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Get available forms (lists) from ConvertKit.
     *
     * @param array $credentials The ConvertKit API credentials.
     * @return array Array of forms with their IDs and names.
     */
    public function getLists(array $credentials): array {
        if (empty($credentials['api_key'])) {
            return array();
        }

        $response = wp_remote_get(self::API_ENDPOINT . 'forms?api_key=' . $credentials['api_key']);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $lists = array();

        if (!empty($body['forms'])) {
            foreach ($body['forms'] as $form) {
                $lists[] = array(
                    'id' => $form['id'],
                    'name' => $form['name'],
                );
            }
        }

        return $lists;
    }

    /**
     * Subscribe an email to a ConvertKit form.
     *
     * @param array $credentials The ConvertKit API credentials.
     * @param string $email The email address to subscribe.
     * @param string $listId The ID of the form to subscribe to.
     * @param array $formData Additional form data (optional).
     * @return bool True if subscription was successful, false otherwise.
     */
    public function subscribeEmail(array $credentials, string $email, string $listId, array $formData = array()): bool {
        if (empty($credentials['api_key']) || empty($email) || empty($listId)) {
            return false;
        }

        $response = wp_remote_post(self::API_ENDPOINT . 'forms/' . $listId . '/subscribe', array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
            ),
            'body' => json_encode(array(
                'api_key' => $credentials['api_key'],
                'email' => $email,
                // ConvertKit also supports 'first_name', 'fields', 'tags' here if needed in future
            )),
        ));

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Get the name of the EMS provider.
     *
     * @return string The name of the EMS provider.
     */
    public function getProviderName(): string {
        return 'ConvertKit';
    }
} 