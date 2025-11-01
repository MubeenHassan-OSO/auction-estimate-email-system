<?php
if (!defined('ABSPATH')) {
    exit;
}

class AEES_Admin_Page
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_menu()
    {
        // Top-level menu
        add_menu_page(
            'Auction Estimate Emails',
            'Auction Estimate Emails',
            'manage_options',
            'aees',
            [$this, 'render_page'],
            'dashicons-email-alt2',
            6
        );
    }

    public function enqueue_assets($hook)
    {
        // the hook for our top-level page is "toplevel_page_{menu_slug}"
        if ($hook !== 'toplevel_page_aees') {
            return;
        }

        wp_enqueue_style('aees-admin-css', AEES_PLUGIN_URL . 'assets/css/admin-style.css');
    }

    public function render_page()
    {
        echo '<div class="wrap">';
        echo '<h1>Auction Estimate Email System</h1>';

        if (!class_exists('AEES_Submission_Table')) {
            echo '<div class="notice notice-error"><p>Submission table class not found.</p></div>';
            echo '</div>';
            return;
        }

        $table = new AEES_Submission_Table();
        $table->prepare_items();

        $table->display();
        echo '</div>';
    }
}
