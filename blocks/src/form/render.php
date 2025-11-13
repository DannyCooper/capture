<?php
/**
 * WP Capture Form Block Render
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

$options             = get_option( 'capture_options', array() );
$privacy_policy_text = isset( $options['privacy_policy_text'] ) ? $options['privacy_policy_text'] : '';

// Validate required attributes.
$ems_connection_id = isset( $attributes['emsConnectionId'] ) ? $attributes['emsConnectionId'] : false;
$selected_list_id  = isset( $attributes['selectedListId'] ) ? $attributes['selectedListId'] : false;

// Form configuration.
$form_html_id        = ! empty( $attributes['formId'] ) ? $attributes['formId'] : uniqid( 'capture-form-' );
$success_message     = ! empty( $attributes['successMessage'] ) ? $attributes['successMessage'] : '';
$show_privacy_policy = ! empty( $attributes['showPrivacyPolicy'] ) ? $attributes['showPrivacyPolicy'] : false;
$disable_core_styles = ! empty( $attributes['disableCoreStyles'] ) ? $attributes['disableCoreStyles'] : false;
$input_text_color    = ! empty( $attributes['inputTextColor'] ) ? $attributes['inputTextColor'] : '';
$input_bg_color      = ! empty( $attributes['inputBackgroundColor'] ) ? $attributes['inputBackgroundColor'] : '';
$input_border        = ! empty( $attributes['inputBorder'] ) ? $attributes['inputBorder'] : array();

// Get current post ID.
$current_post_id = get_the_ID();

// Build unique ID for this form instance.
$unique_form_id = 'capture-form-' . $form_html_id;

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $disable_core_styles ? 'capture-form capture-form--no-core-styles' : 'capture-form',
		'id'    => $unique_form_id,
	)
);

// Validate settings - only show error if EMS is partially configured.
if ( $ems_connection_id && ! $selected_list_id ) {
	printf(
		'<div class="capture-form__error">%s</div>',
		esc_html__( 'Admin notice: Please select a list for the selected EMS connection.', 'capture' )
	);
	return;
}

// Check if local storage is enabled when no EMS is configured.
if ( ! $ems_connection_id ) {
	$options              = get_option( 'capture_options', array() );
	$enable_local_storage = isset( $options['enable_local_storage'] ) ? $options['enable_local_storage'] : true;

	if ( ! $enable_local_storage ) {
		printf(
			'<div class="capture-form-error">%s</div>',
			esc_html__( 'Admin notice: Form requires EMS connection or local storage to be enabled.', 'capture' )
		);
		return;
	}
}

if ( ! $disable_core_styles ) {
	// Enqueue the base form CSS file.
	wp_enqueue_style(
		'capture-form',
		CAPTURE_PLUGIN_URL . 'assets/css/form.css',
		array(),
		CAPTURE_VERSION
	);
	
	// Generate custom CSS for this form instance if colors or borders are set.
	if ( $input_text_color || $input_bg_color || ! empty( $input_border ) ) {
		$custom_css = ":root :where(#$unique_form_id) .capture-form__input {";
		if ( $input_text_color ) {
			$custom_css .= 'color:' . esc_attr( $input_text_color ) . ';';
		}
		if ( $input_bg_color ) {
			$custom_css .= 'background-color:' . esc_attr( $input_bg_color ) . ';';
		}
		if ( ! empty( $input_border ) ) {
			if ( ! empty( $input_border['color'] ) ) {
				$custom_css .= 'border-color:' . esc_attr( $input_border['color'] ) . ';';
			}
			if ( ! empty( $input_border['style'] ) ) {
				$custom_css .= 'border-style:' . esc_attr( $input_border['style'] ) . ';';
			}
			if ( ! empty( $input_border['width'] ) ) {
				$custom_css .= 'border-width:' . esc_attr( $input_border['width'] ) . ';';
			}
		}
		$custom_css .= '}';
		
		// Add inline style after enqueue.
		wp_add_inline_style( 'capture-form', $custom_css );
	}
}
?>

<form 
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	data-success-message="<?php echo esc_attr( $success_message ); ?>" 
	<?php if ( $selected_list_id ) : ?>
	data-list-id="<?php echo esc_attr( $selected_list_id ); ?>" 
	<?php endif; ?>
	<?php if ( $ems_connection_id ) : ?>
	data-ems-connection-id="<?php echo esc_attr( $ems_connection_id ); ?>" 
	<?php endif; ?>
	data-post-id="<?php echo esc_attr( $current_post_id ); ?>" 
	data-form-id="<?php echo esc_attr( $form_html_id ); ?>"
>	
	<?php echo $content; ?>
</form>