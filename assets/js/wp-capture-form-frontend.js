(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Hoist frequently used wpCaptureFrontend properties and regex
        const wpFrontend = typeof wpCaptureFrontend !== 'undefined' ? wpCaptureFrontend : {};
        const i18n = wpFrontend.i18n || {};
        const ajaxUrl = wpFrontend.ajaxUrl || null;
        const nonce = wpFrontend.nonce || null;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!ajaxUrl || !nonce) {
            console.error('WP Capture: AJAX URL or nonce not defined.');
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
                formData.append('action', 'capture_submit');
                formData.append('nonce', nonce); // Use hoisted nonce
                formData.append('email', email);
                formData.append('list_id', listId);

                if (postId) formData.append('post_id', postId);
                if (formId) formData.append('form_id', formId);
                if (firstName) formData.append('first_name', firstName);
                if (emsConnectionId) formData.append('ems_connection_id', emsConnectionId);

                try {
                    const response = await fetch(ajaxUrl, { // Use hoisted ajaxUrl
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        let errorMessage = response.statusText || 'Network response was not ok.';
                        try {
                            // Attempt to parse JSON error response from the server
                            const errorData = await response.json();
                            if (errorData && errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            // If response isn't JSON or parsing fails, stick with statusText
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
                        // Handle business logic errors (e.g., email already subscribed)
                        alert(data.message || i18n.errorMessage || 'An error occurred. Please try again.');
                    }
                } catch (error) {
                    // Handle network errors or errors thrown from the !response.ok block
                    const details = error && error.message ? String(error.message) : 'No additional details.';
                    alert((i18n.fetchError || 'A network error occurred. Please try again.') + ' Details: ' + details);
                }
            });
        });
    });

})();