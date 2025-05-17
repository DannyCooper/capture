<?php
/**
 * WP Capture Form Block Render
 *
 * @package WP_Capture
 */

$ems_connection_id = isset( $attributes['emsConnectionId'] ) ? $attributes['emsConnectionId'] : false;
$selected_list_id  = isset( $attributes['selectedListId'] ) ? $attributes['selectedListId'] : false;
$form_html_id      = ! empty( $attributes['formId'] ) ? $attributes['formId'] : uniqid( 'capture-form-' );
$form_layout       = ! empty( $attributes['formLayout'] ) ? $attributes['formLayout'] : '';
$field_gap         = ! empty( $attributes['fieldGap'] ) ? $attributes['fieldGap'] : '';
$success_message   = ! empty( $attributes['successMessage'] ) ? $attributes['successMessage'] : '';
$button_text       = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : '';
$button_color      = ! empty( $attributes['buttonColor'] ) ? $attributes['buttonColor'] : '';
$button_text_color = ! empty( $attributes['buttonTextColor'] ) ? $attributes['buttonTextColor'] : '';
$button_hover_color = ! empty( $attributes['buttonHoverColor'] ) ? $attributes['buttonHoverColor'] : '';
$current_post_id   = get_the_ID(); // Current post ID.

$style = '
<style>

#capture-form-' . esc_attr( $form_html_id ) . ' {
	gap: ' . esc_attr( $field_gap ) . 'rem;
}

#capture-form-' . esc_attr( $form_html_id ) . ' .capture-form__button {
	background-color: ' . esc_attr( $button_color ) . ';
	color: ' . esc_attr( $button_text_color ) . ';
}

#capture-form-' . esc_attr( $form_html_id ) . ' .capture-form__button:hover {
	background-color: ' . esc_attr( $button_hover_color ) . ';
}
</style>';

$wrapper_attributes = get_block_wrapper_attributes();


if ( ! $ems_connection_id  && $selected_list_id ) {
    echo '<div class="capture-form-error">' . esc_html__( 'Admin notice: Please select an EMS connection and list.', 'wp-capture' ) . '</div>';
	return;
}
if ( ! $ems_connection_id) {
    echo '<div class="capture-form-error">' . esc_html__( 'Admin notice: Please select an EMS connection.', 'wp-capture' ) . '</div>';
	return;
}

if ( ! $selected_list_id) {
    echo '<div class="capture-form-error">' . esc_html__( 'Admin notice: Please connect this form to a list.', 'wp-capture' ) . '</div>';
	return;
}

echo $style;
?>

<div <?php echo $wrapper_attributes; ?>>
	<form class="capture-form capture-form--<?php echo esc_attr( $form_layout ); ?>" 
		id="capture-form-<?php echo esc_attr( $form_html_id ); ?>" 
		style="<?php echo esc_attr( $style ); ?>" 
		data-success-message="<?php echo esc_attr( $success_message ); ?>" 
		data-list-id="<?php echo esc_attr( $selected_list_id ); ?>" 
		data-ems-connection-id="<?php echo esc_attr( $ems_connection_id ); ?>" 
		data-post-id="<?php echo esc_attr( $current_post_id ); ?>" 
		data-form-id="<?php echo esc_attr( $form_html_id ); ?>">
		<?php /* Keep hidden fields for non-JS submission, though JS will use data attributes */ ?>
		<input type="hidden" name="form_id" value="<?php echo esc_attr( $form_html_id ); ?>" />
		<input type="hidden" name="ems_connection_id" value="<?php echo esc_attr( $ems_connection_id ); ?>" />
		<input type="hidden" name="list_id" value="<?php echo esc_attr( $selected_list_id ); ?>" />
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $current_post_id ); ?>" />
		<input type="email" class="capture-form__input capture-form__input--email" name="email" placeholder="<?php echo esc_attr__( 'Enter your email address', 'wp-capture' ); ?>" required />
		<button type="submit" class="capture-form__button"><?php echo esc_html__( 'Subscribe', 'wp-capture' ); ?></button>
	</form>
</div> 