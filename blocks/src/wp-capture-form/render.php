<?php
/**
 * WP Capture Form Block Render
 *
 * @package WP_Capture
 *
 * @param array $attributes Block attributes.
 * @return void
 */

// Validate required attributes
$ems_connection_id  = isset( $attributes['emsConnectionId'] ) ? $attributes['emsConnectionId'] : false;
$selected_list_id   = isset( $attributes['selectedListId'] ) ? $attributes['selectedListId'] : false;

// Form configuration
$form_html_id       = ! empty( $attributes['formId'] ) ? $attributes['formId'] : uniqid( 'capture-form-' );
$form_layout        = ! empty( $attributes['formLayout'] ) ? $attributes['formLayout'] : '';
$field_gap          = ! empty( $attributes['fieldGap'] ) ? $attributes['fieldGap'] : '';
$success_message    = ! empty( $attributes['successMessage'] ) ? $attributes['successMessage'] : '';
$button_text        = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : '';
$button_color       = ! empty( $attributes['buttonColor'] ) ? $attributes['buttonColor'] : '';
$button_text_color  = ! empty( $attributes['buttonTextColor'] ) ? $attributes['buttonTextColor'] : '';
$button_hover_color = ! empty( $attributes['buttonHoverColor'] ) ? $attributes['buttonHoverColor'] : '';
$show_name_field    = ! empty( $attributes['showNameField'] ) ? $attributes['showNameField'] : false;

// Get current post ID
$current_post_id    = get_the_ID();

// Generate form styles
$form_styles = sprintf(
    '<style>
        #capture-form-%1$s {
            gap: %2$srem;
        }
        #capture-form-%1$s .capture-form__button {
            background-color: %3$s;
            color: %4$s;
        }
        #capture-form-%1$s .capture-form__button:hover {
            background-color: %5$s;
        }
    </style>',
    esc_attr( $form_html_id ),
    esc_attr( $field_gap ),
    esc_attr( $button_color ),
    esc_attr( $button_text_color ),
    esc_attr( $button_hover_color )
);

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Validate settings - only show error if EMS is partially configured
if ( $ems_connection_id && ! $selected_list_id ) {
    printf(
        '<div class="capture-form-error">%s</div>',
        esc_html__( 'Admin notice: Please select a list for the selected EMS connection.', 'capture' )
    );
    return;
}

// Check if local storage is enabled when no EMS is configured
if ( ! $ems_connection_id ) {
    $options = get_option( 'wp_capture_options', array() );
    $enable_local_storage = isset( $options['enable_local_storage'] ) ? $options['enable_local_storage'] : true;
    
    if ( ! $enable_local_storage ) {
        printf(
            '<div class="capture-form-error">%s</div>',
            esc_html__( 'Admin notice: Form requires EMS connection or local storage to be enabled.', 'capture' )
        );
        return;
    }
}

// Output form styles
echo wp_kses(
    $form_styles,
    array(
        'style' => array(),
    )
);
?>

<div <?php echo $wrapper_attributes; ?>>
    <form 
        class="capture-form capture-form--<?php echo esc_attr( $form_layout ); ?>" 
        id="capture-form-<?php echo esc_attr( $form_html_id ); ?>" 
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
        <?php /* Hidden fields for non-JS submission */ ?>
        <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_html_id ); ?>" />
        <?php if ( $ems_connection_id ) : ?>
        <input type="hidden" name="ems_connection_id" value="<?php echo esc_attr( $ems_connection_id ); ?>" />
        <?php endif; ?>
        <?php if ( $selected_list_id ) : ?>
        <input type="hidden" name="list_id" value="<?php echo esc_attr( $selected_list_id ); ?>" />
        <?php endif; ?>
        <input type="hidden" name="post_id" value="<?php echo esc_attr( $current_post_id ); ?>" />
        
        <?php if ( $show_name_field ) : ?>
            <input 
                type="text" 
                class="capture-form__input capture-form__input--name" 
                name="first_name" 
                placeholder="<?php echo esc_attr__( 'Enter your first name', 'capture' ); ?>" 
                required 
            />
        <?php endif; ?>
        
        <input 
            type="email" 
            class="capture-form__input capture-form__input--email" 
            name="email" 
            placeholder="<?php echo esc_attr__( 'Enter your email address', 'capture' ); ?>" 
            required 
        />
        
        <button type="submit" class="capture-form__button">
            <?php echo esc_html( $button_text ?: __( 'Subscribe', 'capture' ) ); ?>
        </button>
    </form>
</div> 