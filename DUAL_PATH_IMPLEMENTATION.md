# Dual Submission Path Implementation

## Overview
The WP Capture plugin now supports dual submission paths:
1. **EMS Path**: Submit to external Email Marketing Service (Mailchimp, ConvertKit, etc.)
2. **Local Storage Path**: Store subscribers in WordPress database when no EMS is connected

## Implementation Details

### Backend Changes

#### 1. Updated AJAX Handler (`includes/frontend-ajax-handlers.php`)
- **Main function**: `wp_capture_ajax_submit_form()` now implements dual path logic
- **EMS Path**: `wp_capture_submit_to_ems()` - handles external service submission
- **Local Path**: `wp_capture_store_locally()` - saves to WordPress database
- **Analytics**: `wp_capture_record_analytics()` - unified analytics tracking

#### 2. Path Selection Logic
```php
// Determine submission path
$has_ems_connection = ! empty( $ems_connection_id ) && ! empty( $list_id ) && isset( $connections[ $ems_connection_id ] );

if ( $has_ems_connection ) {
    // Submit to EMS
    $result = wp_capture_submit_to_ems(...);
} elseif ( $enable_local_storage ) {
    // Store locally
    $result = wp_capture_store_locally(...);
} else {
    // Error: No valid submission path
    wp_send_json_error(...);
}
```

### Frontend Changes

#### 1. Block Editor Updates (`blocks/src/wp-capture-form/edit.js`)
- Added local storage notice when no EMS provider is selected
- Notice: "ℹ️ No EMS provider selected. Subscribers will be stored locally in your WordPress database."

#### 2. Form Render Updates (`blocks/src/wp-capture-form/render.php`)
- Removed requirement for EMS connection and list ID
- Form now renders when no EMS is configured (if local storage enabled)
- Conditional data attributes for EMS-specific fields

### Database Integration

#### 1. Local Storage Function
```php
function wp_capture_store_locally( $email, $form_id, $first_name, $post_id ) {
    // Create subscriber object
    $subscriber_data = array(
        'email'      => $email,
        'name'       => $first_name,
        'form_id'    => $form_id,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'status'     => 'active',
        'source_url' => get_permalink( $post_id ),
    );
    
    $subscriber = new WP_Capture_Subscriber( $subscriber_data );
    return $subscriber->save();
}
```

#### 2. Duplicate Handling
- Checks for existing email/form_id combination
- Returns user-friendly error: "This email address is already subscribed."

### User Experience

#### 1. Form Behavior
- **With EMS**: Works as before - submits to external service
- **Without EMS**: Stores locally with same user experience
- **Partially configured EMS**: Shows admin error to complete setup

#### 2. Success Messages
- Configurable success message from plugin options
- Default: "Thank you for subscribing!"
- Same message for both EMS and local submissions

#### 3. Error Handling
- User-friendly error messages for public display
- Detailed error logging for admin troubleshooting
- Graceful fallback handling

### Configuration Options

#### 1. Plugin Settings
```php
$options = array(
    'enable_local_storage' => true,  // Allow local storage
    'default_success_message' => 'Thank you for subscribing!',
    'ems_connections' => array(...)  // EMS configurations
);
```

#### 2. Form Validation
- Form requires either EMS connection OR local storage enabled
- Admin notices guide proper configuration

## Testing Scenarios

### 1. EMS Connected
- Form has `emsConnectionId` and `selectedListId`
- Submits to external EMS service
- Analytics recorded under form ID

### 2. No EMS Connection
- Form has no `emsConnectionId` or `selectedListId`
- Stores in local database table `wp_capture_subscribers`
- Analytics recorded under form ID

### 3. Local Storage Disabled
- Admin sets `enable_local_storage` to false
- Forms without EMS show configuration error
- Prevents accidental data loss

### 4. Partially Configured EMS
- EMS provider selected but no list chosen
- Shows admin error: "Please select a list for the selected EMS connection"
- Guides user to complete setup

## Database Schema

The existing `wp_capture_subscribers` table handles local storage:

```sql
CREATE TABLE wp_capture_subscribers (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    form_id VARCHAR(255) DEFAULT NULL,
    date_subscribed DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_agent TEXT DEFAULT NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    source_url TEXT DEFAULT NULL,
    UNIQUE KEY unique_email_form (email, form_id)
);
```

## Admin Interface

The existing admin interface at `wp-admin/admin.php?page=wp-capture-subscribers` provides:
- View all local subscribers
- Search and filter functionality
- CSV export capability
- Bulk management actions

## Next Steps

The dual submission path is now implemented. Remaining work from the PRD:
1. Settings page integration for local storage options
2. GDPR compliance features (unsubscribe links, data export)
3. Privacy policy integration
4. Email notifications for admin on new local subscribers

## Backward Compatibility

- Existing forms with EMS connections continue to work unchanged
- No data migration required
- All existing functionality preserved 