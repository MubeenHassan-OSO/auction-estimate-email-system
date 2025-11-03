<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AEES_Submission_Table extends WP_List_Table
{

    private $form_id = 2902;
    private $per_page = 30; // Number of items per page

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Submission',
            'plural'   => 'Submissions',
            'ajax'     => false
        ]);
    }

    /**
     * Get current filter values from request
     */
    private function get_filter_values()
    {
        return [
            'entry_status' => isset($_GET['filter_entry_status']) ? sanitize_text_field($_GET['filter_entry_status']) : '',
            'user_response' => isset($_GET['filter_user_response']) ? sanitize_text_field($_GET['filter_user_response']) : '',
            'auction_approval' => isset($_GET['filter_auction_approval']) ? sanitize_text_field($_GET['filter_auction_approval']) : '',
            'email_search' => isset($_GET['filter_email_search']) ? sanitize_text_field($_GET['filter_email_search']) : ''
        ];
    }

    /**
     * Display filter form above the table
     */
    protected function extra_tablenav($which)
    {
        if ($which !== 'top') {
            return;
        }

        $filters = $this->get_filter_values();
        ?>
        <div class="aees-filters-wrapper">
            <h3>Filters</h3>
            <form method="get" class="aees-filters-form">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? ''); ?>">

                <div class="aees-filters-row aees-submission-dropdown-filter" style="width: 60%;">
                    <div class="aees-filter-group">
                        <label for="filter_entry_status">Entry Status</label>
                        <select name="filter_entry_status" id="filter_entry_status">
                            <option value="">All Statuses</option>
                            <option value="open" <?php selected($filters['entry_status'], 'open'); ?>>Open</option>
                            <option value="closed" <?php selected($filters['entry_status'], 'closed'); ?>>Closed</option>
                        </select>
                    </div>

                    <div class="aees-filter-group">
                        <label for="filter_user_response">User Response</label>
                        <select name="filter_user_response" id="filter_user_response">
                            <option value="">All Responses</option>
                            <option value="no_email" <?php selected($filters['user_response'], 'no_email'); ?>>No Email Sent</option>
                            <option value="pending" <?php selected($filters['user_response'], 'pending'); ?>>Pending</option>
                            <option value="accepted" <?php selected($filters['user_response'], 'accepted'); ?>>Accepted</option>
                            <option value="rejected" <?php selected($filters['user_response'], 'rejected'); ?>>Declined</option>
                        </select>
                    </div>

                    <div class="aees-filter-group">
                        <label for="filter_auction_approval">Auction Approval</label>
                        <select name="filter_auction_approval" id="filter_auction_approval">
                            <option value="">All Approvals</option>
                            <option value="na" <?php selected($filters['auction_approval'], 'na'); ?>>N/A</option>
                            <option value="pending" <?php selected($filters['auction_approval'], 'pending'); ?>>Pending Authorization</option>
                            <option value="approved" <?php selected($filters['auction_approval'], 'approved'); ?>>Approved</option>
                        </select>
                    </div>
                </div>

                <div class="aees-filters-row aees-submission-email-filter" style="width: 20%;">
                    <div class="aees-filter-group aees-filter-search">
                        <label for="filter_email_search">Search by Email</label>
                        <input type="text"
                               name="filter_email_search"
                               id="filter_email_search"
                               value="<?php echo esc_attr($filters['email_search']); ?>"
                               placeholder="Enter email address...">
                    </div>
                </div>

                <div class="aees-filters-actions" style="width: 20%;">
                    <button type="submit" class="button button-primary apply-filter-btn" >Apply</button>
                    <a href="<?php echo admin_url('admin.php?page=' . esc_attr($_GET['page'] ?? '')); ?>"
                       class="button clear-filter-btn">Clear</a>
                </div>
            </form>
        </div>
        <?php
    }

    public function get_columns()
    {
        return [
            'id'             => 'ID',
            'date_submitted' => 'Date Submitted',
            'email'          => 'Email',
            'entry_status'   => 'Entry Status',
            'status'         => 'User Response',
            'auction_approval' => 'Auction House Approval',
            'actions'        => 'Actions'
        ];
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $current_page = $this->get_pagenum();
        $total_items  = $this->get_total_items();

        $this->items = $this->get_submission_data($current_page, $this->per_page);

        // Set pagination args
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $this->per_page,
            'total_pages' => ceil($total_items / $this->per_page)
        ]);
    }

    private function get_total_items()
    {
        global $wpdb;

        $entry_table = $wpdb->prefix . 'frmt_form_entry';
        $meta_table  = $wpdb->prefix . 'frmt_form_entry_meta';
        $proposals_table = $wpdb->prefix . 'aees_proposals';
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';
        $tokens_table_exists = ($wpdb->get_var("SHOW TABLES LIKE '{$tokens_table}'") == $tokens_table);

        // Get filter values
        $filters = $this->get_filter_values();

        // Build filter WHERE clauses (same logic as get_submission_data)
        $where_clauses = ["e.form_id = %d"];
        $prepare_values = [$this->form_id];
        $email_join = false;

        // Filter by Entry Status
        if (!empty($filters['entry_status'])) {
            if ($filters['entry_status'] === 'open') {
                // Open includes NULL (new entries without proposals) and explicitly 'open'
                $where_clauses[] = "(p.entry_status = %s OR p.entry_status IS NULL)";
                $prepare_values[] = $filters['entry_status'];
            } else {
                // Closed must be explicitly set
                $where_clauses[] = "p.entry_status = %s";
                $prepare_values[] = $filters['entry_status'];
            }
        }

        // Filter by Email Search
        if (!empty($filters['email_search'])) {
            $email_join = true;
            $where_clauses[] = "m.meta_value LIKE %s";
            $prepare_values[] = '%' . $wpdb->esc_like($filters['email_search']) . '%';
        }

        // Filter by User Response
        if (!empty($filters['user_response']) && $tokens_table_exists) {
            switch ($filters['user_response']) {
                case 'no_email':
                    $where_clauses[] = "(p.email_sent_at IS NULL)";
                    break;
                case 'pending':
                    $where_clauses[] = "(p.email_sent_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status IN ('rejected', 'accepted')))";
                    break;
                case 'accepted':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted'))";
                    break;
                case 'rejected':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'rejected'))";
                    break;
            }
        }

        // Filter by Auction Approval Status
        if (!empty($filters['auction_approval']) && $tokens_table_exists) {
            switch ($filters['auction_approval']) {
                case 'na':
                    $where_clauses[] = "(p.email_sent_at IS NULL OR NOT EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted'))";
                    break;
                case 'pending':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted' AND t.authorized_at IS NULL))";
                    break;
                case 'approved':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.authorized_at IS NOT NULL))";
                    break;
            }
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Build count query
        $count_query = "SELECT COUNT(DISTINCT e.entry_id)
                        FROM {$entry_table} e
                        LEFT JOIN {$proposals_table} p ON e.entry_id = p.entry_id";

        // Add email join if needed
        if ($email_join) {
            $count_query .= " LEFT JOIN {$meta_table} m ON e.entry_id = m.entry_id AND m.meta_key LIKE '%email%'";
        }

        $count_query .= " WHERE {$where_sql}";

        $count = $wpdb->get_var(
            $wpdb->prepare($count_query, $prepare_values)
        );

        return (int) $count;
    }

    private function get_submission_data($current_page = 1, $per_page = 20)
    {
        global $wpdb;

        $entries = [];
        $entry_table = $wpdb->prefix . 'frmt_form_entry';
        $meta_table  = $wpdb->prefix . 'frmt_form_entry_meta';
        $proposals_table = $wpdb->prefix . 'aees_proposals';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$entry_table}'") != $entry_table) {
            error_log("AEES: Entry table not found - {$entry_table}");
            return [];
        }

        // Detect date column dynamically (safe method using WordPress function)
        $columns = $wpdb->get_col($wpdb->prepare("SHOW COLUMNS FROM %i", $entry_table), 0);
        $date_column = in_array('date_created', $columns) ? 'date_created' : 'time_created';

        // Pagination math
        $offset = ($current_page - 1) * $per_page;

        // Check if tokens table exists for optimization
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';
        $tokens_table_exists = ($wpdb->get_var("SHOW TABLES LIKE '{$tokens_table}'") == $tokens_table);

        // Get filter values
        $filters = $this->get_filter_values();

        // Build filter WHERE clauses
        $where_clauses = ["e.form_id = %d"];
        $prepare_values = [$this->form_id];
        $email_join = false;

        // Filter by Entry Status
        if (!empty($filters['entry_status'])) {
            if ($filters['entry_status'] === 'open') {
                // Open includes NULL (new entries without proposals) and explicitly 'open'
                $where_clauses[] = "(p.entry_status = %s OR p.entry_status IS NULL)";
                $prepare_values[] = $filters['entry_status'];
            } else {
                // Closed must be explicitly set
                $where_clauses[] = "p.entry_status = %s";
                $prepare_values[] = $filters['entry_status'];
            }
        }

        // Filter by Email Search
        if (!empty($filters['email_search'])) {
            $email_join = true;
            $where_clauses[] = "m.meta_value LIKE %s";
            $prepare_values[] = '%' . $wpdb->esc_like($filters['email_search']) . '%';
        }

        // Filter by User Response (complex conditions)
        if (!empty($filters['user_response']) && $tokens_table_exists) {
            switch ($filters['user_response']) {
                case 'no_email':
                    $where_clauses[] = "(p.email_sent_at IS NULL)";
                    break;
                case 'pending':
                    $where_clauses[] = "(p.email_sent_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status IN ('rejected', 'accepted')))";
                    break;
                case 'accepted':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted'))";
                    break;
                case 'rejected':
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'rejected'))";
                    break;
            }
        }

        // Filter by Auction Approval Status
        if (!empty($filters['auction_approval']) && $tokens_table_exists) {
            switch ($filters['auction_approval']) {
                case 'na':
                    // N/A: No email sent, pending, or rejected
                    $where_clauses[] = "(p.email_sent_at IS NULL OR NOT EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted'))";
                    break;
                case 'pending':
                    // Accepted but not authorized
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.status = 'accepted' AND t.authorized_at IS NULL))";
                    break;
                case 'approved':
                    // Authorized
                    $where_clauses[] = "(EXISTS (SELECT 1 FROM {$tokens_table} t WHERE t.entry_id = e.entry_id AND t.authorized_at IS NOT NULL))";
                    break;
            }
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Optimized query with proposals data
        if ($tokens_table_exists) {
            // Use tokens table for fast status lookup (no JSON parsing)
            // Also check if accepted proposals have been authorized
            $base_query = "SELECT DISTINCT
                        e.entry_id,
                        e.{$date_column},
                        p.proposals,
                        p.email_sent_at,
                        p.entry_status,
                        (SELECT t.status
                         FROM {$tokens_table} t
                         WHERE t.entry_id = e.entry_id
                         AND t.status IN ('rejected', 'accepted')
                         LIMIT 1) as response_status,
                        (SELECT t.authorized_at
                         FROM {$tokens_table} t
                         WHERE t.entry_id = e.entry_id
                         AND t.authorized_at IS NOT NULL
                         LIMIT 1) as authorized_at
                     FROM {$entry_table} e
                     LEFT JOIN {$proposals_table} p ON e.entry_id = p.entry_id";

            // Add email join if needed
            if ($email_join) {
                $base_query .= " LEFT JOIN {$meta_table} m ON e.entry_id = m.entry_id AND m.meta_key LIKE '%email%'";
            }

            $base_query .= " WHERE {$where_sql}
                     ORDER BY e.{$date_column} DESC
                     LIMIT %d OFFSET %d";

            $prepare_values[] = $per_page;
            $prepare_values[] = $offset;

            $entry_rows = $wpdb->get_results(
                $wpdb->prepare($base_query, $prepare_values)
            );
        } else {
            // Fallback: Standard query without tokens table
            $base_query = "SELECT
                        e.entry_id,
                        e.{$date_column},
                        p.proposals,
                        p.email_sent_at,
                        p.entry_status
                     FROM {$entry_table} e
                     LEFT JOIN {$proposals_table} p ON e.entry_id = p.entry_id";

            // Add email join if needed
            if ($email_join) {
                $base_query .= " LEFT JOIN {$meta_table} m ON e.entry_id = m.entry_id AND m.meta_key LIKE '%email%'";
            }

            $base_query .= " WHERE {$where_sql}
                     ORDER BY e.{$date_column} DESC
                     LIMIT %d OFFSET %d";

            $prepare_values[] = $per_page;
            $prepare_values[] = $offset;

            $entry_rows = $wpdb->get_results(
                $wpdb->prepare($base_query, $prepare_values)
            );
        }

        if (empty($entry_rows)) {
            return [];
        }

        // Fetch email field for each entry (batch query)
        $entry_ids = array_column($entry_rows, 'entry_id');
        $placeholders = implode(',', array_fill(0, count($entry_ids), '%d'));

        $email_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT entry_id, meta_value
                 FROM {$meta_table}
                 WHERE entry_id IN ({$placeholders})
                 AND meta_key LIKE %s",
                array_merge($entry_ids, ['%email%'])
            ),
            OBJECT_K
        );

        // Build entries array
        foreach ($entry_rows as $row) {
            // Get email from batch result
            $email_meta = isset($email_results[$row->entry_id]) ? $email_results[$row->entry_id]->meta_value : 'N/A';

            // Check if email has been sent
            $email_sent = !empty($row->email_sent_at);

            // Determine status
            // Default to 'no_email' if no email sent, otherwise 'pending'
            $status = $email_sent ? 'pending' : 'no_email';

            if ($tokens_table_exists && isset($row->response_status)) {
                // OPTIMIZED: Get status from tokens table (no JSON parsing)
                if ($row->response_status) {
                    $status = $row->response_status;
                }

                // Check if accepted proposal has been authorized
                if ($status === 'accepted' && !empty($row->authorized_at)) {
                    $status = 'authorized';
                }
            } elseif (!empty($row->proposals)) {
                // FALLBACK: Parse JSON to get status
                $proposals = json_decode($row->proposals, true);
                if (is_array($proposals)) {
                    foreach ($proposals as $prop) {
                        if (isset($prop['status']) && $prop['status'] === 'rejected') {
                            $status = 'rejected';
                            break;
                        } elseif (isset($prop['status']) && $prop['status'] === 'accepted') {
                            // Check if this accepted proposal has been authorized
                            if (isset($prop['authorization_status']) && $prop['authorization_status'] === 'authorized') {
                                $status = 'authorized';
                            } else {
                                $status = 'accepted';
                            }
                            break;
                        }
                    }
                }
            }

            // Format status HTML for User Response column
            switch ($status) {
                case 'rejected':
                    $status_html = '<span class="aees-approval-rejected">Declined</span>';
                    break;
                case 'authorized':
                    $status_html = '<span class="aees-approval-approved">Accepted</span>';
                    break;
                case 'accepted':
                    $status_html = '<span class="aees-approval-approved">Accepted</span>';
                    break;
                case 'pending':
                    $status_html = '<span class="aees-approval-pending">Pending</span>';
                    break;
                case 'no_email':
                default:
                    $status_html = '<span class="aees-approval-na">N/A</span>';
            }

            // Format Auction House Approval status
            if ($status === 'rejected' || $status === 'pending' || $status === 'no_email') {
                // Not applicable if user hasn't accepted or no email sent
                $auction_approval_html = '<span class="aees-approval-na">N/A</span>';
            } elseif ($status === 'accepted') {
                // User accepted, awaiting auction house authorization
                $auction_approval_html = '<span class="aees-approval-pending">Pending</span>';
            } elseif ($status === 'authorized') {
                // Auction house has authorized
                $auction_approval_html = '<span class="aees-approval-approved">Approved</span>';
            } else {
                $auction_approval_html = '<span class="aees-approval-na">-</span>';
            }

            // Format Entry Status (open or closed)
            $entry_status_value = $row->entry_status ?? 'open';
            if ($entry_status_value === 'closed') {
                $entry_status_html = '<span class="aees-entry-status-closed">🔒 Closed</span>';
            } else {
                $entry_status_html = '<span class="aees-entry-status-open">🔓 Open</span>';
            }


            $timestamp = mysql2date('U', $row->$date_column, true);
            $date_formatted = wp_date(
                get_option('date_format'),
                $timestamp
            );

            $entries[] = [
                'id'             => $row->entry_id,
                'date_submitted' => $date_formatted,
                'email'          => $email_meta ? esc_html($email_meta) : 'N/A',
                'entry_status'   => $entry_status_html,
                'status'         => $status_html,
                'auction_approval' => $auction_approval_html,
                'actions'        => '<a href="' . admin_url('admin.php?page=aees-edit-entry&edit=' . $row->entry_id) . '">Edit</a>'
            ];
        }

        return $entries;
    }

    public function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }
}