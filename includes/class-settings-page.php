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
        add_filter('wp_redirect', [$this, 'redirect_to_active_tab'], 10, 2);
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
     * Redirect to active tab after saving settings
     */
    public function redirect_to_active_tab($location, $status)
    {
        // Only modify redirect if we're saving settings
        if (strpos($location, 'aees-settings') !== false && isset($_POST['aees_active_tab'])) {
            $active_tab = sanitize_text_field($_POST['aees_active_tab']);
            $location = add_query_arg('tab', $active_tab, $location);
        }
        return $location;
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

        // Enqueue WordPress media uploader
        wp_enqueue_media();

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

        // Register service providers option separately
        register_setting(
            'aees_settings_group',
            'aees_service_providers',
            [$this, 'sanitize_service_providers']
        );

        // ============================================
        // GENERAL SETTINGS TAB
        // ============================================

        // General Settings Section
        add_settings_section(
            'aees_general_section',
            'Email Configuration',
            [$this, 'render_general_section'],
            'aees-settings-general'
        );

        // Admin Email Address
        add_settings_field(
            'admin_email',
            'Admin Email Address',
            [$this, 'render_admin_email_field'],
            'aees-settings-general',
            'aees_general_section'
        );

        // Send From Email
        add_settings_field(
            'send_from_email',
            'Send From Email',
            [$this, 'render_send_from_email_field'],
            'aees-settings-general',
            'aees_general_section'
        );

        // Send From Name
        add_settings_field(
            'send_from_name',
            'Send From Name',
            [$this, 'render_send_from_name_field'],
            'aees-settings-general',
            'aees_general_section'
        );

        // ============================================
        // PROVIDERS TAB
        // ============================================

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

        // Service Providers Section
        add_settings_section(
            'aees_service_providers_section',
            'Service Providers',
            [$this, 'render_service_providers_section'],
            'aees-settings-providers'
        );

        // Service Providers Field
        add_settings_field(
            'service_providers',
            'Manage Service Providers',
            [$this, 'render_service_providers_field'],
            'aees-settings-providers',
            'aees_service_providers_section'
        );

        // Auction Houses Section
        add_settings_section(
            'aees_auction_houses_section',
            'Auction Houses',
            [$this, 'render_auction_houses_section'],
            'aees-settings-providers'
        );

        // Auction Houses Field
        add_settings_field(
            'auction_houses',
            'Manage Auction Houses',
            [$this, 'render_auction_houses_field'],
            'aees-settings-providers',
            'aees_auction_houses_section'
        );
    }

    /**
     * Render general section description
     */
    public function render_general_section()
    {
        echo '<p>Configure email addresses for receiving admin notifications and sending emails to users.</p>';
    }

    /**
     * Render admin email field
     */
    public function render_admin_email_field()
    {
        $settings = get_option($this->option_name, []);
        $default_admin_email = get_option('admin_email');
        $value = isset($settings['admin_email']) && !empty($settings['admin_email'])
            ? $settings['admin_email']
            : $default_admin_email;

        echo '<input type="email"
                     name="' . $this->option_name . '[admin_email]"
                     value="' . esc_attr($value) . '"
                     class="regular-text"
                     placeholder="' . esc_attr($default_admin_email) . '" />';
        echo '<p class="description">Email address to receive admin notifications (proposal acceptance, rejections, authorization). Default: WordPress admin email.</p>';
    }

    /**
     * Render send from email field
     */
    public function render_send_from_email_field()
    {
        $settings = get_option($this->option_name, []);
        $default_email = 'noreply@' . wp_parse_url(home_url(), PHP_URL_HOST);
        $value = isset($settings['send_from_email']) && !empty($settings['send_from_email'])
            ? $settings['send_from_email']
            : $default_email;

        echo '<input type="email"
                     name="' . $this->option_name . '[send_from_email]"
                     value="' . esc_attr($value) . '"
                     class="regular-text"
                     placeholder="' . esc_attr($default_email) . '" />';
        echo '<p class="description">Email address used as sender for all outgoing emails. Default: noreply@yourdomain.com</p>';
        echo '<p class="description" style="color: #d63638;"><strong>Important:</strong> For better deliverability, use a real email address configured with your email provider.</p>';
    }

    /**
     * Render send from name field
     */
    public function render_send_from_name_field()
    {
        $settings = get_option($this->option_name, []);
        $default_name = get_bloginfo('name');
        $value = isset($settings['send_from_name']) && !empty($settings['send_from_name'])
            ? $settings['send_from_name']
            : $default_name;

        echo '<input type="text"
                     name="' . $this->option_name . '[send_from_name]"
                     value="' . esc_attr($value) . '"
                     class="regular-text"
                     placeholder="' . esc_attr($default_name) . '" />';
        echo '<p class="description">Name displayed as sender in outgoing emails. Default: Your site name.</p>';
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
     * Render service providers section description
     */
    public function render_service_providers_section()
    {
        echo '<p>Manage the list of service providers available for selection in proposals. Each provider can have a name and an icon/image.</p>';
    }

    /**
     * Render service providers repeater field
     */
    public function render_service_providers_field()
    {
        $service_providers = get_option('aees_service_providers', []);

        // Ensure it's an array
        if (!is_array($service_providers)) {
            $service_providers = [];
        }

        ?>
        <div id="aees-service-providers-repeater">
            <table class="widefat" style="max-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 50%;">Provider Name</th>
                        <th style="width: 35%;">Icon/Image</th>
                        <th style="width: 15%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="aees-service-providers-list">
                    <?php if (empty($service_providers)): ?>
                        <tr class="aees-no-providers-row">
                            <td colspan="3" style="text-align: center; padding: 20px; color: #999;">
                                No service providers added yet. Click "Add Service Provider" below to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($service_providers as $index => $provider): ?>
                            <tr class="aees-service-provider-row aees-saved-row">
                                <td>
                                    <input type="text"
                                           name="aees_service_providers[<?php echo $index; ?>][name]"
                                           value="<?php echo esc_attr($provider['name']); ?>"
                                           class="regular-text aees-provider-name"
                                           placeholder="e.g., Standard Shipping"
                                           readonly
                                           style="background-color: #f0f0f1; cursor: not-allowed;"
                                           required />
                                </td>
                                <td>
                                    <div class="aees-provider-image-wrapper" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden"
                                               name="aees_service_providers[<?php echo $index; ?>][image]"
                                               value="<?php echo esc_attr($provider['image'] ?? ''); ?>"
                                               class="aees-provider-image-url" />
                                        <div class="aees-provider-image-preview" style="flex-shrink: 0;">
                                            <?php if (!empty($provider['image'])): ?>
                                                <img src="<?php echo esc_url($provider['image']); ?>"
                                                     alt="Provider Icon"
                                                     style="max-width: 60px; max-height: 60px; border: 1px solid #ddd; border-radius: 4px; display: block;" />
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; border: 2px dashed #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">No Image</div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="button aees-upload-provider-image" data-readonly="true" style="pointer-events: none; opacity: 0.6;">
                                            <span class="dashicons dashicons-format-image"></span> <?php echo !empty($provider['image']) ? 'Change' : 'Upload'; ?>
                                        </button>
                                        <?php if (!empty($provider['image'])): ?>
                                            <button type="button" class="button aees-remove-provider-image" data-readonly="true" style="pointer-events: none; opacity: 0.6;">
                                                <span class="dashicons dashicons-no-alt"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="button aees-remove-provider" title="Remove">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p style="margin-top: 15px;">
                <button type="button" id="aees-add-service-provider" class="button button-secondary">
                    <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> Add Service Provider
                </button>
            </p>
            <p class="description">
                Add service providers that will appear in the dropdown when creating proposals. The image is optional but recommended for better visual presentation.
            </p>
        </div>
        <?php
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
            <table class="widefat" style="max-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 45%;">Auction House Name</th>
                        <th style="width: 40%;">Email Address</th>
                        <th style="width: 15%; text-align: center;">Action</th>
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
     * IMPORTANT: Preserve existing settings not in current form
     */
    public function sanitize_settings($input)
    {
        // Check which tab was submitted
        $active_tab = isset($_POST['aees_active_tab']) ? sanitize_text_field($_POST['aees_active_tab']) : 'general';

        // Get existing settings to preserve values not in current form
        $existing = get_option($this->option_name, []);

        // If we're not on the general tab, preserve all existing settings
        if ($active_tab !== 'general') {
            return $existing;
        }

        // We're on general tab, start with existing values and update only submitted fields
        $sanitized = $existing;

        // Sanitize form ID (only if present in form)
        if (isset($input['forminator_form_id'])) {
            $sanitized['forminator_form_id'] = absint($input['forminator_form_id']);
        }

        // Sanitize user response expiration (only if present in form)
        if (isset($input['user_response_expiration_days'])) {
            $days = absint($input['user_response_expiration_days']);
            $sanitized['user_response_expiration_days'] = max(1, min(90, $days)); // Between 1-90 days
        }

        // Sanitize authorization expiration (only if present in form)
        if (isset($input['authorization_expiration_days'])) {
            $days = absint($input['authorization_expiration_days']);
            $sanitized['authorization_expiration_days'] = max(1, min(90, $days)); // Between 1-90 days
        }

        // Sanitize admin email (only if present in form)
        if (isset($input['admin_email'])) {
            $email = sanitize_email($input['admin_email']);
            if (is_email($email)) {
                $sanitized['admin_email'] = $email;
            }
        }

        // Sanitize send from email (only if present in form)
        if (isset($input['send_from_email'])) {
            $email = sanitize_email($input['send_from_email']);
            if (is_email($email)) {
                $sanitized['send_from_email'] = $email;
            }
        }

        // Sanitize send from name (only if present in form)
        if (isset($input['send_from_name'])) {
            $sanitized['send_from_name'] = sanitize_text_field($input['send_from_name']);
        }

        return $sanitized;
    }

    /**
     * Sanitize service providers before saving
     * IMPORTANT: Preserve existing data if not on current tab
     */
    public function sanitize_service_providers($input)
    {
        // Check which tab was submitted
        $active_tab = isset($_POST['aees_active_tab']) ? sanitize_text_field($_POST['aees_active_tab']) : '';

        // If we're not on the providers tab, preserve existing data
        if ($active_tab !== 'providers') {
            $existing = get_option('aees_service_providers', []);
            return $existing;
        }

        // If input is not an array, return empty
        if (!is_array($input)) {
            return [];
        }

        // If input is empty array, user wants to clear all providers
        if (empty($input)) {
            return [];
        }

        $sanitized = [];

        foreach ($input as $provider) {
            // Skip if name is empty
            if (empty($provider['name'])) {
                continue;
            }

            // Sanitize name
            $name = sanitize_text_field($provider['name']);

            // Sanitize image URL (optional)
            $image = '';
            if (!empty($provider['image'])) {
                $image = esc_url_raw($provider['image']);
            }

            // Add to sanitized array
            if (!empty($name)) {
                $sanitized[] = [
                    'name' => $name,
                    'image' => $image
                ];
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize auction houses before saving
     * IMPORTANT: Preserve existing data if not on current tab
     */
    public function sanitize_auction_houses($input)
    {
        // Check which tab was submitted
        $active_tab = isset($_POST['aees_active_tab']) ? sanitize_text_field($_POST['aees_active_tab']) : '';

        // If we're not on the providers tab, preserve existing data
        if ($active_tab !== 'providers') {
            $existing = get_option('aees_auction_houses', []);
            return $existing;
        }

        // If input is not an array, return empty
        if (!is_array($input)) {
            return [];
        }

        // If input is empty array, user wants to clear all houses
        if (empty($input)) {
            return [];
        }

        $sanitized = [];

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

        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors(); ?>

            <!-- Tab Navigation -->
            <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
                <a href="?page=aees-settings&tab=general"
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-generic" style="margin-top: 3px;"></span> General Settings
                </a>
                <a href="?page=aees-settings&tab=providers"
                   class="nav-tab <?php echo $active_tab === 'providers' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-businessperson" style="margin-top: 3px;"></span> Service Providers & Auction Houses
                </a>
            </nav>

            <!-- Tab Content -->
            <form class="service-providers-form" method="post" action="options.php">
                <?php
                settings_fields('aees_settings_group');

                // Add hidden field to indicate which tab is being saved
                echo '<input type="hidden" name="aees_active_tab" value="' . esc_attr($active_tab) . '" />';

                if ($active_tab === 'general') {
                    // General Settings Tab
                    do_settings_sections('aees-settings-general');
                } elseif ($active_tab === 'providers') {
                    // Service Providers & Auction Houses Tab
                    do_settings_sections('aees-settings-providers');
                }

                submit_button('Save Settings');
                ?>
            </form>
        </div>

        <style>
            /* Tab styling enhancements */
            .nav-tab-wrapper {
                border-bottom: 1px solid #ccc;
                padding-left: 10px;
            }
            .nav-tab {
                font-size: 14px;
                padding: 8px 15px;
            }
            .nav-tab .dashicons {
                font-size: 16px;
            }
            .nav-tab-active {
                background-color: #fff;
                border-bottom: 1px solid #fff;
            }

            /* Form styling */
            .form-table th {
                width: 220px;
                font-weight: 600;
            }
            .form-table td input[type="text"],
            .form-table td input[type="email"] {
                width: 100%;
                max-width: 500px;
            }
        </style>
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
     * Get saved service providers
     *
     * @return array Array of service providers with name and image
     */
    public static function get_service_providers()
    {
        $service_providers = get_option('aees_service_providers', []);

        // Ensure it's an array
        if (!is_array($service_providers)) {
            $service_providers = [];
        }

        return $service_providers;
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

    /**
     * Get admin email address for notifications
     * Falls back to WordPress admin email if not set
     *
     * @return string Admin email address
     */
    public static function get_admin_email()
    {
        $settings = get_option('aees_settings', []);
        $admin_email = isset($settings['admin_email']) && !empty($settings['admin_email'])
            ? $settings['admin_email']
            : get_option('admin_email');

        return $admin_email;
    }

    /**
     * Get send from email address
     * Falls back to noreply@domain.com if not set
     *
     * @return string Send from email address
     */
    public static function get_send_from_email()
    {
        $settings = get_option('aees_settings', []);
        $send_from_email = isset($settings['send_from_email']) && !empty($settings['send_from_email'])
            ? $settings['send_from_email']
            : 'noreply@' . wp_parse_url(home_url(), PHP_URL_HOST);

        return $send_from_email;
    }

    /**
     * Get send from name
     * Falls back to site name if not set
     *
     * @return string Send from name
     */
    public static function get_send_from_name()
    {
        $settings = get_option('aees_settings', []);
        $send_from_name = isset($settings['send_from_name']) && !empty($settings['send_from_name'])
            ? $settings['send_from_name']
            : get_bloginfo('name');

        return $send_from_name;
    }
}
