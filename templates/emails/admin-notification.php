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
$edit_url = admin_url("admin.php?page=aees-edit-entry&edit={$entry_id}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Response Notification</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #F3F4F6; line-height: 1.6;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F3F4F6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #db0f31; padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                User Response Received
                            </h1>
                            <p style="margin: 12px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 15px; font-weight: 400;">
                                A user has responded to a proposal
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
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
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; font-weight: 500;">User Email:</td>
                                                <td style="padding: 8px 0; color: #1F2937; font-size: 14px;"><?php echo esc_html($user_email); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Response Date:</td>
                                                <td style="padding: 8px 0; color: #1F2937; font-size: 14px;"><?php echo wp_date('F j, Y @ g:i A'); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Proposal Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid <?php echo $action_color; ?>; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Proposal Information
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
                                        <a href="<?php echo esc_url($edit_url); ?>" style="display: inline-block; padding: 16px 40px; background-color: #db0f31; color: #FFFFFF; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(219, 15, 49, 0.2);">
                                            View Entry in Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #EFF6FF; border-radius: 8px; margin-top: 30px; border-left: 4px solid #3B82F6;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <div style="color: #1E40AF; font-size: 14px; line-height: 1.6;">
                                            <strong>ℹ️ What's Next?</strong><br>
                                            <?php if ($action === 'rejected'): ?>
                                            The user has declined this proposal. You may want to reach out directly or submit a revised proposal.
                                            <?php else: ?>
                                            The user has accepted this proposal. Please proceed with the next steps in your workflow.
                                            <?php endif; ?>
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
