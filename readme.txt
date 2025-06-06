=== Capture ===
Contributors: dannycooper
Tags: email, forms, subscribers, email marketing, newsletter
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
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

**Perfect For:**

* Bloggers and content creators
* Small businesses building email lists
* Developers needing a flexible subscription solution
* Anyone wanting control over their subscriber data

**How It Works:**

1. **With EMS Connected**: Subscribers are automatically sent to your configured email marketing service
2. **Without EMS**: Subscribers are stored securely in your WordPress database with full admin management tools
3. **Always Functional**: Forms work regardless of your EMS connection status

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

= What happens to my data if I deactivate the plugin? =

Your subscriber data is safely preserved in your WordPress database. The plugin only removes scheduled tasks when deactivated.

== Screenshots ==

1. Capture block in the WordPress block editor
2. Subscriber management admin interface
3. Plugin settings page
4. CSV export functionality
5. Form display on frontend

== Changelog ==

= 1.0.0 =
* Initial release
* Email capture with EMS integration
* Local subscriber storage and management
* Block editor support
* CSV export functionality
* Admin notification system
* Privacy compliance features
* Search and filtering tools

== Upgrade Notice ==

= 1.0.0 =
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

**REST API:**

Capture includes REST API endpoints for subscription handling and admin management:

* `POST /wp-json/capture/v1/subscribe` - Submit subscription
* `GET /wp-json/capture/v1/admin/subscribers` - Get subscribers (admin only)
* `DELETE /wp-json/capture/v1/admin/subscribers/{id}` - Delete subscriber (admin only)

For more technical documentation, visit the [GitHub repository](https://github.com/dannycooper/capture).
