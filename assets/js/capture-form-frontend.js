(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Hoist frequently used wpCaptureFrontend properties and regex
        const frontendConfig = typeof captureFrontend !== 'undefined' ? captureFrontend : {};
        const i18n = frontendConfig.i18n || {};
        const ajax_url = frontendConfig.ajax_url || null;
        const nonce = frontendConfig.nonce || null;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!ajax_url || !nonce) {
            alert(i18n.configError || 'Form configuration error. Please contact site admin.');
            return; // Stop if essential config is missing
        }

        const forms = document.querySelectorAll('.capture-form');

        forms.forEach(form => {
            form.addEventListener('submit', async function(event) { // Use async function for await
                event.preventDefault();

                const emailInput = form.querySelector('.capture-form__input--email');
                const email = emailInput ? emailInput.value : null; // Gracefully handle missing input

                // Validations
                if (!email || email.trim() === '') {
                    alert(i18n.emptyEmail || 'Email address cannot be empty.');
                    return;
                }

                if (!emailPattern.test(email)) { // Uses hoisted emailPattern
                    alert(i18n.invalidEmail || 'Please enter a valid email address.');
                    return;
                }

                // Prepare FormData
                const listId = form.dataset.listId;
                const postId = form.dataset.postId;
                const formId = form.dataset.formId;
                const emsConnectionId = form.dataset.emsConnectionId;
                const firstName = form.querySelector('.capture-form__input--name')?.value || '';
                
                const formData = new FormData();
                formData.append('action', 'capture_submit_form');
                formData.append('nonce', nonce); // Use hoisted nonce
                formData.append('email', email);
                formData.append('list_id', listId);

                if (postId) formData.append('post_id', postId);
                if (formId) formData.append('form_id', formId);
                if (firstName) formData.append('first_name', firstName);
                if (emsConnectionId) formData.append('ems_connection_id', emsConnectionId);

                try {
                    const response = await fetch(ajax_url, { // Use hoisted ajaxUrl
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        let errorMessage = response.statusText || 'Network response was not ok.';
                        let serverResponse = '';
                        
                        try {
                            // Try to get the actual response text first
                            const responseText = await response.text();
                            serverResponse = responseText;
                            
                            // Then try to parse as JSON
                            const errorData = JSON.parse(responseText);
                            if (errorData && errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            // If response isn't JSON or parsing fails, use the raw text
                            if (serverResponse) {
                                errorMessage += ' Server response: ' + serverResponse;
                            }
                        }
                        throw new Error(errorMessage);
                    }

                    const data = await response.json();

                    if (data.success) {
                        const customSuccessMessage = form.dataset.successMessage;
                        const messageToDisplay = customSuccessMessage || data.message || i18n.successMessage || 'Thank you for subscribing!';

                        form.style.display = 'none'; // Hide the form

                        const successMessageElement = document.createElement('p');
                        successMessageElement.className = 'capture-form-success-message'; // For styling
                        successMessageElement.textContent = messageToDisplay;
                        form.parentNode.insertBefore(successMessageElement, form.nextSibling); // Insert message after form

                        if (emailInput) {
                            emailInput.value = ''; // Clear input on success
                        }
                    } else {
                        alert(data.data.message || i18n.errorMessage || 'An error occurred. Please try again.');
                    }
                } catch (error) {
                    const details = error && error.message ? String(error.message) : 'No additional details.';
                    alert((i18n.fetchError || 'A network error occurred. Please try again.') + ' Details: ' + details);
                }
            });
        });
    });

})();