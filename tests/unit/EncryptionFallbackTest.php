<?php
/**
 * Tests for Encryption class fallback scenarios.
 * These tests specifically target uncovered error paths and fallback behavior.
 *
 * @package Capture
 */

use PHPUnit\Framework\TestCase;

/**
 * Test Encryption class fallback and error scenarios.
 * 
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class EncryptionFallbackTest extends TestCase {

	/**
	 * Test behavior when no custom encryption constants are defined.
	 * This should trigger fallback key/salt logic.
	 */
	public function test_fallback_to_wordpress_keys() {
		// Don't define custom constants, but define WordPress ones
		if ( ! defined( 'LOGGED_IN_KEY' ) ) {
			define( 'LOGGED_IN_KEY', 'wordpress-logged-in-key-for-testing' );
		}
		if ( ! defined( 'NONCE_SALT' ) ) {
			define( 'NONCE_SALT', 'wordpress-nonce-salt-for-testing' );
		}

		require_once 'includes/class-encryption.php';

		$reflection = new ReflectionClass( 'Capture\Encryption' );
		
		// Test get_default_key method
		$get_key_method = $reflection->getMethod( 'get_default_key' );
		$get_key_method->setAccessible( true );
		$key = $get_key_method->invoke( null );
		
		// Should return WordPress fallback key
		$this->assertEquals( 'wordpress-logged-in-key-for-testing', $key );
		
		// Test get_default_salt method
		$get_salt_method = $reflection->getMethod( 'get_default_salt' );
		$get_salt_method->setAccessible( true );
		$salt = $get_salt_method->invoke( null );
		
		// Should return WordPress fallback salt
		$this->assertEquals( 'wordpress-nonce-salt-for-testing', $salt );
	}

	/**
	 * Test completely insecure fallback when no keys are defined.
	 */
	public function test_insecure_fallback_keys() {
		// Don't define any constants
		require_once 'includes/class-encryption.php';

		$reflection = new ReflectionClass( 'Capture\Encryption' );
		
		// Test get_default_key method
		$get_key_method = $reflection->getMethod( 'get_default_key' );
		$get_key_method->setAccessible( true );
		$key = $get_key_method->invoke( null );
		
		// Should return insecure fallback
		$this->assertEquals( 'this-is-not-a-secure-key', $key );
		
		// Test get_default_salt method
		$get_salt_method = $reflection->getMethod( 'get_default_salt' );
		$get_salt_method->setAccessible( true );
		$salt = $get_salt_method->invoke( null );
		
		// Should return insecure fallback
		$this->assertEquals( 'this-is-not-a-secure-salt', $salt );
	}

	/**
	 * Test is_properly_configured with insecure fallbacks.
	 */
	public function test_is_properly_configured_insecure() {
		// Don't define any secure constants
		require_once 'includes/class-encryption.php';

		$result = \Capture\Encryption::is_properly_configured();
		
		// Should return false because we're using insecure fallbacks
		$this->assertFalse( $result );
	}

	/**
	 * Test is_properly_configured with WordPress fallbacks.
	 */
	public function test_is_properly_configured_wordpress_fallback() {
		// Define WordPress constants but not custom ones
		if ( ! defined( 'LOGGED_IN_KEY' ) ) {
			define( 'LOGGED_IN_KEY', 'secure-wordpress-key-32-chars-long' );
		}
		if ( ! defined( 'NONCE_SALT' ) ) {
			define( 'NONCE_SALT', 'secure-wordpress-salt-for-testing' );
		}

		require_once 'includes/class-encryption.php';

		$result = \Capture\Encryption::is_properly_configured();
		
		// Should return true because WordPress keys are considered secure
		$this->assertTrue( $result );
	}

	/**
	 * Test encryption with different key/salt combinations.
	 */
	public function test_encryption_with_wordpress_keys() {
		// Define WordPress constants
		if ( ! defined( 'LOGGED_IN_KEY' ) ) {
			define( 'LOGGED_IN_KEY', 'test-wp-key-32-characters-long!' );
		}
		if ( ! defined( 'LOGGED_IN_SALT' ) ) {
			define( 'LOGGED_IN_SALT', 'test-wp-salt-for-encryption' );
		}

		require_once 'includes/class-encryption.php';

		$test_value = 'test@example.com';
		$encrypted = \Capture\Encryption::encrypt( $test_value );
		$decrypted = \Capture\Encryption::decrypt( $encrypted );

		$this->assertEquals( $test_value, $decrypted );
		$this->assertNotEquals( $test_value, $encrypted );
	}
} 