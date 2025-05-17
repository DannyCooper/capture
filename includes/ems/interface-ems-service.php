<?php
/**
 * Interface for EMS service implementations.
 *
 * @since      1.0.0
 * @package    WP_Capture
 */

interface EmsServiceInterface {
    /**
     * Validate the EMS credentials.
     *
     * @param array $credentials The EMS credentials to validate.
     * @return bool True if credentials are valid, false otherwise.
     */
    public function validate_credentials(array $credentials): bool;

    /**
     * Get available lists from the EMS.
     *
     * @param array $credentials The EMS credentials to use.
     * @return array Array of lists with their IDs and names.
     */
    public function get_lists(array $credentials): array;

    /**
     * Subscribe an email to a list.
     *
     * @param array $credentials The EMS credentials to use.
     * @param string $email The email address to subscribe.
     * @param string $listId The ID of the list to subscribe to.
     * @param array $formData Additional form data (optional).
     * @return bool True if subscription was successful, false otherwise.
     */
    public function subscribe_email(array $credentials, string $email, string $listId, array $formData = array()): bool;

    /**
     * Get the name of the EMS provider.
     *
     * @return string The name of the EMS provider.
     */
    public function get_provider_name(): string;
} 