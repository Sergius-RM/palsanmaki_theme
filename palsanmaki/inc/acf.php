<?php
/**
 * ACF Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Add options page
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(
        [
            'page_title' => __('Site Settings', 'greatcompany'),
            'menu_title' => __('Site Settings', 'greatcompany'),
            'menu_slug' => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect' => false,
        ]
    );
}

