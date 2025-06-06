<?php
/**
 * Edge case tests for the Encryption class.
 *
 * @package Capture
 */

use PHPUnit\Framework\TestCase;
use Capture\Encryption;

/**
 * Test edge cases and boundary conditions for Encryption class.
 */
class EncryptionEdgeCasesTest extends TestCase {

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		
		if ( ! defined( 'CAPTURE_API_ENCRYPTION_KEY' ) ) {
			define( 'CAPTURE_API_ENCRYPTION_KEY', 'test-secure-key-32-characters-long!' );
		}
		if ( ! defined( 'CAPTURE_API_ENCRYPTION_SALT' ) ) {
			define( 'CAPTURE_API_ENCRYPTION_SALT', 'test-secure-salt-for-encryption' );
		}
	}

	/**
	 * Test decryption with truncated IV data.
	 * This specifically targets line 142 (IV length validation).
	 */
	public function test_decrypt_truncated_iv() {
		// AES-256-CTR uses 16-byte IV, create data with less than 16 bytes
		$truncated_data = base64_encode( str_repeat( 'x', 8 ) ); // Only 8 bytes
		
		$result = Encryption::decrypt( $truncated_data );
		
		$this->assertFalse( $result );
	}

	/**
	 * Test decryption with exact IV length but no ciphertext.
	 * Edge case where data length equals IV length.
	 */
	public function test_decrypt_iv_only() {
		// Create data exactly 16 bytes (IV length for AES-256-CTR)
		$iv_only_data = base64_encode( str_repeat( 'x', 16 ) );
		
		$result = Encryption::decrypt( $iv_only_data );
		
		// Should fail because there's no actual ciphertext after IV
		$this->assertFalse( $result );
	}

	/**
	 * Test encryption with very long strings.
	 */
	public function test_encrypt_very_long_string() {
		// Test with 10KB of data
		$long_string = str_repeat( 'A', 10240 );
		
		$encrypted = Encryption::encrypt( $long_string );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $long_string, $decrypted );
		$this->assertTrue( strlen( $encrypted ) > strlen( $long_string ) );
	}

	/**
	 * Test with binary data.
	 */
	public function test_encrypt_binary_data() {
		// Create some binary data
		$binary_data = pack( 'C*', 0, 1, 2, 255, 254, 253 );
		
		$encrypted = Encryption::encrypt( $binary_data );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $binary_data, $decrypted );
	}

	/**
	 * Test decryption with malformed base64.
	 */
	public function test_decrypt_malformed_base64() {
		// Base64 with invalid characters
		$malformed = 'invalid-base64-@#$%^&*()';
		
		$result = Encryption::decrypt( $malformed );
		
		$this->assertFalse( $result );
	}

	/**
	 * Test decryption with valid base64 but wrong encryption format.
	 */
	public function test_decrypt_wrong_format() {
		// Valid base64 but not our encryption format
		$wrong_format = base64_encode( 'this is just plain text, not encrypted' );
		
		$result = Encryption::decrypt( $wrong_format );
		
		// Should fail because it's not properly encrypted data
		$this->assertFalse( $result );
	}

	/**
	 * Test encryption with newlines and special whitespace.
	 */
	public function test_encrypt_whitespace_data() {
		$whitespace_data = "Line 1\nLine 2\r\nLine 3\t\tTabbed\n\n\nMultiple newlines";
		
		$encrypted = Encryption::encrypt( $whitespace_data );
		$decrypted = Encryption::decrypt( $encrypted );
		
		$this->assertEquals( $whitespace_data, $decrypted );
	}

	/**
	 * Test multiple encryptions of same data produce different results.
	 */
	public function test_encryption_randomness() {
		$data = 'test-data-for-randomness-check';
		
		$encryptions = [];
		for ( $i = 0; $i < 10; $i++ ) {
			$encryptions[] = Encryption::encrypt( $data );
		}
		
		// All encrypted values should be different (due to random IV)
		$unique_encryptions = array_unique( $encryptions );
		$this->assertCount( 10, $unique_encryptions );
		
		// But all should decrypt to the same value
		foreach ( $encryptions as $encrypted ) {
			$this->assertEquals( $data, Encryption::decrypt( $encrypted ) );
		}
	}

	/**
	 * Test encryption/decryption performance with repeated operations.
	 */
	public function test_performance_multiple_operations() {
		$test_data = 'performance-test-data-string';
		
		$start_time = microtime( true );
		
		// Perform 100 encrypt/decrypt cycles
		for ( $i = 0; $i < 100; $i++ ) {
			$encrypted = Encryption::encrypt( $test_data );
			$decrypted = Encryption::decrypt( $encrypted );
			$this->assertEquals( $test_data, $decrypted );
		}
		
		$end_time = microtime( true );
		$execution_time = $end_time - $start_time;
		
		// Should complete within reasonable time (adjust threshold as needed)
		$this->assertLessThan( 5.0, $execution_time, 'Encryption operations taking too long' );
	}
} 