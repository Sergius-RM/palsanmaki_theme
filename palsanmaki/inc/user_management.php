<?php
/**
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Обработчик удаления пользователя
add_action('wp_ajax_delete_user', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_user_nonce')) {
        wp_send_json_error('Ошибка проверки безопасности.');
        return;
    }

    $user_id = intval($_POST['user_id']);
    require_once(ABSPATH.'wp-admin/includes/user.php' );
    $delete = wp_delete_user($user_id);

    if ($delete) {
        wp_send_json_success('Käyttäjä poistettu');
    } else {
        wp_send_json_error('Ошибка при удалении пользователя.');
    }
});

add_action('delete_user', 'delete_user_bids');

function delete_user_bids($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    $wpdb->delete($table_name, ['user_id' => $user_id], ['%d']);
}


// Обработчик бана пользователя
add_action('wp_ajax_ban_user', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Недостаточно прав.');
    }

    $user_id = intval($_POST['user_id']);
    $banned = update_user_meta($user_id, 'banned', true);

    if ($banned) {
        wp_send_json_success('Käyttäjä estetty');
    } else {
        wp_send_json_error('Ошибка при бане пользователя.');
    }
});

// Обработчик снятия бана с пользователя
add_action('wp_ajax_unban_user', function() {
    if (!current_user_can('manage_options') || !check_ajax_referer('unban_user_nonce', 'nonce')) {
        wp_send_json_error('Недостаточно прав или ошибка проверки nonce.');
        return;
    }

    $user_id = intval($_POST['user_id']);
    $unbanned = delete_user_meta($user_id, 'banned');

    if ($unbanned) {
        wp_send_json_success('Пользователь разбанен.');
    } else {
        wp_send_json_error('Ошибка при снятии бана с пользователя.');
    }
});


