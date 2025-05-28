# WP Capture Plugin - Local Subscribers Feature

## Overview
When users don't have an EMS (Email Marketing Service) connected, the WP Capture plugin should store email addresses locally in a WordPress database table and provide admin tools to manage these subscribers.

## Current State Analysis
- Form currently requires EMS provider selection to function
- No fallback mechanism when EMS is unavailable
- Form becomes non-functional without EMS connection

## Feature Requirements

### 1. Database Schema
**New Table: `wp_capture_subscribers`**
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
    UNIQUE KEY unique_email_form (email, form_id),
    INDEX idx_email (email),
    INDEX idx_form_id (form_id),
    INDEX idx_date_subscribed (date_subscribed),
    INDEX idx_status (status)
);
```

### 2. Form Behavior Updates

#### 2.1 Frontend Form Logic
- **When EMS is connected**: Continue current behavior (submit to EMS)
- **When no EMS connected**: Submit to local database via REST API
- **Form validation**: Same validation rules apply
- **Success message**: Display configurable success message

#### 2.2 Block Editor Updates
- Update form preview to show it's functional even without EMS
- Add notice when no EMS is connected: "Subscribers will be stored locally"
- Keep all existing styling and layout options

### 3. Backend API Endpoints

#### 3.1 Subscription Endpoint
```
POST /wp-json/wp-capture/v1/subscribe
```
**Request Body:**
```json
{
    "email": "user@example.com",
    "name": "John Doe",
    "form_id": "block-uuid",
    "source_url": "https://example.com/page"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Successfully subscribed!",
    "subscriber_id": 123
}
```

#### 3.2 Admin API Endpoints
```
GET /wp-json/wp-capture/v1/admin/subscribers
GET /wp-json/wp-capture/v1/admin/subscribers/export
DELETE /wp-json/wp-capture/v1/admin/subscribers/{id}
```

### 4. Admin Interface

#### 4.1 Main Subscribers Page
**Location:** `wp-admin/admin.php?page=wp-capture-subscribers`

**Features:**
- Paginated table of all subscribers
- Columns: Email, Name, Form ID, Date Subscribed, Status, Actions
- Search by email or name
- Filter by form ID, date range, status
- Bulk actions: Delete, Export selected
- Total subscriber count display

#### 4.2 Export Functionality
**CSV Export Features:**
- Export all subscribers or filtered results
- Filename format: `wp-capture-subscribers-YYYY-MM-DD.csv`
- CSV Headers: Email, Name, Form ID, Date Subscribed, Status, Source URL

#### 4.3 Individual Subscriber Management
- View subscriber details
- Change subscriber status (active/unsubscribed)
- Delete individual subscribers
- View subscription source and metadata

### 5. Frontend Form Submission Flow

#### 5.1 JavaScript Handling
```javascript
// Update form submission logic in frontend
async function handleFormSubmission(formData) {
    // Check if EMS is configured for this form
    if (hasEMSConnection) {
        // Submit to EMS (existing logic)
        return submitToEMS(formData);
    } else {
        // Submit to local database
        return submitToLocal(formData);
    }
}
```

#### 5.2 Form State Management
- Add loading states during submission
- Handle success/error responses appropriately
- Prevent duplicate submissions
- Clear form on successful submission

### 6. Settings Integration

#### 6.1 Plugin Settings Updates
- Add toggle: "Enable local subscriber storage"
- Add setting for default success message
- Add GDPR compliance text option
- Email notification settings for new subscribers

#### 6.2 Privacy Considerations
- Add privacy policy text for data collection
- Implement data retention settings
- Add unsubscribe functionality via email links
- GDPR-compliant data export/deletion

### 7. Implementation Tasks

#### Phase 1: Database & Core Logic
1. Create database migration for subscribers table
2. Implement subscriber model/class
3. Create REST API endpoints for subscription
4. Update form submission logic to handle local storage

#### Phase 2: Admin Interface
1. Create admin menu page for subscribers
2. Build subscribers list table with pagination
3. Implement search and filtering
4. Add CSV export functionality

#### Phase 3: Frontend Integration
1. Update block edit.js to show local storage notice
2. Modify frontend JavaScript for dual submission paths
3. Update form validation and success handling
4. Add unsubscribe link generation

#### Phase 4: Settings & Privacy
1. Add plugin settings for local subscribers
2. Implement privacy compliance features
3. Add email notifications for admin
4. Create unsubscribe page functionality

### 8. Testing Requirements

#### 8.1 Functional Testing
- Form submission with no EMS connected
- Form submission with EMS connected (ensure no regression)
- Admin interface CRUD operations
- CSV export with various filters
- Duplicate email handling

#### 8.2 Performance Testing
- Large subscriber list pagination
- CSV export with thousands of records
- Database query optimization
- Memory usage during exports

### 9. Success Metrics
- Zero form submission failures when no EMS connected
- Admin can successfully export subscriber data
- Database performance remains optimal
- User experience maintains current quality

### 10. Future Enhancements
- Email marketing campaigns for local subscribers
- Subscriber analytics and growth tracking
- Import subscribers from CSV
- Integration with popular email marketing tools
- Automated email sequences for local subscribers
