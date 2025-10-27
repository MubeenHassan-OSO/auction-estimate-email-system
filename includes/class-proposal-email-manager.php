<?php
if (!defined('ABSPATH')) exit;

/**
 * Handles all email sending functionality for proposals
 * Extracted from AEES_Edit_Entry_Page for better organization
 */
class AEES_Proposal_Email_Manager
{
    /**
     * Send proposals email to user
     *
     * @param string $user_email The user's email address
     * @param int $entry_id The entry ID
     * @param array $proposals Array of proposals
     * @param array $form_data Form submission data
     * @return bool Success status
     */
    public function send_proposals_email($user_email, $entry_id, $proposals, $form_data)
    {
        // Get site name and logo
        $site_name = get_bloginfo('name');
        $site_logo = get_custom_logo();

        // If custom logo exists, extract URL
        $logo_url = '';
        if (!empty($site_logo)) {
            preg_match('/src="([^"]+)"/', $site_logo, $matches);
            $logo_url = $matches[1] ?? '';
        }

        // Fallback to site icon if no logo
        if (empty($logo_url) && has_site_icon()) {
            $logo_url = get_site_icon_url(200);
        }

        // Load email template
        ob_start();
        include AEES_PLUGIN_DIR . 'templates/emails/user-proposals.php';
        $email_body = ob_get_clean();

        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
        ];

        $subject = 'Please Check the Auction Shipping Estimates of your Request.';

        // Send email
        $sent = wp_mail($user_email, $subject, $email_body, $headers);

        return $sent;
    }

    /**
     * Send admin notification email
     *
     * @param int $entry_id The entry ID
     * @param array $proposal The proposal data
     * @param string $action The action taken (rejected/accepted)
     */
    public function send_admin_notification($entry_id, $proposal, $action)
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        // Get user email from form data
        $form_data_handler = new AEES_Form_Data_Handler();
        $form_data = $form_data_handler->get_form_submission_data($entry_id);
        $user_email = $form_data['user_email'] ?? 'Not available';

        // Load HTML email template
        ob_start();
        include AEES_PLUGIN_DIR . 'templates/emails/admin-notification.php';
        $email_body = ob_get_clean();

        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
        ];

        $action_text = ucfirst($action);
        $subject = "[{$site_name}] User {$action_text} Proposal - Entry #{$entry_id}";

        wp_mail($admin_email, $subject, $email_body, $headers);
    }

    /**
     * Send authorization request email to auction house
     * Called when user accepts a proposal
     *
     * @param int $entry_id The entry ID
     * @param array $proposal The proposal data
     * @param string $auction_email The auction house email
     * @param string $authorization_token The authorization token
     * @param array $form_data Form submission data
     */
    public function send_auction_authorization_email($entry_id, $proposal, $auction_email, $authorization_token, $form_data)
    {
        $site_name = get_bloginfo('name');

        // Build authorization URL
        $authorization_url = add_query_arg([
            'aees_authorize' => '1',
            'token' => $authorization_token
        ], home_url('/proposal-response/'));

        // Load HTML email template
        ob_start();
        include AEES_PLUGIN_DIR . 'templates/emails/auction-authorization.php';
        $email_body = ob_get_clean();

        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
        ];

        $subject = "[{$site_name}] Authorization Required - Proposal Accepted by Customer";

        wp_mail($auction_email, $subject, $email_body, $headers);
    }

    /**
     * Send admin notification when user accepts a proposal
     * Notifies admin that authorization request was sent to auction house
     *
     * @param int $entry_id The entry ID
     * @param array $proposal The proposal data
     * @param array $form_data Form submission data
     */
    public function send_admin_acceptance_notification($entry_id, $proposal, $form_data)
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        // Load HTML email template
        ob_start();
        include AEES_PLUGIN_DIR . 'templates/emails/admin-acceptance-notification.php';
        $email_body = ob_get_clean();

        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
        ];

        $subject = "[{$site_name}] Proposal Accepted - Awaiting Authorization (Entry #{$entry_id})";

        wp_mail($admin_email, $subject, $email_body, $headers);
    }

    /**
     * Send final admin notification when auction house authorizes
     * This is the last email in the workflow - confirms everything is complete
     *
     * @param int $entry_id The entry ID
     * @param array $proposal The proposal data
     * @param array $form_data Form submission data
     * @param string $auction_email The auction house email
     */
    public function send_admin_authorization_complete($entry_id, $proposal, $form_data, $auction_email)
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        // Load HTML email template
        ob_start();
        include AEES_PLUGIN_DIR . 'templates/emails/admin-authorization-complete.php';
        $email_body = ob_get_clean();

        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
        ];

        $subject = "[{$site_name}] ✅ Order Authorized - Ready to Proceed (Entry #{$entry_id})";

        wp_mail($admin_email, $subject, $email_body, $headers);
    }
}
