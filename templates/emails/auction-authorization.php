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

        /* Data Section */
        .data-section {
            margin: 16px 0;
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
            .email-container {
                margin: 20px auto;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-header h1 {
                font-size: 20px;
            }

            .email-body {
                padding: 24px 16px;
            }

            .info-card {
                padding: 16px;
            }

            .info-row {
                flex-direction: column;
                padding: 12px 0;
            }

            .info-label {
                flex: none;
                margin-bottom: 6px;
                font-size: 13px;
            }

            .info-value {
                font-size: 14px;
            }

            .btn-authorize {
                display: block;
                width: 100%;
                padding: 12px 24px;
            }

            .group-title {
                font-size: 13px;
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

                        <?php if (!empty($form_data['grouped_data'])): ?>
                            <div class="info-card">
                                <h2>Form Submission Details</h2>
                                <?php
                                // Define fields to show from Other Information/ungrouped group
                                $other_info_allowed_fields = ['Insured Value', 'Customs Value', 'Contact Email'];

                                foreach ($form_data['grouped_data'] as $group_id => $group):
                                    $group_title = $group['title'];

                                    // Filter logic based on group
                                    $should_show_group = false;
                                    $filtered_data = [];

                                    // Handle "Other Information" / ungrouped - show only specific fields
                                    if ($group_id === 'ungrouped' || stripos($group_title, 'Other Info') !== false) {
                                        if (!empty($group['fields'])) {
                                            foreach ($group['fields'] as $field) {
                                                // Check if field is in allowed list OR if it's an invoice/upload field
                                                $is_allowed = in_array($field['label'], $other_info_allowed_fields);
                                                $is_invoice = stripos($field['label'], 'Invoice') !== false || stripos($field['label'], 'Attach') !== false;

                                                if (!empty($field['raw_value']) && ($is_allowed || $is_invoice)) {
                                                    $filtered_data[] = $field;
                                                    $should_show_group = true;
                                                }
                                            }
                                        }
                                    }
                                    // Handle "Seller" group - only show first row
                                    elseif (stripos($group_title, 'Seller') !== false) {
                                        if ($group['is_repeater'] && !empty($group['rows'])) {
                                            // Get only the first row
                                            $first_row = reset($group['rows']);
                                            if ($first_row) {
                                                foreach ($first_row as $field) {
                                                    if (!empty($field['raw_value'])) {
                                                        $filtered_data[] = $field;
                                                        $should_show_group = true;
                                                    }
                                                }
                                            }
                                        } elseif (!empty($group['fields'])) {
                                            foreach ($group['fields'] as $field) {
                                                if (!empty($field['raw_value'])) {
                                                    $filtered_data[] = $field;
                                                    $should_show_group = true;
                                                }
                                            }
                                        }
                                    }
                                    // Handle "Ship To" group - show if exists
                                    elseif (stripos($group_title, 'Ship To') !== false || stripos($group_title, 'Shipping') !== false) {
                                        if ($group['is_repeater'] && !empty($group['rows'])) {
                                            foreach ($group['rows'] as $row_fields) {
                                                foreach ($row_fields as $field) {
                                                    if (!empty($field['raw_value'])) {
                                                        $filtered_data[] = $field;
                                                        $should_show_group = true;
                                                    }
                                                }
                                            }
                                        } elseif (!empty($group['fields'])) {
                                            foreach ($group['fields'] as $field) {
                                                if (!empty($field['raw_value'])) {
                                                    $filtered_data[] = $field;
                                                    $should_show_group = true;
                                                }
                                            }
                                        }
                                    }

                                    // Display the group if we have data to show
                                    if ($should_show_group && !empty($filtered_data)):
                                ?>
                                    <h4 class="group-title">
                                        <?php echo esc_html($group_title); ?>
                                    </h4>
                                    <div class="data-section">
                                        <?php foreach ($filtered_data as $field): ?>
                                            <div class="info-row">
                                                <div class="info-label"><?php echo esc_html($field['label']); ?></div>
                                                <div class="info-value"><?php echo $field['value']; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
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
