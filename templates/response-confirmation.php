<?php
/**
 * Template for Proposal Response Confirmation Page
 * Displayed after user clicks accept/reject in email
 */

if (!defined('ABSPATH')) exit;

// No header/footer - standalone page
$site_name = get_bloginfo('name');
$site_logo = get_custom_logo();
$logo_url = '';

if (!empty($site_logo)) {
    preg_match('/src="([^"]+)"/', $site_logo, $matches);
    $logo_url = $matches[1] ?? '';
}

if (empty($logo_url) && has_site_icon()) {
    $logo_url = get_site_icon_url(200);
}

$success = get_transient('aees_response_success');
$error = get_transient('aees_response_error');
$proposal_title = get_transient('aees_response_proposal_title');
$action = get_transient('aees_response_action');
$existing_status = get_transient('aees_response_status');

// Clear transients
delete_transient('aees_response_success');
delete_transient('aees_response_error');
delete_transient('aees_response_proposal_title');
delete_transient('aees_response_action');
delete_transient('aees_response_status');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?> - Proposal Response</title>
    <?php wp_head(); ?>


<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .aees-response-container {
        max-width: 650px;
        width: 100%;
        background: #FFFFFF;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .aees-response-header {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        padding: 40px 30px;
        text-align: center;
    }

    .aees-response-logo {
        max-width: 180px;
        max-height: 70px;
        margin-bottom: 20px;
        filter: brightness(0) invert(1);
    }

    .aees-response-header h1 {
        color: #FFFFFF;
        font-size: 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
        opacity: 0.95;
    }

    .aees-response-content {
        padding: 50px 40px;
    }

    .aees-response-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        animation: scaleIn 0.5s ease-out 0.3s both;
    }

    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }

    .aees-response-icon.success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #FFFFFF;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }

    .aees-response-icon.error {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: #FFFFFF;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }

    .aees-response-icon.info {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        color: #FFFFFF;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    }

    .aees-response-title {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
        text-align: center;
        letter-spacing: -0.5px;
    }

    .aees-response-message {
        font-size: 16px;
        color: #4B5563;
        line-height: 1.8;
        margin-bottom: 32px;
    }

    .aees-response-message p {
        margin-bottom: 16px;
    }

    .aees-response-message p:last-child {
        margin-bottom: 0;
    }

    .aees-response-proposal {
        background: linear-gradient(135deg, #F0F6FC 0%, #E0EFFE 100%);
        border-left: 4px solid #667EEA;
        padding: 20px 24px;
        margin: 24px 0;
        border-radius: 12px;
    }

    .aees-response-proposal strong {
        color: #667EEA;
        font-weight: 600;
    }

    .aees-info-box {
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border-left: 4px solid #3B82F6;
        padding: 24px;
        margin: 28px 0;
        border-radius: 12px;
    }

    .aees-info-box p {
        margin: 0 0 12px 0;
        color: #1E40AF;
        line-height: 1.7;
    }

    .aees-info-box p:last-child {
        margin-bottom: 0;
    }

    .aees-info-box strong {
        font-weight: 700;
        font-size: 17px;
    }

    .aees-response-button {
        display: inline-block;
        width: 100%;
        padding: 18px 40px;
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: #FFFFFF;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        margin-top: 28px;
        transition: all 0.3s ease;
        text-align: center;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .aees-response-button:hover {
        background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
        color: #FFFFFF;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    .aees-response-footer {
        background: #F9FAFB;
        padding: 30px 40px;
        text-align: center;
        border-top: 1px solid #E5E7EB;
    }

    .aees-response-footer p {
        color: #6B7280;
        font-size: 14px;
        margin: 8px 0;
    }

    .aees-response-footer strong {
        color: #1F2937;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .aees-response-container {
            border-radius: 16px;
        }

        .aees-response-header {
            padding: 30px 20px;
        }

        .aees-response-content {
            padding: 40px 24px;
        }

        .aees-response-title {
            font-size: 24px;
        }

        .aees-response-message {
            font-size: 15px;
        }

        .aees-response-footer {
            padding: 24px 20px;
        }

        .aees-response-button {
            padding: 16px 32px;
            font-size: 15px;
        }
    }
</style>
</head>
<body>

<div class="aees-response-container">
    <!-- Header -->
    <div class="aees-response-header">
        <?php if (!empty($logo_url)): ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="aees-response-logo">
        <?php endif; ?>
        <h1><?php echo esc_html($site_name); ?></h1>
    </div>

    <!-- Content -->
    <div class="aees-response-content">
        <?php if ($success): ?>
            <?php if ($action === 'rejected'): ?>
                <!-- Rejection Message -->
                <div class="aees-response-icon error">✕</div>
                <h1 class="aees-response-title">We're Sorry This Didn't Work Out</h1>

                <div class="aees-response-message">
                    <p>We understand that this proposal wasn't the right fit for you.</p>

                    <?php if ($proposal_title): ?>
                        <div class="aees-response-proposal">
                            <strong>Declined Proposal:</strong> <?php echo esc_html($proposal_title); ?>
                        </div>
                    <?php endif; ?>

                    <p>Your response has been recorded and our team has been notified.</p>

                    <div class="aees-info-box">
                        <p><strong>💡 Want to try again?</strong></p>
                        <p>You can submit a new request at any time by visiting our auction estimate page. We're here to help you find the best solution for your needs.</p>
                    </div>

                    <p>If you have any questions or would like to discuss your options, please don't hesitate to contact us.</p>
                </div>
            <?php elseif ($action === 'accepted'): ?>
                <!-- Acceptance Message -->
                <div class="aees-response-icon success">✓</div>
                <h1 class="aees-response-title">Proposal Accepted Successfully!</h1>

                <div class="aees-response-message">
                    <p>Thank you for accepting this proposal. We're excited to move forward with you!</p>

                    <?php if ($proposal_title): ?>
                        <div class="aees-response-proposal">
                            <strong>Accepted Proposal:</strong> <?php echo esc_html($proposal_title); ?>
                        </div>
                    <?php endif; ?>

                    <div class="aees-info-box">
                        <p><strong>📋 What Happens Next?</strong></p>
                        <p><strong>1.</strong> Our team has been notified of your acceptance</p>
                        <p><strong>2.</strong> An authorization request has been sent to the auction house</p>
                        <p><strong>3.</strong> Once they authorize, we'll proceed with your order</p>
                        <p style="margin-top: 16px;">We'll keep you updated every step of the way via email.</p>
                    </div>

                    <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
                </div>

            <?php elseif ($action === 'authorized'): ?>
                <!-- Authorization Complete Message -->
                <div class="aees-response-icon success">✅</div>
                <h1 class="aees-response-title">Authorization Confirmed!</h1>

                <div class="aees-response-message">
                    <p>Thank you for authorizing this order. The complete workflow has been confirmed.</p>

                    <?php if ($proposal_title): ?>
                        <div class="aees-response-proposal">
                            <strong>Authorized Proposal:</strong> <?php echo esc_html($proposal_title); ?>
                        </div>
                    <?php endif; ?>

                    <div class="aees-info-box">
                        <p><strong>✅ Authorization Complete</strong></p>
                        <p>The customer has accepted this proposal and you have now authorized it. Our administrative team has been notified and will proceed with fulfillment.</p>
                        <p style="margin-top: 16px;">You will receive further communication regarding next steps shortly.</p>
                    </div>

                    <p>Thank you for your prompt response and partnership.</p>
                </div>

            <?php else: ?>
                <!-- Generic Success Message -->
                <div class="aees-response-icon success">✓</div>
                <h1 class="aees-response-title">Thank You for Your Response</h1>

                <div class="aees-response-message">
                    <p>We have received your response and our team will be notified.</p>

                    <?php if ($proposal_title): ?>
                        <div class="aees-response-proposal">
                            <strong>Proposal:</strong> <?php echo esc_html($proposal_title); ?><br>
                            <strong>Your Action:</strong> <?php echo ucfirst(esc_html($action)); ?>
                        </div>
                    <?php endif; ?>

                    <p>If you have any questions, please don't hesitate to contact us.</p>
                </div>
            <?php endif; ?>

        <?php elseif ($error): ?>
            <!-- Error Message -->
            <div class="aees-response-icon error">✕</div>
            <h1 class="aees-response-title">Unable to Process Response</h1>

            <div class="aees-response-message">
                <p><?php echo esc_html($error); ?></p>

                <?php if ($existing_status): ?>
                    <p style="font-style: italic; color: #6B7280;">Previous response: <?php echo ucfirst(esc_html($existing_status)); ?></p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Default Message -->
            <div class="aees-response-icon info">ℹ️</div>
            <h1 class="aees-response-title">Proposal Response</h1>
            <div class="aees-response-message">
                <p>Please use the link from your email to respond to a proposal.</p>
            </div>
        <?php endif; ?>

        <?php
        // Determine button text and link based on action
        if ($success && $action === 'rejected') {
            $button_text = 'Submit New Request';
            $button_url = home_url('/auction-estimate/');
        } else {
            $button_text = 'Return to Home';
            $button_url = home_url('/');
        }
        ?>

        <a href="<?php echo esc_url($button_url); ?>" class="aees-response-button">
            <?php echo esc_html($button_text); ?>
        </a>
    </div>

    <!-- Footer -->
    <div class="aees-response-footer">
        <p><strong><?php echo esc_html($site_name); ?></strong></p>
        <p>&copy; <?php echo date('Y'); ?> All rights reserved.</p>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
