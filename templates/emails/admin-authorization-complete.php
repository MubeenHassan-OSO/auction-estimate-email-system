<?php
/**
 * Email Template: Admin Authorization Complete Notification
 * Sent when auction house authorizes an accepted proposal - final confirmation
 *
 * Variables available:
 * - $entry_id: Form entry ID
 * - $proposal: Proposal data array
 * - $form_data: Complete form submission data
 * - $auction_email: Auction house email that authorized
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
    <title>Authorization Complete - Order Confirmed</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #F3F4F6;
            line-height: 1.6;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            flex: 0 0 180px;
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            flex: 1;
            color: #1F2937;
            font-size: 14px;
        }

        .info-value-bold {
            font-weight: 600;
        }

        .timeline-row {
            display: flex;
            padding: 4px 0;
        }

        .timeline-label {
            flex: 0 0 140px;
            color: #78350F;
            font-size: 13px;
            font-weight: 600;
        }

        .timeline-value {
            flex: 1;
            color: #92400E;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .info-row,
            .timeline-row {
                flex-direction: column;
            }

            .info-label,
            .timeline-label {
                flex: none;
                margin-bottom: 4px;
            }

            .info-value,
            .timeline-value {
                font-size: 14px;
            }

            .proposal-row{
                flex-direction: column;
                gap: 10px !important;
            }
            
            .table-header-title{
                font-size: 22px !important;
            }
        }
            
            .proposal-row p {
                margin: 0;
            }

            
        
    </style>
</head>
<body>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F3F4F6; padding: 40px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); max-width: 600px;">

                    <!-- Header -->
                    <tr>
                          <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px 25px; text-align: center;">
                            <h1 class="table-header-title" style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                Order Fully Authorized!
                            </h1>
                              <p style="margin: 0; color: rgba(255, 255, 255, 0.95); font-size: 15px; font-weight: 400;">
                                The auction house has confirmed authorization
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 25px;">
                            <!-- Success Alert -->
                             <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #d1fae55c;border-radius: 8px;border: 1px solid #92ffc7; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #065F46; font-size: 14px; line-height: 1.6;">
                                            <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Workflow Complete!</strong>
                                            This order has been fully approved through all stages:
                                            <ul style="margin: 6px 0; padding-left: 20px;">
                                                <li>Customer accepted the proposal</li>
                                                <li>Auction house has now authorized it</li>
                                            </ul>
                                            You can now proceed with fulfillment.
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Authorization Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #FFFFFF; border-radius: 8px; border: 1px solid #d4d4d4; margin-bottom: 20px; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Authorization Details
                                        </h3>
                                        <div>
                                            <div class="info-row">
                                                <div class="info-label">Authorized By:</div>
                                                <div class="info-value info-value-bold"><?php echo esc_html($auction_email); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Authorization Date:</div>
                                                <div class="info-value"><?php echo wp_date('F j, Y @ g:i A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Entry Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #FFFFFF; border-radius: 8px; border: 1px solid #d4d4d4; margin-bottom: 20px; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Order Details
                                        </h3>
                                        <div>
                                            <div class="info-row">
                                                <div class="info-label">Entry ID:</div>
                                                <div class="info-value info-value-bold">#<?php echo esc_html($entry_id); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Customer Email:</div>
                                                <div class="info-value"><?php echo esc_html($user_email); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Customer Accepted:</div>
                                                <div class="info-value"><?php echo wp_date('F j, Y @ g:i A', strtotime($proposal['user_response_date'])); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Proposal Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #FFFFFF; border-radius: 8px; border: 1px solid #d4d4d4; margin-bottom: 20px; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Authorized Proposal
                                        </h3>
                                        <div style="display: flex; margin-bottom: 16px; gap: 45px;" class="proposal-row">
                                            <div style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Title</div>
                                            <div style="color: #1F2937; font-size: 16px; font-weight: 600;"><?php echo esc_html($proposal['title']); ?></div>
                                        </div>
                                        <div style="display: flex; margin-bottom: 16px; gap: 45px;" class="proposal-row">
                                            <div style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Price</div>
                                            <div style="color: #10B981; font-size: 16px; font-weight: 700;">$<?php echo esc_html(number_format((float)$proposal['price'], 2)); ?></div>
                                        </div>
                                        <?php if (!empty($proposal['details'])): ?>
                                        <div style="display: flex; margin-bottom: 16px; gap: 45px;" class="proposal-row">
                                            <div style="color: #6B7280; font-size: 16px; font-weight: 400; letter-spacing: 0.5px; min-width: 70px;">Details</div>
                                            <div style="color: #4B5563; font-size: 16px; line-height: 1.6;">
                                                <?php
                                                // Data is already sanitized with wp_kses_post() when saved
                                                // Use wpautop() to convert line breaks to <p> and <br> tags
                                                echo wpautop($proposal['details']);
                                                ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <!-- Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?php echo esc_url($edit_url); ?>" style="display: inline-block; padding: 16px 40px; background-color: #10b981; color: #FFFFFF; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                                            View Full Entry
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Timeline Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #FFFBEB;border-radius: 8px;margin-top: 30px;border: 1px solid #ffdf5e;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #92400E; font-size: 14px; line-height: 1.6;">
                                             <strong style="display: block; margin-bottom: 12px; font-size: 16px;">Complete Timeline:</strong>
                                            <div>
                                                <div class="timeline-row">
                                                    <div class="timeline-label">1. Submitted:</div>
                                                    <div class="timeline-value"><?php echo !empty($form_data['date_created']) ? wp_date('M j, Y', strtotime($form_data['date_created'])) : 'N/A'; ?></div>
                                                </div>
                                                <div class="timeline-row">
                                                    <div class="timeline-label">2. User Accepted:</div>
                                                    <div class="timeline-value"><?php echo wp_date('M j, Y @ g:i A', strtotime($proposal['user_response_date'])); ?></div>
                                                </div>
                                                <div class="timeline-row">
                                                    <div class="timeline-label">3. Authorized:</div>
                                                    <div class="timeline-value"><?php echo wp_date('M j, Y @ g:i A'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #F9FAFB; padding: 20px ; text-align: center; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; color: #6B7280; font-size: 16px;">
                                <strong style="color: #1F2937;"><?php echo esc_html($site_name); ?></strong>
                            </p>
                             <p style="margin: 0; color: #9CA3AF; font-size: 14px;">
                                &copy; <?php echo date('Y'); ?> All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
