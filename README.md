# Auction Estimate Email System (AEES)

**Version:** 1.7.1
**Author:** Mubeen Hassan
**Requires WordPress:** 5.0 or higher
**Requires PHP:** 7.2 or higher
**License:** GPL v2 or later

## Description

The Auction Estimate Email System is a specialized WordPress plugin that manages a comprehensive three-party auction estimate workflow. It seamlessly integrates with Forminator forms to handle estimate requests, proposal creation, email delivery, and multi-step approval processes for auction houses and shipping estimates.

### Key Features

- **Forminator Integration** - Automatically captures and displays form submission data
- **Proposal Management** - Create, edit, and manage multiple proposals per estimate request
- **Email Automation** - Send professionally designed HTML emails to users and auction houses
- **Two-Step Approval Process** - User acceptance followed by auction house authorization
- **Token-Based Security** - Secure email response links with expiration tracking
- **Performance Optimized** - Smart caching system for 10x faster page loads
- **Modern Admin Interface** - Clean, responsive dashboard with status tracking
- **Email Expiration Control** - Configurable expiration periods for responses
- **Permanent Proposal History** - Complete audit trail that survives entry reopening
- **Entry Status Tracking** - Visual indicators for open/closed entries on dashboard
- **Smart Response Boxes** - Context-aware status displays with professional messaging

---

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [How It Works](#how-it-works)
- [User Guide](#user-guide)
- [Email Workflow](#email-workflow)
- [Database Schema](#database-schema)
- [Performance Features](#performance-features)
- [Security Features](#security-features)
- [Troubleshooting](#troubleshooting)
- [Developer Documentation](#developer-documentation)
- [Changelog](#changelog)

---

## Installation

### Requirements

1. WordPress 5.0 or higher
2. PHP 7.2 or higher
3. **Forminator Plugin** (Required) - [Download from WordPress.org](https://wordpress.org/plugins/forminator/)
4. MySQL 5.7 or higher

### Installation Steps

1. **Install Forminator Plugin**
   ```
   WordPress Admin → Plugins → Add New → Search "Forminator"
   Install and activate Forminator
   ```

2. **Upload AEES Plugin**
   - Upload the `auction-estimate-email-system` folder to `/wp-content/plugins/`
   - Or install via WordPress Admin → Plugins → Add New → Upload Plugin

3. **Activate the Plugin**
   - Navigate to WordPress Admin → Plugins
   - Find "Auction Estimate Email System"
   - Click "Activate"

4. **Automatic Setup**
   - Upon activation, the plugin will:
     - Create two custom database tables (`wp_aees_proposals` and `wp_aees_response_tokens`)
     - Generate a "Proposal Response" page for email responses
     - Initialize default settings

5. **Verify Installation**
   - Check for "Auction Estimate Emails" in the admin menu
   - Verify the response page exists at `/proposal-response/`

---

## Configuration

### Initial Setup

1. **Configure Settings**
   - Navigate to **Auction Estimate Emails → Settings**
   - Enter your Forminator Form ID
   - Configure email expiration periods (optional)
   - Click "Save Settings"

2. **Find Your Form ID**
   - Go to **Forminator → Forms**
   - Hover over your estimate form
   - Note the ID in the URL: `...forminator-cform-wizard&id=2902` → Form ID is `2902`

### Settings Reference

| Setting | Default | Description |
|---------|---------|-------------|
| **Forminator Form ID** | 2902 | The ID of the Forminator form used for estimate requests |
| **User Response Expiration** | 7 days | How long users have to accept/reject proposals |
| **Authorization Expiration** | 14 days | How long auction houses have to authorize accepted proposals |

### System Requirements Check

The Settings page displays system information including:
- Plugin version
- Database version
- Forminator status
- Response page status

---

## How It Works

### Complete Workflow

```
1. User submits estimate request via Forminator form
   ↓
2. Admin receives notification and views submission in AEES dashboard
   ↓
3. Admin creates proposal(s) with pricing and details
   ↓
4. Admin sends proposals to user via email
   ↓
5. User clicks Accept or Reject in email
   ↓
6a. If REJECTED → Workflow ends, admin receives notification
6b. If ACCEPTED → Continue to step 7
   ↓
7. System sends authorization request to auction house
   ↓
8. Auction house clicks Authorize in email
   ↓
9. Admin receives final authorization confirmation
   ↓
10. Workflow complete ✓
```

### Status Progression

| Status | Description | Next Action |
|--------|-------------|-------------|
| **Pending** | Proposal created but not sent | Send email to user |
| **Email Sent** | Awaiting user response | User accepts/rejects |
| **Accepted** | User accepted, awaiting authorization | Auction house authorizes |
| **Rejected** | User rejected proposal | Workflow ends |
| **Authorized** | Fully approved and complete | Archive/process order |
| **Expired** | Response period elapsed | Resend or cancel |

---

## User Guide

### Dashboard Overview

Access: **Auction Estimate Emails** (top-level menu)

The dashboard displays all form submissions with:
- **Entry ID** - Unique submission identifier
- **Date Submitted** - When the form was submitted
- **User Email** - Submitter's email address
- **User Response** - Accept/Reject status with color badges
- **Auction Approval** - Authorization status
- **Actions** - Edit button to manage proposals

### Creating Proposals

1. **Access Entry**
   - Dashboard → Click "Edit" on any submission

2. **View Form Data**
   - Review all submitted form fields
   - Check user contact information
   - Examine uploaded files/documents

3. **Add Proposal**
   - Click "Add Proposal" button
   - Fill in proposal details:
     - **Proposal Title** - Brief description (e.g., "Standard Shipping")
     - **Price** - Numeric value (e.g., 150.00)
     - **Details** - Full description with formatting (WYSIWYG editor)

4. **Multiple Proposals**
   - Click "Add Proposal" again for additional options
   - Users can choose from multiple pricing tiers
   - Each proposal is tracked independently

5. **Save Proposals**
   - Click "Save Entry" button
   - Proposals are saved to database
   - Cache is automatically refreshed

### Sending Email to User

1. **Enter Auction House Email**
   - Required for authorization step
   - Format: `auctions@example.com`

2. **Review Proposals**
   - Verify all proposals are correct
   - Check pricing and details

3. **Send Email**
   - Click "Send Email" button
   - Confirmation prompt appears
   - Email is sent immediately

4. **Email Contains:**
   - Professional header with logo
   - Form submission summary
   - All proposals with Accept/Reject buttons
   - Unique token-based response links
   - Expiration notice (7 days default)

### Managing Responses

#### User Response Tracking

After sending email, the page displays:

- **Email Status** - Sent timestamp and expiration
- **Response Cards** - One per proposal showing:
  - Proposal title and price
  - Response status (Pending/Accepted/Rejected)
  - Response date/time if received
  - Authorization status (if accepted)

#### Status Badges

| Badge | Color | Meaning |
|-------|-------|---------|
| Pending | Yellow | Awaiting user response |
| Accepted | Green | User approved proposal |
| Rejected | Red | User declined proposal |
| Authorized | Blue | Auction house approved |
| Expired | Gray | Response period ended |

### Refreshing Cache

The plugin uses smart caching for performance:

- **Automatic** - Cache expires after 1 hour
- **Manual Refresh** - Click "Refresh Cache" button to force update
- **When to Refresh:**
  - After form structure changes
  - If data appears outdated
  - After Forminator updates

---

## Email Workflow

### Email Types

The plugin sends five distinct email types:

#### 1. User Proposals Email

**To:** Form submitter
**Subject:** "Your Auction Estimate Proposals"
**Contains:**
- Submission summary
- All proposals with pricing
- Accept/Reject buttons (unique tokens)
- Expiration notice

**Email Features:**
- Responsive HTML design
- Mobile-friendly layout
- Professional branding with logo
- Secure token-based links

#### 2. Admin User Response Notification

**To:** Site administrator
**When:** User accepts or rejects a proposal
**Contains:**
- User's decision (Accept/Reject)
- Proposal details
- Link to admin edit page

#### 3. Auction House Authorization Request

**To:** Auction house (email provided by admin)
**When:** User accepts a proposal
**Contains:**
- Accepted proposal details
- Authorization button
- 14-day expiration notice
- Contact information

#### 4. Admin Authorization Notification

**To:** Site administrator
**When:** Auction house authorizes a proposal
**Contains:**
- Authorization confirmation
- Authorized proposal details
- Next steps information

#### 5. Admin Acceptance Notification

**To:** Site administrator
**When:** User accepts before authorization
**Contains:**
- User acceptance details
- Reminder to await authorization

### Email Design

All emails feature:
- Modern, professional HTML layout
- Responsive design for mobile devices
- Site logo (custom logo or site icon)
- Brand colors and consistent styling
- Clear call-to-action buttons
- Footer with site name and branding

### Response Page

URL: `/proposal-response/`

**Purpose:** Displays confirmation messages when users/auction houses click email links

**States:**
- **Success** - Response recorded successfully
- **Already Responded** - Duplicate response attempt
- **Expired** - Token has expired
- **Invalid** - Token not found or malformed
- **Error** - System error occurred

**Features:**
- Clean, standalone page design
- No header/footer distractions
- Animated success icons
- Mobile-responsive layout
- Clear next steps

---

## Database Schema

### Table: `wp_aees_proposals`

Stores proposal data and email tracking.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) | Primary key, auto-increment |
| `entry_id` | INT(11) | Forminator entry ID (unique) |
| `auction_house_email` | VARCHAR(255) | Auction house contact email |
| `proposals` | LONGTEXT | JSON array of proposals |
| `email_sent_at` | DATETIME | When proposals were emailed |
| `email_expires_at` | DATETIME | When email links expire |
| `date_created` | DATETIME | Record creation timestamp |
| `date_updated` | DATETIME | Last modification timestamp |

**Indexes:**
- Primary: `id`
- Unique: `entry_id`
- Indexed: `date_created`, `date_updated`, `email_sent_at`, `email_expires_at`

**Proposal JSON Structure:**
```json
[
  {
    "uid": "unique-id-12345",
    "title": "Standard Shipping",
    "price": "150.00",
    "details": "Full proposal description with HTML"
  }
]
```

### Table: `wp_aees_response_tokens`

Stores response tokens for O(1) lookup performance.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT(20) | Primary key, auto-increment |
| `token` | VARCHAR(64) | User response token (unique) |
| `entry_id` | INT(11) | Forminator entry ID |
| `proposal_uid` | VARCHAR(50) | Proposal unique identifier |
| `status` | VARCHAR(20) | pending, accepted, rejected |
| `created_at` | DATETIME | Token creation time |
| `expires_at` | DATETIME | User response expiration |
| `authorization_token` | VARCHAR(64) | Auction house auth token (unique) |
| `authorization_expires_at` | DATETIME | Authorization expiration |
| `authorized_at` | DATETIME | When authorization completed |
| `authorized_by` | VARCHAR(255) | Authorizer email/info |

**Indexes:**
- Primary: `id`
- Unique: `token`, `authorization_token`
- Indexed: `entry_id + proposal_uid`, `status`, `expires_at`, `authorization_expires_at`

**Token Generation:**
- Uses HMAC-SHA256 for security
- Format: `hash_hmac('sha256', $data, wp_salt())`
- Cryptographically secure random values

### Table: `wp_aees_proposal_history`

Permanent storage for all proposal responses (added in v1.7.0).

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT(20) | Primary key, auto-increment |
| `entry_id` | INT(11) | Forminator entry ID |
| `proposal_uid` | VARCHAR(50) | Proposal unique identifier |
| `proposal_title` | VARCHAR(255) | Proposal title |
| `proposal_price` | VARCHAR(50) | Proposal price |
| `proposal_details` | LONGTEXT | Full proposal description |
| `status` | VARCHAR(20) | rejected, accepted, authorized, invalid |
| `user_email` | VARCHAR(255) | User who responded (optional) |
| `user_response_date` | DATETIME | When user responded |
| `authorization_status` | VARCHAR(20) | Authorization state (if applicable) |
| `authorization_date` | DATETIME | When authorized |
| `authorized_by` | VARCHAR(255) | Who authorized |
| `created_at` | DATETIME | History record creation |

**Indexes:**
- Primary: `id`
- Indexed: `entry_id`, `proposal_uid`, `status`, `user_response_date`, `created_at`

**Purpose:**
- Permanent audit trail of all proposal responses
- Survives entry reopening and proposal clearing
- Complete historical record for compliance and reporting
- Enables rejection history display across multiple reopen cycles

---

## Performance Features

### Smart Caching System

The plugin implements a multi-layer caching strategy:

#### 1. Form Structure Caching

**What:** Field labels, types, groups from Forminator
**Duration:** 1 hour (configurable)
**Benefit:** Reduces database queries by 80%
**Cache Key:** `aees_form_structure_{form_id}`

#### 2. Submission Data Caching

**What:** Processed form submission data
**Duration:** 1 hour (configurable)
**Benefit:** 10x faster page loads (~2s → ~0.2s)
**Cache Key:** `aees_submission_data_{entry_id}`

#### 3. Token Table Optimization

**What:** Separate table for response tokens
**Benefit:** O(1) lookup instead of O(n) JSON parsing
**Performance:** Instant status checks for thousands of proposals

### Cache Management

**Automatic Expiration:**
- Transients expire after 1 hour
- WordPress automatically cleans expired transients
- No manual intervention required

**Manual Refresh:**
- Click "Refresh Cache" button on edit page
- Clears both form structure and submission data
- Useful after Forminator changes

**Cache Invalidation:**
- Automatically cleared when proposals are saved
- Ensures data consistency
- No stale data issues

---

## Security Features

### Input Validation

- **Nonce Verification** - All AJAX requests protected with WordPress nonces
- **Capability Checks** - `manage_options` required for admin access
- **Input Sanitization**:
  - `sanitize_text_field()` for text inputs
  - `sanitize_email()` for email addresses
  - `wp_kses_post()` for proposal details (allows safe HTML)
  - `absint()` for numeric IDs

### Output Escaping

All output is properly escaped:
- `esc_html()` - Plain text output
- `esc_url()` - URL attributes
- `esc_attr()` - HTML attributes
- `wp_kses_post()` - Rich text content

### Token Security

**Generation:**
- HMAC-SHA256 algorithm
- WordPress salt as secret key
- Microtime + random data for uniqueness

**Storage:**
- Tokens stored in separate database table
- Never exposed in URLs as plaintext IDs
- Unique indexes prevent duplicates

**Validation:**
- Token existence checked before processing
- Expiration dates enforced
- Status checked to prevent duplicate responses

### SQL Injection Prevention

- WordPress `$wpdb->prepare()` used for all queries
- Parameterized queries throughout
- No direct SQL concatenation

### Email Security

- Recipient addresses validated
- No user input directly in email headers
- WordPress `wp_mail()` function used
- HTML content sanitized

---

## Troubleshooting

### Common Issues

#### 1. "Form not found" Error

**Symptoms:** Settings page shows "Form not found" warning
**Cause:** Incorrect Form ID or Forminator not active

**Solutions:**
1. Verify Forminator plugin is active
2. Check Form ID in Forminator → Forms
3. Update Form ID in Settings page
4. Save settings and refresh

#### 2. Emails Not Sending

**Symptoms:** "Send Email" button succeeds but emails not received
**Possible Causes:**
- WordPress email configuration issues
- Server SMTP restrictions
- Spam filters blocking emails

**Solutions:**
1. Install and configure SMTP plugin (e.g., WP Mail SMTP)
2. Check spam/junk folders
3. Verify auction house email address
4. Test WordPress email with other plugins
5. Check server email logs

#### 3. Response Page Not Found

**Symptoms:** 404 error when clicking email links
**Cause:** Proposal Response page deleted or missing

**Solutions:**
1. Go to Auction Estimate Emails dashboard
2. Click "Create Page Now" button if shown
3. Or manually create page with slug `proposal-response`
4. Flush WordPress permalinks (Settings → Permalinks → Save)

#### 4. Slow Page Loads

**Symptoms:** Edit entry page takes several seconds to load
**Cause:** Cache expired or disabled

**Solutions:**
1. Wait for cache to rebuild (first load after expiration)
2. Check for Forminator performance issues
3. Ensure transients are enabled in WordPress
4. Verify database server performance

#### 5. Token Expired Messages

**Symptoms:** Users see "Token Expired" when clicking email links
**Cause:** Response period elapsed

**Solutions:**
1. Check email expiration settings (Settings page)
2. Increase expiration days if needed (default: 7 days)
3. Resend email with fresh tokens
4. Contact user directly if urgent

#### 6. Missing Form Data

**Symptoms:** Edit entry page shows "Form data not available"
**Causes:**
- Entry deleted in Forminator
- Form ID changed
- Forminator database corruption

**Solutions:**
1. Verify entry exists in Forminator → Submissions
2. Check Form ID matches in Settings
3. Click "Refresh Cache" button
4. Reinstall Forminator if corrupted

### Debug Mode

To enable debugging:

1. Edit `wp-config.php`
2. Add:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
3. Check logs in `wp-content/debug.log`

### Getting Help

If issues persist:

1. Check CHANGELOG.md for known issues
2. Review WordPress error logs
3. Deactivate other plugins to check for conflicts
4. Contact: Mubeen Hassan
5. Provide:
   - WordPress version
   - PHP version
   - Plugin version
   - Error messages from logs
   - Steps to reproduce

---

## Developer Documentation

### Class Structure

The plugin follows a modular, service-oriented architecture:

| Class | File | Purpose |
|-------|------|---------|
| `AEES_Admin_Page` | `class-admin-page.php` | Dashboard menu and listing |
| `AEES_Settings_Page` | `class-settings-page.php` | Settings interface and configuration |
| `AEES_Submission_Table` | `class-submission-table.php` | WP_List_Table for submissions |
| `AEES_Edit_Entry_Page` | `class-edit-entry-page.php` | Edit page controller |
| `AEES_Form_Data_Handler` | `class-form-data-handler.php` | Forminator integration |
| `AEES_Proposal_Data_Handler` | `class-proposal-data-handler.php` | Database CRUD operations |
| `AEES_Proposal_Email_Manager` | `class-proposal-email-manager.php` | Email composition and sending |
| `AEES_Proposal_Response_Handler` | `class-proposal-response-handler.php` | Response processing |

### Hooks and Filters

**Actions:**
```php
// Suppress admin notices on edit page
add_action('admin_head', [$this, 'suppress_notices']);

// Handle AJAX requests
add_action('wp_ajax_aees_save_entry', [$this, 'ajax_save_entry']);
add_action('wp_ajax_aees_send_email', [$this, 'ajax_send_email']);
add_action('wp_ajax_aees_refresh_cache', [$this, 'ajax_refresh_cache']);

// Public response handlers
add_action('template_redirect', [$this, 'handle_proposal_response']);
add_action('template_redirect', [$this, 'handle_authorization_response']);
```

**Filters:**
```php
// Custom response template
add_filter('template_include', [$this, 'load_response_template']);
```

### Helper Functions

**Get Form ID:**
```php
$form_id = AEES_Settings_Page::get_form_id();
```

**Get Expiration Days:**
```php
$user_days = AEES_Settings_Page::get_user_response_expiration_days();
$auth_days = AEES_Settings_Page::get_authorization_expiration_days();
```

**Clear Cache:**
```php
$form_data_handler = new AEES_Form_Data_Handler();
$form_data_handler->clear_submission_cache($entry_id);
```

### Database Queries

**Get Proposal Data:**
```php
$data_handler = new AEES_Proposal_Data_Handler();
$proposal_data = $data_handler->get_proposal_data($entry_id);
```

**Get Token Status:**
```php
$token_data = $data_handler->get_token_by_value($token);
```

### Creating Custom Email Templates

Email templates are located in `templates/emails/`

Basic structure:
```php
<?php
// templates/emails/custom-email.php
if (!defined('ABSPATH')) exit;

// Variables available:
// $site_name, $logo_url, $user_email, $proposals, etc.
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?></title>
</head>
<body>
    <!-- Your email content -->
</body>
</html>
```

### Custom Styling

CSS files in `assets/css/`:
- `admin-style.css` - Dashboard styling
- `edit-entry.css` - Edit page styling
- `edit-entry-responses.css` - Response tracking styling

JavaScript files in `assets/js/`:
- `edit-entry.js` - Edit page functionality
- `admin-script.js` - Dashboard scripts

### Extending the Plugin

**Add Custom Proposal Fields:**

1. Modify proposal save handler in `class-edit-entry-page.php`
2. Update email templates to display new fields
3. Adjust JavaScript validation in `edit-entry.js`

**Add Custom Email Types:**

1. Create new method in `AEES_Proposal_Email_Manager`
2. Create template in `templates/emails/`
3. Call from appropriate handler

**Add Custom Status Types:**

1. Update database schema (migration function)
2. Modify status badges in templates
3. Update email logic in handlers

---

## System Requirements

### Server Requirements

- **PHP:** 7.2 or higher (7.4+ recommended)
- **MySQL:** 5.7 or higher
- **WordPress:** 5.0 or higher (latest version recommended)
- **PHP Extensions:**
  - `mysqli`
  - `mbstring`
  - `json`
- **WordPress Functions:**
  - Transients API
  - AJAX API
  - WP Mail

### Plugin Dependencies

- **Forminator** (Required) - Free form builder plugin
- **WP Mail SMTP** (Recommended) - For reliable email delivery

### Recommended Server Configuration

```
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

---

## Support and Contribution

### Author

**Mubeen Hassan**
Plugin Developer

### Reporting Issues

When reporting issues, please include:

1. Plugin version (found in Settings)
2. WordPress version
3. PHP version
4. Forminator version
5. Detailed description of the problem
6. Steps to reproduce
7. Screenshots (if applicable)
8. Error messages from debug log

### Feature Requests

Suggestions for new features are welcome. Consider:

- Use case and business value
- Technical feasibility
- Impact on existing functionality
- Backward compatibility

---

## Credits

### Built With

- **WordPress** - Content Management System
- **Forminator** - Form Builder Integration
- **SweetAlert2** - Beautiful alerts and notifications
- **TinyMCE** - Rich text editor

### Icons and Graphics

- **Dashicons** - WordPress icon font
- Email templates use responsive HTML/CSS

---

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and release notes.

---

**Last Updated:** 2024
**Documentation Version:** 1.5.0
