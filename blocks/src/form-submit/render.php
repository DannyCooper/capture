<?php
/**
 * WP Capture Form Submit Block Render
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

$button_text = ! empty( $attributes['text'] ) ? $attributes['text'] : __( 'Subscribe', 'capture' );

// Get wrapper attributes for styling support.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'capture-form__button capture-form__field--submit',
	)
);
?>


<div class="capture-form__field">
	<button <?php echo wp_kses_data( $wrapper_attributes ); ?> type="submit">
			<?php echo esc_html( $button_text ); ?>
	</button>
</div>
