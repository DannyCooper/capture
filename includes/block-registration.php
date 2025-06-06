<?php
/**
 * Handles the registration of Gutenberg blocks for the WP Capture plugin.
 *
 * @package Capture
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'Capture\register_blocks' ) ) {
	/**
	 * Register Gutenberg blocks.
	 */
	function register_blocks() {
		$block_directories = glob( CAPTURE_PLUGIN_DIR . 'blocks/build/*', GLOB_ONLYDIR );

		foreach ( $block_directories as $block_directory ) {
			$block_json = $block_directory . '/block.json';
			if ( file_exists( $block_json ) ) {
				register_block_type( $block_json );
			}
		}
	}
	add_action( 'init', 'Capture\register_blocks' );
}
