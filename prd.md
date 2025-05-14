### PRD: Email Forms for WordPress (WP Capture) - Version 1.0

**Document Version:** 1.0
**Date:** 2024-03-15

## 1. Introduction

### 1.1. Document Purpose
This document outlines the product requirements for Version 1.0 of the "Email Forms for WordPress (WP Capture)" plugin. It details the project's objectives, target users, features, and acceptance criteria for the initial release.

### 1.2. Project Name
Email Forms for WordPress (WP Capture)

### 1.3. Version
1.0

## 2. Goals and Objectives

The primary goal of WP Capture V1.0 is to provide WordPress users with a simple, intuitive, and reliable method to:
*   Connect their WordPress website to their preferred Email Marketing Service (EMS).
*   Embed email capture forms into their posts and pages using the Gutenberg editor.
*   Collect subscriber email addresses and send them directly to their EMS.
*   Track basic form submission analytics.

## 3. Target Audience

*   **WordPress Website Owners:** Individuals and businesses looking to grow their email list directly from their WordPress site.
*   **Marketers:** Professionals seeking an easy way to integrate their email marketing efforts with their WordPress content.
*   **Content Creators (e.g., Bloggers, Publishers):** Users who want to capture leads from their audience within their content.

## 4. V1.0 Scope and Features

### 4.1. EMS Integration

*   **Generic Framework:** The plugin will be architected with a generic framework to simplify the addition of new EMS providers in future versions.
*   **V1 Supported Providers:** Initial support will be implemented for:
    *   Mailchimp
    *   ConvertKit
*   **Global Default EMS:** Within the plugin settings, users will configure their EMS connections and designate *one* as the "global default." All "WP Capture Form" blocks on the site will use this globally set EMS for V1.0.

### 4.2. Plugin Settings Page (WP Admin)

A dedicated settings page within the WordPress admin area will provide the following functionalities:

*   **EMS Connection Management:**
    *   **Adding Connections:** Users can add new EMS connections via an "Add New Connection" button.
    *   **Provider Selection:** Upon adding, users will select the EMS provider (e.g., Mailchimp, ConvertKit) from a dropdown list of supported providers.
    *   **Credential Entry:** Users will input the necessary API credentials (e.g., API Key) for the selected provider.
    *   **API Key Guidance:** Helpful links to the respective EMS provider's documentation on locating API keys will be provided directly next to the input fields to assist users.
*   **API Key Validation:** When API credentials are saved, the plugin will attempt to validate them immediately (e.g., by making a test API call to the EMS). Feedback (e.g., "Connection successful!" or "Invalid credentials. Please check and try again.") will be provided to the user.
*   **API Key Security:** API keys will be stored securely (e.g., encrypted in the database) and will not be displayed in full in the admin interface after initial entry and successful save, for security reasons.
*   **Designating Global Default EMS:** Users will be able to select one of their successfully configured EMS connections as the "global default" for the plugin.
*   **Analytics Display (V1):**
    *   A simple table will list each page or post where a "WP Capture Form" block is currently used.
    *   Associated with each listed page/post, a count of successful email submissions for that specific form instance will be displayed.

### 4.3. Gutenberg Block ("WP Capture Form")

A dedicated Gutenberg block will be the primary method for users to embed email capture forms.

*   **Embedding:** Users can add the "WP Capture Form" block to any post or page using the Gutenberg editor.
*   **Form Fields (V1):** The initial version of the form will contain a single input field for "Email Address."
*   **EMS List Selection:** Within the Gutenberg block's inspector controls (sidebar), users will be able to select the specific subscriber list to which new email addresses should be added. This list will be dynamically populated by fetching available lists from the globally configured default EMS.
*   **Styling:**
    *   **Theme Inheritance:** The form will primarily inherit its styling from the active WordPress theme to ensure visual consistency.
    *   **Block Controls:** The block will provide basic Gutenberg core controls for minor adjustments:
        *   **Spacing:** Controls for padding/margin around the form.
        *   **Alignment:** Controls for the alignment of form elements.
        *   **Button Color:** A control to customize the submit button's background color.

### 4.4. Form Submission & Data Handling

*   **Successful Submission:** Upon valid form submission by a site visitor, the captured email address is sent directly to the selected list within the user's globally configured default EMS.
*   **Error Handling (on submission failure, e.g., EMS API unavailable, invalid email format for EMS):**
    *   **User Notification:** An appropriate error message will be displayed to the person attempting to submit the form (e.g., "Could not subscribe. Please try again later." or specific EMS error if suitable for public display).
    *   **Admin Notification:** An email notification will be sent to the site administrator's email address (as configured in WordPress General Settings). This email will contain:
        *   The error message returned by the EMS or a description of the internal error.
        *   The date and time of the failed submission.
    *   **No Local Storage of Submissions:** To respect user privacy and simplify V1.0, failed submissions (containing email addresses) will *not* be stored locally in the WordPress database for retry.

## 5. User Stories (V1 Focus)

*   **As a website owner,** I want to easily connect my Mailchimp (or ConvertKit) account to WordPress so I can start collecting email subscribers on my site without complex configurations.
*   **As a website owner,** I want to set a default EMS connection once in the plugin settings, so all my forms use it without needing to configure each one individually.
*   **As a content creator,** I want to add an email capture form directly into my blog posts or pages using the familiar Gutenberg editor.
*   **As a content creator,** I want to choose which specific email list (from my connected EMS) new subscribers are added to for each form I create.
*   **As a website owner,** I want the form to largely match my website's existing design, but I'd like simple controls to adjust spacing, alignment, and the button color if needed.
*   **As a website owner,** I want to be notified by email if form submissions start failing, so I can investigate any issues with my EMS connection or configuration promptly.
*   **As a website owner,** I want to see a simple count of how many successful submissions each of my forms has received, directly within my WordPress admin area.
*   **As a user setting up the plugin,** when I enter my EMS API key, I want the plugin to immediately tell me if the key is correct and the connection is working.
*   **As a user setting up the plugin,** I want helpful links to my EMS provider's documentation if I'm unsure where to find my API key.
*   **As a site visitor,** if my subscription fails, I want to see a message on the form so I know what happened.

## 6. Design and UX Considerations (V1)

*   **Simplicity and Ease of Use:** The primary focus for V1 is a straightforward and intuitive user experience for both setup and form creation.
*   **Clear Feedback:** Provide immediate and clear feedback for actions like API key validation (success/failure) and form submission (success/failure to the visitor).
*   **Helpful Guidance:** Include direct links to EMS provider documentation for API key retrieval to reduce user friction.
*   **Minimal Configuration for Block:** The Gutenberg block should be simple to configure, leveraging the global EMS default.
*   **Theme Compatibility:** Prioritize seamless integration with existing WordPress themes by inheriting styles.
*   **Non-Intrusive Admin Notifications:** Admin error emails should be informative but not overly frequent (e.g., consider debouncing if errors are persistent and identical, though this might be post-V1).

## 7. Technical Considerations (V1)

*   **EMS API Abstraction:** Implement a generic framework/interface for EMS integrations to allow for easier addition of new providers in the future.
*   **Secure API Key Storage:** API keys must be stored securely in the WordPress database (e.g., using WordPress encryption functions if appropriate, or at least not plain text).
*   **API Validation:** Each EMS integration module should include a method to validate API credentials.
*   **Gutenberg Block Development:** Utilize standard WordPress block development practices.
*   **Data Privacy:** No local storage of subscriber email addresses on submission failure to minimize privacy risks and complexity for V1.
*   **Error Logging:** Besides admin email notifications, server-side errors related to plugin functionality or EMS communication should be logged appropriately (e.g., to PHP error log or WordPress debug log if WP_DEBUG is enabled).

## 8. Success Metrics (V1)

*   Number of active plugin installations.
*   Number of successful EMS connections configured by users.
*   Total number of successful email submissions processed by the plugin across all users.
*   User feedback and reviews (qualitative).
*   The primary quantitative metric available within the plugin itself will be the submission counts per form, visible to the site admin.

## 9. Acceptance Criteria (V1)

1.  **EMS Configuration:**
    *   Users can navigate to a dedicated WP Capture settings page in WP Admin.
    *   Users can add a new EMS connection, selecting either Mailchimp or ConvertKit.
    *   Users can input API credentials for Mailchimp and save them.
    *   Users can input API credentials for ConvertKit and save them.
    *   Upon saving, API credentials are validated, and success or failure message is shown.
    *   Helpful links to Mailchimp/ConvertKit documentation for API keys are present near input fields.
    *   Users can designate one successfully configured EMS as the "global default."
2.  **Gutenberg Block Functionality:**
    *   A "WP Capture Form" block is available in the Gutenberg editor.
    *   The block, when added, displays a single input field for "Email Address" and a submit button.
    *   The block's inspector controls allow selection of a subscriber list fetched from the globally configured default EMS.
    *   The form inherits styling from the active WordPress theme.
    *   Block controls for spacing, alignment, and submit button color are functional and apply changes to the form in the editor and frontend.
3.  **Form Submission:**
    *   When a visitor submits a valid email, it is sent to the selected list in the globally configured default EMS.
    *   If the EMS submission fails (e.g., API down, invalid API key after initial setup), an error message is displayed to the visitor below the form.
    *   If the EMS submission fails, an email is sent to the site administrator's email address containing the EMS error details and a timestamp.
    *   No email data from failed submissions is stored in the WordPress database.
4.  **Analytics:**
    *   The plugin settings page displays a table.
    *   The table lists posts/pages containing the "WP Capture Form" block and their respective successful submission counts.
    *   Submission counts update accurately after successful submissions.

## 10. Future Considerations (Post-V1)

*   **Expanded EMS Support:** Integration with other popular EMS providers (e.g., AWeber, ActiveCampaign, Sendinblue, etc.).
*   **Advanced Form Fields:** Support for additional common fields (e.g., First Name, Last Name) and potentially custom fields.
*   **Enhanced Customization:** More granular form styling options (e.g., layout controls, typography, custom CSS input).
*   **Conditional Logic:** Ability to show/hide fields or change form behavior based on user input or other conditions.
*   **Submission Retry Queue:** Option to locally cache failed submissions (with user consent/awareness for privacy) and retry sending them later.
*   **Detailed Analytics:** More comprehensive analytics within the plugin (e.g., conversion rates per form, views vs. submissions, basic time-series data).
*   **Per-Block EMS Selection:** Allow users to choose a different configured EMS provider for individual blocks, overriding the global default.
*   **Alternative Embedding Options:** Support for shortcodes, widgets, or PHP functions for embedding forms.
*   **Pre-defined Form Templates:** Allow users to create and save reusable form templates (EMS, list, basic styling) in plugin settings for quick application via the Gutenberg block.
*   **Double Opt-in Handling:** Clearer guidance or specific features to support double opt-in workflows if required by EMS.
*   **GDPR/Privacy Tools:** Features to aid in compliance, such as consent checkboxes or links to privacy policies.
