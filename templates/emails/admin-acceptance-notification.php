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
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #F3F4F6; line-height: 1.6;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F3F4F6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #10b981; padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                🎉 Proposal Accepted!
                            </h1>
                            <p style="margin: 12px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 15px; font-weight: 400;">
                                Awaiting auction house authorization
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <!-- Success Alert -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #D1FAE5; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #065F46; font-size: 14px; line-height: 1.6;">
                                            <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Good News!</strong>
                                            A customer has accepted a proposal. An authorization request has been automatically sent to the auction house. You'll receive another notification once they authorize.
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Entry Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid #db0f31; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Entry Details
                                        </h3>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; font-weight: 500; width: 140px;">Entry ID:</td>
                                                <td style="padding: 8px 0; color: #1F2937; font-size: 14px; font-weight: 600;">#<?php echo esc_html($entry_id); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Customer Email:</td>
                                                <td style="padding: 8px 0; color: #1F2937; font-size: 14px;"><?php echo esc_html($user_email); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Accepted On:</td>
                                                <td style="padding: 8px 0; color: #1F2937; font-size: 14px;"><?php echo wp_date('F j, Y @ g:i A', strtotime($proposal['user_response_date'])); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Proposal Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Accepted Proposal
                                        </h3>
                                        <div style="margin-bottom: 16px;">
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Title</div>
                                            <div style="color: #1F2937; font-size: 16px; font-weight: 600;"><?php echo esc_html($proposal['title']); ?></div>
                                        </div>
                                        <div style="margin-bottom: 16px;">
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Price</div>
                                            <div style="color: #10B981; font-size: 24px; font-weight: 700;">$<?php echo esc_html(number_format((float)$proposal['price'], 2)); ?></div>
                                        </div>
                                        <?php if (!empty($proposal['details'])): ?>
                                        <div>
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Details</div>
                                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6;">
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
                                            View Entry in Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Workflow Status -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #EFF6FF; border-radius: 8px; margin-top: 30px; border-left: 4px solid #3B82F6;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #1E40AF; font-size: 14px; line-height: 1.6;">
                                            <strong style="display: block; margin-bottom: 12px; font-size: 16px;">📋 Next Steps in the Workflow:</strong>
                                            <ol style="margin: 0; padding-left: 20px;">
                                                <li style="margin-bottom: 8px;"><strong>✓ User accepted proposal</strong> (Complete)</li>
                                                <li style="margin-bottom: 8px;"><strong>⏳ Awaiting auction house authorization</strong> (In Progress)</li>
                                                <li style="margin-bottom: 0;">Final confirmation email will be sent to you once authorized</li>
                                            </ol>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #F9FAFB; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 13px;">
                                This is an automated notification from <strong style="color: #1F2937;"><?php echo esc_html($site_name); ?></strong>
                            </p>
                            <p style="margin: 0; color: #9CA3AF; font-size: 12px;">
                                &copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
