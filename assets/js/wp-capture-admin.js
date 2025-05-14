(function($) {
    'use strict';

    $(document).ready(function() {
        const connectionsWrapper = $('#wp-capture-connections-wrapper');
        const addConnectionButton = $('#wp-capture-add-new-connection');
        const connectionTemplateHtml = $('#wp-capture-connection-template').html();
        
        let connectionCounter = Date.now(); // Simple unique key generation

        if (!connectionTemplateHtml) {
            console.error('WP Capture: Connection template not found.');
            return;
        }

        addConnectionButton.on('click', function() {
            connectionCounter++;
            const newKey = 'new_' + connectionCounter; // Use a temporary key for new unsaved connections
            let newConnectionHtml = connectionTemplateHtml.replace(/NEW_KEY_PLACEHOLDER/g, newKey);
            
            const $newConnection = $(newConnectionHtml);
            
            connectionsWrapper.append($newConnection);
        });

        // Handle removal of connections (both new and existing)
        $(document).on('click', '.wp-capture-remove-connection', function(e) {
            e.preventDefault();
            console.log('WP Capture: Remove button clicked.'); // Debug
            const $button = $(this);
            const $connectionItem = $button.closest('.wp-capture-connection-item');
            const connectionId = $connectionItem.data('id');
            console.log('WP Capture: Connection ID found:', connectionId, 'Type:', typeof connectionId); // Debug
            const $statusArea = $connectionItem.find('.wp-capture-connection-status');

            const confirmed = confirm(wpCaptureAdmin.text.removeConnection);
            console.log('WP Capture: Confirmation result:', confirmed); // Debug

            if (confirmed) {
                const isExistingConnection = connectionId && typeof connectionId === 'string' && !connectionId.startsWith('new_');
                console.log('WP Capture: Is existing connection (should make AJAX call)?', isExistingConnection); // Debug

                if (isExistingConnection) {
                    // Existing connection, make AJAX call
                    console.log('WP Capture: Attempting AJAX call to remove existing connection.'); // Debug
                    $button.prop('disabled', true);
                    showStatus($statusArea, wpCaptureAdmin.text.removing || 'Removing connection...', 'info');

                    $.ajax({
                        url: wpCaptureAdmin.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wp_capture_remove_connection',
                            nonce: wpCaptureAdmin.nonce,
                            connection_id: connectionId
                        },
                        success: function(response) {
                            console.log('WP Capture: AJAX remove success response:', response); // Debug
                            $button.prop('disabled', false);
                            if (response.success) {
                                showStatus($statusArea, response.data.message || wpCaptureAdmin.text.removedSuccess, 'success');
                                setTimeout(function() { 
                                    $connectionItem.fadeOut(500, function() { 
                                        $(this).remove(); 
                                    }); 
                                }, 1500);
                            } else {
                                showStatus($statusArea, response.data.message || wpCaptureAdmin.text.removeError, 'error');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('WP Capture: AJAX remove error:', textStatus, errorThrown, jqXHR); // Debug
                            $button.prop('disabled', false);
                            showStatus($statusArea, wpCaptureAdmin.text.errorOccurred, 'error');
                        }
                    });
                } else {
                    // New, unsaved connection, or invalid ID, just remove from DOM
                    console.log('WP Capture: Removing item from DOM (not an existing connection or ID invalid).'); // Debug
                    $connectionItem.remove();
                }
            }
        });

        // Handle save & test for new connections
        $(document).on('click', '.wp-capture-save-test-connection', function() {
            const $button = $(this);
            const $connectionItem = $button.closest('.wp-capture-connection-item');
            const connectionId = $connectionItem.data('id');
            const provider = $connectionItem.find('select.wp-capture-provider-select').val();
            const apiKey = $connectionItem.find('input.wp-capture-api-key-input').val();
            const name = $connectionItem.find('input[name$="[name]"]').val();
            const $statusArea = $connectionItem.find('.wp-capture-connection-status');
            
            // Basic validation
            if (!provider) {
                showStatus($statusArea, wpCaptureAdmin.text.selectProvider, 'error');
                return;
            }
            
            if (!apiKey) {
                showStatus($statusArea, wpCaptureAdmin.text.apiKeyRequired, 'error');
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true);
            showStatus($statusArea, wpCaptureAdmin.text.testing, 'info');
            
            // Make AJAX call to save and test
            $.ajax({
                url: wpCaptureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_capture_save_test_connection',
                    nonce: wpCaptureAdmin.nonce,
                    connection_id: connectionId,
                    provider: provider,
                    api_key: apiKey,
                    name: name
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    
                    console.log('WP Capture (Save & Test): AJAX Success Response:', response); // Debug

                    if (response.success) {
                        // Change the status message
                        showStatus($statusArea, response.data.message, 'success');
                        
                        console.log('WP Capture (Save & Test): Original data-id:', $connectionItem.data('id')); // Debug
                        console.log('WP Capture (Save & Test): Original data-id attr:', $connectionItem.attr('data-id')); // Debug
                        console.log('WP Capture (Save & Test): response.data.connection_id:', response.data.connection_id); // Debug

                        // Update the connection item to reflect its saved state
                        $connectionItem.removeClass('is-new');
                        $connectionItem.data('id', response.data.connection_id);
                        $connectionItem.attr('data-id', response.data.connection_id);
                        
                        console.log('WP Capture (Save & Test): New data-id:', $connectionItem.data('id')); // Debug
                        console.log('WP Capture (Save & Test): New data-id attr:', $connectionItem.attr('data-id')); // Debug

                        // Update the heading with the connection name
                        const displayName = name ? name : provider;
                        $connectionItem.find('h4').text(displayName + ' (' + provider + ')');
                    } else {
                        showStatus($statusArea, response.data.message, 'error');
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    showStatus($statusArea, wpCaptureAdmin.text.errorOccurred, 'error');
                }
            });
        });

        // Handle test for existing connections
        $(document).on('click', '.wp-capture-test-connection', function() {
            const $button = $(this);
            const $connectionItem = $button.closest('.wp-capture-connection-item');
            const connectionId = $connectionItem.data('id');
            const $statusArea = $connectionItem.find('.wp-capture-connection-status');
            
            // Show loading state
            $button.prop('disabled', true);
            showStatus($statusArea, wpCaptureAdmin.text.testing, 'info');
            
            // Make AJAX call to test the connection
            $.ajax({
                url: wpCaptureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_capture_test_connection',
                    nonce: wpCaptureAdmin.nonce,
                    connection_id: connectionId
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        showStatus($statusArea, response.data.message, 'success');
                    } else {
                        showStatus($statusArea, response.data.message, 'error');
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    showStatus($statusArea, wpCaptureAdmin.text.errorOccurred, 'error');
                }
            });
        });

        // Update Existing Connection
        $('#wp-capture-connections-wrapper').on('click', '.wp-capture-update-connection', function(e) {
            e.preventDefault();
            console.log('WP Capture: Update button clicked.'); // Debug

            const $button = $(this);
            const $item = $button.closest('.wp-capture-connection-item');
            const connectionId = $button.data('id');
            const name = $item.find('input[name*="[name]"]').val();
            const apiKey = $item.find('input.wp-capture-api-key-input').val(); // API key specific to existing connection forms
            // Provider is fixed for existing connections, no need to send it, PHP will use stored one.

            wpCaptureShowStatus(wpCaptureAdmin.text.updating, $item, true);
            $button.prop('disabled', true);

            $.ajax({
                url: wpCaptureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_capture_update_connection',
                    nonce: wpCaptureAdmin.nonce,
                    connection_id: connectionId,
                    name: name,
                    api_key: apiKey // Send empty if user wants to keep the old key
                },
                success: function(response) {
                    console.log('WP Capture: Update success response:', response); // Debug
                    if (response.success) {
                        wpCaptureShowStatus(response.data.message || wpCaptureAdmin.text.updatedSuccess, $item, true);
                        // Update the displayed name in the H4 tag if it changed
                        const currentH4 = $item.find('h4');
                        const providerText = currentH4.text().match(/\(([^)]+)\)/); // Extract (provider)
                        let newTitle = response.data.new_name;
                        if (providerText && providerText[1]) {
                            newTitle += ' (' + providerText[1] + ')';
                        }
                        currentH4.text(newTitle);

                        // Update the name in the global default dropdown
                        const $globalDefaultSelect = $('#global_default_ems');
                        const $optionToUpdate = $globalDefaultSelect.find('option[value="' + connectionId + '"]');
                        if ($optionToUpdate.length) {
                            // Reconstruct the label similar to how PHP does it for consistency
                            // If a custom name exists, use it. Otherwise, use provider_timestamp.
                            let dropdownLabel = response.data.new_name;
                            if (!dropdownLabel) { // If name was cleared, reconstruct default
                                const idParts = connectionId.split('_');
                                const timestampPart = idParts.length > 1 ? idParts[idParts.length - 1] : connectionId;
                                let providerNameForDropdown = '';
                                if (providerText && providerText[1]) {
                                     providerNameForDropdown = providerText[1].toLowerCase() === 'convertkit' ? 'convertkit' : providerText[1];
                                }
                                dropdownLabel = providerNameForDropdown + '_' + timestampPart;
                            }
                            $optionToUpdate.text(dropdownLabel);
                        }

                    } else {
                        wpCaptureShowStatus(response.data.message || wpCaptureAdmin.text.errorOccurred, $item, false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('WP Capture: Update error:', xhr, status, error); // Debug
                    wpCaptureShowStatus(wpCaptureAdmin.text.errorOccurred + ' (AJAX Error)', $item, false);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        // Helper function to show status messages
        function showStatus($container, message, type) {
            $container.html('<div class="notice notice-' + type + ' inline"><p>' + message + '</p></div>');
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    $container.find('.notice').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        }

        // Handle API key fields to submit actual value if not changed
        // This is a simplified approach. For more robust handling, you might need to track initial values.
        $('form').on('submit', function() {
            connectionsWrapper.find('.wp-capture-api-key-masked').each(function() {
                const $this = $(this);
                // If the value is still the masked placeholder (all asterisks and possibly last 4 chars) and it has a data-actual-api-key,
                // it means user hasn't typed a new key. We need to make sure the *actual* old key is submitted.
                // The sanitize_options in PHP now handles keeping the old key if asterisks are submitted.
                // This JS part is more about ensuring the input field value itself is correct if needed for other JS logic,
                // but for submission, PHP side is more reliable for this specific masked key scenario.
                
                // For now, we rely on PHP to handle the masked value correctly.
                // If the user types something new, that new value will be submitted.
                // If they leave it as asterisks, PHP will check if it should keep the old key.
            });
        });

    });

})(jQuery);

function wpCaptureShowStatus(message, $item, isSuccess) {
    const $statusDiv = $item.find('.wp-capture-connection-status');
    $statusDiv.text(message).removeClass('error success').addClass(isSuccess ? 'success' : 'error').show();
    setTimeout(() => {
        $statusDiv.fadeOut();
    }, 5000);
} 