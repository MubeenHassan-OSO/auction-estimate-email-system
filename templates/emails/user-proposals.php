<?php
/**
 * Modern Email Template for User Proposals
 * Sent when admin creates proposals for a user's auction estimate request
 */

$site_name = get_bloginfo('name');
$site_url = home_url('/');

// Get logo URL
$custom_logo_id = get_theme_mod('custom_logo');
$logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';

// Fallback to site icon if no custom logo
if (empty($logo_url) && has_site_icon()) {
    $logo_url = get_site_icon_url(200);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your Auction Shipping Estimates - <?php echo esc_html($site_name); ?></title>

    <style>
        /* Reset & Base Styles */
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

        /* Email Container */
        .email-wrapper {
            width: 100%;
            background-color: #F9FAFB;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Header Section */
        .email-header {
            background: #667EEA;
            padding: 48px 32px;
            text-align: center;
        }

        .header-logo {
            max-width: 180px;
            max-height: 80px;
            margin-bottom: 24px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .header-title {
            color: #FFFFFF;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
            font-weight: 400;
        }

        /* Body Section */
        .email-body {
            padding: 48px 32px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 16px 0;
        }

        .intro-text {
            font-size: 16px;
            color: #6B7280;
            margin: 0 0 40px 0;
            line-height: 1.7;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 24px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #E5E7EB;
        }

        /* Proposal Cards */
        .proposals-wrapper {
            margin-bottom: 32px;
        }

        .proposal-card {
            background: #FFFFFF;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .proposal-card:hover {
            border-color: #667EEA;
            box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.1), 0 4px 6px -2px rgba(102, 126, 234, 0.05);
        }

        .proposal-card:last-child {
            margin-bottom: 0;
        }

        .proposal-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #F3F4F6;
        }

        .proposal-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .proposal-icon svg {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .proposal-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            flex: 1;
        }

        .price-section {
            background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .price-label {
            font-size: 14px;
            font-weight: 600;
            color: #1E40AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-value {
            font-size: 32px;
            font-weight: 700;
            color: #1E3A8A;
            letter-spacing: -1px;
        }

        .details-section {
            background: #F9FAFB;
            border-left: 4px solid #667EEA;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }

        .details-section p {
            font-size: 15px;
            color: #4B5563;
            line-height: 1.7;
            margin: 0;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }

        .btn-accept {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #FFFFFF !important;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
        }

        .btn-decline {
            background: #FFFFFF;
            color: #DC2626 !important;
            border: 2px solid #FCA5A5;
        }

        .btn-decline:hover {
            background: #FEF2F2;
            border-color: #DC2626;
        }

        /* Footer Section */
        .email-footer {
            background: #F9FAFB;
            padding: 40px 32px;
            border-top: 1px solid #E5E7EB;
        }

        .footer-help {
            text-align: center;
            margin-bottom: 32px;
        }

        .footer-help-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px 0;
        }

        .footer-help-text {
            font-size: 15px;
            color: #6B7280;
            margin: 0 0 16px 0;
            line-height: 1.6;
        }

        .footer-contact {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667EEA;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }

        .footer-divider {
            height: 1px;
            background: #E5E7EB;
            margin: 32px 0;
        }

        .footer-info {
            text-align: center;
        }

        .footer-brand {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px 0;
        }

        .footer-copyright {
            font-size: 14px;
            color: #9CA3AF;
            margin: 0 0 16px 0;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-link {
            font-size: 14px;
            color: #6B7280;
            text-decoration: none;
        }

        .footer-link:hover {
            color: #667EEA;
            text-decoration: underline;
        }

        /* Responsive */
        @media only screen and (max-width: 640px) {
            .email-wrapper {
                padding: 20px 10px;
            }

            .email-header {
                padding: 32px 24px;
            }

            .header-title {
                font-size: 24px;
            }

            .header-subtitle {
                font-size: 15px;
            }

            .email-body {
                padding: 32px 24px;
            }

            .proposal-card {
                padding: 24px 20px;
            }

            .proposal-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .proposal-icon {
                margin-bottom: 12px;
            }

            .price-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .price-value {
                font-size: 28px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .email-footer {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">

            <!-- Header -->
            <div class="email-header">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="header-logo">
                <?php endif; ?>
                <h1 class="header-title">Your Auction Estimates Are Ready! 📦</h1>
                <p class="header-subtitle">We've received quotes from our trusted auction partners</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p class="greeting">Hello!</p>
                <p class="intro-text">
                    Thank you for reaching out to us. We have reviewed your request and sent you shipping estimate proposals below. Please review each proposal carefully and choose the one that works best for you.
                </p>

                <h2 class="section-title">Your Proposals (<?php echo count($proposals); ?>)</h2>

                <div class="proposals-wrapper">
                    <?php foreach ($proposals as $index => $proposal): ?>
                        <div class="proposal-card">
                            <!-- Proposal Header -->
                            <div class="proposal-header">
                                <div class="proposal-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h16v11z"/>
                                    </svg>
                                </div>
                                <h3 class="proposal-title">
                                    <?php echo esc_html($proposal['title'] ?? 'Proposal #' . ($index + 1)); ?>
                                </h3>
                            </div>

                            <!-- Price -->
                            <?php if (!empty($proposal['price'])): ?>
                                <div class="price-section">
                                    <span class="price-label">Estimated Value</span>
                                    <span class="price-value">$<?php echo number_format((float)$proposal['price'], 2); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Details -->
                            <?php if (!empty($proposal['details'])): ?>
                                <div class="details-section">
                                    <?php echo wp_kses_post($proposal['details']); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <?php
                            $response_token = $proposal['response_token'] ?? '';
                            $accept_url = add_query_arg([
                                'aees_response' => 'accept',
                                'token' => $response_token
                            ], home_url('/proposal-response/'));
                            $reject_url = add_query_arg([
                                'aees_response' => 'reject',
                                'token' => $response_token
                            ], home_url('/proposal-response/'));
                            ?>
                            <div class="button-group">
                                <a href="<?php echo esc_url($accept_url); ?>" class="btn btn-accept">
                                    ✓ Accept Proposal
                                </a>
                                <a href="<?php echo esc_url($reject_url); ?>" class="btn btn-decline">
                                    ✕ Decline Proposal
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-help">
                    <h3 class="footer-help-title">Need Help?</h3>
                    <p class="footer-help-text">
                        Have questions about these proposals? Our team is here to help you make the right decision.
                    </p>
                    <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>" class="footer-contact">
                        📧 Contact Support
                    </a>
                </div>

                <div class="footer-divider"></div>

                <div class="footer-info">
                    <p class="footer-brand"><?php echo esc_html($site_name); ?></p>
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> All rights reserved.</p>
                    <div class="footer-links">
                        <a href="<?php echo esc_url($site_url); ?>" class="footer-link">Visit Website</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
