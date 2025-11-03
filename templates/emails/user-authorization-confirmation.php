<?php
/**
 * Email Template: User Authorization Confirmation
 * Sent to user when auction house authorizes their accepted proposal
 *
 * Variables available:
 * - $entry_id: Form entry ID
 * - $proposal: Proposal data array
 * - $form_data: Complete form submission data
 * - $site_name: Site name
 * - $logo_url: Site logo URL
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order Has Been Confirmed!</title>
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
            flex: 0 0 140px;
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            flex: 1;
            color: #1F2937;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }

            .info-label {
                flex: none;
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F3F4F6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

                    <!-- Header with Logo -->
                    <?php if (!empty($logo_url)): ?>
                    <tr>
                        <td style=" background: #059669; padding: 30px 30px 0 30px; text-align: center;">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" style="max-width: 180px; height: 70px; display: block; margin: 0 auto;" />
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Header -->
                    <tr>
                        <td style="background: #059669; padding: 0 20px 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                Great News!
                            </h1>
                            <p style="margin: 0; color: rgba(255, 255, 255, 0.95); font-size: 16px; font-weight: 400;">
                                Your order has been confirmed
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 30px;">

                            <!-- Success Message -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #ECFDF5; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #065F46; font-size: 15px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 8px; font-size: 17px; color: #047857;">🎉 Your Order is Confirmed!</strong>
                                            The auction house has reviewed and authorized your selected shipping proposal. We will now proceed with fulfilling your order.
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Confirmed Service Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 20px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Your Selected Service
                                        </h3>

                                        <!-- Service Provider with Image -->
                                        <?php if (!empty($proposal['image'])): ?>
                                        <div style="text-align: center; margin-bottom: 20px;">
                                            <img src="<?php echo esc_url($proposal['image']); ?>" alt="<?php echo esc_attr($proposal['title']); ?>" style="max-width: 100px; max-height: 100px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);" />
                                        </div>
                                        <?php endif; ?>

                                        <div style="margin-bottom: 16px;">
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Service</div>
                                            <div style="color: #1F2937; font-size: 18px; font-weight: 600;"><?php echo esc_html($proposal['title']); ?></div>
                                        </div>

                                        <div style="margin-bottom: 16px;">
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Price</div>
                                            <div style="color: #10B981; font-size: 28px; font-weight: 700;">$<?php echo esc_html(number_format((float)$proposal['price'], 2)); ?></div>
                                        </div>

                                        <?php if (!empty($proposal['details'])): ?>
                                        <div>
                                            <div style="color: #6B7280; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Details</div>
                                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6;">
                                                <?php echo wpautop($proposal['details']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border-radius: 8px; border-left: 4px solid #db0f31; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px 0; color: #1F2937; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Order Information
                                        </h3>
                                        <div>
                                            <div class="info-row">
                                                <div class="info-label">Order ID:</div>
                                                <div class="info-value" style="font-weight: 600;">#<?php echo esc_html($entry_id); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Confirmation Date:</div>
                                                <div class="info-value"><?php echo wp_date('F j, Y @ g:i A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Next Steps -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #EFF6FF; border-radius: 8px; border-left: 4px solid #3B82F6; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="color: #1E40AF; font-size: 14px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 8px; font-size: 16px;">📋 What Happens Next?</strong>
                                            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                                                <li style="margin-bottom: 6px;">We will begin processing your order immediately</li>
                                                <li style="margin-bottom: 6px;">You will receive updates as your order progresses</li>
                                                <li>If you have any questions, please don't hesitate to contact us</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support Message -->
                            <div style="text-align: center; padding: 20px 0;">
                                <p style="margin: 0; color: #6B7280; font-size: 14px; line-height: 1.6;">
                                    Thank you for choosing us! If you have any questions about your order,<br>
                                    feel free to reach out to our support team.
                                </p>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #F9FAFB; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 13px;">
                                <strong style="color: #1F2937;"><?php echo esc_html($site_name); ?></strong>
                            </p>
                            <p style="margin: 0; color: #9CA3AF; font-size: 12px;">
                                &copy; <?php echo date('Y'); ?>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
