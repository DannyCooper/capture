<?php
/**
 * Handles shortcode registration and processing for the WP Capture plugin.
 *
 * @package Capture
 */

namespace Capture;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Shortcodes
 */
class Shortcodes {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_shortcode( 'capture_form', array( $this, 'render_form' ) );
	}

	/**
	 * Render the form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'capture_form'
		);

		$form_id = absint( $atts['id'] );

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

		return wp_kses( do_blocks( $form->post_content ), $allowed_html );
	}
}

new Shortcodes();
