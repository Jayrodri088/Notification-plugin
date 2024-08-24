<?php
/**
 * Plugin Name: Notification and Alerts Plugin
 * Plugin URI:  https://example.com/notification-alerts-plugin
 * Description: A plugin to display site-wide notifications and alerts.
 * Version:     2.0.0
 * Author:      Jay
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 */

// Add admin menu item for Notifications
function nap_add_admin_menu() {
    add_menu_page(
        __('Notification Alerts', 'text_domain'), // Page title
        __('Notifications', 'text_domain'),       // Menu title
        'manage_options',                         // Capability
        'nap_settings',                           // Menu slug
        'nap_settings_page',                      // Callback function
        'dashicons-megaphone',                    // Icon
        100                                       // Position
    );
}
add_action('admin_menu', 'nap_add_admin_menu');

// Render the admin page
function nap_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Notification Alerts Settings', 'text_domain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nap_settings_group');
            do_settings_sections('nap_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings, sections, and fields
function nap_register_settings() {
    register_setting('nap_settings_group', 'nap_notification_message');
    register_setting('nap_settings_group', 'nap_notification_color');
    register_setting('nap_settings_group', 'nap_notification_text_color');
    register_setting('nap_settings_group', 'nap_notification_font_size');
    register_setting('nap_settings_group', 'nap_dismiss_button');
    register_setting('nap_settings_group', 'nap_start_date');
    register_setting('nap_settings_group', 'nap_end_date');
    register_setting('nap_settings_group', 'nap_target_pages');

    add_settings_section(
        'nap_settings_section',
        __('Notification Settings', 'text_domain'),
        null,
        'nap_settings'
    );

    add_settings_field('nap_notification_message', __('Notification Message', 'text_domain'), 'nap_notification_message_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_notification_color', __('Notification Background Color', 'text_domain'), 'nap_notification_color_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_notification_text_color', __('Text Color', 'text_domain'), 'nap_notification_text_color_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_notification_font_size', __('Font Size (in px)', 'text_domain'), 'nap_notification_font_size_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_dismiss_button', __('Enable Dismiss Button', 'text_domain'), 'nap_dismiss_button_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_start_date', __('Start Date', 'text_domain'), 'nap_start_date_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_end_date', __('End Date', 'text_domain'), 'nap_end_date_callback', 'nap_settings', 'nap_settings_section');
    add_settings_field('nap_target_pages', __('Target Pages/Posts', 'text_domain'), 'nap_target_pages_callback', 'nap_settings', 'nap_settings_section');
}
add_action('admin_init', 'nap_register_settings');

// Callback functions for fields
function nap_notification_message_callback() {
    $value = get_option('nap_notification_message', '');
    echo '<textarea name="nap_notification_message" rows="5" class="large-text">' . esc_textarea($value) . '</textarea>';
}

function nap_notification_color_callback() {
    $value = get_option('nap_notification_color', '#ffcc00');
    echo '<input type="text" name="nap_notification_color" value="' . esc_attr($value) . '" class="color-field">';
}

function nap_notification_text_color_callback() {
    $value = get_option('nap_notification_text_color', '#000000');
    echo '<input type="text" name="nap_notification_text_color" value="' . esc_attr($value) . '" class="color-field">';
}

function nap_notification_font_size_callback() {
    $value = get_option('nap_notification_font_size', '16');
    echo '<input type="number" name="nap_notification_font_size" value="' . esc_attr($value) . '" class="small-text"> px';
}

function nap_dismiss_button_callback() {
    $value = get_option('nap_dismiss_button', 'no');
    echo '<input type="checkbox" name="nap_dismiss_button" value="yes"' . checked($value, 'yes', false) . '> ' . __('Show Dismiss Button', 'text_domain');
}

function nap_start_date_callback() {
    $value = get_option('nap_start_date', '');
    echo '<input type="date" name="nap_start_date" value="' . esc_attr($value) . '">';
}

function nap_end_date_callback() {
    $value = get_option('nap_end_date', '');
    echo '<input type="date" name="nap_end_date" value="' . esc_attr($value) . '">';
}

function nap_target_pages_callback() {
    $selected = get_option('nap_target_pages', array());
    $pages = get_pages();
    echo '<select name="nap_target_pages[]" multiple="multiple" class="large-text">';
    foreach ($pages as $page) {
        echo '<option value="' . esc_attr($page->ID) . '"' . (in_array($page->ID, $selected) ? ' selected' : '') . '>' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}

// Enqueue styles for notification
function nap_enqueue_styles() {
    wp_enqueue_style('nap-custom-style', plugin_dir_url(__FILE__) . 'assets/nap-style.css');
    wp_enqueue_script('nap-dismiss-script', plugin_dir_url(__FILE__) . 'assets/nap-script.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'nap_enqueue_styles');

// Display notification on the frontend
function nap_display_notification() {
    $message = get_option('nap_notification_message', '');
    $color = get_option('nap_notification_color', '#ffcc00');
    $text_color = get_option('nap_notification_text_color', '#000000');
    $font_size = get_option('nap_notification_font_size', '16');
    $dismiss_button = get_option('nap_dismiss_button', 'no');
    $start_date = get_option('nap_start_date', '');
    $end_date = get_option('nap_end_date', '');
    $target_pages = get_option('nap_target_pages', array());
    $current_date = date('Y-m-d');

    if (!empty($message) && (!$start_date || $current_date >= $start_date) && (!$end_date || $current_date <= $end_date) && (empty($target_pages) || is_page($target_pages) || is_single($target_pages))) {
        echo '<div class="nap-notification" style="background-color: ' . esc_attr($color) . '; color: ' . esc_attr($text_color) . '; font-size: ' . esc_attr($font_size) . 'px;">';
        echo '<p>' . esc_html($message) . '</p>';
        if ($dismiss_button == 'yes') {
            echo '<button class="nap-dismiss-button">X</button>';
        }
        echo '</div>';
    }
}
add_action('wp_footer', 'nap_display_notification');

// Enqueue scripts for dismiss functionality
function nap_enqueue_scripts() {
    wp_enqueue_script('nap-dismiss-script', plugin_dir_url(__FILE__) . 'assets/nap-script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'nap_enqueue_scripts');
