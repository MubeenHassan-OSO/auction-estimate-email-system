# Changelog

All notable changes to the Auction Estimate Email System plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.7.1] - 2025-01-28

### Fixed
- **Email Formatting Bug** - HTML and formatting now displays correctly in all emails
  - Removed double sanitization that was stripping HTML formatting
  - Proposal details in emails now preserve bold, italic, lists, and other formatting
  - Data is still securely sanitized once with `wp_kses_post()` when saved to database
  - Affected templates: user-proposals.php, admin-notification.php, admin-authorization-complete.php, admin-acceptance-notification.php
- **Line Breaks Not Displaying in Emails** - Line breaks and paragraphs now render properly
  - Added `wpautop()` function to convert plain text line breaks to HTML `<p>` and `<br>` tags
  - Single line breaks → `<br>` tags
  - Double line breaks → `<p>` paragraph tags with proper spacing
  - Works with WYSIWYG editor content while preserving existing HTML
  - Fixed in all 4 email templates
- **Critical Security Bug** - Email links now properly invalidate when admin manually closes entry
  - User response links (Accept/Reject) are now invalid if admin closes entry before user responds
  - Auction house authorization links are now invalid if admin closes entry before authorization
  - Prevents responses from being processed after admin has manually closed an entry
  - Added entry status validation in `handle_proposal_response()` and `handle_authorization_response()` methods
  - Users see clear error message: "This request has been closed by the administrator. This link is no longer valid."
  - Ensures complete admin control over entry workflow

### Security
- Email link validation now includes entry status check
- Prevents unauthorized responses after manual entry closure
- Proper error messaging for closed entry link attempts

### Technical Details
- Updated `class-proposal-response-handler.php` with entry status validation
- Modified all email templates to use `wpautop()` for line break conversion
- Removed redundant `wp_kses_post()` calls from email output (data already sanitized on save)

---

## [1.7.0] - 2025-01-27

### Added
- **Permanent Proposal History Table** - Major data persistence improvement
  - New database table: `wp_aees_proposal_history`
  - Stores complete history of all proposal responses (rejected, accepted, authorized)
  - History survives entry reopening - never loses data
  - Comprehensive audit trail with timestamps and user email tracking
  - Migration function automatically preserves existing rejection data
  - Database version 1.4 schema upgrade
- **Entry Status Column** - Enhanced main admin dashboard
  - New "Entry Status" column showing Open/Closed state
  - Visual badges: 🔓 Open (blue) and 🔒 Closed (gray)
  - Instant visibility of entry availability
  - Consistent styling with other status columns
- **History Recording on All Actions**
  - Proposals saved to history when user rejects
  - Proposals saved to history when user accepts
  - Proposals saved to history when auction house authorizes
  - Proposals saved to history before entry is reopened
  - Automatic duplicate prevention

### Changed
- **Consolidated Duplicate Messages** - Improved UX
  - Single unified notice for proposal decline + entry closure
  - Single unified notice for proposal acceptance + entry closure
  - Clear messaging: "Proposal Declined - Entry Closed"
  - Eliminated confusing dual messages
- **Response Status Boxes** - Smarter display logic
  - Only shown when email sent OR user has responded
  - Hidden on fresh entries with no proposals
  - Hidden after entry reopening
  - Updates correctly after new responses
- **Professional Terminology Updates**
  - "User Response" → "Customer Response"
  - "Rejected" → "Declined" (softer language)
  - "Waiting for user response" → "Awaiting customer response"
  - "Pending authorization" → "Awaiting authorization"
  - "No response yet" → "Awaiting customer response"
- **Admin Dashboard Improvements**
  - "N/A" shown when no email sent (instead of "Pending")
  - Context-aware Auction House status (shows "N/A - Proposal declined" when rejected)
  - Unified badge styling across all status columns
  - Green color scheme for Accepted/Approved (changed from purple)
  - Authorization date displayed when approved
- **Rejection History Filtering**
  - Only shows rejected/invalid proposals (excludes accepted/authorized)
  - Prevents accepted proposals from appearing in rejection history
  - Cleaner, more accurate historical view

### Fixed
- **Customer Response Not Updating** - After entry reopen and new acceptance
  - `$responded_proposal` variable now available globally in template
  - Status boxes correctly reflect current proposal state
  - Fixed scope issue preventing status display
- **Accepted Proposals in Rejection History** - Data filtering bug
  - Added SQL filter: `WHERE status IN ('rejected', 'invalid')`
  - Accepted proposals only in main proposal view
  - History section accurately named and filtered
- **History Loss on Entry Reopen** - Critical data preservation
  - `clear_proposals()` now saves all responses to history before clearing
  - Multiple reopen cycles accumulate complete history
  - No data loss regardless of workflow

### Database
- **Version 1.4 Migration**
  - Creates `wp_aees_proposal_history` table
  - Columns: id, entry_id, proposal_uid, proposal_title, proposal_price, proposal_details, status, user_email, user_response_date, authorization_status, authorization_date, authorized_by, created_at
  - Indexes on entry_id, proposal_uid, status, user_response_date, created_at
  - Automatic migration of existing rejected/accepted proposals
  - Runs once on first admin visit after update
  - Duplicate prevention during migration

### Performance
- Query optimization: Entry status fetched in single JOIN
- History queries use indexed columns for fast retrieval
- Efficient duplicate checking on history inserts

### UI/UX Improvements
- Response Status Boxes: "Awaiting customer response" → "Awaiting customer acceptance"
- Authorization status shows "N/A - Proposal declined" when user rejects
- Entry Status badges added to main dashboard with lock icons
- All status badges use consistent `.aees-approval-*` class structure
- Green gradient badges for positive responses (Accepted/Approved)
- Gray badges for N/A states
- Yellow badges for pending states
- Red badges for declined/rejected states

### Developer Notes
- New method: `save_proposal_to_history($entry_id, $proposal, $user_email)`
- Updated method: `get_rejection_history($entry_id)` now reads from history table
- Updated method: `clear_proposals($entry_id)` saves to history before clearing
- History saving integrated into response handler methods:
  - `process_rejection()` - Saves rejected/invalid proposals
  - `process_acceptance()` - Saves accepted proposals
  - `process_authorization()` - Updates history with authorization
- Template variables: `$responded_proposal` available at top scope

---

## [1.6.0] - 2024-TBD

### Added
- **Settings Page** - New admin settings interface for plugin configuration
  - Configurable Forminator Form ID (no more hardcoded values)
  - Adjustable email expiration periods for user responses
  - Adjustable authorization expiration periods
  - System information dashboard
  - Forminator status check
  - Response page status verification
  - Form validation with real-time feedback
- **Comprehensive Documentation**
  - Complete README.md with installation, configuration, and usage guides
  - CHANGELOG.md for version tracking
  - Developer documentation with class structure and API reference
  - Troubleshooting guide with common issues and solutions
  - Performance optimization documentation
  - Security features documentation

### Changed
- **Form ID Management** - Replaced hardcoded Form ID (2902) with settings-based configuration
  - Updated `AEES_Form_Data_Handler` to use `AEES_Settings_Page::get_form_id()`
  - Removed unused form ID property from `AEES_Edit_Entry_Page`
  - Settings provide fallback to default if not configured
- **Code Organization** - Improved modularity and maintainability
  - Settings page properly integrated into plugin initialization
  - Clear separation of concerns between configuration and functionality

### Fixed
- Settings menu now properly appears under main AEES menu
- Form ID can be changed without editing code
- Better error handling for missing Forminator form

### Developer Notes
- New static helper methods in `AEES_Settings_Page`:
  - `get_form_id()` - Retrieve configured form ID with fallback
  - `get_user_response_expiration_days()` - Get user response expiration setting
  - `get_authorization_expiration_days()` - Get authorization expiration setting

---

## [1.5.0] - 2024

### Added
- **Response Tracking Interface** - Enhanced UI for tracking proposal responses
  - Response status cards for each proposal
  - Visual status indicators (Pending/Accepted/Rejected/Authorized)
  - Real-time status updates
  - Response timestamp tracking
  - Authorization status display
- **Email Expiration Tracking**
  - Email sent timestamp logging
  - Expiration date calculation and display
  - Visual warnings for expired emails
- **Advanced CSS Styling** - `edit-entry-responses.css`
  - Modern response card design
  - Color-coded status badges
  - Responsive layout for all devices
  - Smooth animations and transitions

### Changed
- Enhanced edit entry page to display response history
- Improved email status visualization
- Better mobile responsiveness for response tracking

### Performance
- Optimized database queries for response token lookups
- Reduced page load time by caching response data

---

## [1.4.0] - 2024

### Added
- **Cache Refresh Functionality**
  - Manual cache refresh button on edit page
  - AJAX endpoint for cache clearing: `aees_refresh_cache`
  - Force refresh option for submission data
  - User feedback with SweetAlert2 notifications
- **Performance Monitoring**
  - Cache hit/miss tracking
  - Performance benchmarks in code comments
  - Transient key standardization

### Changed
- Improved cache invalidation strategy
- Better cache key naming conventions
- Enhanced user feedback for cache operations

### Developer Notes
- New method: `AEES_Form_Data_Handler::clear_submission_cache()`
- Cache keys follow pattern: `aees_{type}_{identifier}`

---

## [1.3.0] - 2024

### Added
- **Smart Caching System** - Major performance enhancement
  - Form structure caching (1-hour duration)
  - Submission data caching (1-hour duration)
  - 80% reduction in database queries
  - 10x faster page loads (2s → 0.2s average)
  - WordPress Transients API integration
- **Cache Management**
  - Automatic cache expiration
  - Cache invalidation on data updates
  - Separate caches for structure and data

### Performance
- Page load time improved from ~2 seconds to ~0.2 seconds
- Database queries reduced by 80% on cached pages
- Massive scalability improvement for high-traffic sites

### Technical Details
- Cache keys: `aees_form_structure_{form_id}` and `aees_submission_data_{entry_id}`
- Cache duration: `HOUR_IN_SECONDS` (3600 seconds)
- Implemented in `AEES_Form_Data_Handler`

---

## [1.2.0] - 2024

### Added
- **Email Tracking Columns** - Database schema enhancement
  - `email_sent_at` column in proposals table
  - `email_expires_at` column for expiration tracking
  - Database indexes for performance
  - Automatic migration for existing installations
- **Database Upgrade System**
  - Version tracking with `aees_db_version` option
  - Automatic schema upgrades on admin_init
  - Migration function: `aees_upgrade_proposals_table_v12()`
  - Safe column addition with existence checks

### Changed
- Enhanced proposal data tracking
- Improved email status management
- Better support for email lifecycle tracking

### Database
- Added indexes on `email_sent_at` and `email_expires_at`
- Migration runs automatically on first admin visit after update

---

## [1.1.0] - 2024

### Added
- **Two-Step Authorization Flow** - Major feature addition
  - Authorization token generation for auction houses
  - Separate authorization email template
  - `authorization_token` column in response tokens table
  - `authorization_expires_at` column (14-day default)
  - `authorized_at` timestamp column
  - `authorized_by` tracking column
  - Authorization response handler
  - Admin notification on authorization complete
- **Database Schema Enhancement**
  - Four new columns in `aees_response_tokens` table
  - Unique index on `authorization_token`
  - Index on `authorization_expires_at`
  - Automatic migration for existing installations
- **New Email Templates**
  - `auction-authorization.php` - Authorization request to auction house
  - `admin-authorization-complete.php` - Final admin notification
  - `admin-acceptance-notification.php` - User acceptance notification

### Changed
- Extended workflow from single-step (user response) to two-step (user + authorization)
- Enhanced response handler to support authorization flow
- Improved status tracking with authorization states

### Database
- Migration function: `aees_upgrade_tokens_table()`
- Automatic schema updates on activation
- Backward compatible with existing data

---

## [1.0.0] - 2024

### Added - Initial Release
- **Core Functionality**
  - Forminator form integration
  - Proposal creation and management interface
  - Email proposal delivery system
  - Token-based response handling (Accept/Reject)
  - Secure HMAC-SHA256 token generation
- **Database Tables**
  - `wp_aees_proposals` - Proposal storage with JSON structure
  - `wp_aees_response_tokens` - Token tracking for O(1) lookups
  - Proper indexing for performance
- **Admin Interface**
  - Top-level menu: "Auction Estimate Emails"
  - Dashboard with WP_List_Table integration
  - Edit entry page for proposal management
  - Form submission data display with grouping
  - Repeater field support
  - File upload display with download links
- **Handler Classes** - Modular architecture
  - `AEES_Admin_Page` - Dashboard controller
  - `AEES_Submission_Table` - Submission listing
  - `AEES_Edit_Entry_Page` - Edit interface controller
  - `AEES_Form_Data_Handler` - Forminator integration
  - `AEES_Proposal_Data_Handler` - Database operations
  - `AEES_Proposal_Email_Manager` - Email composition
  - `AEES_Proposal_Response_Handler` - Response processing
- **Email System**
  - Professional HTML email templates
  - Responsive design for mobile devices
  - Logo integration (custom logo or site icon)
  - User proposals email with Accept/Reject buttons
  - Admin notification emails
  - Token-based secure response links
- **Response Page**
  - Standalone confirmation page (`/proposal-response/`)
  - Auto-created on plugin activation
  - Clean, distraction-free design
  - Success/error message display
  - Mobile-responsive layout
- **Security Features**
  - WordPress nonce protection on AJAX
  - Input sanitization and validation
  - Output escaping throughout
  - Capability checks (`manage_options`)
  - SQL injection prevention with `$wpdb->prepare()`
  - Secure token generation and validation
- **Performance Features**
  - Token table for fast status lookups
  - Optimized database queries
  - Proper indexing strategy
  - JSON storage for flexible proposal structure
- **Form Data Handling**
  - Field label mapping from Forminator
  - Group/repeater field support
  - File upload processing
  - Name field formatting
  - Address field formatting
  - Checkbox/multi-select handling
  - Date/time formatting
- **UI/UX**
  - Modern admin interface styling
  - Color-coded status badges
  - SweetAlert2 integration for notifications
  - TinyMCE WYSIWYG editor for proposal details
  - Responsive design for all screen sizes
  - Black header with white text aesthetic
  - Gradient status badges
- **AJAX Endpoints**
  - `aees_save_entry` - Save proposals
  - `aees_send_email` - Send proposals to user
- **Assets**
  - `admin-style.css` - Dashboard styling (388 lines)
  - `edit-entry.css` - Edit page styling (1011 lines)
  - `edit-entry.js` - Edit page functionality (638 lines)
  - `admin-script.js` - Dashboard scripts

### Technical Details
- **Minimum Requirements**
  - WordPress 5.0+
  - PHP 7.2+
  - MySQL 5.7+
  - Forminator plugin (required)
- **Database Charset**
  - Uses WordPress default charset collation
  - UTF-8 support for international characters
- **Plugin Constants**
  - `AEES_PLUGIN_DIR` - Plugin directory path
  - `AEES_PLUGIN_URL` - Plugin URL
  - `AEES_VERSION` - Version string

---

## Version History Summary

| Version | Release Date | Major Features |
|---------|--------------|----------------|
| 1.7.0 | 2025-01-27 | Proposal history table, entry status column, improved messaging, permanent data preservation |
| 1.6.0 | TBD | Settings page, configurable form ID, comprehensive documentation |
| 1.5.0 | 2024 | Response tracking UI, expiration tracking |
| 1.4.0 | 2024 | Cache refresh functionality |
| 1.3.0 | 2024 | Smart caching system (10x performance) |
| 1.2.0 | 2024 | Email tracking columns, database v1.2 |
| 1.1.0 | 2024 | Two-step authorization flow, database v1.1 |
| 1.0.0 | 2024 | Initial release |

---

## Upgrade Notes

### Upgrading to 1.6.0

1. **Configure Form ID**
   - After upgrade, visit **Auction Estimate Emails → Settings**
   - Enter your Forminator Form ID
   - Click "Save Settings"
   - Plugin will continue using default (2902) until configured

2. **Review Expiration Settings**
   - Default user response: 7 days
   - Default authorization: 14 days
   - Adjust if needed for your workflow

3. **No Database Changes**
   - This version only adds settings storage
   - No table schema modifications
   - No data migration required

### Upgrading to 1.5.0

- No action required
- UI enhancements are automatically available
- Existing proposals show new response tracking

### Upgrading to 1.4.0

- No action required
- Cache refresh button automatically appears

### Upgrading to 1.3.0

- No action required
- Caching is enabled automatically
- First page load may be slower (cache building)
- Subsequent loads are 10x faster

### Upgrading to 1.2.0

- **Automatic database migration**
- Runs on first admin visit after update
- Adds email tracking columns to proposals table
- No data loss or downtime
- Check database version in Settings → System Information

### Upgrading to 1.1.0

- **Automatic database migration**
- Adds authorization columns to tokens table
- Existing proposals remain functional
- New authorization workflow available for new proposals
- Check database version in Settings → System Information

---

## Migration Guide

### From 1.0.x to 1.6.0

If upgrading from v1.0.x directly to v1.6.0:

1. **Backup Database** (recommended)
   ```sql
   -- Backup commands
   mysqldump -u user -p database wp_aees_proposals > proposals_backup.sql
   mysqldump -u user -p database wp_aees_response_tokens > tokens_backup.sql
   ```

2. **Update Plugin Files**
   - Replace plugin directory contents
   - Preserve any custom modifications

3. **Database Migrations Run Automatically**
   - v1.1 migration adds authorization columns
   - v1.2 migration adds email tracking columns
   - Check `wp_options` for `aees_db_version` = '1.2'

4. **Configure New Settings**
   - Visit Settings page
   - Set Form ID
   - Adjust expiration periods if needed

5. **Test Functionality**
   - View existing proposals (should display correctly)
   - Create test proposal
   - Send test email
   - Verify response links work

---

## Known Issues

### Version 1.6.0
- None currently

### Version 1.5.0
- None

### Version 1.4.0
- None

### Version 1.3.0
- Cache may show stale data if Forminator form structure changes
  - **Solution:** Use "Refresh Cache" button

### Version 1.2.0
- None

### Version 1.1.0
- None

### Version 1.0.0
- Form ID hardcoded (fixed in 1.6.0)
- No cache refresh button (fixed in 1.4.0)
- No caching system (fixed in 1.3.0)

---

## Deprecation Notices

### Version 1.6.0
- **Hardcoded Form ID** - The hardcoded form ID approach is deprecated
  - Use Settings page to configure form ID
  - Backward compatible: defaults to 2902 if not set
  - Will be removed in future major version (2.0.0)

---

## Security Updates

### Version 1.6.0
- Settings page includes proper capability checks
- Settings sanitization via WordPress Settings API
- No security vulnerabilities introduced

### Version 1.5.0
- No security issues

### Version 1.4.0
- Cache refresh endpoint requires nonce verification
- No security vulnerabilities

### Version 1.3.0
- No security issues with caching implementation
- Transients stored securely in database

### Version 1.2.0
- No security changes

### Version 1.1.0
- Authorization tokens use same HMAC-SHA256 security
- Unique token validation prevents replay attacks

### Version 1.0.0
- Initial security implementation
- HMAC-SHA256 token generation
- WordPress nonce protection
- SQL injection prevention
- XSS prevention via output escaping

---

## Roadmap

### Planned for 2.0.0
- Multi-form support (configure multiple forms)
- Email template customization in admin
- Bulk operations (resend, export, archive)
- Email delivery logging and retry mechanism
- Status filtering on dashboard
- Advanced search and filtering
- Custom proposal fields
- Email preview before sending
- Audit trail and changelog tracking
- REST API for integrations
- Webhook support for external systems

### Under Consideration
- PDF proposal generation
- SMS notifications (Twilio integration)
- Calendar integration for deadlines
- Multi-language support (i18n)
- Custom user roles and permissions
- Proposal templates library
- Analytics and reporting dashboard
- Automated follow-up reminders
- Integration with popular CRMs

---

## Credits

### Version 1.6.0
- Settings page implementation: Mubeen Hassan
- Documentation: Comprehensive README and CHANGELOG created

### Version 1.5.0
- Response tracking UI: Mubeen Hassan

### Version 1.4.0
- Cache management: Mubeen Hassan

### Version 1.3.0
- Caching system: Mubeen Hassan

### Version 1.2.0
- Email tracking: Mubeen Hassan

### Version 1.1.0
- Authorization flow: Mubeen Hassan

### Version 1.0.0
- Original development: Mubeen Hassan

---

## Support

For support, bug reports, or feature requests:

1. Check README.md for documentation
2. Review troubleshooting guide
3. Check known issues in this changelog
4. Contact: Mubeen Hassan

---

**Changelog Last Updated:** 2025-01-27
**Current Version:** 1.7.0
**Stable Version:** 1.7.0
