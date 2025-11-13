<?php
/**
 * WP Capture Form Input Block Render
 *
 * @package Capture
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return void
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$block_id     = $attributes['blockId'] ?? uniqid( 'field-' );
$field_type   = $attributes['fieldType'] ?? '';
$field_name   = $attributes['fieldName'] ?? '';
$label        = $attributes['label'] ?? '';
$placeholder  = $attributes['placeholder'] ?? '';
$required     = $attributes['required'] ?? false;
$autocomplete = $attributes['autocomplete'] ?? '';
$show_label   = $attributes['showLabel'] ?? true;

// Get wrapper attributes for styling support - apply to input element.
$input_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'capture-form__input capture-form__input--' . esc_attr( $field_type ),
		'id'    => 'capture-form__field-' . esc_attr( $block_id ),
	)
);
?>

<div class="capture-form__field">
	<?php if ( ! empty( $label ) && $show_label ) : ?>
		<label class="capture-form__label" for="capture-form__field-<?php echo esc_attr( $block_id ); ?>">
			<?php echo esc_html( $label ); ?>
		</label>
	<?php endif; ?>
	<input 
		<?php echo wp_kses_data( $input_attributes ); ?>
		type="<?php echo esc_attr( $field_type ); ?>" 
		autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
		aria-label="<?php echo esc_attr( $placeholder ); ?>"
		name="<?php echo esc_attr( $field_name ); ?>" 
		placeholder="<?php echo esc_attr( $placeholder ); ?>" 
		<?php echo $required ? 'required' : ''; ?>
	/>
</div>

