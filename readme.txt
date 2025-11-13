=== Capture ===
Contributors: dannycooper
Tags: email, forms, subscribers, email marketing, newsletter
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin for capturing email subscriptions with EMS integration and local storage options.

== Description ==

Capture is a powerful WordPress plugin designed to help you build your email list with ease. Whether you're connecting to external Email Marketing Services (EMS) or storing subscribers locally, Capture provides a seamless solution for collecting and managing email subscriptions.

**Key Features:**

* **Dual Storage Options**: Connect to your favorite Email Marketing Service or store subscribers locally in your WordPress database
* **Block Editor Support**: Native WordPress block for easy form creation and customization
* **Local Subscriber Management**: Built-in admin interface to view, search, and manage subscribers
* **CSV Export**: Export your subscriber list for use in other tools
* **Privacy Compliant**: GDPR-ready with data retention settings and unsubscribe functionality
* **Customizable Forms**: Flexible form styling and layout options
* **Admin Notifications**: Optional email notifications for new subscribers
* **Search & Filtering**: Advanced admin tools to find and manage specific subscribers

**Supported EMS Providers:**

* Mailerlite
* Mailchimp
* Kit (previously known as ConvertKit)

**Perfect For:**

* Bloggers and content creators
* Small businesses building email lists
* Developers needing a flexible subscription solution
* Anyone wanting control over their subscriber data

**How It Works:**

1. **With EMS Connected**: Subscribers are automatically sent to your configured email marketing service
2. **Without EMS**: Subscribers are stored securely in your WordPress database with full admin management tools
3. **Always Functional**: Forms work regardless of your EMS connection status

== External services ==

This plugin connects to third-party Email Marketing Service (EMS) APIs to synchronize subscriber data when an EMS integration is configured. The plugin only connects to these services when users explicitly configure their API credentials in the plugin settings.

When an EMS connection is configured, the plugin sends subscriber information (email address and optional first name) to the selected email marketing service each time a user submits a subscription form.

**Supported External Services:**

* **ConvertKit (Kit)**: https://api.convertkit.com/v3/
  - Used for: Validating API credentials, retrieving available forms/lists, and subscribing users
  - Data sent: Email address, first name (optional), custom fields, and tags
  - Privacy policy: https://convertkit.com/privacy
  - Terms of service: https://convertkit.com/terms

* **Mailchimp**: https://{dc}.api.mailchimp.com/3.0/
  - Used for: Validating API credentials, retrieving available lists, and subscribing users
  - Data sent: Email address and first name (optional)
  - Privacy policy: https://www.intuit.com/privacy/statement/
  - Terms of service: https://www.intuit.com/legal/

* **MailerLite**: https://connect.mailerlite.com/api
  - Used for: Validating API credentials, retrieving available groups/lists, and subscribing users
  - Data sent: Email address and first name (optional)
  - Privacy policy: https://www.mailerlite.com/legal/privacy-policy
  - Terms of service: https://www.mailerlite.com/legal/terms-of-use

**Important Notes:**

- No data is sent to external services unless users explicitly configure an EMS connection
- When no EMS is configured, all subscriber data remains stored locally in your WordPress database
- Users can disable EMS integration at any time, reverting to local-only storage
- The plugin respects user privacy and only transmits data necessary for email marketing functionality

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/capture` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Capture screen to configure the plugin
4. Add the Capture block to any page or post to start collecting subscribers

== Frequently Asked Questions ==

= Does this plugin require an Email Marketing Service? =

No! Capture works with or without an external EMS connection. When no EMS is connected, subscribers are stored locally in your WordPress database with full management tools.

= What Email Marketing Services are supported? =

Capture supports integration with popular EMS providers. Check the plugin settings for the most up-to-date list of supported services.

= Can I export my subscribers? =

Yes! The plugin includes a CSV export feature that allows you to export all subscribers or filtered results from the admin interface.

= Is the plugin GDPR compliant? =

Yes, Capture includes privacy-compliant features including data retention settings, unsubscribe functionality, and proper data handling practices.

= Can I customize the subscription forms? =

Absolutely! The plugin includes a native WordPress block with extensive styling and layout customization options.

= Can I contribute to the plugin? =

Yes! Please feel free to contribute to the plugin by submitting a pull request or opening an issue. You can find the repository [here](https://github.com/dannycooper/capture).

= What happens to my data if I deactivate the plugin? =

Your subscriber data is safely preserved in your WordPress database.

== Screenshots ==

1. Capture block in the WordPress block editor
2. Subscriber management admin interface
3. Plugin settings page
4. CSV export functionality
5. Form display on frontend

== Changelog ==

= 1.0.5 =
* Fix typo in plugin URI

= 1.0.4 =
* Removed data retention system for simplified data handling
* Enhanced admin interface with new subscriber management tools
* Added dedicated CSS and JS assets for improved styling and functionality
* Fixed wp_kses_post stripping form elements in form-embed block
* Improved form rendering to allow necessary form HTML elements
* Major code optimization and cleanup across admin classes
* Updated EMS service implementations for better reliability
* Enhanced testing framework with improved bootstrap and unit tests
* Updated build system and dependency management
* Improved unsubscribe functionality with dedicated assets
* Code formatting improvements with consistent styling standards

= 1.0.3 =
* Added MailerLite EMS integration support
* Simplified form block structure (renamed wp-capture-form to form, wp-capture-form-embed to form-embed)
* Enhanced admin analytics and subscriber management
* Improved unsubscribe functionality
* Updated EMS service implementations
* Better testing framework integration

= 1.0.2 =
* Major block architecture refactoring for improved performance and maintainability
* Added MailerLite EMS integration support
* Simplified form block structure (renamed wp-capture-form to form, wp-capture-form-embed to form-embed)
* Removed data retention system for simplified data handling
* Enhanced admin analytics and subscriber management
* Code optimization and cleanup (significant codebase reduction)
* Improved unsubscribe functionality
* Updated EMS service implementations
* Better testing framework integration

= 1.0.1 =
* Initial release
* Email capture with EMS integration
* Local subscriber storage and management
* Block editor support
* CSV export functionality
* Admin notification system
* Privacy compliance features
* Search and filtering tools

== Upgrade Notice ==

= 1.0.2 =
Major update with improved performance, new MailerLite integration, and simplified block architecture. Significant code optimization included.

= 1.0.1 =
Initial release of Capture plugin. Start building your email list today!

== Developer Notes ==

**Hooks & Filters:**

The plugin provides various hooks and filters for developers to extend functionality:

* `capture_before_subscribe` - Action fired before processing subscription
* `capture_after_subscribe` - Action fired after successful subscription
* `capture_form_fields` - Filter to modify form fields
* `capture_success_message` - Filter to customize success messages

**Database:**

The plugin creates a `capture_subscribers` table to store local subscriptions with proper indexing for performance.

**Source Code:**

To view the unminified source code, visit the [GitHub repository](https://github.com/dannycooper/capture).
