<?php

/**
 * Modern Email Template for User Proposals
 * Sent when admin creates proposals for a user's auction estimate request
 */

$site_name = get_bloginfo('name');
$site_url = home_url('/');

// Get logo URL
$logo_url = $site_url . "wp-content/uploads/2025/10/Main-Logo.png";

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
            max-width: 768px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Header Section */
        .email-header {
            background: #667EEA;
            padding: 30px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .header-logo {
            max-width: 180px;
            max-height: 80px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .header-title {
            color: #FFFFFF;
            font-size: 28px;
            font-weight: 700;
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
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }

        .intro-text {
            font-size: 16px;
            color: #6B7280;
            margin: 0 0 20px 0;
            line-height: 1.7;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 15px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #E5E7EB;
        }

        /* Proposal Cards */
        .proposals-wrapper {
            margin-bottom: 15px;
        }

        .proposal-card {
            background: #fffcf56e;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 25px;
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
            border-bottom: 1px solid #F3F4F6;
        }

        .proposal-icon {
            width: 40px;
            height: 40px;
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
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            flex: 1;
        }

        .price-section {
            background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
            border-radius: 0 0 8px 8px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-width: 0 1px 1px 1px;
            border-style: solid;
            border-color: #d4d4d4;
        }

        .price-label {
            font-size: 14px;
            font-weight: 600;
            color: #1E40AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-value {
            font-size: 24px;
            font-weight: 700;
            color: #1E3A8A;
            letter-spacing: -1px;
        }

        .details-section {
            padding: 15px 20px;
            font-size: 18px;
            margin-bottom: -25px;
            border-radius: 8px 8px 0 0;
            border-width: 1px 1px 0 1px;
            border-style: solid;
            border-color: #d4d4d4;
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
            justify-content: right;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 20px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            max-width: 200px;
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

        .decline-wrapper {
            margin-top: 20px;
            padding: 30px 20px;
            background: #ff000008;
            border: 1px solid #ff000052;
            border-radius: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        h3.decline-heading {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4em;
        }

        p.decline-details {
            font-size: 16px;
            color: grey;
            font-weight: 400;
            width: 95%;
            line-height: 1.3em;
        }    

        .btn-decline-all{
            background: #FFFFFF; 
            color: #DC2626 !important; 
            border: 2px solid #FCA5A5; 
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-decline-all:hover {
            background: #DC2626;
            border-color: #DC2626;
            color: #FFFFFF !important;
        }

        /* Footer Section */
        .email-footer {
            background: #F9FAFB;
            padding: 20px;
            border-top: 1px solid #E5E7EB;
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
                line-height: 1.3em;
                margin: 10px 0;
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
                align-items: center;
                gap: 0;
            }

            .price-value {
                font-size: 24px;
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
                <h1 class="header-title">Your Auction Estimates Are Ready!</h1>
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
                                        <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h16v11z" />
                                    </svg>
                                </div>
                                <h3 class="proposal-title">
                                    <?php echo esc_html($proposal['title'] ?? 'Proposal #' . ($index + 1)); ?>
                                </h3>
                            </div>


                            <!-- Details -->
                            <?php if (!empty($proposal['details'])): ?>
                                <div class="details-section">
                                    <?php
                                    // Data is already sanitized with wp_kses_post() when saved
                                    // Use wpautop() to convert line breaks to <p> and <br> tags
                                    echo wpautop($proposal['details']);
                                    ?>
                                </div>
                            <?php endif; ?>


                            <!-- Price -->
                            <?php if (!empty($proposal['price'])): ?>
                                <div class="price-section">
                                    <span class="price-label">Estimated Value</span>
                                    <span class="price-value">$<?php echo number_format((float)$proposal['price'], 2); ?></span>
                                </div>
                            <?php endif; ?>



                            <!-- Action Button -->
                            <?php
                            $response_token = $proposal['response_token'] ?? '';
                            $accept_url = add_query_arg([
                                'aees_response' => 'accept',
                                'token' => $response_token
                            ], home_url('/proposal-response/'));
                            ?>
                            <div class="button-group">
                                <a href="<?php echo esc_url($accept_url); ?>" class="btn btn-accept">
                                    ✓ Accept Proposal
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Single Decline Button Section -->
                <div class="decline-wrapper">
                    <h3 class="decline-heading">Not interested in any of these proposals?</h3>
                    <p class="decline-details">
                        You can decline all proposals below. This will notify our team that these estimates don't work for you.
                    </p>
                    <?php
                    // Use the first proposal's token for decline (any rejection rejects all)
                    $first_proposal_token = $proposals[0]['response_token'] ?? '';
                    $decline_all_url = add_query_arg([
                        'aees_response' => 'reject',
                        'token' => $first_proposal_token
                    ], home_url('/proposal-response/'));
                    ?>
                    <a href="<?php echo esc_url($decline_all_url); ?>" class="btn btn-decline-all">
                        ✕ Decline Proposals
                    </a>
                    <p style="color: #9CA3AF; font-size: 13px; font-style: italic;">
                        This will decline all proposals shown above
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="email-footer">

                <div class="footer-info">
                    <p class="footer-brand"><?php echo esc_html($site_name); ?></p>
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> All rights reserved.</p>
                </div>
            </div>

        </div>
    </div>
</body>

</html>