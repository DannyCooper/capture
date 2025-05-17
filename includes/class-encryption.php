<?php

class Encryption
{

	/**
	 * The encryption key.
	 * @var string|null
	 */
	private static $key = null;
	/**
	 * The encryption salt.
	 * @var string|null
	 */
	private static $salt = null;

	/**
	 * Initializes the static key and salt properties if they haven't been set.
	 */
	private static function init_properties()
	{
		if (null === self::$key) {
			self::$key = self::get_default_key();
		}
		if (null === self::$salt) {
			self::$salt = self::get_default_salt();
		}
	}

	/**
	 * Gets the default encryption key, which should ideally be added to wp-config.php.
	 *
	 * @return string
	 */
	private static function get_default_key()
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
		return 'this-is-not-a-secure-key'; // Fallback.
	}

	/**
	 * Gets the salt, which should ideally be added to wp-config.php.
	 *
	 * @return string
	 */
	private static function get_default_salt()
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
		return 'this-is-not-a-secure-salt'; // Fallback.
	}

	/**
	 * Encrypt the value using the salt
	 *
	 * @param string $value The value to encrypt.
	 * @return string|false The encrypted string, or false on failure.
	 */
	public static function encrypt($value)
	{
		self::init_properties();

		if (!extension_loaded('openssl')) {
			error_log('WP Capture Encryption Warning: OpenSSL extension is not loaded. API key will be handled in plaintext.');
			return $value; // Return plaintext if OpenSSL is not available
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length($method);
		$iv     = openssl_random_pseudo_bytes($ivlen);

		$raw_value = openssl_encrypt($value . self::$salt, $method, self::$key, 0, $iv);

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
	public static function decrypt($raw_value)
	{
		self::init_properties();

		if (!extension_loaded('openssl')) {
			error_log('WP Capture Encryption Warning: OpenSSL extension is not loaded. Attempting to use stored API key value as is.');
			return $raw_value; // Return raw value (could be plaintext or ciphertext) if OpenSSL is not available
		}

		$decoded_value = base64_decode($raw_value, true);

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length($method);

		if (strlen($decoded_value) < $ivlen) {
			// Not enough data for IV and ciphertext.
			return false;
		}
		$iv     = substr($decoded_value, 0, $ivlen);
		$ciphertext = substr($decoded_value, $ivlen);

		if (false === $iv || false === $ciphertext) {
			// Substr failed, which is unlikely if length check passed, but good for robustness.
			return false;
		}

		$decrypted_value = openssl_decrypt($ciphertext, $method, self::$key, 0, $iv);

		// Check if decryption failed or if the salt is missing.
		// Ensure self::$salt is not empty before checking substr, to avoid errors if salt is an empty string.
		if (false === $decrypted_value || (self::$salt !== '' && substr($decrypted_value, -strlen(self::$salt)) !== self::$salt)) {
			return false;
		}
		
		// Remove salt if it's present and not an empty string
		if (self::$salt !== '') {
			return substr($decrypted_value, 0, -strlen(self::$salt));
		}
		return $decrypted_value; // Return as is if salt is empty
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

		$key = self::get_default_key();
		$salt = self::get_default_salt();

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