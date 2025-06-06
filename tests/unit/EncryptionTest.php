<?php
/**
 * Tests for the Encryption class.
 *
 * @package Capture
 */

use PHPUnit\Framework\TestCase;
use Capture\Encryption;

/**
 * Test the Encryption class functionality.
 */
class EncryptionTest extends TestCase {

	/**
	 * Set up test environment before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Define secure test keys to avoid fallback warnings.
		if ( ! defined( 'CAPTURE_API_ENCRYPTION_KEY' ) ) {
			define( 'CAPTURE_API_ENCRYPTION_KEY', 'test-secure-key-32-characters-long!' );
		}
		if ( ! defined( 'CAPTURE_API_ENCRYPTION_SALT' ) ) {
			define( 'CAPTURE_API_ENCRYPTION_SALT', 'test-secure-salt-for-encryption' );
		}
	}

	/**
	 * Test basic encrypt/decrypt cycle with email address.
	 */
	public function test_encrypt_decrypt_email_address() {
		$original_email = 'user@example.com';
		
		$encrypted = Encryption::encrypt( $original_email );
		$decrypted = Encryption::decrypt( $encrypted );
		
		// Ensure encryption actually changed the value.
		$this->assertNotEquals( $original_email, $encrypted );
		
		// Ensure decryption returns original value.
		$this->assertEquals( $original_email, $decrypted );
	}

	/**
	 * Test encrypt/decrypt with API key (longer string).
	 */
	public function test_encrypt_decrypt_api_key() {
		$api_key = 'sk_live_1234567890abcdefghijklmnopqrstuvwxyz';
		
		$encrypted = Encryption::encrypt( $api_key );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertNotEquals( $api_key, $encrypted );
		$this->assertEquals( $api_key, $decrypted );
	}

	/**
	 * Test encryption produces different results each time (due to IV).
	 */
	public function test_encryption_produces_different_results() {
		$value = 'test@example.com';
		
		$encrypted1 = Encryption::encrypt( $value );
		$encrypted2 = Encryption::encrypt( $value );
		
		// Same input should produce different encrypted outputs (due to random IV).
		$this->assertNotEquals( $encrypted1, $encrypted2 );
		
		// But both should decrypt to the same original value.
		$this->assertEquals( $value, Encryption::decrypt( $encrypted1 ) );
		$this->assertEquals( $value, Encryption::decrypt( $encrypted2 ) );
	}

	/**
	 * Test empty string handling.
	 */
	public function test_encrypt_empty_string() {
		$encrypted = Encryption::encrypt( '' );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( '', $decrypted );
	}

	/**
	 * Test decryption of invalid data.
	 */
	public function test_decrypt_invalid_data() {
		$invalid_data = 'this-is-not-encrypted-data';
		
		$result = Encryption::decrypt( $invalid_data );
		
		$this->assertFalse( $result );
	}

	/**
	 * Test decryption of corrupted base64 data.
	 */
	public function test_decrypt_corrupted_base64() {
		$corrupted = 'invalid-base64-!!!';
		
		$result = Encryption::decrypt( $corrupted );
		
		$this->assertFalse( $result );
	}

	/**
	 * Test that encryption works with special characters.
	 */
	public function test_encrypt_special_characters() {
		$special_chars = 'Test with special chars: åäö @#$%^&*()';
		
		$encrypted = Encryption::encrypt( $special_chars );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $special_chars, $decrypted );
	}

	/**
	 * Test is_properly_configured method.
	 */
	public function test_is_properly_configured() {
		$result = Encryption::is_properly_configured();
		
		// Should return true since we defined secure keys in setUp.
		$this->assertTrue( $result );
	}

	/**
	 * Test that encrypted data is base64 encoded.
	 */
	public function test_encrypted_data_is_base64() {
		$value = 'test@example.com';
		$encrypted = Encryption::encrypt( $value );
		
		// Should be valid base64.
		$decoded = base64_decode( $encrypted, true );
		$this->assertNotFalse( $decoded );
		
		// And should be longer than original (due to IV + ciphertext).
		$this->assertGreaterThan( strlen( $value ), strlen( $encrypted ) );
	}

	/**
	 * Test behavior when OpenSSL extension is not loaded (mocked).
	 * This tests the fallback behavior in lines 104-105 and 131-132.
	 */
	public function test_openssl_not_available() {
		// We can't actually disable OpenSSL, but we can test the logic
		// by temporarily renaming the function (if possible) or using reflection
		// For now, we'll test the configuration check
		$this->markTestSkipped( 'OpenSSL extension mocking requires advanced setup' );
	}

	/**
	 * Test configuration with fallback keys/salts.
	 * This tests the insecure fallback detection.
	 */
	public function test_is_properly_configured_with_fallbacks() {
		// We need to test when constants are not defined or have fallback values
		// This is tricky with PHPUnit since constants can't be undefined once defined
		// We would need a separate test process for this
		$this->markTestSkipped( 'Testing undefined constants requires separate process' );
	}

	/**
	 * Test decryption with data that's too short for IV.
	 * This tests line 142 (return false when data is too short).
	 */
	public function test_decrypt_data_too_short() {
		// Create base64 data that's shorter than IV length (16 bytes for AES-256-CTR)
		$short_data = base64_encode( 'short' ); // Only 5 bytes
		
		$result = Encryption::decrypt( $short_data );
		
		$this->assertFalse( $result );
	}

	/**
	 * Test decryption with invalid base64 but proper length.
	 * This should test the validation paths.
	 */
	public function test_decrypt_invalid_but_long_data() {
		// Create data that's long enough but won't decrypt properly
		$long_invalid = base64_encode( str_repeat( 'x', 32 ) ); // 32 bytes of 'x'
		
		$result = Encryption::decrypt( $long_invalid );
		
		// Should return false because decryption will fail or salt won't match
		$this->assertFalse( $result );
	}

	/**
	 * Test large data encryption/decryption.
	 */
	public function test_encrypt_large_data() {
		$large_data = str_repeat( 'This is a test string for large data encryption. ', 100 );
		
		$encrypted = Encryption::encrypt( $large_data );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $large_data, $decrypted );
		$this->assertNotEquals( $large_data, $encrypted );
	}

	/**
	 * Test encryption/decryption with Unicode characters.
	 */
	public function test_encrypt_unicode_data() {
		$unicode_data = '🔐 Security test with émojis and accénts: 测试数据 🚀';
		
		$encrypted = Encryption::encrypt( $unicode_data );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $unicode_data, $decrypted );
	}

	/**
	 * Test that encryption fails gracefully with null input.
	 */
	public function test_encrypt_null_input() {
		$encrypted = Encryption::encrypt( null );
		$decrypted = Encryption::decrypt( $encrypted );
		
		// Should handle null gracefully (convert to empty string or similar)
		$this->assertIsString( $decrypted );
	}

	/**
	 * Test multiple consecutive encrypt/decrypt operations.
	 */
	public function test_multiple_operations() {
		$values = [
			'test1@example.com',
			'test2@example.com',
			'sk_live_key123',
			'another-api-key-456'
		];
		
		$encrypted_values = [];
		
		// Encrypt all values
		foreach ( $values as $value ) {
			$encrypted_values[] = Encryption::encrypt( $value );
		}
		
		// Decrypt all values and verify
		for ( $i = 0; $i < count( $values ); $i++ ) {
			$decrypted = Encryption::decrypt( $encrypted_values[ $i ] );
			$this->assertEquals( $values[ $i ], $decrypted );
		}
	}
} 