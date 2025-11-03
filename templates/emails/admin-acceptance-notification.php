<?php
/**
 * Email Template: Admin Acceptance Notification
 * Sent when a user accepts a proposal - notifies admin and explains next steps
 *
 * Variables available:
 * - $entry_id: Form entry ID
 * - $proposal: Proposal data array
 * - $form_data: Complete form submission data
 */

if (!defined('ABSPATH')) exit;

$site_name = get_bloginfo('name');
$edit_url = admin_url("admin.php?page=aees-edit-entry&edit={$entry_id}");
$user_email = $form_data['user_email'] ?? 'Not available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Accepted - Authorization Pending</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #F9FAFB;
            color: #111827;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .confirmation-header {
            background-color: #10b981;
            padding: 30px;
            text-align: center;
        }

        .confirmation-heading {
            margin: 0;
            color: #FFFFFF;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .confirmation-details {
            margin: 8px 0 0 0;
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            font-weight: 400;
        }

        .body-content {
            padding: 30px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
        }

        .response-alert {
            background: #d1fae55c;
            border: 1px solid #92ffc7;
            margin-bottom: 30px;
        }

        .response-entry-details,
        .response-accepted-proposal {
            background: #FFFFFF;
            border: 1px solid #d4d4d4;
        }

        .card-title {
            margin: 0 0 16px 0;
            color: #1F2937;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            gap: 20px;
        }

        .info-label {
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
            min-width: 140px;
            flex-shrink: 0;
        }

        .info-value {
            color: #1F2937;
            font-size: 14px;
            flex: 1;
        }

        .info-value-bold {
            font-weight: 600;
        }

        .proposal-row {
            display: flex;
            margin-bottom: 16px;
            gap: 45px;
        }

        .proposal-label {
            color: #6B7280;
            font-size: 16px;
            font-weight: 400;
            letter-spacing: 0.5px;
            min-width: 70px;
            flex-shrink: 0;
        }

        .proposal-value {
            color: #1F2937;
            font-size: 16px;
            font-weight: 600;
            flex: 1;
        }

        .proposal-price {
            color: #10B981;
            font-size: 16px;
            font-weight: 700;
        }

        .proposal-details-content {
            color: #4B5563;
            font-size: 14px;
            line-height: 1.6;
        }

        .proposal-details-content p {
            margin: 0 0 8px 0;
        }

        .action-button-container {
            text-align: center;
            padding: 20px 0;
        }

        .action-button {
            display: inline-block;
            padding: 16px 40px;
            background-color: #10b981;
            color: #FFFFFF !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .accepted-proposal-next {
            background: #EFF6FF;
            border: 1px solid #3B82F6;
        }

        .next-steps-content {
            color: #1E40AF;
            font-size: 14px;
            line-height: 1.6;
        }

        .next-steps-title {
            display: block;
            margin-bottom: 12px;
            font-size: 18px;
            font-weight: bold;
        }

        .next-steps-list {
            margin: 0;
            padding-left: 20px;
        }

        .next-steps-list li {
            margin-bottom: 8px;
        }

        .next-steps-list li:last-child {
            margin-bottom: 0;
        }

        .footer {
            background: #F9FAFB;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .footer-site {
            margin: 0 0 10px 0;
            color: #6B7280;
            font-size: 13px;
        }

        .footer-site strong {
            color: #1F2937;
        }

        .footer-copyright {
            margin: 0;
            color: #9CA3AF;
            font-size: 12px;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .body-content {
                padding: 20px !important;
            }

            .card {
                padding: 16px !important;
            }

            .confirmation-header {
                padding: 24px 20px !important;
            }

            .confirmation-heading {
                font-size: 24px !important;
            }

            .confirmation-details {
                font-size: 14px !important;
            }

            .info-row {
                flex-direction: column;
                gap: 4px !important;
                padding: 6px 0 !important;
            }

            .info-label {
                min-width: auto !important;
                font-weight: 600 !important;
            }

            .proposal-row {
                flex-direction: column;
                gap: 8px !important;
                margin-bottom: 12px !important;
            }

            .proposal-label {
                min-width: auto !important;
                font-weight: 600 !important;
            }

            .action-button {
                padding: 14px 30px !important;
                font-size: 15px !important;
                width: 100%;
                max-width: 280px;
            }

            .footer {
                padding: 24px 20px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #F3F4F6; line-height: 1.6;">

    <!-- Outer wrapper table for email client compatibility -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F3F4F6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Email Container -->
                <div class="email-container" style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <div class="confirmation-header" style="background-color: #10b981; padding: 30px; text-align: center;">
                        <h1 class="confirmation-heading" style="margin: 0; color: #FFFFFF; font-size: 30px; font-weight: 700; letter-spacing: -0.5px;">
                            Proposal Accepted!
                        </h1>
                        <p class="confirmation-details" style="margin: 8px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 16px; font-weight: 400;">
                            Awaiting auction house authorization
                        </p>
                    </div>

                    <!-- Body Content -->
                    <div class="body-content" style="padding: 30px; text-align: left;">

                        <!-- Success Alert -->
                        <div class="card response-alert" style="background: #d1fae55c; border-radius: 8px; border: 1px solid #92ffc7; margin-bottom: 30px; padding: 20px;">
                            <div style="color: #065F46; font-size: 14px; line-height: 1.6;">
                                <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Good News!</strong>
                                A customer has accepted a proposal. An authorization request has been automatically sent to the auction house. You'll receive another notification once they authorize.
                            </div>
                        </div>

                        <!-- Entry Info Card -->
                        <div class="card response-entry-details" style="background: #FFFFFF; border-radius: 8px; border: 1px solid #d4d4d4; margin-bottom: 20px; padding: 20px;">
                            <h3 class="card-title" style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Entry Details
                            </h3>
                            <div class="info-row" style="display: flex; padding: 8px 0; gap: 20px;">
                                <div class="info-label" style="color: #6B7280; font-size: 14px; font-weight: 500; min-width: 140px;">Entry ID:</div>
                                <div class="info-value info-value-bold" style="color: #1F2937; font-size: 14px; font-weight: 600; flex: 1;">#<?php echo esc_html($entry_id); ?></div>
                            </div>
                            <div class="info-row" style="display: flex; padding: 8px 0; gap: 20px;">
                                <div class="info-label" style="color: #6B7280; font-size: 14px; font-weight: 500; min-width: 140px;">Customer Email:</div>
                                <div class="info-value" style="color: #1F2937; font-size: 14px; flex: 1;"><?php echo esc_html($user_email); ?></div>
                            </div>
                        </div>

                        <!-- Proposal Info Card -->
                        <div class="card response-accepted-proposal" style="background: #FFFFFF; border-radius: 8px; border: 1px solid #d4d4d4; margin-bottom: 20px; padding: 20px;">
                            <h3 class="card-title" style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Accepted Proposal
                            </h3>
                            <div class="proposal-row" style="display: flex; margin-bottom: 16px; gap: 45px;">
                                <div class="proposal-label" style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Title:</div>
                                <div class="proposal-value" style="color: #1F2937; font-size: 16px; font-weight: 600; flex: 1;"><?php echo esc_html($proposal['title']); ?></div>
                            </div>
                            <div class="proposal-row" style="display: flex; margin-bottom: 16px; gap: 45px;">
                                <div class="proposal-label" style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Price:</div>
                                <div class="proposal-price" style="color: #10B981; font-size: 16px; font-weight: 700; flex: 1;">$<?php echo esc_html(number_format((float)$proposal['price'], 2)); ?></div>
                            </div>
                            <?php if (!empty($proposal['details'])): ?>
                            <div class="proposal-row" style="display: flex; margin-bottom: 16px; gap: 45px;">
                                <div class="proposal-label" style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Details:</div>
                                <div class="proposal-details-content" style="color: #4B5563; font-size: 16px; line-height: 1.6; flex: 1;">
                                    <?php echo wpautop($proposal['details']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="action-button-container" style="text-align: center; padding: 20px 0;">
                            <a href="<?php echo esc_url($edit_url); ?>" class="action-button" style="display: inline-block; padding: 16px 40px; background-color: #10b981; color: #FFFFFF !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                                View Entry in Dashboard
                            </a>
                        </div>

                        <!-- Workflow Status -->
                        <div class="card accepted-proposal-next" style="background: #EFF6FF; border-radius: 8px; border: 1px solid #3B82F6; margin-bottom: 20px; padding: 20px;">
                            <div class="next-steps-content" style="color: #1E40AF; font-size: 14px; line-height: 1.6;">
                                <strong class="next-steps-title" style="display: block; margin-bottom: 12px; font-size: 18px; font-weight: bold;">Next Steps:</strong>
                                <ol class="next-steps-list" style="margin: 0; padding-left: 20px;">
                                    <li style="margin-bottom: 8px;">Awaiting auction house authorization (In Progress)</li>
                                    <li style="margin-bottom: 0;">Final confirmation email will be sent to you once authorized</li>
                                </ol>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="footer" style="background: #F9FAFB; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                        <p class="footer-site" style="margin: 0 0 10px 0; color: #6B7280; font-size: 16px;">
                            <strong style="color: #1F2937;"><?php echo esc_html($site_name); ?></strong>
                        </p>
                        <p class="footer-copyright" style="margin: 0; color: #9CA3AF; font-size: 12px;">
                            &copy; <?php echo date('Y'); ?> All rights reserved.
                        </p>
                    </div>

                </div>
            </td>
        </tr>
    </table>

</body>
</html>
