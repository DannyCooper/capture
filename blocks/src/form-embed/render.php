<?php
/**
 * Server-side rendering of the `capture/form-embed` block.
 *
 * @package Capture
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$form_id = isset( $attributes['formId'] ) ? $attributes['formId'] : null;

if ( ! $form_id ) {
	return '';
}

$form = get_post( $form_id );

if ( ! $form || 'capture_form' !== $form->post_type ) {
	return '';
}

// Allow form elements in addition to post content.
$allowed_html = wp_kses_allowed_html( 'post' );

$allowed_html['form'] = array(
	'class'  => true,
	'data-*' => true,
	'id'     => true,
	'method' => true,
	'action' => true,
);

$allowed_html['input'] = array(
	'type'         => true,
	'class'        => true,
	'autocomplete' => true,
	'aria-label'   => true,
	'name'         => true,
	'placeholder'  => true,
	'required'     => true,
	'value'        => true,
	'id'           => true,
);

$allowed_html['button'] = array(
	'type'   => true,
	'class'  => true,
	'id'     => true,
	'data-*' => true,
);

echo wp_kses( do_blocks( $form->post_content ), $allowed_html );
