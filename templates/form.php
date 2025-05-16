<?php
/**
 * Template for rendering a capture form.
 *
 * @package WP_Capture
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get form data
$form_content = get_post_field( 'post_content', $form_id );

?>

<?php if ( $form_content ) : ?>
	<div class="wp-capture-form-content">
		<?php echo $form_content; ?>
    </div>
<?php endif; ?>