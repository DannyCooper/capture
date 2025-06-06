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
} 