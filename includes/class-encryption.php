<?php

class Encryption
{

	private $key;
	private $salt;

	public function __construct()
	{
		$this->key  = $this->getDefaultKey();
		$this->salt = $this->getDefaultSalt();
	}

	/**
	 * Gets the default encryption key, which should ideally be added to wp-config.php.
	 *
	 * @return string
	 */
	private function getDefaultKey()
	{
		if (defined('CAPTURE_API_ENCRYPTION_KEY') && '' !== CAPTURE_API_ENCRYPTION_KEY) {
			return CAPTURE_API_ENCRYPTION_KEY;
		}

		// Prefer WordPress standard keys if custom one is not set
		if (defined('LOGGED_IN_KEY') && '' !== LOGGED_IN_KEY) {
			return LOGGED_IN_KEY;
		}
		// It's crucial that a secure key is defined.
		// The is_properly_configured() method will warn if this fallback is used.
		return 'this-is-not-a-secure-key'; // Fallback
	}

	/**
	 * Gets the salt, which should ideally be added to wp-config.php.
	 *
	 * @return string
	 */
	private function getDefaultSalt()
	{
		if (defined('CAPTURE_API_ENCRYPTION_SALT') && '' !== CAPTURE_API_ENCRYPTION_SALT) {
			return CAPTURE_API_ENCRYPTION_SALT;
		}

		// Prefer WordPress standard salts if custom one is not set
		// NONCE_SALT is generally a good choice for this type of salt.
		if (defined('NONCE_SALT') && '' !== NONCE_SALT) {
			return NONCE_SALT;
		}
		if (defined('LOGGED_IN_SALT') && '' !== LOGGED_IN_SALT) { // Fallback to LOGGED_IN_SALT if NONCE_SALT not there
			return LOGGED_IN_SALT;
		}
		// It's crucial that a secure salt is defined.
		// The is_properly_configured() method will warn if this fallback is used.
		return 'this-is-not-a-secure-salt'; // Fallback
	}

	/**
	 * Encrypt the value using the salt
	 *
	 * @param string $value The value to encrypt.
	 * @return string|false The encrypted string, or false on failure.
	 */
	public function encrypt($value)
	{
		if (!extension_loaded('openssl')) {
			error_log('WP Capture Encryption Warning: OpenSSL extension is not loaded. API key will be handled in plaintext.');
			return $value; // Return plaintext if OpenSSL is not available
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length($method);
		$iv     = openssl_random_pseudo_bytes($ivlen);

		$raw_value = openssl_encrypt($value . $this->salt, $method, $this->key, 0, $iv);

		if (!$raw_value) {
			return false;
		}

		return base64_encode($iv . $raw_value);
	}

	/**
	 * Decrypt the value from the database and remove the salt value
	 *
	 * @param string $raw_value
	 * @return string
	 */
	public function decrypt($raw_value)
	{
		if (!extension_loaded('openssl')) {
			error_log('WP Capture Encryption Warning: OpenSSL extension is not loaded. Attempting to use stored API key value as is.');
			return $raw_value; // Return raw value (could be plaintext or ciphertext) if OpenSSL is not available
		}

		$raw_value = base64_decode($raw_value, true);

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length($method);
		$iv     = substr($raw_value, 0, $ivlen);

		$raw_value = substr($raw_value, $ivlen);

		$value = openssl_decrypt($raw_value, $method, $this->key, 0, $iv);
		if (!$value || substr($value, -strlen($this->salt)) !== $this->salt) {
			return false;
		}

		return substr($value, 0, -strlen($this->salt));
	}

	/**
	 * Checks if encryption is properly configured and available.
	 *
	 * @return bool True if properly configured, false otherwise.
	 */
	public static function is_properly_configured() {
		if (!extension_loaded('openssl')) {
			return false;
		}

		// We need to instantiate to check the resolved key/salt without exposing getDefaultKey/Salt as public static
		// This is a bit of a workaround. A better design might involve passing key/salt to constructor or using static methods for key/salt retrieval.
		$temp_instance = new self();
		$key = $temp_instance->getDefaultKey();
		$salt = $temp_instance->getDefaultSalt();

		if ($key === 'this-is-not-a-secure-key' || $salt === 'this-is-not-a-secure-salt') {
			return false; // Using insecure fallback keys/salts
		}
		
		// Check if specific WordPress constants are defined if custom ones are not
		// This logic is now implicitly covered by checking against the fallback strings.
		// If defined('CAPTURE_API_ENCRYPTION_KEY') is false, it will try LOGGED_IN_KEY.
		// If LOGGED_IN_KEY is also not defined/empty, it hits the fallback.

		return true;
	}
}