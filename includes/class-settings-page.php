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
        // Temporarily commented out - settings page hidden from menu
        // add_action('admin_menu', [$this, 'register_settings_submenu'], 15);
        add_action('admin_init', [$this, 'register_settings']);
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
     * Register plugin settings
     */
    public function register_settings()
    {
        register_setting(
            'aees_settings_group',      // Option group
            $this->option_name,         // Option name
            [$this, 'sanitize_settings'] // Sanitization callback
        );

        // General Settings Section
        add_settings_section(
            'aees_general_section',
            'General Settings',
            [$this, 'render_general_section'],
            'aees-settings'
        );

        // Form ID Setting
        add_settings_field(
            'forminator_form_id',
            'Forminator Form ID',
            [$this, 'render_form_id_field'],
            'aees-settings',
            'aees_general_section'
        );

        // Email Expiration Settings Section
        add_settings_section(
            'aees_email_section',
            'Email Expiration Settings',
            [$this, 'render_email_section'],
            'aees-settings'
        );

        // User Response Expiration
        add_settings_field(
            'user_response_expiration_days',
            'User Response Expiration (days)',
            [$this, 'render_user_expiration_field'],
            'aees-settings',
            'aees_email_section'
        );

        // Authorization Expiration
        add_settings_field(
            'authorization_expiration_days',
            'Authorization Expiration (days)',
            [$this, 'render_authorization_expiration_field'],
            'aees-settings',
            'aees_email_section'
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

            <hr>

            <h2>System Information</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Plugin Version</th>
                    <td><?php echo esc_html(AEES_VERSION); ?></td>
                </tr>
                <tr>
                    <th scope="row">Database Version</th>
                    <td><?php echo esc_html(get_option('aees_db_version', '1.0')); ?></td>
                </tr>
                <tr>
                    <th scope="row">Forminator Status</th>
                    <td>
                        <?php if (class_exists('Forminator_API')): ?>
                            <span style="color: #46b450;">
                                <span class="dashicons dashicons-yes-alt"></span> Active
                            </span>
                        <?php else: ?>
                            <span style="color: #dc3232;">
                                <span class="dashicons dashicons-warning"></span> Not Active (Required)
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Response Page</th>
                    <td>
                        <?php
                        $page = get_page_by_path('proposal-response');
                        if ($page): ?>
                            <span style="color: #46b450;">
                                <span class="dashicons dashicons-yes-alt"></span> Created
                            </span>
                            <a href="<?php echo get_permalink($page->ID); ?>" target="_blank">View Page</a>
                        <?php else: ?>
                            <span style="color: #dc3232;">
                                <span class="dashicons dashicons-warning"></span> Missing
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <hr>

            <h2>Need Help?</h2>
            <p>
                For documentation, troubleshooting, and support:
                <ul>
                    <li>Check the <strong>README.md</strong> file in the plugin directory</li>
                    <li>Review the <strong>CHANGELOG.md</strong> for recent updates</li>
                    <li>Contact: Mubeen Hassan</li>
                </ul>
            </p>
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
}
