<?php
if (!defined('ABSPATH')) exit;

/**
 * Main class for Edit Entry Page
 * Refactored to use separated handler classes for better organization
 */
class AEES_Edit_Entry_Page
{
    private $entry_id;

    // Handler classes for separated concerns
    private $data_handler;
    private $form_data_handler;
    private $email_manager;
    private $response_handler;

    public function __construct()
    {
        // Initialize handler classes
        $this->data_handler = new AEES_Proposal_Data_Handler();
        $this->form_data_handler = new AEES_Form_Data_Handler();
        $this->email_manager = new AEES_Proposal_Email_Manager();
        $this->response_handler = new AEES_Proposal_Response_Handler();

        add_action('admin_head', [$this, 'suppress_notices']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_aees_save_entry', [$this, 'ajax_save_entry']);
        add_action('wp_ajax_aees_send_email', [$this, 'ajax_send_email']);
        add_action('wp_ajax_aees_refresh_cache', [$this, 'ajax_refresh_cache']);
        add_action('wp_ajax_aees_toggle_entry_status', [$this, 'ajax_toggle_entry_status']);

        // Public-facing response handlers - delegate to response handler
        add_action('template_redirect', [$this->response_handler, 'handle_proposal_response']);
        add_action('template_redirect', [$this->response_handler, 'handle_authorization_response']);
        add_filter('template_include', [$this, 'load_response_template']);
    }

    public function register_submenu()
    {
        add_submenu_page(
            null,
            'Edit Entry',
            'Edit Entry',
            'manage_options',
            'aees-edit-entry',
            [$this, 'render_page']
        );
    }

    public function suppress_notices()
    {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        if ($page === 'aees-edit-entry') {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    public function enqueue_assets($hook)
    {
        // Early return: Only load assets on our specific admin page
        // Double-check both $hook and $_GET['page'] for security and performance
        // Hidden submenu pages don't have a standard hook, so we check $_GET
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

        if ($page !== 'aees-edit-entry') {
            return; // Exit early - prevents loading assets on other admin pages
        }

        // Enqueue WordPress editor assets (required for WYSIWYG)
        wp_enqueue_editor();
        wp_enqueue_media();

        // Enqueue our custom JavaScript
        wp_enqueue_script(
            'aees-admin-edit-js',
            AEES_PLUGIN_URL . 'assets/js/edit-entry.js',
            ['jquery', 'wp-editor'],
            AEES_VERSION . '.3', // Cache buster - increment when JS changes
            true
        );

        // Enqueue our custom CSS files
        wp_enqueue_style(
            'aees-admin-edit-css',
            AEES_PLUGIN_URL . 'assets/css/edit-entry.css',
            [],
            AEES_VERSION
        );

        wp_enqueue_style(
            'aees-admin-responses-css',
            AEES_PLUGIN_URL . 'assets/css/edit-entry-responses.css',
            [],
            AEES_VERSION
        );

        // Enqueue SweetAlert2 for notifications (pinned version for stability)
        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js',
            [],
            '11.7.32',
            true
        );

        // Localize script data for AJAX calls
        $nonce = wp_create_nonce('aees_save_entry_nonce');
        $current_entry_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

        // Get email status and entry status for current entry to control button states
        $email_status = $current_entry_id ? $this->data_handler->get_email_status($current_entry_id) : null;
        $entry_status = $current_entry_id ? $this->data_handler->get_entry_status($current_entry_id) : 'open';
        $has_authorized = $current_entry_id ? $this->data_handler->has_authorized_proposals($current_entry_id) : false;

        wp_localize_script('aees-admin-edit-js', 'aeesData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => $nonce,
            'entry_id' => $current_entry_id,
            'email_status' => $email_status, // Pass email status to JavaScript
            'entry_status' => $entry_status,  // Pass entry status to JavaScript
            'has_authorized_proposals' => $has_authorized // Pass authorization flag
        ]);
    }

    public function render_page()
    {
        if (!isset($_GET['edit'])) {
            echo '<div class="wrap"><h2>Invalid Entry</h2></div>';
            return;
        }

        $this->entry_id = intval($_GET['edit']);

        // Get data from custom table - delegated to data handler
        $proposal_data = $this->data_handler->get_proposal_data($this->entry_id);
        $auction_email = $proposal_data['auction_email'] ?? '';
        $proposals = $proposal_data['proposals'] ?? [];
        $entry_id = $this->entry_id;

        // Fetch form submission data - delegated to form data handler
        $form_data = $this->form_data_handler->get_form_submission_data($this->entry_id);

        // Get email status to show in UI and control button state
        $email_status = $this->data_handler->get_email_status($this->entry_id);

        // Get entry status (open or closed)
        $entry_status = $this->data_handler->get_entry_status($this->entry_id);

        // Get rejection history if any
        $rejection_history = $this->data_handler->get_rejection_history($this->entry_id);

        // Check if entry has authorized proposals (permanent closure)
        $has_authorized_proposals = $this->data_handler->has_authorized_proposals($this->entry_id);

        $template_path = AEES_PLUGIN_DIR . 'templates/edit-entry-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="notice notice-error"><p>Template file missing: <code>edit-entry-template.php</code></p></div>';
        }
    }

    public function ajax_save_entry()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        check_ajax_referer('aees_save_entry_nonce', 'nonce');

        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID'], 400);
        }

        $auction_email = isset($_POST['auction_email']) ? sanitize_email(wp_unslash($_POST['auction_email'])) : '';
        if (empty($auction_email)) {
            wp_send_json_error(['message' => 'Auction house email is required'], 400);
        }
        if (!is_email($auction_email)) {
            wp_send_json_error(['message' => 'Invalid auction house email format'], 400);
        }

        $proposals_in = isset($_POST['proposals']) && is_array($_POST['proposals']) ? $_POST['proposals'] : [];
        $proposals = [];

        foreach ($proposals_in as $p) {
            $uid = isset($p['uid']) ? sanitize_text_field($p['uid']) : uniqid('p_');
            $title = isset($p['title']) ? sanitize_text_field($p['title']) : '';
            $price = isset($p['price']) ? sanitize_text_field($p['price']) : '';
            $details = isset($p['details']) ? wp_kses_post($p['details']) : '';
            $image = isset($p['image']) ? esc_url_raw($p['image']) : '';

            // Skip completely empty proposals
            if ($title === '' && $price === '' && $details === '') {
                continue;
            }

            // Validate proposal fields
            if (empty($title)) {
                wp_send_json_error(['message' => 'Proposal title is required'], 400);
            }
            if (strlen($title) > 200) {
                wp_send_json_error(['message' => 'Proposal title must not exceed 200 characters'], 400);
            }
            if (empty($price)) {
                wp_send_json_error(['message' => 'Proposal price is required'], 400);
            }
            if (empty($details)) {
                wp_send_json_error(['message' => 'Proposal details are required'], 400);
            }

            // Preserve existing status and response data if updating
            $existing_status = isset($p['status']) ? $p['status'] : 'pending';
            $existing_response_date = isset($p['user_response_date']) ? $p['user_response_date'] : null;

            // Generate secure response token
            $response_token = hash_hmac('sha256', $uid . '_' . $entry_id, wp_salt());

            $proposals[] = [
                'uid' => $uid,
                'title' => $title,
                'price' => $price,
                'details' => $details,
                'image' => $image,
                'status' => $existing_status,
                'user_response_date' => $existing_response_date,
                'response_token' => $response_token,
            ];
        }

        // Note: Empty proposals array is allowed - user might just be updating auction email
        // Validation above ensures any non-empty proposal has all required fields

        // Save to custom table - delegated to data handler
        $result = $this->data_handler->save_proposal_data($entry_id, $auction_email, $proposals);

        if ($result) {
            wp_send_json_success(['message' => 'Entry saved']);
        } else {
            wp_send_json_error(['message' => 'Failed to save entry']);
        }
    }

    public function ajax_send_email()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        check_ajax_referer('aees_save_entry_nonce', 'nonce');

        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID'], 400);
        }

        if (!$this->data_handler->can_send_email($entry_id)) {
            $email_status = $this->data_handler->get_email_status($entry_id);
            $expires_date = !empty($email_status['email_expires_at'])
                ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($email_status['email_expires_at']))
                : 'unknown';

            wp_send_json_error([
                'message' => 'Email already sent and has not expired yet.',
                'details' => "The email was sent and will expire on {$expires_date}. You cannot send another email until it expires."
            ]);
        }

        // Get user email and proposals - delegated to handlers
        $form_data = $this->form_data_handler->get_form_submission_data($entry_id);
        $user_email = $form_data['user_email'] ?? '';
        $proposal_data = $this->data_handler->get_proposal_data($entry_id);
        $proposals = $proposal_data['proposals'];

        if (empty($user_email)) {
            wp_send_json_error(['message' => 'User email not found'], 400);
        }

        if (empty($proposals)) {
            wp_send_json_error(['message' => 'No proposals to send'], 400);
        }

        // Send the email - delegated to email manager
        $result = $this->email_manager->send_proposals_email($user_email, $entry_id, $proposals, $form_data);

        if ($result) {
            $this->data_handler->mark_email_sent($entry_id, 7);

            // Get updated email status to return to client
            $email_status = $this->data_handler->get_email_status($entry_id);

            wp_send_json_success([
                'message' => 'Email sent successfully',
                'email_status' => $email_status
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to send email']);
        }
    }

    /**
     * AJAX handler to refresh cached form data
     * Useful when form data changes and needs to be immediately reflected
     */
    public function ajax_refresh_cache()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        check_ajax_referer('aees_save_entry_nonce', 'nonce');

        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID'], 400);
        }

        // Clear the cache
        $this->form_data_handler->clear_submission_cache($entry_id);

        // Also clear form structure cache to ensure everything is fresh
        $form_id = AEES_Settings_Page::get_form_id();
        delete_transient('aees_form_structure_' . $form_id);

        wp_send_json_success(['message' => 'Cache cleared successfully']);
    }

    /**
     * AJAX handler to toggle entry status (open/closed)
     * Allows admin to reopen closed entries or manually close open entries
     */
    public function ajax_toggle_entry_status()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        check_ajax_referer('aees_save_entry_nonce', 'nonce');

        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID'], 400);
        }

        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        if (!in_array($new_status, ['open', 'closed'])) {
            wp_send_json_error(['message' => 'Invalid status'], 400);
        }

        // Prevent reopening if entry has authorized proposals
        if ($new_status === 'open' && $this->data_handler->has_authorized_proposals($entry_id)) {
            wp_send_json_error([
                'message' => 'Cannot reopen this entry',
                'details' => 'This entry has authorized proposals and is permanently closed. Authorized orders cannot be reopened.'
            ], 403);
        }

        // Update the entry status
        $result = $this->data_handler->update_entry_status($entry_id, $new_status);

        // If reopening, clear old proposals to allow fresh start
        if ($result !== false && $new_status === 'open') {
            $this->data_handler->clear_proposals($entry_id);
        }

        if ($result !== false) {
            $message = $new_status === 'open'
                ? 'Entry reopened successfully. You can now create new proposals.'
                : 'Entry closed successfully.';

            wp_send_json_success([
                'message' => $message,
                'new_status' => $new_status
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to update entry status']);
        }
    }

    /**
     * Load custom template for response confirmation page
     */
    public function load_response_template($template)
    {
        // Check by page slug or page ID
        $page_id = get_option('aees_response_page_id');

        if (is_page($page_id) || is_page('proposal-response')) {
            $custom_template = AEES_PLUGIN_DIR . 'templates/response-confirmation.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }
}
