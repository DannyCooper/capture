(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // console.log('WP Capture Frontend JS Loaded');
        // console.log('Localized data:', typeof wpCaptureFrontend !== 'undefined' ? wpCaptureFrontend : 'wpCaptureFrontend not defined');

        const forms = document.querySelectorAll('.wp-capture-form');

        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                // console.log('Form submitted');

                const emailInput = form.querySelector('input[name="wp_capture_email"]');
                const email = emailInput ? emailInput.value : null;
                const listId = form.dataset.listId;
                const postId = form.dataset.postId;
                const formId = form.dataset.formId; // Use data-attribute directly

                // Ensure wpCaptureFrontend and its properties are defined before use
                const i18n = (typeof wpCaptureFrontend !== 'undefined' && wpCaptureFrontend.i18n) ? wpCaptureFrontend.i18n : {};
                const ajaxUrl = (typeof wpCaptureFrontend !== 'undefined') ? wpCaptureFrontend.ajaxUrl : null;
                const nonce = (typeof wpCaptureFrontend !== 'undefined') ? wpCaptureFrontend.nonce : null;

                if (!ajaxUrl || !nonce) {
                    console.error('WP Capture: AJAX URL or nonce not defined.');
                    alert(i18n.configError || 'Form configuration error. Please contact site admin.');
                    return;
                }
                
                if (!email || email.trim() === '') {
                    alert(i18n.emptyEmail || 'Email address cannot be empty.');
                    return;
                }

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    alert(i18n.invalidEmail || 'Please enter a valid email address.');
                    return;
                }

                // console.log('Email:', email);
                // console.log('List ID:', listId);
                // console.log('Block ID:', blockId);
                // console.log('Nonce:', nonce);
                // console.log('AJAX URL:', ajaxUrl);

                const formData = new FormData();
                formData.append('action', 'wp_capture_submit');
                formData.append('nonce', nonce);
                formData.append('email', email);
                formData.append('list_id', listId);
                if (postId) formData.append('post_id', postId);
                if (formId) formData.append('form_id', formId);
                // formData.append('block_id', blockId); // Removed: block_id is redundant now

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        // Try to get error from JSON response, otherwise use status text
                        return response.json().catch(() => {
                            throw new Error(response.statusText || 'Network response was not ok.');
                        }).then(errData => {
                            throw new Error(errData.message || response.statusText || 'Network response was not ok.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // console.log('Success:', data.message);
                        alert(data.message || i18n.successMessage || 'Thank you for subscribing!');
                        if (emailInput) {
                            emailInput.value = ''; // Clear input on success
                        }
                    } else {
                        // console.error('Error:', data.message);
                        alert(data.message || i18n.errorMessage || 'An error occurred. Please try again.');
                    }
                })
                .catch(error => {
                    // console.error('Fetch Error:', error.message);
                    alert(i18n.fetchError || 'A network error occurred. Please try again. Details: ' + error.message);
                });
            });
        });
    });

})();

// We can remove jQuery wrapper if not needed or if we ensure vanilla JS compatibility.
// For now, using vanilla JS querySelectorAll and fetch. 