<?php
/**
 * Email Template: Admin Notification
 *
 * Variables available:
 * - $entry_id: Form entry ID
 * - $proposal: Proposal data array
 * - $action: User action (rejected/accepted)
 * - $user_email: User's email address
 */

if (!defined('ABSPATH')) exit;

$site_name = get_bloginfo('name');
$action_text = ucfirst($action);
$action_color = $action === 'rejected' ? '#EF4444' : '#10B981';
$action_icon = $action === 'rejected' ? '✕' : '✓';
$header_bg_color = $action === 'rejected' ? '#EF4444' : '#10B981';
$header_title = $action === 'rejected' ? 'Proposal Rejected' : 'Proposal Accepted';
$header_subtitle = $action === 'rejected' ? 'A user has declined a proposal' : 'A user has accepted a proposal';
$edit_url = admin_url("admin.php?page=aees-edit-entry&edit={$entry_id}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Response Notification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #F3F4F6;
            color: #111827;
            line-height: 1.6;
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

        .header {
            padding: 40px 30px;
            text-align: center;
        }

        .header-title {
            margin: 0;
            color: #FFFFFF;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            margin: 12px 0 0 0;
            color: rgba(255, 255, 255, 0.95);
            font-size: 15px;
            font-weight: 400;
        }

        .body-content {
            padding: 40px 30px;
        }

        .card {
            background: #F9FAFB;
            border-radius: 8px;
            margin-bottom: 30px;
            padding: 24px;
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

        .proposal-field {
            margin-bottom: 16px;
        }

        .proposal-field:last-child {
            margin-bottom: 0;
        }

        .proposal-field-label {
            color: #6B7280;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .proposal-field-value {
            color: #1F2937;
            font-size: 16px;
            font-weight: 600;
        }

        .proposal-price {
            color: #10B981;
            font-size: 24px;
            font-weight: 700;
        }

        .proposal-details-text {
            color: #4B5563;
            font-size: 14px;
            line-height: 1.6;
        }

        .proposal-details-text p {
            margin: 0 0 8px 0;
        }

        .action-button-container {
            text-align: center;
            padding: 20px 0;
        }

        .action-button {
            display: inline-block;
            padding: 16px 40px;
            color: #FFFFFF !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }

        .info-box {
            background: #EFF6FF;
            border-radius: 8px;
            border-left: 4px solid #3B82F6;
            margin-top: 30px;
            padding: 20px;
            text-align: center;
        }

        .info-box-content {
            color: #1E40AF;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-box-content strong {
            display: block;
            margin-bottom: 8px;
        }

        .footer {
            background: #F9FAFB;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .footer-text {
            margin: 0 0 10px 0;
            color: #6B7280;
            font-size: 13px;
        }

        .footer-text strong {
            color: #1F2937;
        }

        .footer-copyright {
            margin: 0;
            color: #9CA3AF;
            font-size: 12px;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .header {
                padding: 32px 20px !important;
            }

            .header-title {
                font-size: 24px !important;
            }

            .header-subtitle {
                font-size: 14px !important;
            }

            .body-content {
                padding: 30px 20px !important;
            }

            .card {
                padding: 20px !important;
                margin-bottom: 24px !important;
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

            .proposal-price {
                font-size: 20px !important;
            }

            .action-button {
                padding: 14px 30px !important;
                font-size: 15px !important;
                width: 100%;
                max-width: 280px;
            }

            .info-box {
                padding: 16px !important;
                margin-top: 24px !important;
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
                    <div class="header" style="background-color: <?php echo $header_bg_color; ?>; padding: 40px 30px; text-align: center;">
                        <h1 class="header-title" style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                            <?php echo $header_title; ?>
                        </h1>
                        <p class="header-subtitle" style="margin: 12px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 15px; font-weight: 400;">
                            <?php echo $header_subtitle; ?>
                        </p>
                    </div>

                    <!-- Body Content -->
                    <div class="body-content" style="padding: 40px 30px;">

                        <!-- Entry Info Card -->
                        <div class="card" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid <?php echo $action_color; ?>; margin-bottom: 30px; padding: 24px;">
                            <h3 class="card-title" style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Entry Details
                            </h3>
                            <div class="info-row" style="display: flex; padding: 8px 0; gap: 20px;">
                                <div class="info-label" style="color: #6B7280; font-size: 14px; font-weight: 500; min-width: 140px;">Entry ID:</div>
                                <div class="info-value info-value-bold" style="color: #1F2937; font-size: 14px; font-weight: 600; flex: 1;">#<?php echo esc_html($entry_id); ?></div>
                            </div>
                            <div class="info-row" style="display: flex; padding: 8px 0; gap: 20px;">
                                <div class="info-label" style="color: #6B7280; font-size: 14px; font-weight: 500; min-width: 140px;">User Email:</div>
                                <div class="info-value" style="color: #1F2937; font-size: 14px; flex: 1;"><?php echo esc_html($user_email); ?></div>
                            </div>
                            <div class="info-row" style="display: flex; padding: 8px 0; gap: 20px;">
                                <div class="info-label" style="color: #6B7280; font-size: 14px; font-weight: 500; min-width: 140px;">Response Date:</div>
                                <div class="info-value" style="color: #1F2937; font-size: 14px; flex: 1;"><?php echo wp_date('F j, Y @ g:i A'); ?></div>
                            </div>
                        </div>

                        <!-- Proposal Info Card -->
                        <div class="card" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid <?php echo $action_color; ?>; margin-bottom: 30px; padding: 24px;">
                            <h3 class="card-title" style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Proposal Information
                            </h3>
                            <div class="proposal-field" style="margin-bottom: 16px;">
                                <div class="proposal-field-label" style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Title</div>
                                <div class="proposal-field-value" style="color: #1F2937; font-size: 16px; font-weight: 600;"><?php echo esc_html($proposal['title']); ?></div>
                            </div>
                            <div class="proposal-field" style="margin-bottom: 16px;">
                                <div class="proposal-field-label" style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Price</div>
                                <div class="proposal-price" style="color: #10B981; font-size: 24px; font-weight: 700;">$<?php echo esc_html(number_format((float)$proposal['price'], 2)); ?></div>
                            </div>
                            <?php if (!empty($proposal['details'])): ?>
                            <div class="proposal-field">
                                <div class="proposal-field-label" style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Details</div>
                                <div class="proposal-details-text" style="color: #4B5563; font-size: 16px; line-height: 1.6;">
                                    <?php
                                    // Data is already sanitized with wp_kses_post() when saved
                                    // Use wpautop() to convert line breaks to <p> and <br> tags
                                    echo wpautop($proposal['details']);
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="action-button-container" style="text-align: center; padding: 20px 0;">
                            <a href="<?php echo esc_url($edit_url); ?>" class="action-button" style="display: inline-block; padding: 16px 40px; background-color: <?php echo $header_bg_color; ?>; color: #FFFFFF !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);">
                                View Entry in Dashboard
                            </a>
                        </div>

                        <!-- Info Box -->
                        <div class="info-box" style="background: #EFF6FF; border-radius: 8px; border-left: 4px solid #3B82F6; margin-top: 30px; padding: 20px; text-align: center;">
                            <div class="info-box-content" style="color: #1E40AF; font-size: 14px; line-height: 1.6;">
                                <strong style="display: block; margin-bottom: 8px;">ℹ️ What's Next?</strong>
                                <?php if ($action === 'rejected'): ?>
                                The user has declined this proposal. You may want to reach out directly or submit a revised proposal.
                                <?php else: ?>
                                The user has accepted this proposal. Please proceed with the next steps in your workflow.
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="footer" style="background: #F9FAFB; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                        <p class="footer-text" style="margin: 0 0 10px 0; color: #6B7280; font-size: 16px;">
                            <strong style="color: #1F2937;"><?php echo esc_html($site_name); ?></strong>
                        </p>
                        <p class="footer-copyright" style="margin: 0; color: #9CA3AF; font-size: 14px;">
                            &copy; <?php echo date('Y'); ?>. All rights reserved.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
