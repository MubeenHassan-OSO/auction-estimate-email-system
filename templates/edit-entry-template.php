<?php
if (!defined('ABSPATH')) exit;

// Check if user has responded and find the responded proposal
$user_has_responded = false;
$responded_proposal = null;
if (!empty($proposals)) {
    foreach ($proposals as $prop) {
        if (isset($prop['status']) && $prop['status'] !== 'pending') {
            $user_has_responded = true;
            $responded_proposal = $prop; // Store the responded proposal
            break;
        }
    }
}

$readonly_class = $user_has_responded ? 'aees-entry-readonly' : '';
?>

<div class="aees-edit-entry-container <?php echo $readonly_class; ?>">

    <!-- Header -->
    <div class="aees-header">
        <div class="aees-header-content">
            <div class="aees-header-left">
                <h1 class="aees-title">
                    Entry #<?php echo esc_html($entry_id); ?><?php if (!empty($form_data['user_email'])): ?> - <?php echo esc_html($form_data['user_email']); ?><?php endif; ?>
                </h1>
                <div class="aees-meta">
                    <?php if (!empty($form_data['date_created'])): ?>
                        Submitted: <?php echo wp_date(get_option('date_format'), mysql2date('U', $form_data['date_created'], true)); ?>
                    <?php endif; ?>
                </div>
                <div class="aees-status-badges">
                    <!-- Entry Status Badge -->
                    <?php if (isset($entry_status)): ?>
                        <span class="aees-badge aees-entry-status-badge <?php echo $entry_status === 'closed' ? 'aees-badge-danger' : 'aees-badge-info'; ?>" id="aees-entry-status-badge">
                            <?php echo $entry_status === 'closed' ? '🔒 Entry Closed' : '🔓 Entry Open'; ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($email_status) && $email_status['is_sent'] && $entry_status === 'open'): ?>
                        <span class="aees-badge aees-badge-success">✓ Email Sent</span>
                    <?php endif; ?>
                    <?php
                    // Check if user has responded
                    $has_user_response = false;
                    if (!empty($proposals)) {
                        foreach ($proposals as $prop) {
                            if (isset($prop['status']) && $prop['status'] !== 'pending') {
                                $has_user_response = true;
                                break;
                            }
                        }
                    }
                    if (!$has_user_response && !empty($email_status) && $email_status['is_sent'] && $entry_status === 'open'): ?>
                        <span class="aees-badge aees-badge-warning">⏳ Awaiting Response</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aees-header-buttons">
                <!-- Toggle Entry Status Button (only show if not authorized) -->
                <?php if (isset($entry_status) && !$has_authorized_proposals): ?>
                    <button id="aees-toggle-entry-status" class="button <?php echo $entry_status === 'closed' ? 'button-primary' : 'button-secondary'; ?>"
                            data-current-status="<?php echo esc_attr($entry_status); ?>"
                            title="<?php echo $entry_status === 'closed' ? 'Click to reopen this entry' : 'Click to close this entry'; ?>">
                        <span class="dashicons dashicons-<?php echo $entry_status === 'closed' ? 'unlock' : 'lock'; ?>" style="margin-top: 3px;"></span>
                        <?php echo $entry_status === 'closed' ? 'Reopen Entry' : 'Close Entry'; ?>
                    </button>
                <?php endif; ?>

                <button id="aees-refresh-cache" class="button button-primary" title="Refresh page data from database">
                    <span class="dashicons dashicons-update" style="margin-top: 3px;"></span> Refresh
                </button>
                <a href="<?php echo admin_url('admin.php?page=aees'); ?>" class="button button-primary">← Back</a>
            </div>
        </div>
    </div>

    <!-- Email Sent Notification -->
    <!-- Only show if entry is OPEN (don't show if manually closed or user responded) -->
    <?php if (!empty($email_status) && $email_status['is_sent'] && !$email_status['is_expired'] && !$user_has_responded && $entry_status === 'open'): ?>
        <div class="aees-email-sent-notice">
            <div class="aees-email-sent-icon">✉️</div>
            <div class="aees-email-sent-content">
                <h3>Email Sent - Awaiting User Response</h3>
                <p>Proposals have been sent to the customer on <strong><?php echo wp_date(get_option('date_format') . ' at ' . get_option('time_format'), strtotime($email_status['email_sent_at'])); ?></strong></p>
                <p style="margin: 8px 0 0 0; font-size: 13px; opacity: 0.9;">
                    📅 Expires: <?php echo wp_date(get_option('date_format') . ' at ' . get_option('time_format'), strtotime($email_status['email_expires_at'])); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Manually Closed Notice (show when entry is closed manually without user response) -->
    <?php if (!$user_has_responded && $entry_status === 'closed'): ?>
        <div class="aees-response-notice" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-left: 4px solid #F59E0B;">
            <div class="aees-response-notice-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                🔒
            </div>
            <div class="aees-response-notice-content">
                <h3 style="color: #92400E;">Entry Manually Closed</h3>
                <p style="color: #78350F;">
                    This entry has been manually closed by an administrator.
                    <?php if (!empty($email_status) && $email_status['is_sent']): ?>
                        <?php if (!$email_status['is_expired']): ?>
                            Although an email was sent to the customer, the response links are now <strong>invalid</strong> and will not work.
                        <?php else: ?>
                            The email that was sent to the customer has expired.
                        <?php endif; ?>
                        <br>
                        <strong>💡 To allow responses:</strong> Click the <strong>"Reopen Entry"</strong> button above to reactivate this entry.
                    <?php else: ?>
                        You cannot create new proposals or send emails while the entry is closed.
                        <br>
                        <strong>💡 To continue:</strong> Click the <strong>"Reopen Entry"</strong> button above to reactivate this entry.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Response Notice (only show if entry is closed - hide when reopened) -->
    <?php if ($user_has_responded && $entry_status === 'closed' && $responded_proposal): ?>
        <?php
            $is_rejected = $responded_proposal['status'] === 'rejected';
            $is_accepted = $responded_proposal['status'] === 'accepted';
            $is_fully_authorized = $is_accepted && isset($responded_proposal['authorization_status']) && $responded_proposal['authorization_status'] === 'authorized';
        ?>
            <div class="aees-response-notice <?php echo $is_rejected ? 'rejected' : ($is_fully_authorized ? 'completed' : 'accepted'); ?>">
                <div class="aees-response-notice-icon">
                    <?php
                    if ($is_rejected) {
                        echo '📋';
                    } elseif ($is_fully_authorized) {
                        echo '✅';
                    } else {
                        echo '✓';
                    }
                    ?>
                </div>
                <div class="aees-response-notice-content">
                    <h3>
                        <?php
                        if ($is_rejected) {
                            echo 'Proposal Declined - Entry Closed';
                        } elseif ($is_fully_authorized) {
                            echo 'Order Completed & Authorized - Entry Permanently Closed';
                        } else {
                            echo 'Proposal Accepted - Entry Closed';
                        }
                        ?>
                    </h3>
                    <p>
                        <?php if ($is_rejected): ?>
                            The customer declined this proposal on <strong><?php echo wp_date('F j, Y', strtotime($responded_proposal['user_response_date'])); ?></strong>.
                            This entry has been closed and cannot accept new proposals or send emails.
                            <br>
                            <strong>💡 To try again:</strong> Click the <strong>"Reopen Entry"</strong> button above to create new proposals and resend to the customer.
                        <?php else: ?>
                            <?php if ($is_fully_authorized): ?>
                                The customer accepted this proposal on <strong><?php echo wp_date('F j, Y', strtotime($responded_proposal['user_response_date'])); ?></strong> and the auction house authorized it on <strong><?php echo wp_date('F j, Y', strtotime($responded_proposal['authorization_date'])); ?></strong>.
                                <br>
                                This order is complete and the entry is <strong>permanently closed</strong>. Authorized entries cannot be reopened.
                            <?php else: ?>
                                The customer accepted this proposal on <strong><?php echo wp_date('F j, Y', strtotime($responded_proposal['user_response_date'])); ?></strong>.
                                The entry is temporarily closed while awaiting auction house authorization.
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
    <?php endif; ?>

    <!-- Response Status Boxes - Only show when email has been sent OR user has responded -->
    <?php
    // Only show status boxes when there's an active email or user has responded
    $show_status_boxes = (!empty($email_status) && $email_status['is_sent']) || $user_has_responded;
    ?>
    <?php if ($show_status_boxes): ?>
        <div class="aees-response-status-boxes">
            <!-- User Response Box -->
            <div class="aees-response-box <?php echo $user_has_responded ? 'has-response' : 'pending'; ?>">
                <div class="aees-response-box-title">Customer Response</div>
                <div class="aees-response-box-content">
                    <?php
                    if ($user_has_responded && isset($responded_proposal)) {
                        $status_text = $responded_proposal['status'] === 'rejected' ? '✕ Declined' : '✓ Accepted';
                        $status_class = $responded_proposal['status'] === 'rejected' ? 'color: #EF4444;' : 'color: #10B981;';
                        echo '<strong style="' . $status_class . '">' . esc_html($status_text) . '</strong>';
                        if (!empty($responded_proposal['user_response_date'])) {
                            echo '<br><small style="color: #6B7280;">' . wp_date('M j, Y @ g:i A', strtotime($responded_proposal['user_response_date'])) . '</small>';
                        }
                    } else {
                        echo '<em style="color: #9CA3AF;">Awaiting customer response</em>';
                    }
                    ?>
                </div>
            </div>

            <!-- Auction House Authorization Box -->
            <div class="aees-response-box <?php
                // Check if authorized
                $is_authorized = false;
                $authorization_date = null;
                if (!empty($proposals)) {
                    foreach ($proposals as $prop) {
                        if (isset($prop['authorization_status']) && $prop['authorization_status'] === 'authorized') {
                            $is_authorized = true;
                            $authorization_date = $prop['authorization_date'] ?? null;
                            break;
                        }
                    }
                }
                echo $is_authorized ? 'has-response' : 'pending';
            ?>">
                <div class="aees-response-box-title">Auction House Authorization</div>
                <div class="aees-response-box-content">
                    <?php
                    if ($is_authorized) {
                        echo '<strong style="color: #10B981;">✓ Authorized</strong>';
                        if (!empty($authorization_date)) {
                            echo '<br><small style="color: #6B7280;">' . wp_date('M j, Y @ g:i A', strtotime($authorization_date)) . '</small>';
                        }
                    } else if ($user_has_responded && isset($responded_proposal)) {
                        if ($responded_proposal['status'] === 'rejected') {
                            echo '<em style="color: #6B7280;">N/A - Proposal declined</em>';
                        } else if ($responded_proposal['status'] === 'accepted') {
                            echo '<em style="color: #F59E0B;">⏳ Awaiting authorization</em>';
                        }
                    } else {
                        echo '<em style="color: #9CA3AF;">Awaiting customer acceptance</em>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rejection History Section (Collapsible) -->
    <?php if (!empty($rejection_history)): ?>
        <div class="aees-collapsible aees-rejection-history-collapsible" id="aees-rejection-history-collapsible">
            <div class="aees-collapsible-header">
                <div>
                    <div class="aees-collapsible-title">📋 Rejection History</div>
                    <div class="aees-collapsible-subtitle">Previous proposals that were declined by the customer (<?php echo count($rejection_history); ?> item<?php echo count($rejection_history) > 1 ? 's' : ''; ?>)</div>
                </div>
                <div class="aees-collapsible-icon">+</div>
            </div>
            <div class="aees-collapsible-content">
                <div class="aees-rejection-history-items">
                    <?php foreach ($rejection_history as $index => $rejected): ?>
                        <div class="aees-rejection-history-item">
                            <div class="aees-rejection-history-item-header">
                                <span class="aees-rejection-number">#<?php echo $index + 1; ?></span>
                                <span class="aees-rejection-status <?php echo $rejected['status'] === 'rejected' ? 'status-rejected' : 'status-invalid'; ?>">
                                    <?php echo $rejected['status'] === 'rejected' ? '✕ Rejected' : '⊘ Invalidated'; ?>
                                </span>
                                <?php if (!empty($rejected['user_response_date'])): ?>
                                    <span class="aees-rejection-date">
                                        <?php echo wp_date('M j, Y @ g:i A', strtotime($rejected['user_response_date'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="aees-rejection-history-item-body">
                                <div class="aees-rejection-field">
                                    <strong>Title:</strong> <?php echo esc_html($rejected['title']); ?>
                                </div>
                                <div class="aees-rejection-field">
                                    <strong>Price:</strong> $<?php echo esc_html($rejected['price']); ?>
                                </div>
                                <?php if (!empty($rejected['details'])): ?>
                                    <div class="aees-rejection-field aees-rejection-details">
                                        <strong>Details:</strong>
                                        <div class="aees-rejection-details-content"><?php echo wp_kses_post($rejected['details']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Collapsible Form Data -->
    <div class="aees-collapsible" id="aees-form-data-collapsible">
        <div class="aees-collapsible-header">
            <div>
                <div class="aees-collapsible-title">Form Submission Data</div>
                <div class="aees-collapsible-subtitle">Click to view customer details</div>
            </div>
            <div class="aees-collapsible-icon">+</div>
        </div>
        <div class="aees-collapsible-content">

        <!-- DEBUG SECTION -->
        <?php if (isset($form_data['debug_fields']) && !empty($form_data['debug_fields'])): ?>
            <details style="margin-bottom: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc;">
                <summary style="cursor: pointer; font-weight: bold; color: #d00;">DEBUG: Raw Field Slugs (Click to expand)</summary>
                <pre style="max-height: 400px; overflow-y: auto; font-size: 11px; margin-top: 10px;"><?php
                                                                                                        foreach ($form_data['debug_fields'] as $slug => $val) {
                                                                                                            echo esc_html($slug) . " => " . esc_html(substr($val, 0, 100)) . "\n";
                                                                                                        }
                                                                                                        ?></pre>
            </details>
        <?php endif; ?>

        <?php if ($form_data && !empty($form_data['grouped_data'])): ?>

            <?php foreach ($form_data['grouped_data'] as $group_id => $group): ?>
                <div class="aees-field-group-section">

                    <?php if ($group['is_repeater'] && !empty($group['rows'])): ?>
                        <!-- Repeater Section with Card -->
                        <div class="aees-form-section">
                            <div class="aees-section-header-icon">
                                <div class="aees-section-icon">📋</div>
                                <h3 class="aees-group-title"><?php echo esc_html($group['title']); ?></h3>
                            </div>
                        <?php
                        // Sort rows by index to ensure correct order
                        ksort($group['rows']);

                        // Filter out empty rows and collect data
                        $valid_rows = [];
                        $column_headers = [];

                        foreach ($group['rows'] as $row_index => $row_fields) {
                            // Skip completely empty rows
                            $has_content = false;
                            foreach ($row_fields as $field) {
                                if (!empty($field['raw_value'])) {
                                    $has_content = true;
                                }
                                // Collect unique column headers
                                if (!in_array($field['label'], $column_headers)) {
                                    $column_headers[] = $field['label'];
                                }
                            }
                            if ($has_content) {
                                $valid_rows[$row_index] = $row_fields;
                            }
                        }

                        // Determine if this should be shown as a table (Seller Details) or rows (Ship To, etc.)
                        // You can customize this condition based on group title or ID
                        $show_as_table = (stripos($group['title'], 'Seller') !== false || stripos($group['title'], 'Auction') !== false);
                        ?>

                        <?php if (!empty($valid_rows)): ?>
                            <?php if ($show_as_table): ?>
                                <!-- Table Format for Seller Details -->
                                <div class="aees-repeater-table-wrapper">
                                    <table class="aees-repeater-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <?php foreach ($column_headers as $header): ?>
                                                    <th><?php echo esc_html($header); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $row_counter = 1;
                                            foreach ($valid_rows as $row_index => $row_fields):
                                            ?>
                                                <tr>
                                                    <td class="aees-row-number"><?php echo $row_counter; ?></td>
                                                    <?php
                                                    // Create associative array for easy lookup
                                                    $field_values = [];
                                                    foreach ($row_fields as $field) {
                                                        $field_values[$field['label']] = $field;
                                                    }

                                                    // Output values in header order
                                                    foreach ($column_headers as $header):
                                                        $field = $field_values[$header] ?? null;
                                                    ?>
                                                        <td class="aees-field-value">
                                                            <?php
                                                            if ($field && !empty($field['value'])) {
                                                                echo $field['value'];
                                                            } else {
                                                                echo '<em style="color: #999;">N/A</em>';
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php
                                                $row_counter++;
                                            endforeach;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <!-- Original Row Format for Ship To and other repeaters -->
                                <?php
                                $row_counter = 1;
                                foreach ($valid_rows as $row_index => $row_fields):
                                ?>
                                    <div class="aees-repeater-row">
                                        <div class="aees-repeater-row-number">Row #<?php echo $row_counter; ?></div>
                                        <table class="aees-data-table">
                                            <tbody>
                                                <?php foreach ($row_fields as $field): ?>
                                                    <tr>
                                                        <td class="aees-field-label"><strong><?php echo esc_html($field['label']); ?></strong></td>
                                                        <td class="aees-field-value"><?php echo !empty($field['value']) ? $field['value'] : '<em>N/A</em>'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                    $row_counter++;
                                endforeach;
                                ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div><!-- .aees-form-section -->
                    <?php else: ?>
                        <!-- Regular Group - Card with Grid Layout -->
                        <div class="aees-form-section">
                            <?php
                            // Determine icon based on group title
                            $icon = '📄'; // default
                            $title_lower = strtolower($group['title']);
                            if (strpos($title_lower, 'contact') !== false || strpos($title_lower, 'seller') !== false) {
                                $icon = '👤';
                            } elseif (strpos($title_lower, 'ship') !== false || strpos($title_lower, 'address') !== false) {
                                $icon = '📍';
                            } elseif (strpos($title_lower, 'item') !== false || strpos($title_lower, 'product') !== false || strpos($title_lower, 'auction') !== false) {
                                $icon = '📦';
                            } elseif (strpos($title_lower, 'payment') !== false || strpos($title_lower, 'billing') !== false) {
                                $icon = '💳';
                            }
                            ?>
                            <div class="aees-section-header-icon">
                                <div class="aees-section-icon"><?php echo $icon; ?></div>
                                <h3 class="aees-group-title"><?php echo esc_html($group['title']); ?></h3>
                            </div>

                            <?php if (!empty($group['fields'])): ?>
                                <?php
                                // Separate short and long fields
                                $short_fields = [];
                                $long_fields = [];
                                // Helper function to safely convert any value to string
                                $flatten_value = function($val) use (&$flatten_value) {
                                    if (is_array($val)) {
                                        $flattened = array_map($flatten_value, $val);
                                        return implode(', ', array_filter($flattened));
                                    } elseif (is_object($val)) {
                                        return '';
                                    } elseif (is_bool($val)) {
                                        return $val ? 'Yes' : 'No';
                                    } elseif (is_null($val)) {
                                        return '';
                                    } else {
                                        return (string)$val;
                                    }
                                };

                                foreach ($group['fields'] as $field) {
                                    // Use raw_value if available, otherwise use value
                                    $raw_value = isset($field['raw_value']) ? $field['raw_value'] : (isset($field['value']) ? strip_tags($field['value']) : '');

                                    // Convert to string safely (handles any nesting level)
                                    $raw_value = $flatten_value($raw_value);

                                    $value_length = strlen($raw_value);
                                    if ($value_length > 100) {
                                        $long_fields[] = $field;
                                    } else {
                                        $short_fields[] = $field;
                                    }
                                }
                                ?>

                                <?php if (!empty($short_fields)): ?>
                                    <div class="aees-field-grid">
                                        <?php foreach ($short_fields as $field): ?>
                                            <div class="aees-field-item">
                                                <div class="aees-field-item-label"><?php echo esc_html($field['label'] ?? 'N/A'); ?></div>
                                                <div class="aees-field-item-value"><?php echo isset($field['value']) ? $field['value'] : '<em>N/A</em>'; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($long_fields)): ?>
                                    <div style="margin-top: 12px;">
                                        <?php foreach ($long_fields as $field): ?>
                                            <div class="aees-field-item aees-field-item-full">
                                                <div class="aees-field-item-label"><?php echo esc_html($field['label'] ?? 'N/A'); ?></div>
                                                <div class="aees-field-item-value"><?php echo isset($field['value']) ? $field['value'] : '<em>N/A</em>'; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div><!-- .aees-form-section -->
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p>No form data available for this entry.</p>
        <?php endif; ?>

        </div><!-- .aees-collapsible-content -->
    </div><!-- .aees-collapsible -->

    <!-- Proposals Section -->
    <div class="aees-section aees-proposals">
        <div class="aees-section-header">
            <h2>Proposals</h2>
            <div>
                <button type="button" id="aees-add-proposal" class="button button-primary">+ Add Proposal</button>
            </div>
        </div>

        <!-- Auction email (editable) -->
        <div class="aees-field-group" style="margin-bottom:12px;">
            <label>Auction House Email <span style="color: #dc3545; font-weight: 700;">*</span></label>
            <input type="email" id="aees-auction-email" value="<?php echo esc_attr($auction_email); ?>" placeholder="auction@house.com" required />
        </div>

        <div id="aees-proposals-wrap">
            <?php if (!empty($proposals)) : ?>
                <?php foreach ($proposals as $i => $p) : ?>
                    <div class="aees-proposal-card locked" data-locked="true" data-saved="true" data-uid="<?php echo esc_attr($p['uid']); ?>">
                        <div class="aees-proposal-header">
                            <h3>Proposal #<?php echo $i + 1; ?></h3>
                            <div class="aees-proposal-actions">
                                <button type="button" class="button aees-edit-proposal">✏️ Edit</button>
                                <button type="button" class="button button-link-delete aees-remove-proposal">🗑️ Delete</button>
                            </div>
                        </div>

                        <div class="aees-proposal-body">
                            <div class="aees-field-group">
                                <label>Proposal Title</label>
                                <input type="text" name="proposals[<?php echo $i; ?>][title]" value="<?php echo esc_attr($p['title']); ?>" readonly placeholder="e.g., Standard Shipping & Appraisal" maxlength="200" />
                            </div>

                            <div class="aees-field-group">
                                <label>Price</label>
                                <div class="aees-price-input-wrapper">
                                    <span class="aees-price-prefix">$</span>
                                    <input type="text" name="proposals[<?php echo $i; ?>][price]" class="aees-price-input" value="<?php echo esc_attr($p['price']); ?>" readonly placeholder="0.00" pattern="[0-9]*\.?[0-9]*" />
                                </div>
                            </div>

                            <div class="aees-field-group full">
                                <label>Proposal Details</label>
                                <textarea name="proposals[<?php echo $i; ?>][details]" rows="3" readonly><?php echo esc_textarea($p['details']); ?></textarea>
                            </div>

                            <input type="hidden" name="proposals[<?php echo $i; ?>][uid]" value="<?php echo esc_attr($p['uid']); ?>" />
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Email Status Inline -->
        <?php if (!empty($email_status) && $email_status['is_sent']): ?>
            <div class="aees-email-status-inline">
                ✉ Email sent <?php echo wp_date(get_option('date_format') . ' at ' . get_option('time_format'), strtotime($email_status['email_sent_at'])); ?>
                •
                <?php echo $email_status['is_expired'] ? 'Expired' : 'Expires'; ?> <?php echo wp_date(get_option('date_format'), strtotime($email_status['email_expires_at'])); ?>
            </div>
        <?php endif; ?>

        <!-- Footer buttons (Save via AJAX) -->
        <div class="aees-footer" style="margin-top:18px;">
            <button type="button" id="aees-save-entry" class="button button-primary aees-save-btn" disabled>💾 Update Entry</button>
            <button type="button" id="aees-send-email" class="button" disabled>✉️ Send Email</button>
        </div>
    </div>

</div><!-- .aees-edit-entry-container -->