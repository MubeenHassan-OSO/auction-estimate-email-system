<?php
if (!defined('ABSPATH')) exit;

/**
 * Handles all proposal response processing (accept/reject/authorize)
 * Extracted from AEES_Edit_Entry_Page for better organization
 */
class AEES_Proposal_Response_Handler
{
    private $data_handler;
    private $email_manager;
    private $form_data_handler;

    public function __construct()
    {
        $this->data_handler = new AEES_Proposal_Data_Handler();
        $this->email_manager = new AEES_Proposal_Email_Manager();
        $this->form_data_handler = new AEES_Form_Data_Handler();
    }

    /**
     * Handle proposal response (reject/accept) from email link
     */
    public function handle_proposal_response()
    {
        if (!isset($_GET['aees_response']) || !isset($_GET['token'])) {
            return;
        }

        $token = sanitize_text_field($_GET['token']);
        $action = sanitize_text_field($_GET['aees_response']); // 'reject' or 'accept'

        // Find the proposal by token
        $result = $this->data_handler->find_proposal_by_token($token);

        if (!$result) {
            set_transient('aees_response_error', 'Invalid or expired link', 300);
            return;
        }

        extract($result); // $entry_id, $proposal_uid, $proposal

        // Get all proposals for this entry to check overall status
        $proposal_data = $this->data_handler->get_proposal_data($entry_id);
        $all_proposals = $proposal_data['proposals'];

        // PRIORITY 1: Check if user has already responded (check this BEFORE entry status)
        // This ensures correct message when user clicks link multiple times after responding

        // Check if ANY proposal has been rejected - if so, all proposals were declined
        foreach ($all_proposals as $p) {
            if ($p['status'] === 'rejected') {
                set_transient('aees_response_error', 'You have already declined all proposals in this email. All proposals are now invalid.', 300);
                set_transient('aees_response_status', 'rejected', 300);
                return;
            }
        }

        // Check if this specific proposal has already been responded to
        if ($proposal['status'] !== 'pending') {
            set_transient('aees_response_error', 'You have already responded to this request', 300);
            set_transient('aees_response_status', $proposal['status'], 300);
            return;
        }

        // PRIORITY 2: Check if entry is manually closed by admin (only if user hasn't responded yet)
        // This check comes AFTER response checks to avoid showing "closed by admin" when user already responded
        $entry_status = $this->data_handler->get_entry_status($entry_id);
        if ($entry_status === 'closed') {
            set_transient('aees_response_error', 'This request has been closed by the administrator. This link is no longer valid.', 300);
            set_transient('aees_response_status', 'closed', 300);
            return;
        }

        // Process the response based on action
        if ($action === 'reject') {
            // Rejection invalidates ALL proposals in this batch
            $this->process_rejection($entry_id, $proposal_uid, $proposal, $all_proposals);
        } elseif ($action === 'accept') {
            $this->process_acceptance($entry_id, $proposal_uid, $proposal);
        } else {
            set_transient('aees_response_error', 'Invalid action', 300);
            return;
        }

        // Redirect to confirmation page
        wp_redirect(add_query_arg('aees_confirmed', '1', home_url('/proposal-response/')));
        exit;
    }

    /**
     * Handle authorization response from auction house
     */
    public function handle_authorization_response()
    {
        if (!isset($_GET['aees_authorize']) || !isset($_GET['token'])) {
            return;
        }

        $authorization_token = sanitize_text_field($_GET['token']);

        // Find the proposal by authorization token
        $token_data = $this->data_handler->find_by_authorization_token($authorization_token);

        if (!$token_data) {
            set_transient('aees_response_error', 'Invalid or expired authorization link', 300);
            return;
        }

        // Check if authorization token has expired (14 days from acceptance) - compare in WordPress timezone
        if (!empty($token_data['authorization_expires_at']) && strtotime($token_data['authorization_expires_at']) < strtotime(current_time('mysql'))) {
            set_transient('aees_response_error', 'Authorization link has expired (valid for 14 days)', 300);
            set_transient('aees_response_status', 'expired', 300);
            return;
        }

        // Get full proposal data (needed for status checks)
        $proposal_data = $this->data_handler->get_proposal_data($token_data['entry_id']);
        $proposals = $proposal_data['proposals'];
        $auction_email = $proposal_data['auction_email'];

        // Find the specific proposal
        $proposal = null;
        foreach ($proposals as $p) {
            if ($p['uid'] === $token_data['proposal_uid']) {
                $proposal = $p;
                break;
            }
        }

        if (!$proposal) {
            set_transient('aees_response_error', 'Proposal not found', 300);
            return;
        }

        // PRIORITY 1: Check if already authorized (check this BEFORE entry status)
        // This ensures correct message when auction house clicks link multiple times after authorizing
        if (isset($proposal['authorization_status']) && $proposal['authorization_status'] === 'authorized') {
            set_transient('aees_response_error', 'This proposal has already been authorized', 300);
            set_transient('aees_response_status', 'authorized', 300);
            return;
        }

        // PRIORITY 2: Check if entry is manually closed by admin (only if not already authorized)
        // This check comes AFTER authorization check to avoid showing "closed by admin" when already authorized
        $entry_status = $this->data_handler->get_entry_status($token_data['entry_id']);
        if ($entry_status === 'closed') {
            set_transient('aees_response_error', 'This authorization request has been closed by the administrator. This link is no longer valid.', 300);
            set_transient('aees_response_status', 'closed', 300);
            return;
        }

        // Check if proposal was accepted (required before authorization)
        if ($token_data['status'] !== 'accepted') {
            set_transient('aees_response_error', 'This proposal has not been accepted yet', 300);
            return;
        }

        // Process authorization
        $this->process_authorization($token_data['entry_id'], $token_data['proposal_uid'], $proposal, $auction_email);

        // Redirect to confirmation page
        wp_redirect(add_query_arg('aees_authorized', '1', home_url('/proposal-response/')));
        exit;
    }

    /**
     * Process rejection
     * When user clicks "Decline All Proposals", ALL proposals are marked as rejected
     *
     * Updated in v1.7.1: Changed to mark ALL proposals as "rejected" instead of "invalid"
     * because the single "Decline All" button means user is explicitly rejecting all proposals
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID of the token used (doesn't matter which one)
     * @param array $proposal The proposal data
     * @param array $all_proposals All proposals in this batch
     */
    private function process_rejection($entry_id, $proposal_uid, $proposal, $all_proposals)
    {
        // Get auction email
        $proposal_data = $this->data_handler->get_proposal_data($entry_id);
        $auction_email = $proposal_data['auction_email'];

        // Mark ALL proposals as rejected (user clicked "Decline All Proposals" button)
        foreach ($all_proposals as &$p) {
            if ($p['status'] === 'pending') {
                $p['status'] = 'rejected';
                $p['user_response_date'] = current_time('mysql');
            }
        }

        // Save back to custom table
        $this->data_handler->save_proposal_data($entry_id, $auction_email, $all_proposals);

        // Save all rejected proposals to history table for permanent record
        foreach ($all_proposals as $p) {
            if ($p['status'] === 'rejected') {
                $this->data_handler->save_proposal_to_history($entry_id, $p);
            }
        }

        // Close the entry (no further proposals can be sent until admin reopens)
        $this->data_handler->update_entry_status($entry_id, 'closed');

        // Store for confirmation page
        set_transient('aees_response_success', true, 300);
        set_transient('aees_response_proposal_title', $proposal['title'], 300);
        set_transient('aees_response_action', 'rejected', 300);

        // Send admin notification
        $this->email_manager->send_admin_notification($entry_id, $proposal, 'rejected');
    }

    /**
     * Process acceptance - when user accepts a proposal
     * Generates authorization token and sends emails to admin and auction house
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID
     * @param array $proposal The proposal data
     */
    private function process_acceptance($entry_id, $proposal_uid, $proposal)
    {
        // Get current proposals from custom table
        $proposal_data = $this->data_handler->get_proposal_data($entry_id);
        $proposals = $proposal_data['proposals'];
        $auction_email = $proposal_data['auction_email'];

        // Get form data for email context
        $form_data = $this->form_data_handler->get_form_submission_data($entry_id);

        // Generate secure authorization token for auction house
        $authorization_token = bin2hex(random_bytes(32));

        // Update the proposal status
        foreach ($proposals as &$p) {
            if ($p['uid'] === $proposal_uid) {
                $p['status'] = 'accepted';
                $p['user_response_date'] = current_time('mysql');
                $p['authorization_token'] = $authorization_token;
            }
        }

        // Save back to custom table
        $this->data_handler->save_proposal_data($entry_id, $auction_email, $proposals);

        // Save accepted proposal to history table for permanent record
        $accepted_proposal = null;
        foreach ($proposals as $p) {
            if ($p['uid'] === $proposal_uid) {
                $accepted_proposal = $p;
                break;
            }
        }
        if ($accepted_proposal) {
            $user_email = $form_data['user_email'] ?? null;
            $this->data_handler->save_proposal_to_history($entry_id, $accepted_proposal, $user_email);
        }

        // Update tokens table with authorization token and set status to accepted
        $this->data_handler->update_token_authorization($entry_id, $proposal_uid, $authorization_token, 'accepted');

        // Set authorization token expiration to 14 days from now
        $this->data_handler->set_authorization_expiration($entry_id, $proposal_uid, $authorization_token);

        // Store for confirmation page
        set_transient('aees_response_success', true, 300);
        set_transient('aees_response_proposal_title', $proposal['title'], 300);
        set_transient('aees_response_action', 'accepted', 300);

        // Send admin notification about acceptance
        $this->email_manager->send_admin_acceptance_notification($entry_id, $proposal, $form_data);

        // Send authorization request to auction house
        $this->email_manager->send_auction_authorization_email($entry_id, $proposal, $auction_email, $authorization_token, $form_data);
    }

    /**
     * Process authorization - when auction house authorizes an accepted proposal
     * Final step in the acceptance workflow
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID
     * @param array $proposal The proposal data
     * @param string $auction_email The auction house email
     */
    private function process_authorization($entry_id, $proposal_uid, $proposal, $auction_email)
    {
        // Get current proposals from custom table
        $proposal_data = $this->data_handler->get_proposal_data($entry_id);
        $proposals = $proposal_data['proposals'];

        // Get form data for email context
        $form_data = $this->form_data_handler->get_form_submission_data($entry_id);

        // Update the proposal status to authorized
        foreach ($proposals as &$p) {
            if ($p['uid'] === $proposal_uid) {
                $p['authorization_status'] = 'authorized';
                $p['authorization_date'] = current_time('mysql');
                $p['authorized_by'] = $auction_email;
            }
        }

        // Save back to custom table
        $this->data_handler->save_proposal_data($entry_id, $auction_email, $proposals);

        // Update history with authorization details
        $authorized_proposal = null;
        foreach ($proposals as $p) {
            if ($p['uid'] === $proposal_uid) {
                $authorized_proposal = $p;
                break;
            }
        }
        if ($authorized_proposal) {
            $user_email = $form_data['user_email'] ?? null;
            $this->data_handler->save_proposal_to_history($entry_id, $authorized_proposal, $user_email);
        }

        // Update tokens table
        $this->data_handler->update_token_authorized($entry_id, $proposal_uid, $auction_email);

        // Close the entry permanently (authorized orders are complete - no reopening allowed)
        $this->data_handler->update_entry_status($entry_id, 'closed');

        // Store for confirmation page
        set_transient('aees_response_success', true, 300);
        set_transient('aees_response_proposal_title', $proposal['title'], 300);
        set_transient('aees_response_action', 'authorized', 300);

        // Send final confirmation email to admin
        $this->email_manager->send_admin_authorization_complete($entry_id, $proposal, $form_data, $auction_email);
    }
}
