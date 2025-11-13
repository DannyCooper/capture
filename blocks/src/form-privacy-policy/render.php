<?php
/**
 * WP Capture Form Privacy Policy Block Render
 *
 * @package Capture
 *
 * @param array $attributes Block attributes.
 * @return void
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get the privacy policy text from block attributes or plugin options.
$text = ! empty( $attributes['text'] ) ? $attributes['text'] : '';

// If no text in block attributes, get from plugin options.
if ( empty( $text ) ) {
	$options = get_option( 'capture_options', array() );
	$text    = isset( $options['privacy_policy_text'] ) ? $options['privacy_policy_text'] : '';
}

// Don't render anything if there's no text.
if ( empty( $text ) ) {
	return;
}

// Get wrapper attributes for styling support.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'capture-form__privacy-policy',
	)
);
?>

<span <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php echo wp_kses_post( $text ); ?>
</span>

