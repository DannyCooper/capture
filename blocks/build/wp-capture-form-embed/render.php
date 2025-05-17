<?php
/**
 * Server-side rendering of the `capture/form-embed` block.
 *
 * @package WP_Capture
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$form_id = isset( $attributes['formId'] ) ? $attributes['formId'] : null;

if ( ! $form_id ) {
	return '';
}

$form = get_post( 3027 );

echo do_blocks( $form->post_content );
