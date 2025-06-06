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

		// Start output buffering.
		ob_start();

		// Include the form template.
		include CAPTURE_PLUGIN_DIR . 'templates/form.php';

		// Return the buffered content.
		return ob_get_clean();
	}
}
