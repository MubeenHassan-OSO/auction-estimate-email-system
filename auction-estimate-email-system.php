<?php

/**
 * Plugin Name: Auction Estimate Email System
 * Description: Custom add-on to manage and email Forminator form submissions.
 * Version: 1.8.0
 * Author: Mubeen Hassan
 * Text Domain: auction-estimate-email-system
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('AEES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AEES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AEES_VERSION', '1.8.0');

// Load core classes
require_once AEES_PLUGIN_DIR . 'includes/class-submission-table.php';
require_once AEES_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once AEES_PLUGIN_DIR . 'includes/class-settings-page.php';
require_once AEES_PLUGIN_DIR . 'includes/functions.php';

// Load refactored handler classes (must be loaded before AEES_Edit_Entry_Page)
require_once AEES_PLUGIN_DIR . 'includes/class-proposal-data-handler.php';
require_once AEES_PLUGIN_DIR . 'includes/class-form-data-handler.php';
require_once AEES_PLUGIN_DIR . 'includes/class-proposal-email-manager.php';
require_once AEES_PLUGIN_DIR . 'includes/class-proposal-response-handler.php';

// Load main edit entry page (depends on handler classes)
require_once AEES_PLUGIN_DIR . 'includes/class-edit-entry-page.php';

register_activation_hook(__FILE__, 'aees_create_proposals_table');
function aees_create_proposals_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aees_proposals';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id INT(11) NOT NULL AUTO_INCREMENT,
        entry_id INT(11) NOT NULL,
        auction_house_email VARCHAR(255) DEFAULT '',
        proposals LONGTEXT,
        email_sent_at DATETIME DEFAULT NULL,
        email_expires_at DATETIME DEFAULT NULL,
        entry_status VARCHAR(20) DEFAULT 'open',
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY entry_id (entry_id),
        KEY idx_date_created (date_created),
        KEY idx_date_updated (date_updated),
        KEY idx_email_sent (email_sent_at),
        KEY idx_email_expires (email_expires_at),
        KEY idx_entry_status (entry_status)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Create response tokens table for fast token lookups
    $tokens_table = $wpdb->prefix . 'aees_response_tokens';
    $tokens_sql = "CREATE TABLE IF NOT EXISTS {$tokens_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        token VARCHAR(64) NOT NULL,
        entry_id INT(11) NOT NULL,
        proposal_uid VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME DEFAULT NULL,
        authorization_token VARCHAR(64) DEFAULT NULL,
        authorization_expires_at DATETIME DEFAULT NULL,
        authorized_at DATETIME DEFAULT NULL,
        authorized_by VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY token (token),
        UNIQUE KEY authorization_token (authorization_token),
        KEY idx_entry_proposal (entry_id, proposal_uid),
        KEY idx_status (status),
        KEY idx_expires (expires_at),
        KEY idx_auth_expires (authorization_expires_at)
    ) {$charset_collate};";

    dbDelta($tokens_sql);

    // Ensure authorization columns exist (for existing installations)
    aees_upgrade_tokens_table();

    // Create proposal response page
    aees_create_response_page();
}

/**
 * Upgrade tokens table - adds authorization columns if missing
 * Called during activation to ensure existing tables have new columns
 */
function aees_upgrade_tokens_table()
{
    global $wpdb;
    $tokens_table = $wpdb->prefix . 'aees_response_tokens';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$tokens_table}'") != $tokens_table) {
        return; // Table doesn't exist yet, will be created by dbDelta
    }

    // Get existing columns
    $columns = $wpdb->get_col("SHOW COLUMNS FROM {$tokens_table}", 0);

    // Add authorization_token column if missing
    if (!in_array('authorization_token', $columns)) {
        $wpdb->query("ALTER TABLE {$tokens_table} ADD COLUMN authorization_token VARCHAR(64) DEFAULT NULL AFTER expires_at");
        $wpdb->query("ALTER TABLE {$tokens_table} ADD UNIQUE KEY authorization_token (authorization_token)");
    }

    // Add authorized_at column if missing
    if (!in_array('authorized_at', $columns)) {
        $wpdb->query("ALTER TABLE {$tokens_table} ADD COLUMN authorized_at DATETIME DEFAULT NULL AFTER authorization_token");
    }

    // Add authorized_by column if missing
    if (!in_array('authorized_by', $columns)) {
        $wpdb->query("ALTER TABLE {$tokens_table} ADD COLUMN authorized_by VARCHAR(255) DEFAULT NULL AFTER authorized_at");
    }

    // Add authorization_expires_at column if missing
    if (!in_array('authorization_expires_at', $columns)) {
        $wpdb->query("ALTER TABLE {$tokens_table} ADD COLUMN authorization_expires_at DATETIME DEFAULT NULL AFTER authorization_token");
        $wpdb->query("ALTER TABLE {$tokens_table} ADD KEY idx_auth_expires (authorization_expires_at)");
    }
}

/**
 * Run database upgrades on admin init (once per install)
 */
add_action('admin_init', 'aees_check_database_version');
function aees_check_database_version()
{
    $db_version = get_option('aees_db_version', '1.0');

    // Version 1.1 adds authorization columns to tokens table
    if (version_compare($db_version, '1.1', '<')) {
        aees_upgrade_tokens_table();
        update_option('aees_db_version', '1.1');
    }

    // Version 1.2 adds email tracking columns to proposals table
    if (version_compare($db_version, '1.2', '<')) {
        aees_upgrade_proposals_table_v12();
        update_option('aees_db_version', '1.2');
    }

    // Version 1.3 adds entry_status column to proposals table
    if (version_compare($db_version, '1.3', '<')) {
        aees_upgrade_proposals_table_v13();
        update_option('aees_db_version', '1.3');
    }

    // Version 1.4 adds proposal history table for preserving rejected/accepted proposals
    if (version_compare($db_version, '1.4', '<')) {
        aees_create_proposal_history_table();
        aees_migrate_existing_rejections_to_history();
        update_option('aees_db_version', '1.4');
    }
}

/**
 * Upgrade proposals table - adds email tracking columns
 * Version 1.2 upgrade
 */
function aees_upgrade_proposals_table_v12()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aees_proposals';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        return; // Table doesn't exist yet
    }

    // Get existing columns
    $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);

    // Add email_sent_at column if missing
    if (!in_array('email_sent_at', $columns)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN email_sent_at DATETIME DEFAULT NULL AFTER proposals");
        $wpdb->query("ALTER TABLE {$table_name} ADD KEY idx_email_sent (email_sent_at)");
    }

    // Add email_expires_at column if missing
    if (!in_array('email_expires_at', $columns)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN email_expires_at DATETIME DEFAULT NULL AFTER email_sent_at");
        $wpdb->query("ALTER TABLE {$table_name} ADD KEY idx_email_expires (email_expires_at)");
    }
}

/**
 * Upgrade proposals table - adds entry_status column
 * Version 1.3 upgrade
 */
function aees_upgrade_proposals_table_v13()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aees_proposals';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        return; // Table doesn't exist yet
    }

    // Get existing columns
    $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);

    // Add entry_status column if missing
    if (!in_array('entry_status', $columns)) {
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN entry_status VARCHAR(20) DEFAULT 'open' AFTER email_expires_at");
        $wpdb->query("ALTER TABLE {$table_name} ADD KEY idx_entry_status (entry_status)");
    }
}

/**
 * Create proposal history table
 * Version 1.4 upgrade
 * Stores all proposal responses (rejected, accepted, authorized) for permanent audit trail
 */
function aees_create_proposal_history_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aees_proposal_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        entry_id INT(11) NOT NULL,
        proposal_uid VARCHAR(50) NOT NULL,
        proposal_title VARCHAR(255) NOT NULL,
        proposal_price VARCHAR(50) NOT NULL,
        proposal_details LONGTEXT,
        status VARCHAR(20) NOT NULL,
        user_email VARCHAR(255) DEFAULT NULL,
        user_response_date DATETIME DEFAULT NULL,
        authorization_status VARCHAR(20) DEFAULT NULL,
        authorization_date DATETIME DEFAULT NULL,
        authorized_by VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_entry_id (entry_id),
        KEY idx_proposal_uid (proposal_uid),
        KEY idx_status (status),
        KEY idx_response_date (user_response_date),
        KEY idx_created_at (created_at)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Migrate existing rejected/accepted proposals to history table
 * Version 1.4 migration
 * Runs once during upgrade to preserve existing history
 */
function aees_migrate_existing_rejections_to_history()
{
    global $wpdb;
    $proposals_table = $wpdb->prefix . 'aees_proposals';
    $history_table = $wpdb->prefix . 'aees_proposal_history';

    // Get all entries with proposals
    $entries = $wpdb->get_results(
        "SELECT entry_id, proposals FROM {$proposals_table} WHERE proposals IS NOT NULL AND proposals != ''",
        ARRAY_A
    );

    foreach ($entries as $entry) {
        $entry_id = $entry['entry_id'];
        $proposals = json_decode($entry['proposals'], true);

        if (!is_array($proposals)) {
            continue;
        }

        // Find rejected or accepted proposals
        foreach ($proposals as $proposal) {
            $status = $proposal['status'] ?? 'pending';

            // Only migrate non-pending proposals (rejected, accepted, or authorized)
            if ($status !== 'pending') {
                // Check if this proposal already exists in history (avoid duplicates)
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$history_table} WHERE entry_id = %d AND proposal_uid = %s",
                    $entry_id,
                    $proposal['uid'] ?? ''
                ));

                if (!$exists) {
                    $wpdb->insert(
                        $history_table,
                        [
                            'entry_id' => $entry_id,
                            'proposal_uid' => $proposal['uid'] ?? '',
                            'proposal_title' => $proposal['title'] ?? '',
                            'proposal_price' => $proposal['price'] ?? '',
                            'proposal_details' => $proposal['details'] ?? '',
                            'status' => $status,
                            'user_response_date' => $proposal['user_response_date'] ?? null,
                            'authorization_status' => $proposal['authorization_status'] ?? null,
                            'authorization_date' => $proposal['authorization_date'] ?? null,
                            'authorized_by' => $proposal['authorized_by'] ?? null,
                            'created_at' => current_time('mysql')
                        ],
                        ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
                    );
                }
            }
        }
    }
}

/**
 * Create proposal response page on activation
 */
function aees_create_response_page()
{
    $page_slug = 'proposal-response';

    // Check if page already exists
    $page = get_page_by_path($page_slug);

    if (!$page) {
        $page_id = wp_insert_post([
            'post_title' => 'Proposal Response',
            'post_name' => $page_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '<!-- Proposal Response Page - Managed by Auction Estimate Email System Plugin -->',
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        ]);

        if ($page_id) {
            update_option('aees_response_page_id', $page_id);
            // Flush rewrite rules to ensure page is accessible
            flush_rewrite_rules();
        }
    } else {
        // Page exists, store its ID
        update_option('aees_response_page_id', $page->ID);
    }
}

/**
 * Check and create page if missing (runs on admin_init)
 */
add_action('admin_init', 'aees_ensure_response_page_exists');
function aees_ensure_response_page_exists()
{
    // Handle manual page creation
    if (isset($_GET['aees_create_page']) && current_user_can('manage_options')) {
        check_admin_referer('aees_create_page');
        aees_create_response_page();
        wp_redirect(admin_url('admin.php?page=aees&page_created=1'));
        exit;
    }

    // Only run once per session
    if (get_transient('aees_page_check_done')) {
        return;
    }

    $page_slug = 'proposal-response';
    $page = get_page_by_path($page_slug);

    if (!$page) {
        aees_create_response_page();
    }

    set_transient('aees_page_check_done', true, HOUR_IN_SECONDS);
}

/**
 * Show admin notice if page is missing
 */
add_action('admin_notices', 'aees_missing_page_notice');
function aees_missing_page_notice()
{
    $page = get_page_by_path('proposal-response');

    if (!$page && current_user_can('manage_options')) {
        $create_url = wp_nonce_url(admin_url('admin.php?aees_create_page=1'), 'aees_create_page');
?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>Auction Estimate Email System:</strong>
                The proposal response page is missing.
                <a href="<?php echo esc_url($create_url); ?>" class="button button-primary" style="margin-left: 10px;">Create Page Now</a>
            </p>
        </div>
    <?php
    }

    if (isset($_GET['page_created'])) {
    ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Success!</strong> The proposal response page has been created.</p>
        </div>
<?php
    }
}

/**
 * Instantiate classes once globally
 */
global $aees_edit_entry_page;

add_action('plugins_loaded', function () {
    global $aees_edit_entry_page;

    if (class_exists('AEES_Admin_Page')) {
        new AEES_Admin_Page();
    }

    if (class_exists('AEES_Settings_Page')) {
        new AEES_Settings_Page();
    }

    if (class_exists('AEES_Edit_Entry_Page')) {
        $aees_edit_entry_page = new AEES_Edit_Entry_Page();
    }
});

/**
 * Register the edit page submenu
 */
add_action('admin_menu', function () {
    global $aees_edit_entry_page;

    if ($aees_edit_entry_page instanceof AEES_Edit_Entry_Page) {
        $aees_edit_entry_page->register_submenu();
    }
}, 20);

/**
 * Keep this function for backward compatibility
 * (in case it's referenced elsewhere in your code)
 */
if (!function_exists('aees_render_edit_entry_page')) {
    function aees_render_edit_entry_page()
    {
        global $aees_edit_entry_page;

        if ($aees_edit_entry_page instanceof AEES_Edit_Entry_Page) {
            $aees_edit_entry_page->render_page();
        } else {
            echo '<div class="notice notice-error"><p>AEES: Edit page class not available.</p></div>';
        }
    }
}
