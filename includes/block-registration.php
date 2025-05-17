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
		// Register the form embed block.
		$form_embed_block = register_block_type( WP_CAPTURE_PLUGIN_DIR . 'blocks/build/wp-capture-form-embed/block.json' );
		
		if ( is_wp_error( $form_embed_block ) ) {
			error_log( 'Failed to register form embed block: ' . $form_embed_block->get_error_message() );
		}

		// Register the form block.
		$form_block = register_block_type( WP_CAPTURE_PLUGIN_DIR . 'blocks/build/wp-capture-form/block.json' );
		
		if ( is_wp_error( $form_block ) ) {
			error_log( 'Failed to register form block: ' . $form_block->get_error_message() );
		}
	}
	add_action( 'init', 'wp_capture_register_blocks' );
}