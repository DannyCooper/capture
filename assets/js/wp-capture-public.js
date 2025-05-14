(function($) {
    'use strict';

    // Handle form submission
    $('.wp-capture-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $message = $form.find('.message');
        const email = $form.find('input[type="email"]').val();
        
        // Basic validation
        if (!email) {
            showMessage($message, 'Please enter your email address.', 'error');
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: wpCapture.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_capture_submit',
                nonce: wpCapture.nonce,
                email: email,
                post_id: $form.data('post-id'),
                block_id: $form.data('block-id')
            },
            success: function(response) {
                if (response.success) {
                    showMessage($message, 'Thank you for subscribing!', 'success');
                    $form.find('input[type="email"]').val('');
                } else {
                    showMessage($message, 'Could not subscribe. Please try again later.', 'error');
                }
            },
            error: function() {
                showMessage($message, 'Could not subscribe. Please try again later.', 'error');
            }
        });
    });

    // Helper function to show messages
    function showMessage($container, message, type) {
        $container
            .removeClass('success error')
            .addClass(type)
            .html(message)
            .show();
    }

})(jQuery); 