<?php
/**
 * Handles the registration of Gutenberg blocks for the WP Capture plugin.
 *
 * @package WP_Capture
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! function_exists( 'wp_capture_register_blocks' ) ) {
	/**
	 * Register Gutenberg blocks.
	 */
	function wp_capture_register_blocks() {
		$block_directories = glob( WP_CAPTURE_PLUGIN_DIR . 'blocks/build/*', GLOB_ONLYDIR );

		foreach ( $block_directories as $block_directory ) {
			$block_json = $block_directory . '/block.json';
			if ( file_exists( $block_json ) ) {
				register_block_type( $block_json );
			}
		}
	}
	add_action( 'init', 'wp_capture_register_blocks' );
}
