<?php

/**
 * Auction House Authorization Request Email Template
 * Sent when user accepts a proposal - asks auction house to confirm authorization
 */

$site_name = get_bloginfo('name');
$logo_url = get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : 'https://via.placeholder.com/160x60?text=Logo';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Request - Proposal Accepted</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            line-height: 1.6;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .email-body {
            padding: 40px 30px;
        }

        .email-header {
            background: #10b981;
            padding: 40px 20px;
            text-align: center;
            color: #fff;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .email-header p {
            font-size: 15px;
            opacity: 0.9;
            margin-top: 10px;
        }

        .alert-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .alert-box strong {
            color: #065f46;
            display: block;
            margin-bottom: 6px;
        }

        .alert-box p {
            margin: 0;
            color: #047857;
            font-size: 14px;
        }

        /* Info Card */
        .info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .info-card h3 {
            margin: 0 0 16px 0;
            font-size: 16px;
            font-weight: 700;
            color: #374151;
        }

        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            flex: 0 0 140px;
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            flex: 1;
            color: #111827;
            font-size: 14px;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }

        .data-table th {
            background: #f9fafb;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            color: #111827;
        }

        .data-table td:first-child {
            font-weight: 600;
            width: 40%;
            background: #f9fafb;
        }

        .group-title {
            margin: 24px 0 8px 0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        /* Button */
        .btn-authorize {
            display: inline-block;
            padding: 14px 32px;
            background: #10b981;
            color: #fff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            transition: all 0.25s ease;
        }

        .btn-authorize:hover {
            background: #059669;
        }

        .button-container {
            text-align: center;
            margin: 32px 0;
        }

        /* Footer */
        .email-footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .email-body {
                padding: 24px 16px;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 4px;
            }

            .btn-authorize {
                display: block;
                width: 100%;
            }

            .data-table td:first-child {
                width: 35%;
            }
        }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6; padding:20px;">
        <tr>
            <td align="center">
                <div class="email-container">
                    <div class="email-header">
                        <h1>🎉 New Authorization Request</h1>
                        <p>Please review the submitted information and authorize</p>
                    </div>

                    <div class="email-body">
                        <div class="alert-box">
                            <strong>Action Required</strong>
                            <p>A new estimate request has been submitted. Please review the details below and authorize if you can proceed.</p>
                        </div>

                        <h2 style="margin:0 0 20px 0; font-size:20px; font-weight:700; color:#111827;">Submitted Information</h2>

                        <?php if (!empty($form_data['grouped_data'])): ?>
                            <div class="info-card">
                                <h3>Form Submission Details</h3>
                                <?php foreach ($form_data['grouped_data'] as $group): ?>
                                    <h4 class="group-title">
                                        <?php echo esc_html($group['title']); ?>
                                    </h4>
                                    <table class="data-table">
                                        <tbody>
                                            <?php
                                            // Handle repeater groups
                                            if ($group['is_repeater'] && !empty($group['rows'])):
                                                foreach ($group['rows'] as $row_fields):
                                                    foreach ($row_fields as $field):
                                                        if (!empty($field['raw_value'])):
                                            ?>
                                                            <tr>
                                                                <td style="font-weight:600; width:40%;"><?php echo esc_html($field['label']); ?></td>
                                                                <td><?php echo $field['value']; ?></td>
                                                            </tr>
                                            <?php
                                                        endif;
                                                    endforeach;
                                                endforeach;
                                            // Handle regular groups
                                            elseif (!empty($group['fields'])):
                                                foreach ($group['fields'] as $field):
                                                    if (!empty($field['raw_value'])):
                                            ?>
                                                        <tr>
                                                            <td style="font-weight:600; width:40%;"><?php echo esc_html($field['label']); ?></td>
                                                            <td><?php echo $field['value']; ?></td>
                                                        </tr>
                                            <?php
                                                    endif;
                                                endforeach;
                                            endif;
                                            ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="button-container">
                            <a href="<?php echo esc_url($authorization_url); ?>" class="btn-authorize">
                                Authorize This Request
                            </a>
                        </div>

                        <p style="text-align:center; color:#6b7280; font-size:13px; margin-top:24px;">
                            By authorizing, you confirm that you can fulfill this request according to the submitted specifications.
                        </p>

                        <p style="text-align:center; color:#6b7280; font-size:12px; margin-top:16px; padding:16px; background:#fef3c7; border-radius:6px;">
                            ⏰ <strong>Time Sensitive:</strong> Please respond within 14 days. This authorization link will expire after that period.
                        </p>
                    </div>

                    <div class="email-footer">
                        <p style="margin:0;">This is an automated message from <?php echo esc_html($site_name); ?></p>
                        <p style="margin:8px 0 0 0;">If you did not expect this request, please contact us immediately.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
