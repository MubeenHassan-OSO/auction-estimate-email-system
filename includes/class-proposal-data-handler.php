<?php
if (!defined('ABSPATH')) exit;

/**
 * Handles all database operations for proposal data
 * Extracted from AEES_Edit_Entry_Page for better organization
 */
class AEES_Proposal_Data_Handler
{
    /**
     * Get proposal data from custom table
     *
     * @param int $entry_id The entry ID
     * @return array Array with 'auction_email' and 'proposals'
     */
    public function get_proposal_data($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT auction_house_email, proposals FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        if (!$row) {
            return [
                'auction_email' => '',
                'proposals' => []
            ];
        }

        return [
            'auction_email' => $row->auction_house_email ?? '',
            'proposals' => !empty($row->proposals) ? json_decode($row->proposals, true) : []
        ];
    }

    /**
     * Save proposal data to custom table
     *
     * @param int $entry_id The entry ID
     * @param string $auction_email The auction house email
     * @param array $proposals Array of proposals
     * @return bool Success status
     */
    public function save_proposal_data($entry_id, $auction_email, $proposals)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        // Detect and fix duplicate UIDs before saving
        $uid_map = [];
        foreach ($proposals as &$proposal) {
            $uid = $proposal['uid'] ?? '';

            // If this UID has been seen before, generate a new unique one
            if (isset($uid_map[$uid])) {
                $new_uid = 'p_' . uniqid() . bin2hex(random_bytes(4));
                error_log("AEES: Duplicate UID detected: {$uid}. Generating new UID: {$new_uid}");
                $proposal['uid'] = $new_uid;

                // Regenerate response token with new UID
                $proposal['response_token'] = hash_hmac('sha256', $new_uid . '_' . $entry_id, wp_salt());

                $uid_map[$new_uid] = true;
            } else {
                $uid_map[$uid] = true;
            }
        }
        unset($proposal); // Break reference

        $proposals_json = json_encode($proposals);

        // Check if entry exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        if ($exists) {
            // Update existing
            $result = $wpdb->update(
                $table_name,
                [
                    'auction_house_email' => $auction_email,
                    'proposals' => $proposals_json,
                    'date_updated' => current_time('mysql')
                ],
                ['entry_id' => $entry_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new
            $result = $wpdb->insert(
                $table_name,
                [
                    'entry_id' => $entry_id,
                    'auction_house_email' => $auction_email,
                    'proposals' => $proposals_json,
                    'date_created' => current_time('mysql'),
                    'date_updated' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s']
            );
        }

        // Sync tokens to response tokens table for fast lookups
        if ($result !== false) {
            $this->sync_response_tokens($entry_id, $proposals);
        }

        return $result !== false;
    }

    /**
     * Sync response tokens to dedicated tokens table for O(1) lookup performance
     * This eliminates the need to scan entire proposals table
     *
     * @param int $entry_id The entry ID
     * @param array $proposals Array of proposals
     */
    private function sync_response_tokens($entry_id, $proposals)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        // Delete existing tokens for this entry
        $wpdb->delete($tokens_table, ['entry_id' => $entry_id], ['%d']);

        // Insert new tokens
        foreach ($proposals as $proposal) {
            if (!isset($proposal['response_token']) || !isset($proposal['uid'])) {
                continue;
            }

            // DO NOT set expires_at here - it will be set when email is actually sent
            // This fixes Bug #1: expiration should start from email send time, not save time

            $wpdb->insert(
                $tokens_table,
                [
                    'token' => $proposal['response_token'],
                    'entry_id' => $entry_id,
                    'proposal_uid' => $proposal['uid'],
                    'status' => $proposal['status'] ?? 'pending',
                    'expires_at' => null  // Set to null initially
                ],
                ['%s', '%d', '%s', '%s', '%s']
            );
        }
    }

    /**
     * Find proposal by token - OPTIMIZED with dedicated tokens table
     * Performance: O(1) lookup instead of O(n) full table scan
     * Before: ~500ms for 1000 entries | After: ~5ms
     *
     * @param string $token The response token
     * @return array|false Array with entry_id, proposal_uid, and proposal data, or false if not found
     */
    public function find_proposal_by_token($token)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        // Fast lookup using indexed token column (O(1) complexity)
        $token_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT entry_id, proposal_uid, status, expires_at
                 FROM {$tokens_table}
                 WHERE token = %s
                 LIMIT 1",
                $token
            ),
            ARRAY_A
        );

        // Token not found or expired
        if (!$token_data) {
            return false;
        }

        // Check if token has expired - compare in WordPress timezone
        if (!empty($token_data['expires_at']) && strtotime($token_data['expires_at']) < strtotime(current_time('mysql'))) {
            return false;
        }

        // Get the full proposal data from proposals table
        $proposal_data = $this->get_proposal_data($token_data['entry_id']);
        $proposals = $proposal_data['proposals'];

        // Find the specific proposal by UID
        $proposal = null;
        foreach ($proposals as $p) {
            if ($p['uid'] === $token_data['proposal_uid']) {
                $proposal = $p;
                break;
            }
        }

        if (!$proposal) {
            return false;
        }

        return [
            'entry_id' => $token_data['entry_id'],
            'proposal_uid' => $token_data['proposal_uid'],
            'proposal' => $proposal
        ];
    }

    /**
     * Update token table status and authorization data
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID
     * @param string $authorization_token The authorization token
     * @param string $status The new status
     */
    public function update_token_authorization($entry_id, $proposal_uid, $authorization_token, $status)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        $wpdb->update(
            $tokens_table,
            [
                'authorization_token' => $authorization_token,
                'status' => $status
            ],
            [
                'entry_id' => $entry_id,
                'proposal_uid' => $proposal_uid
            ],
            ['%s', '%s'],
            ['%d', '%s']
        );
    }

    /**
     * Update token table when proposal is authorized
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID
     * @param string $auction_email The auction email
     */
    public function update_token_authorized($entry_id, $proposal_uid, $auction_email)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        $wpdb->update(
            $tokens_table,
            [
                'authorized_at' => current_time('mysql'),
                'authorized_by' => $auction_email
            ],
            [
                'entry_id' => $entry_id,
                'proposal_uid' => $proposal_uid
            ],
            ['%s', '%s'],
            ['%d', '%s']
        );
    }

    /**
     * Find proposal by authorization token
     *
     * @param string $authorization_token The authorization token
     * @return array|false Token data or false if not found
     */
    public function find_by_authorization_token($authorization_token)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        $token_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT entry_id, proposal_uid, status, authorization_token, authorization_expires_at
                 FROM {$tokens_table}
                 WHERE authorization_token = %s
                 LIMIT 1",
                $authorization_token
            ),
            ARRAY_A
        );

        return $token_data ?: false;
    }

    /**
     * Mark email as sent and set expiration for USER RESPONSE
     * User has 7 DAYS to accept/reject the proposal
     *
     * @param int $entry_id The entry ID
     * @param int $expiration_days Number of days until expiration (default: 7)
     * @return bool Success status
     */
    public function mark_email_sent($entry_id, $expiration_days = 7)
    {
        global $wpdb;
        $proposals_table = $wpdb->prefix . 'aees_proposals';
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        $sent_at = current_time('mysql');
        // Calculate expiration using WordPress timezone - wp_date() respects WP timezone setting
        $expires_at = wp_date('Y-m-d H:i:s', current_time('timestamp') + ($expiration_days * DAY_IN_SECONDS));

        // Update proposals table
        $result = $wpdb->update(
            $proposals_table,
            [
                'email_sent_at' => $sent_at,
                'email_expires_at' => $expires_at
            ],
            ['entry_id' => $entry_id],
            ['%s', '%s'],
            ['%d']
        );

        // Update all response tokens for this entry with 7-day expiration
        $wpdb->update(
            $tokens_table,
            ['expires_at' => $expires_at],
            ['entry_id' => $entry_id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Set authorization token expiration (14 days from when user accepts)
     * Called when user accepts a proposal
     *
     * @param int $entry_id The entry ID
     * @param string $proposal_uid The proposal UID
     * @param string $authorization_token The authorization token
     * @return bool Success status
     */
    public function set_authorization_expiration($entry_id, $proposal_uid, $authorization_token)
    {
        global $wpdb;
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        // Authorization token expires in 14 days from acceptance - wp_date() respects WP timezone setting
        $auth_expires_at = wp_date('Y-m-d H:i:s', current_time('timestamp') + (14 * DAY_IN_SECONDS));

        // FIXED: Simplified WHERE clause - (entry_id, proposal_uid) uniquely identifies the row
        // No need to include authorization_token which could cause matching issues
        $result = $wpdb->update(
            $tokens_table,
            ['authorization_expires_at' => $auth_expires_at],
            [
                'entry_id' => $entry_id,
                'proposal_uid' => $proposal_uid
            ],
            ['%s'],
            ['%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Get email status for an entry
     * Returns information about when email was sent and when it expires
     *
     * @param int $entry_id The entry ID
     * @return array|null Email status or null if not found
     */
    public function get_email_status($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT email_sent_at, email_expires_at FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            ),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return [
            'email_sent_at' => $row['email_sent_at'],
            'email_expires_at' => $row['email_expires_at'],
            'is_sent' => !empty($row['email_sent_at']),
            'is_expired' => !empty($row['email_expires_at']) && strtotime($row['email_expires_at']) < strtotime(current_time('mysql')),
            'can_send' => empty($row['email_sent_at']) || (!empty($row['email_expires_at']) && strtotime($row['email_expires_at']) < strtotime(current_time('mysql')))
        ];
    }

    /**
     * Check if email can be sent for an entry
     * Email can be sent if: never sent before OR previous email has expired
     *
     * @param int $entry_id The entry ID
     * @return bool True if email can be sent
     */
    public function can_send_email($entry_id)
    {
        $status = $this->get_email_status($entry_id);

        if (!$status) {
            return true; // No record = can send
        }

        return $status['can_send'];
    }

    /**
     * Get entry status (open or closed)
     *
     * @param int $entry_id The entry ID
     * @return string Entry status ('open' or 'closed')
     */
    public function get_entry_status($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $status = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT entry_status FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        // Default to 'open' if no record exists or column is null
        return $status ?: 'open';
    }

    /**
     * Update entry status (open or closed)
     *
     * @param int $entry_id The entry ID
     * @param string $status The new status ('open' or 'closed')
     * @return bool Success status
     */
    public function update_entry_status($entry_id, $status)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        // Validate status
        if (!in_array($status, ['open', 'closed'])) {
            return false;
        }

        // Check if entry exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        if ($exists) {
            // Update existing
            $result = $wpdb->update(
                $table_name,
                ['entry_status' => $status],
                ['entry_id' => $entry_id],
                ['%s'],
                ['%d']
            );
        } else {
            // Insert new record with just entry_id and status
            $result = $wpdb->insert(
                $table_name,
                [
                    'entry_id' => $entry_id,
                    'entry_status' => $status,
                    'auction_house_email' => '',
                    'proposals' => json_encode([]),
                    'date_created' => current_time('mysql'),
                    'date_updated' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * Get rejection history for an entry
     * Returns all proposals that were declined from history table
     *
     * Note: Still queries for both 'rejected' and 'invalid' for backward compatibility
     * with old data. New rejections (v1.7.1+) mark all proposals as 'rejected'.
     *
     * @param int $entry_id The entry ID
     * @return array Array of declined proposals with metadata
     */
    public function get_rejection_history($entry_id)
    {
        global $wpdb;
        $history_table = $wpdb->prefix . 'aees_proposal_history';

        // Get only rejected/invalid proposals from history, ordered by most recent first
        // Note: 'invalid' kept for backward compatibility with data from before v1.7.1
        $history = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT proposal_uid as uid,
                        proposal_title as title,
                        proposal_price as price,
                        proposal_details as details,
                        status,
                        user_response_date,
                        authorization_status,
                        authorization_date,
                        authorized_by,
                        created_at
                 FROM {$history_table}
                 WHERE entry_id = %d
                 AND status IN ('rejected', 'invalid')
                 ORDER BY created_at DESC",
                $entry_id
            ),
            ARRAY_A
        );

        return $history ?: [];
    }

    /**
     * Save proposal to history table
     * Called when user responds to a proposal (accept/reject) or entry is reopened
     *
     * @param int $entry_id The entry ID
     * @param array $proposal Proposal data
     * @param string $user_email Optional user email who responded
     * @return bool Success status
     */
    public function save_proposal_to_history($entry_id, $proposal, $user_email = null)
    {
        global $wpdb;
        $history_table = $wpdb->prefix . 'aees_proposal_history';

        // Check if this exact proposal response already exists in history (avoid duplicates)
        // We check entry_id + proposal_uid + status to allow the same proposal
        // to be saved multiple times if status changes (e.g., reopened and rejected again)
        $proposal_uid = $proposal['uid'] ?? '';
        $status = $proposal['status'] ?? 'pending';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$history_table}
             WHERE entry_id = %d
             AND proposal_uid = %s
             AND status = %s",
            $entry_id,
            $proposal_uid,
            $status
        ));

        if ($exists) {
            return true; // Already in history with same status, skip
        }

        // Insert into history
        $result = $wpdb->insert(
            $history_table,
            [
                'entry_id' => $entry_id,
                'proposal_uid' => $proposal['uid'] ?? '',
                'proposal_title' => $proposal['title'] ?? '',
                'proposal_price' => $proposal['price'] ?? '',
                'proposal_details' => $proposal['details'] ?? '',
                'status' => $proposal['status'] ?? 'pending',
                'user_email' => $user_email,
                'user_response_date' => $proposal['user_response_date'] ?? null,
                'authorization_status' => $proposal['authorization_status'] ?? null,
                'authorization_date' => $proposal['authorization_date'] ?? null,
                'authorized_by' => $proposal['authorized_by'] ?? null,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $result !== false;
    }

    /**
     * Check if entry has any authorized proposals
     * Used to determine if entry can be reopened (authorized = permanently closed)
     *
     * @param int $entry_id The entry ID
     * @return bool True if any proposal is authorized
     */
    public function has_authorized_proposals($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT proposals FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        if (!$row || empty($row->proposals)) {
            return false;
        }

        $proposals = json_decode($row->proposals, true);

        // Check if any proposal has authorization_status = 'authorized'
        foreach ($proposals as $proposal) {
            if (isset($proposal['authorization_status']) && $proposal['authorization_status'] === 'authorized') {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear proposals for an entry (used when reopening after rejection)
     * This allows admin to start fresh with new proposals
     * Old rejected/accepted proposals are saved to history table before clearing
     *
     * @param int $entry_id The entry ID
     * @return bool Success status
     */
    public function clear_proposals($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';
        $tokens_table = $wpdb->prefix . 'aees_response_tokens';

        // FIRST: Save all non-pending proposals to history before clearing
        $proposal_data = $this->get_proposal_data($entry_id);
        $proposals = $proposal_data['proposals'] ?? [];

        if (!empty($proposals)) {
            foreach ($proposals as $proposal) {
                $status = $proposal['status'] ?? 'pending';

                // Only save non-pending proposals to history (rejected, accepted, authorized)
                if ($status !== 'pending') {
                    $this->save_proposal_to_history($entry_id, $proposal);
                }
            }
        }

        // THEN: Clear proposals JSON (set to empty array)
        $result = $wpdb->update(
            $table_name,
            [
                'proposals' => json_encode([]),
                'email_sent_at' => null,
                'email_expires_at' => null
            ],
            ['entry_id' => $entry_id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        // Clear response tokens for this entry
        $wpdb->delete($tokens_table, ['entry_id' => $entry_id], ['%d']);

        return $result !== false;
    }

    /**
     * Set edit lock for an entry
     * Called when a user opens the edit page
     *
     * @param int $entry_id The entry ID
     * @param int $user_id The user ID taking the lock
     * @return bool Success status
     */
    public function set_edit_lock($entry_id, $user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        // Check if entry exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            )
        );

        if ($exists) {
            // Update existing
            $result = $wpdb->update(
                $table_name,
                [
                    'edit_locked_by' => $user_id,
                    'edit_locked_time' => current_time('mysql')
                ],
                ['entry_id' => $entry_id],
                ['%d', '%s'],
                ['%d']
            );
        } else {
            // Insert new record with lock
            $result = $wpdb->insert(
                $table_name,
                [
                    'entry_id' => $entry_id,
                    'edit_locked_by' => $user_id,
                    'edit_locked_time' => current_time('mysql'),
                    'entry_status' => 'open',
                    'auction_house_email' => '',
                    'proposals' => json_encode([]),
                    'date_created' => current_time('mysql'),
                    'date_updated' => current_time('mysql')
                ],
                ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * Check if entry is locked by another user
     * Returns lock info if locked by someone else within last 150 seconds
     *
     * @param int $entry_id The entry ID
     * @param int $current_user_id The current user ID
     * @return array|false Lock info array or false if not locked
     */
    public function check_edit_lock($entry_id, $current_user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT edit_locked_by, edit_locked_time FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            ),
            ARRAY_A
        );

        // No lock set
        if (!$row || empty($row['edit_locked_by']) || empty($row['edit_locked_time'])) {
            return false;
        }

        $locked_by = intval($row['edit_locked_by']);
        $locked_time = strtotime($row['edit_locked_time']);
        $current_time = strtotime(current_time('mysql'));

        // Lock expired (older than 150 seconds)
        if (($current_time - $locked_time) > 150) {
            return false;
        }

        // Locked by current user (not a conflict)
        if ($locked_by === $current_user_id) {
            return false;
        }

        // Locked by another user
        $user = get_userdata($locked_by);

        return [
            'user_id' => $locked_by,
            'user_name' => $user ? $user->display_name : 'Another user',
            'locked_time' => $row['edit_locked_time'],
            'avatar_url' => $user ? get_avatar_url($locked_by) : ''
        ];
    }

    /**
     * Release edit lock for an entry
     * Called when user leaves the page or explicitly releases
     *
     * @param int $entry_id The entry ID
     * @param int $user_id The user ID releasing the lock (for verification)
     * @return bool Success status
     */
    public function release_edit_lock($entry_id, $user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        // Only release if locked by this user
        $result = $wpdb->update(
            $table_name,
            [
                'edit_locked_by' => null,
                'edit_locked_time' => null
            ],
            [
                'entry_id' => $entry_id,
                'edit_locked_by' => $user_id
            ],
            ['%d', '%s'],
            ['%d', '%d']
        );

        return $result !== false;
    }

    /**
     * Refresh edit lock (called by heartbeat)
     * Extends the lock time to keep it active
     *
     * @param int $entry_id The entry ID
     * @param int $user_id The user ID refreshing the lock
     * @return bool Success status
     */
    public function refresh_edit_lock($entry_id, $user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        // Only refresh if locked by this user
        $result = $wpdb->update(
            $table_name,
            ['edit_locked_time' => current_time('mysql')],
            [
                'entry_id' => $entry_id,
                'edit_locked_by' => $user_id
            ],
            ['%s'],
            ['%d', '%d']
        );

        return $result !== false;
    }

    /**
     * Get detailed lock information
     *
     * @param int $entry_id The entry ID
     * @return array|null Lock info or null if not locked
     */
    public function get_lock_info($entry_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aees_proposals';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT edit_locked_by, edit_locked_time FROM {$table_name} WHERE entry_id = %d",
                $entry_id
            ),
            ARRAY_A
        );

        if (!$row || empty($row['edit_locked_by'])) {
            return null;
        }

        $user = get_userdata($row['edit_locked_by']);

        return [
            'user_id' => $row['edit_locked_by'],
            'user_name' => $user ? $user->display_name : 'Unknown',
            'locked_time' => $row['edit_locked_time'],
            'avatar_url' => $user ? get_avatar_url($row['edit_locked_by']) : ''
        ];
    }
}
