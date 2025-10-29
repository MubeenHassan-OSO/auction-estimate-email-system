<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AEES Settings Page
 * Manages plugin configuration including form ID and email expiration settings
 */
class AEES_Settings_Page
{
    /**
     * Option name for storing settings
     */
    private $option_name = 'aees_settings';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_settings_submenu'], 15);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_settings_scripts']);
    }

    /**
     * Register settings submenu under main AEES menu
     */
    public function register_settings_submenu()
    {
        add_submenu_page(
            'aees',                             // Parent slug
            'Settings',                         // Page title
            'Settings',                         // Menu title
            'manage_options',                   // Capability
            'aees-settings',                    // Menu slug
            [$this, 'render_settings_page']    // Callback
        );
    }

    /**
     * Enqueue scripts for settings page
     */
    public function enqueue_settings_scripts($hook)
    {
        // Only load on our settings page
        if ($hook !== 'auction-estimate-emails_page_aees-settings') {
            return;
        }

        wp_enqueue_script(
            'aees-settings-script',
            AEES_PLUGIN_URL . 'assets/js/settings-page.js',
            ['jquery'],
            AEES_VERSION,
            true
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings()
    {
        register_setting(
            'aees_settings_group',      // Option group
            $this->option_name,         // Option name
            [$this, 'sanitize_settings'] // Sanitization callback
        );

        // Register auction houses option separately
        register_setting(
            'aees_settings_group',
            'aees_auction_houses',
            [$this, 'sanitize_auction_houses']
        );

        // General Settings Section - Hidden for now
        // add_settings_section(
        //     'aees_general_section',
        //     'General Settings',
        //     [$this, 'render_general_section'],
        //     'aees-settings'
        // );

        // // Form ID Setting
        // add_settings_field(
        //     'forminator_form_id',
        //     'Forminator Form ID',
        //     [$this, 'render_form_id_field'],
        //     'aees-settings',
        //     'aees_general_section'
        // );

        // Email Expiration Settings Section - Hidden for now
        // add_settings_section(
        //     'aees_email_section',
        //     'Email Expiration Settings',
        //     [$this, 'render_email_section'],
        //     'aees-settings'
        // );

        // // User Response Expiration
        // add_settings_field(
        //     'user_response_expiration_days',
        //     'User Response Expiration (days)',
        //     [$this, 'render_user_expiration_field'],
        //     'aees-settings',
        //     'aees_email_section'
        // );

        // // Authorization Expiration
        // add_settings_field(
        //     'authorization_expiration_days',
        //     'Authorization Expiration (days)',
        //     [$this, 'render_authorization_expiration_field'],
        //     'aees-settings',
        //     'aees_email_section'
        // );

        // Auction Houses Section
        add_settings_section(
            'aees_auction_houses_section',
            'Auction Houses',
            [$this, 'render_auction_houses_section'],
            'aees-settings'
        );

        // Auction Houses Field
        add_settings_field(
            'auction_houses',
            'Manage Auction Houses',
            [$this, 'render_auction_houses_field'],
            'aees-settings',
            'aees_auction_houses_section'
        );
    }

    /**
     * Render general section description
     */
    public function render_general_section()
    {
        echo '<p>Configure the core settings for the Auction Estimate Email System.</p>';
    }

    /**
     * Render email section description
     */
    public function render_email_section()
    {
        echo '<p>Set how long email response links remain valid before expiring.</p>';
    }

    /**
     * Render form ID field
     */
    public function render_form_id_field()
    {
        $settings = get_option($this->option_name, []);
        $value = isset($settings['forminator_form_id']) ? $settings['forminator_form_id'] : '';

        echo '<input type="number" name="' . $this->option_name . '[forminator_form_id]"
              value="' . esc_attr($value) . '"
              class="regular-text"
              placeholder="2902"
              min="1" />';
        echo '<p class="description">Enter the Forminator form ID to use for auction estimates. You can find this in Forminator → Forms.</p>';

        // Display current form info if available
        if (!empty($value) && class_exists('Forminator_API')) {
            $form = Forminator_API::get_form($value);
            if ($form && isset($form->settings['formName'])) {
                echo '<p class="description" style="color: #46b450;">
                      <span class="dashicons dashicons-yes-alt"></span>
                      Connected to form: <strong>' . esc_html($form->settings['formName']) . '</strong>
                      </p>';
            } else {
                echo '<p class="description" style="color: #dc3232;">
                      <span class="dashicons dashicons-warning"></span>
                      Form not found. Please verify the form ID.
                      </p>';
            }
        }
    }

    /**
     * Render user response expiration field
     */
    public function render_user_expiration_field()
    {
        $settings = get_option($this->option_name, []);
        $value = isset($settings['user_response_expiration_days']) ? $settings['user_response_expiration_days'] : '7';

        echo '<input type="number" name="' . $this->option_name . '[user_response_expiration_days]"
              value="' . esc_attr($value) . '"
              class="small-text"
              min="1"
              max="90" /> days';
        echo '<p class="description">How many days users have to accept or reject proposals via email. Default: 7 days</p>';
    }

    /**
     * Render authorization expiration field
     */
    public function render_authorization_expiration_field()
    {
        $settings = get_option($this->option_name, []);
        $value = isset($settings['authorization_expiration_days']) ? $settings['authorization_expiration_days'] : '14';

        echo '<input type="number" name="' . $this->option_name . '[authorization_expiration_days]"
              value="' . esc_attr($value) . '"
              class="small-text"
              min="1"
              max="90" /> days';
        echo '<p class="description">How many days auction houses have to authorize accepted proposals. Default: 14 days</p>';
    }

    /**
     * Render auction houses section description
     */
    public function render_auction_houses_section()
    {
        echo '<p>Manage the list of auction houses available for selection when creating proposals.</p>';
    }

    /**
     * Render auction houses repeater field
     */
    public function render_auction_houses_field()
    {
        $auction_houses = get_option('aees_auction_houses', []);

        // Ensure it's an array
        if (!is_array($auction_houses)) {
            $auction_houses = [];
        }

        ?>
        <div id="aees-auction-houses-repeater">
            <table class="widefat" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th style="width: 40%;">Auction House Name</th>
                        <th style="width: 50%;">Email Address</th>
                        <th style="width: 10%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="aees-auction-houses-list">
                    <?php if (empty($auction_houses)): ?>
                        <tr class="aees-no-houses-row">
                            <td colspan="3" style="text-align: center; padding: 20px; color: #999;">
                                No auction houses added yet. Click "Add Auction House" below to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auction_houses as $index => $house): ?>
                            <tr class="aees-auction-house-row aees-saved-row">
                                <td>
                                    <input type="text"
                                           name="aees_auction_houses[<?php echo $index; ?>][name]"
                                           value="<?php echo esc_attr($house['name']); ?>"
                                           class="regular-text aees-house-name"
                                           placeholder="e.g., Christie's"
                                           readonly
                                           style="background-color: #f0f0f1; cursor: not-allowed;"
                                           required />
                                </td>
                                <td>
                                    <input type="email"
                                           name="aees_auction_houses[<?php echo $index; ?>][email]"
                                           value="<?php echo esc_attr($house['email']); ?>"
                                           class="regular-text aees-house-email"
                                           placeholder="e.g., shipping@christies.com"
                                           readonly
                                           style="background-color: #f0f0f1; cursor: not-allowed;"
                                           required />
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="button aees-remove-house" title="Remove">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p style="margin-top: 15px;">
                <button type="button" id="aees-add-auction-house" class="button button-secondary">
                    <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> Add Auction House
                </button>
            </p>
            <p class="description">
                Add auction houses that will appear in the dropdown when creating proposals.
            </p>
        </div>
        <?php
    }

    /**
     * Sanitize settings before saving
     */
    public function sanitize_settings($input)
    {
        $sanitized = [];

        // Sanitize form ID
        if (isset($input['forminator_form_id'])) {
            $sanitized['forminator_form_id'] = absint($input['forminator_form_id']);
        }

        // Sanitize user response expiration
        if (isset($input['user_response_expiration_days'])) {
            $days = absint($input['user_response_expiration_days']);
            $sanitized['user_response_expiration_days'] = max(1, min(90, $days)); // Between 1-90 days
        }

        // Sanitize authorization expiration
        if (isset($input['authorization_expiration_days'])) {
            $days = absint($input['authorization_expiration_days']);
            $sanitized['authorization_expiration_days'] = max(1, min(90, $days)); // Between 1-90 days
        }

        return $sanitized;
    }

    /**
     * Sanitize auction houses before saving
     */
    public function sanitize_auction_houses($input)
    {
        $sanitized = [];

        if (!is_array($input)) {
            return $sanitized;
        }

        foreach ($input as $house) {
            // Skip if either name or email is empty
            if (empty($house['name']) || empty($house['email'])) {
                continue;
            }

            // Sanitize and validate
            $name = sanitize_text_field($house['name']);
            $email = sanitize_email($house['email']);

            // Only add if email is valid
            if (!empty($name) && is_email($email)) {
                $sanitized[] = [
                    'name' => $name,
                    'email' => $email
                ];
            }
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('aees_settings_group');
                do_settings_sections('aees-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get form ID from settings (with fallback to default)
     *
     * @return int Form ID
     */
    public static function get_form_id()
    {
        $settings = get_option('aees_settings', []);
        $form_id = isset($settings['forminator_form_id']) ? absint($settings['forminator_form_id']) : 0;

        // Fallback to hardcoded default if not set
        if (empty($form_id)) {
            $form_id = 2902; // Default form ID
        }

        return $form_id;
    }

    /**
     * Get user response expiration days
     *
     * @return int Number of days
     */
    public static function get_user_response_expiration_days()
    {
        $settings = get_option('aees_settings', []);
        $days = isset($settings['user_response_expiration_days']) ? absint($settings['user_response_expiration_days']) : 0;

        // Fallback to default
        if (empty($days)) {
            $days = 7; // Default 7 days
        }

        return $days;
    }

    /**
     * Get authorization expiration days
     *
     * @return int Number of days
     */
    public static function get_authorization_expiration_days()
    {
        $settings = get_option('aees_settings', []);
        $days = isset($settings['authorization_expiration_days']) ? absint($settings['authorization_expiration_days']) : 0;

        // Fallback to default
        if (empty($days)) {
            $days = 14; // Default 14 days
        }

        return $days;
    }

    /**
     * Get saved auction houses
     *
     * @return array Array of auction houses with name and email
     */
    public static function get_auction_houses()
    {
        $auction_houses = get_option('aees_auction_houses', []);

        // Ensure it's an array
        if (!is_array($auction_houses)) {
            $auction_houses = [];
        }

        return $auction_houses;
    }
}
