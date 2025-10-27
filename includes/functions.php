<?php
if (!defined('ABSPATH')) {
    exit;
}

// You can add common helper functions here later if needed.
function aees_enqueue_admin_assets($hook)
{
    if (strpos($hook, 'aees') === false) {
        return;
    }

    wp_enqueue_style('aees-admin', AEES_PLUGIN_URL . 'assets/css/admin-style.css');
    wp_enqueue_script('aees-admin', AEES_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], false, true);
}
add_action('admin_enqueue_scripts', 'aees_enqueue_admin_assets');
