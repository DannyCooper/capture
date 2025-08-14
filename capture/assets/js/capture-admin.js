(function($) {
    'use strict';

    $(document).ready(function() {
        const connectionsWrapper = $('#capture-connections-wrapper');
        const addConnectionButton = $('#capture-add-new-connection');
        const connectionTemplateHtml = $('#capture-connection-template').html();
        
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
        $(document).on('click', '.capture-remove-connection', function(e) {
            e.preventDefault();
            const $button = $(this);
            const $connectionItem = $button.closest('.capture-connection-item');
            const connectionId = $connectionItem.data('id');
            const $statusArea = $connectionItem.find('.capture-connection-status');

            const confirmed = confirm(captureAdmin.text.removeConnection);

            if (confirmed) {
                const isExistingConnection = connectionId && typeof connectionId === 'string' && !connectionId.startsWith('new_');

                if (isExistingConnection) {
                    // Existing connection, make AJAX call
                    $button.prop('disabled', true);
                    showStatus($statusArea, captureAdmin.text.removing || 'Removing connection...', 'info');

                    $.ajax({
                        url: captureAdmin.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'capture_remove_connection',
                            nonce: captureAdmin.nonce,
                            connection_id: connectionId
                        },
                        success: function(response) {
                            $button.prop('disabled', false);
                            if (response.success) {
                                showStatus($statusArea, response.data.message || captureAdmin.text.removedSuccess, 'success');
                                setTimeout(function() { 
                                    $connectionItem.fadeOut(500, function() { 
                                        $(this).remove(); 
                                    }); 
                                }, 1500);
                            } else {
                                showStatus($statusArea, response.data.message || captureAdmin.text.removeError, 'error');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $button.prop('disabled', false);
                            showStatus($statusArea, captureAdmin.text.errorOccurred, 'error');
                        }
                    });
                } else {
                    // New, unsaved connection, or invalid ID, just remove from DOM
                    $connectionItem.remove();
                }
            }
        });

        // Handle save & test for new connections
        $(document).on('click', '.capture-save-test-connection', function() {
            const $button = $(this);
            const $connectionItem = $button.closest('.capture-connection-item');
            const connectionId = $connectionItem.data('id');
            const provider = $connectionItem.find('select.capture-provider-select').val();
            const apiKey = $connectionItem.find('input.capture-api-key-input').val();
            const name = $connectionItem.find('input[name$="[name]"]').val();
            const $statusArea = $connectionItem.find('.capture-connection-status');
            
            // Basic validation
            if (!provider) {
                showStatus($statusArea, captureAdmin.text.selectProvider, 'error');
                return;
            }
            
            if (!apiKey) {
                showStatus($statusArea, captureAdmin.text.apiKeyRequired, 'error');
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true);
            showStatus($statusArea, captureAdmin.text.testing, 'info');
            
            // Make AJAX call to save and test
            $.ajax({
                url: captureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'capture_save_test_connection',
                    nonce: captureAdmin.nonce,
                    connection_id: connectionId,
                    provider: provider,
                    api_key: apiKey,
                    name: name
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        // Change the status message
                        showStatus($statusArea, response.data.message, 'success');
                        
                        // Update the connection item to reflect its saved state
                        $connectionItem.removeClass('is-new');
                        $connectionItem.data('id', response.data.connection_id);
                        $connectionItem.attr('data-id', response.data.connection_id);
                        
                        // Update the heading with the connection name
                        const displayName = name ? name : provider;
                        $connectionItem.find('h4').text(displayName + ' (' + provider + ')');
                    } else {
                        showStatus($statusArea, response.data.message, 'error');
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    showStatus($statusArea, captureAdmin.text.errorOccurred, 'error');
                }
            });
        });

        // Handle test for existing connections
        $(document).on('click', '.capture-test-connection', function() {
            const $button = $(this);
            const $connectionItem = $button.closest('.capture-connection-item');
            const connectionId = $connectionItem.data('id');
            const $statusArea = $connectionItem.find('.capture-connection-status');
            
            // Show loading state
            $button.prop('disabled', true);
            showStatus($statusArea, captureAdmin.text.testing, 'info');
            
            // Make AJAX call to test the connection
            $.ajax({
                url: captureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'capture_test_connection',
                    nonce: captureAdmin.nonce,
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
                    showStatus($statusArea, captureAdmin.text.errorOccurred, 'error');
                }
            });
        });

        // Update Existing Connection
        $('#capture-connections-wrapper').on('click', '.capture-update-connection', function(e) {
            e.preventDefault();

            const $button = $(this);
            const $item = $button.closest('.capture-connection-item');
            const connectionId = $button.data('id');
            const name = $item.find('input[name*="[name]"]').val();
            const apiKey = $item.find('input.capture-api-key-input').val(); // API key specific to existing connection forms

            captureShowStatus(captureAdmin.text.updating, $item, true);
            $button.prop('disabled', true);

            $.ajax({
                url: captureAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'capture_update_connection',
                    nonce: captureAdmin.nonce,
                    connection_id: connectionId,
                    name: name,
                    api_key: apiKey // Send empty if user wants to keep the old key
                },
                success: function(response) {
                    if (response.success) {
                        captureShowStatus(response.data.message || captureAdmin.text.updatedSuccess, $item, true);
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
                        captureShowStatus(response.data.message || captureAdmin.text.errorOccurred, $item, false);
                    }
                },
                error: function(xhr, status, error) {
                    captureShowStatus(captureAdmin.text.errorOccurred + ' (AJAX Error)', $item, false);
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

    });

})(jQuery);

function captureShowStatus(message, $item, isSuccess) {
    const $statusDiv = $item.find('.capture-connection-status');
    $statusDiv.text(message).removeClass('error success').addClass(isSuccess ? 'success' : 'error').show();
    setTimeout(() => {
        $statusDiv.fadeOut();
    }, 5000);
} 