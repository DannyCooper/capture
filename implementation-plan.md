### Implementation Plan

This plan outlines the steps to implement Version 1.0 of the "Email Forms for WordPress (WP Capture)" plugin, based on the provided Product Requirements Document (PRD).

**General Instructions for Each Step:**
*   After completing the development tasks for a step and verifying them with the manual tests, prompt the development assistant AI to create a Git commit summarizing the work done for that step. For example: "AI, please create a Git commit for the completion of Step X: [Step Title]. Key changes include [brief summary of changes]."

--- 

**Phase 1: Core Plugin Setup & EMS Framework**

**Step 1: Plugin Boilerplate and Basic Setup**
*   **Tasks:**
    *   Create the main plugin file (`wp-capture.php`) with the standard WordPress plugin header.
    *   Implement activation and deactivation hooks. Activation can set up default options if any; deactivation can clean up options if necessary (though V1 has minimal data to clean).
    *   Establish a basic plugin file/folder structure (e.g., `/includes`, `/assets`, `/blocks`).
*   **Manual Tests:**
    1.  Install the plugin via the WordPress admin panel.
    2.  Activate the plugin. Verify no errors occur.
    3.  Deactivate the plugin. Verify no errors occur.
    4.  (Optional) Check `wp_options` table for any default options set on activation.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 2: EMS Abstraction Layer & Core Service Registration**
*   **Tasks:**
    *   Define PHP interfaces for EMS services (e.g., `EmsServiceInterface` with methods like `validateCredentials(array $credentials): bool`, `getLists(array $credentials): array`, `subscribeEmail(array $credentials, string $email, string $listId, array $formData): bool`, `getProviderName(): string`).
    *   Create a system for registering and retrieving EMS service implementations (e.g., a manager class or a filterable array of provider slugs and their corresponding service class names).
*   **Manual Tests:**
    1.  Code review: Verify interfaces and registration mechanism are in place.
    2.  Code review: Assess if the abstraction allows for straightforward addition of new EMS providers in the future.
    *   (No direct UI tests for this foundational step).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 3: Secure API Key Storage Mechanism with Encryption**
*   **Tasks:**
    *   **Define Encryption Key:**
        *   Instruct users to generate a unique, strong, random key and define it as a constant (e.g., `WP_CAPTURE_ENCRYPTION_KEY`) in their `wp-config.php` file. Provide guidance on key generation.
        *   The plugin should check if this constant is defined and provide an admin notice if it's missing, as API key functionality will depend on it.
    *   **Implement Encryption/Decryption Helpers:**
        *   Create helper functions that use `openssl_encrypt` and `openssl_decrypt` (or similar robust PHP encryption functions) along with the `WP_CAPTURE_ENCRYPTION_KEY` and a suitable initialization vector (IV) for AES-256-CBC or similar.
        *   Ensure IVs are handled correctly (e.g., generated per encryption, stored alongside the ciphertext, or derived securely if appropriate).
    *   **Update API Key Save/Retrieve Functions:**
        *   Modify helper functions (from the original Step 3 concept) that save API keys to `wp_options`. These functions will now encrypt the API key using the encryption helper before storing it.
        *   Modify helper functions that retrieve API keys from `wp_options`. These functions will now decrypt the API key using the decryption helper after retrieval.
    *   Ensure API keys are still associated with their respective EMS providers within the stored options structure.
    *   API keys will not be displayed in full in the UI after being saved (masked display remains).
*   **Manual Tests:**
    1.  Define `WP_CAPTURE_ENCRYPTION_KEY` in `wp-config.php`.
    2.  Programmatically save a dummy API key for a test provider using the implemented functions.
    3.  Check the `wp_options` table: Verify the API key portion of the stored data is an encrypted string, not plaintext.
    4.  Programmatically retrieve the API key. Verify it's correctly decrypted and matches the original dummy key.
    5.  Test API key validation with an EMS provider using a retrieved (and decrypted) key to ensure it works.
    6.  If `WP_CAPTURE_ENCRYPTION_KEY` is not defined, verify an appropriate admin notice is shown and saving/retrieving keys fails gracefully or is disabled.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 2: Plugin Settings Page - EMS Management**

**Step 4: Plugin Settings Page Structure**
*   **Tasks:**
    *   Register a top-level admin menu item (e.g., "WP Capture").
    *   Use the WordPress Settings API to create the basic page structure with sections for "EMS Connections" and "Analytics."
*   **Manual Tests:**
    1.  Navigate to the "WP Capture" settings page in the WP Admin.
    2.  Verify the page loads correctly with the title and placeholders/headings for the defined sections.
    3.  Ensure no PHP errors are present.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 5: Implement "Add New Connection" UI & Provider Selection**
*   **Tasks:**
    *   In the "EMS Connections" section, add an "Add New Connection" button.
    *   When clicked, UI elements should appear to: 
        *   Select an EMS provider from a dropdown (initially "Mailchimp", "ConvertKit").
        *   Input API credentials (placeholder for now, specific fields in next steps).
    *   Manage the display of multiple configured connections.
*   **Manual Tests:**
    1.  On the settings page, click the "Add New Connection" button.
    2.  Verify a dropdown appears with "Mailchimp" and "ConvertKit" as options.
    3.  Selecting an option should show a section for credential entry (e.g., an API key field).
    4.  Verify the UI allows for potentially multiple connections to be added/displayed (though only one of each type might be practical for V1 global default).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 6: Mailchimp: Credential Entry & API Key Guidance**
*   **Tasks:**
    *   When "Mailchimp" is selected as the provider, display an API key input field.
    *   Add a descriptive label and a help link next to the API key field, pointing to Mailchimp's official documentation on how to find API keys.
    *   Implement saving the Mailchimp API key using the secure storage mechanism (Step 3).
    *   After saving, ensure the API key is not displayed in full (e.g., show "•••••••••••••" or just the last few characters).
*   **Manual Tests:**
    1.  Select/Add Mailchimp connection.
    2.  Verify the API key input field and the help link are present. Click the link to ensure it goes to a relevant Mailchimp page.
    3.  Enter a dummy API key and save. Verify a settings saved message.
    4.  Reload the page. The Mailchimp connection should be listed, and the API key should be masked or not shown in full.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 7: Mailchimp: API Key Validation**
*   **Tasks:**
    *   Implement the `validateCredentials` method in the Mailchimp service class (from Step 2). This method will make a test API call to Mailchimp (e.g., ping or get account info).
    *   When Mailchimp credentials are saved, call this validation method.
    *   Display immediate feedback to the user: "Connection successful!" or "Invalid credentials. Please check and try again."
*   **Manual Tests:**
    1.  Enter a *valid* Mailchimp API key and save. Verify the "Connection successful!" message is displayed.
    2.  Enter an *invalid* Mailchimp API key and save. Verify the "Invalid credentials..." message is displayed.
    3.  If the connection is successful, it should be clearly indicated (e.g., a status icon).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 8: ConvertKit: Credential Entry & API Key Guidance**
*   **Tasks:**
    *   When "ConvertKit" is selected, display its API key input field.
    *   Add label and help link to ConvertKit's documentation for API keys.
    *   Implement saving the ConvertKit API key (Step 3).
    *   Ensure saved API key is not displayed in full.
*   **Manual Tests:**
    1.  Select/Add ConvertKit connection.
    2.  Verify API key field and help link. Click link to check destination.
    3.  Enter a dummy API key and save. Verify settings saved message.
    4.  Reload page. ConvertKit connection listed, API key masked.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 9: ConvertKit: API Key Validation**
*   **Tasks:**
    *   Implement `validateCredentials` in the ConvertKit service class.
    *   On saving ConvertKit credentials, call validation and display feedback.
*   **Manual Tests:**
    1.  Enter a *valid* ConvertKit API key and save. Verify "Connection successful!".
    2.  Enter an *invalid* ConvertKit API key and save. Verify "Invalid credentials...".
    3.  If successful, indicate connection status.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 10: Designate Global Default EMS**
*   **Tasks:**
    *   On the settings page, list all successfully validated/configured EMS connections.
    *   Provide a mechanism (e.g., radio buttons or a dropdown) next to each to select one as the "global default" EMS.
    *   Save this selection in WordPress options.
*   **Manual Tests:**
    1.  Successfully configure both Mailchimp and ConvertKit connections.
    2.  Verify both are listed with an option to set them as default.
    3.  Select Mailchimp as default and save. Reload page, verify Mailchimp is still marked as default.
    4.  Change default to ConvertKit and save. Reload page, verify ConvertKit is now default.
    5.  If only one service is configured, it should be selectable as default (or automatically become default).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 3: Gutenberg Block - Basic Structure & Form Display**

**Step 11: Register "WP Capture Form" Gutenberg Block**
*   **Tasks:**
    *   Create necessary files for the block (e.g., `block.json`, `edit.js`, `save.js`, `style.scss`, `editor.scss`).
    *   Register the block type using `register_block_type` with `block.json`.
    *   Define basic block attributes (e.g., `listId`, `buttonColor`, `textAlign`, `spacing`).
    *   Implement minimal `Edit` and `Save` components.
*   **Manual Tests:**
    1.  Open the Gutenberg editor for a new post or page.
    2.  Search for the "WP Capture Form" block in the block inserter.
    3.  Add the block to the editor. Verify it appears without errors.
    4.  Save the post. View it on the frontend. Verify a basic placeholder for the block renders without errors.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 12: Implement Basic Block Structure: Email Input & Submit Button**
*   **Tasks:**
    *   Update the block's `Edit` component to render a single email input field and a submit button.
    *   Update the block's `Save` component to render the corresponding HTML structure (an email input and a submit button within a `<form>` tag).
    *   The form should not yet be functional.
*   **Manual Tests:**
    1.  In the Gutenberg editor, verify the added block displays an email input field and a submit button.
    2.  Save the post and view it on the frontend. Verify the same email input field and submit button are rendered.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 13: Ensure Block Inherits Styles from Active Theme**
*   **Tasks:**
    *   Ensure the block's frontend HTML output uses semantic markup and avoids overly specific CSS selectors or inline styles that would prevent theme styles from applying to the input field and button.
    *   Test with default WordPress themes.
*   **Manual Tests:**
    1.  Add the block to a page.
    2.  Activate the "Twenty Twenty-Four" theme. Observe the form's appearance.
    3.  Activate the "Twenty Twenty-Three" theme. Observe the form's appearance.
    4.  Verify that the email input and submit button generally adopt the styling (fonts, borders, colors if not overridden by block controls later) of the active theme.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 4: Gutenberg Block - EMS List Selection & Styling Controls**

**Step 14: EMS List Selection in Block Inspector**
*   **Tasks:**
    *   In the block's `Edit` component, add a `SelectControl` to the Inspector Panel (sidebar).
    *   This dropdown should be populated by fetching the available lists from the *globally configured default EMS*.
        *   This requires making an API call from the editor (e.g., using `apiFetch` to a custom REST endpoint, or preloading data if feasible).
        *   The custom REST endpoint will use the `getLists` method of the active global EMS service.
    *   Store the selected list ID as a block attribute.
    *   If no global EMS is configured, or if list fetching fails, display an appropriate message in the Inspector Panel.
*   **Manual Tests:**
    1.  Ensure a global default EMS (e.g., Mailchimp) is configured with at least two distinct lists.
    2.  Add the "WP Capture Form" block to a page.
    3.  Open the block's Inspector Panel. Verify a dropdown for "Select List" (or similar) is present.
    4.  Verify the dropdown is populated with the lists from the configured Mailchimp account.
    5.  Select a list. Save the post. Reload the editor. Verify the selected list is persisted.
    6.  If no global EMS is set (or if it's misconfigured), verify a helpful message appears in the Inspector Panel prompting the user to configure it.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 15: Block Styling Controls: Spacing (Padding/Margin)**
*   **Tasks:**
    *   Add `DimensionControl` (or similar, like `BoxControl`) in the Inspector Panel for controlling padding and margin of the form block.
    *   Store these values as block attributes.
    *   Apply these styles to the block's wrapper element in both `Edit` (for preview) and `Save` (for frontend) components.
*   **Manual Tests:**
    1.  Add the block. Open the Inspector Panel.
    2.  Verify spacing controls (e.g., for padding and margin) are present.
    3.  Adjust padding values. Verify changes are reflected in the block's appearance in the editor.
    4.  Adjust margin values. Verify changes are reflected.
    5.  Save the post and view on the frontend. Verify the applied spacing is correct.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 5: Form Submission Logic**

**Step 18: Frontend Form Submission Handler (JavaScript)**
*   **Tasks:**
    *   Enqueue a JavaScript file specifically for the frontend when the block is present.
    *   This script will attach an event listener to the `submit` event of all WP Capture forms on the page.
    *   On submit: prevent default form submission, retrieve the email address, perform basic client-side validation (e.g., check if field is empty, basic email pattern).
    *   If valid, send the data (email, list ID from block attribute, post ID, block client ID or unique block ID) via AJAX to a WordPress backend action.
    *   Include a nonce for security.
*   **Manual Tests:**
    1.  Add a configured form (with a selected list) to a page.
    2.  On the frontend, try submitting an empty email. Verify client-side validation prevents submission or shows an error.
    3.  Try submitting an invalid email format (e.g., "test@test"). Verify client-side validation catches it.
    4.  Enter a valid email and click submit. Use browser developer tools (Network tab) to verify an AJAX request is made to the correct WordPress AJAX endpoint.
    5.  Inspect the AJAX request payload: ensure it contains the email, list ID, post ID, block identifier, and a nonce.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 19: Backend AJAX Handler for Form Submission**
*   **Tasks:**
    *   Register WordPress AJAX actions (`wp_ajax_nopriv_wp_capture_submit` and `wp_ajax_wp_capture_submit`).
    *   In the handler function:
        *   Verify the nonce.
        *   Sanitize the input email address.
        *   Retrieve the list ID, post ID, and block identifier from the AJAX request.
        *   Identify the globally configured default EMS provider.
        *   Retrieve its stored API credentials.
        *   (Preparation for next steps: have data ready to pass to EMS service).
*   **Manual Tests:**
    1.  Trigger a frontend submission (from Step 18 tests).
    2.  In the backend AJAX handler, temporarily log (`error_log()`) the received and sanitized data (email, list ID, post ID, block ID, determined EMS provider).
    3.  Verify the log shows the correct data.
    4.  Test submitting with an invalid/missing nonce (e.g., by manually altering the request). Verify the handler rejects it appropriately.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 20: Logic to Send Email to Mailchimp**
*   **Tasks:**
    *   Implement the `subscribeEmail(array $credentials, string $email, string $listId, array $formData): bool` method in the Mailchimp service class.
    *   This method will use Mailchimp's API to add/subscribe the email address to the specified list ID.
    *   In the AJAX handler (Step 19), if the global default EMS is Mailchimp, call this service method.
    *   The AJAX handler should return a JSON response indicating success or failure.
*   **Manual Tests:**
    1.  Configure Mailchimp as the global default EMS, ensuring API key is valid and at least one list exists.
    2.  Configure a WP Capture Form block to use a specific Mailchimp list.
    3.  On the frontend, submit a valid, new email address through this form.
    4.  Verify the email address is successfully added to the selected list in your Mailchimp account.
    5.  Verify the AJAX response indicates success (e.g., `{"success": true}`).
    6.  Attempt to submit an email that Mailchimp might reject (e.g., already subscribed and list settings prevent re-subscription, or a malformed email that passed client-side but not server-side validation). Verify the AJAX response indicates failure and logs any specific error from Mailchimp (for admin notification later).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 21: Logic to Send Email to ConvertKit**
*   **Tasks:**
    *   Implement the `subscribeEmail` method in the ConvertKit service class. ConvertKit might use form IDs or tag IDs instead of list IDs; ensure `getLists` for ConvertKit fetches these appropriately and `subscribeEmail` uses them.
    *   In the AJAX handler, if the global default EMS is ConvertKit, call this service method.
    *   Return JSON success/failure.
*   **Manual Tests:**
    1.  Configure ConvertKit as the global default EMS (valid API key, at least one form/tag available).
    2.  Configure a WP Capture Form block to use a specific ConvertKit form/tag.
    3.  On the frontend, submit a valid, new email address.
    4.  Verify the email is added to the selected form/tag in your ConvertKit account.
    5.  Verify AJAX success response.
    6.  Test with an email ConvertKit might reject. Verify AJAX failure response and error logging.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 6: Error Handling & Notifications**

**Step 22: User-Facing Error/Success Messages on Form Submission**
*   **Tasks:**
    *   Update the frontend JavaScript (from Step 18) to handle the JSON response from the AJAX submission.
    *   If `success: true`, display a configurable success message to the user (e.g., "Thanks for subscribing!") near the form.
    *   If `success: false`, display a generic error message (e.g., "Could not subscribe. Please try again later."). The PRD also mentions "specific EMS error if suitable for public display" - for V1, a generic message is safer. Specific errors will go to admin.
    *   Messages should be dismissible or disappear after a timeout.
*   **Manual Tests:**
    1.  Perform a successful submission. Verify a clear success message is displayed below/near the form.
    2.  Force a submission failure (e.g., temporarily make API key invalid in settings, or use an email that the EMS will definitely reject). Verify a user-friendly error message is displayed.
    3.  Ensure messages don't break form layout.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 23: Admin Email Notification on Submission Failure**
*   **Tasks:**
    *   In the backend AJAX handler (Step 19), if the call to the EMS service's `subscribeEmail` method returns failure or throws an exception:
        *   Construct an email to be sent to the site administrator's email (from WordPress settings).
        *   Email content must include: Error message from EMS (or internal error description), Date and Time of failure, Page/Post where the form is located (requires passing Post ID from frontend and fetching post title/link).
        *   Use `wp_mail()` to send the notification.
*   **Manual Tests:**
    1.  Force a submission failure that results in an error from the EMS (e.g., invalid API key, list ID doesn't exist for the account).
    2.  Verify an email is sent to the site administrator's email address.
    3.  Check the email content: Does it clearly state the error, date/time, and the page/post where the submission failed?
    4.  Ensure the user still sees a generic error message on the frontend (from Step 22).
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 24: Server-Side Error Logging**
*   **Tasks:**
    *   Throughout the plugin, especially in API communication (validation, list fetching, subscription) and critical operations, implement server-side error logging.
    *   Use `error_log()` to log detailed error messages/exceptions, especially if `WP_DEBUG_LOG` is enabled.
    *   This is for deeper technical debugging by the site admin/developer.
*   **Manual Tests:**
    1.  Enable `WP_DEBUG` and `WP_DEBUG_LOG` in `wp-config.php`.
    2.  Force various error scenarios: API key validation failure, list fetching failure (e.g., temporary network issue if mockable), subscription failure.
    3.  Check the `wp-content/debug.log` file. Verify that relevant, detailed error messages from the WP Capture plugin are logged, providing context for troubleshooting.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 7: Analytics**

**Step 25: Data Structure & Logic for Submission Counts**
*   **Tasks:**
    *   To identify form instances: When a block is saved, if it doesn't have a unique ID, get the clientId and store it as a block attribute (e.g., `formId`). This ID will be passed during submission.
    *   On successful submission (in the AJAX handler, after EMS confirms success):
        *   Increment a counter for this `formId`.
        *   Store counts in a WordPress option (e.g., an array like `wp_capture_form_counts['form_id'] => count`).
        *   Also store metadata for each tracked form ID, like the Post ID it belongs to and perhaps the Post Title when first seen (to help display in analytics table).
*   **Manual Tests:**
    1.  Add a WP Capture form block to a post. Save. Inspect block attributes to see if a unique ID (`formId`) is present.
    2.  Successfully submit an email through this form.
    3.  Check the `wp_options` table for the analytics data. Verify a count is initialized/incremented for the form's unique ID, and associated Post ID is stored.
    4.  Submit to the same form again. Verify its count increments.
    5.  Add another WP Capture form block to a *different* post. Submit successfully. Verify a new entry with its own count and Post ID is created in the analytics data.
    6.  Add a *second* WP Capture form block to the *same* initial post. Submit successfully. Verify it has its own unique ID and count, distinct from the first block on that post.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

**Step 26: Display Submission Counts Table on Plugin Settings Page**
*   **Tasks:**
    *   In the "Analytics" section of the plugin settings page:
        *   Retrieve the stored submission counts and associated metadata (form ID, post ID, post title).
        *   Display this data in a table with columns: "Page/Post Title (linked to post)", "Form Identifier (e.g., part of the UUID)", "Successful Submissions Count".
        *   If a post containing a tracked form has been deleted, handle gracefully (e.g., show Post ID or "Post Deleted").
*   **Manual Tests:**
    1.  Ensure there are several forms with varying submission counts across different posts (as per Step 25 tests).
    2.  Navigate to the WP Capture plugin settings page, to the Analytics section.
    3.  Verify a table is displayed.
    4.  Verify the table correctly lists each form instance, the title of the post/page it's on (as a link to the post), and its accurate submission count.
    5.  Submit to one of the forms again. Refresh the settings page. Verify the count updates in the table.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

**Phase 8: Security Enhancements**

**Step 27: Implement API Key Encryption at Rest using WordPress Salts**
*   **Tasks:**
    *   **Encryption Key Source:**
        *   The plugin will use a WordPress standard salt, specifically `LOGGED_IN_KEY` (defined in `wp-config.php`), as the primary basis for its encryption key.
        *   The encryption helper functions should verify that `LOGGED_IN_KEY` is defined and not empty. If not (which would imply a severely misconfigured WordPress site), encryption/decryption should be disabled, and an admin notice should alert the user to the WordPress configuration issue.
    *   **Implement Encryption/Decryption Helper Class/Functions:**
        *   Create a dedicated class or set of helper functions for encryption and decryption.
        *   These functions will use `openssl_encrypt()` and `openssl_decrypt()` with a strong cipher like AES-256-CBC.
        *   **Key Derivation:** To ensure the key material used for AES-256-CBC is of the correct length and binary format, the `LOGGED_IN_KEY` (and potentially other salts like `NONCE_SALT` concatenated with it for added entropy) should be hashed (e.g., using `hash_hkdf('sha256', LOGGED_IN_KEY . NONCE_SALT, 32, 'wp-capture-api-key-encryption', ...)` or similar KDF, or a simple `hash('sha256', LOGGED_IN_KEY, true)` if a KDF is deemed too complex for V1, ensuring the output is a 32-byte binary string). This derived key will be the actual encryption key.
        *   **Initialization Vector (IV):** A unique, cryptographically secure IV (16 bytes for AES-256-CBC) must be generated for each encryption operation using `openssl_random_pseudo_bytes()`. This IV should be prepended to the ciphertext and stored together. The decryption function will extract the IV before decrypting.
        *   The helper functions should gracefully handle cases where the derived encryption key cannot be generated (e.g., missing salts).
    *   **Integrate Encryption into API Key Storage:**
        *   Modify the logic in `WP_Capture_Admin` (specifically the `ajax_save_test_connection` and `sanitize_options` methods where API keys are saved) to encrypt API keys using the new helper functions before they are stored in the `wp_capture_options`.
        *   If encryption is not possible (e.g., missing `LOGGED_IN_KEY`), API key saving should fail, and an error should be returned to the user/logged.
    *   **Integrate Decryption into API Key Retrieval:**
        *   Modify all places where API keys are retrieved for use (e.g., in `WP_Capture_Admin` for validation, by EMS service classes like `MailchimpService` and `ConvertKitService`) to decrypt the stored API key using the helper functions.
        *   If decryption fails (e.g., missing `LOGGED_IN_KEY`, incorrect key due to salt changes, corrupted data), it should be handled gracefully (e.g., treat as invalid credentials, log an error, display a notice to re-save the key).
*   **Manual Tests:**
    1.  **Normal Operation:**
        *   Ensure `LOGGED_IN_KEY` (and any other salts used for derivation) are defined in `wp-config.php`.
        *   Save a new Mailchimp connection with a valid API key.
        *   Inspect the `wp_options` table (e.g., `wp_capture_options`). Verify that the stored API key for the Mailchimp connection is an encrypted string (not plaintext) and includes a prepended IV.
        *   Verify the connection can be successfully validated (meaning the key was decrypted correctly for the validation call).
        *   Perform a frontend form submission that uses this Mailchimp connection. Verify it works (key decrypted correctly for the subscription call).
        *   Repeat for ConvertKit.
    2.  **Simulate Missing/Altered Salt:**
        *   Temporarily comment out or alter `LOGGED_IN_KEY` in `wp-config.php`.
        *   Attempt to save a *new* API key. Verify this fails and an appropriate error is shown or logged.
        *   Attempt to use an *existing, previously encrypted* API key (e.g., for validation or form submission). Verify this fails gracefully (e.g., connection shows as invalid, error logged, user prompted that keys may need re-saving due to configuration changes).
        *   Restore the correct `LOGGED_IN_KEY` and verify functionality is restored for previously encrypted keys and new keys can be saved.
*   **Git Commit Instruction:** After tests pass, prompt the AI for a Git commit.

--- 

This concludes the V1 implementation plan for WP Capture.