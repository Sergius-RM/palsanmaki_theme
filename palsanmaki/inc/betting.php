<?php
/**
 * ACF Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function handle_bid_submission() {
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to make a bid.');
    }

    $item_id = intval($_POST['item_id']);
    $bid_value = floatval($_POST['bid_value']);
    $user_id = get_current_user_id();

    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    // Проверяем, сделал ли уже пользователь ставку на этот товар
    $existing_bid = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table_name WHERE item_id = %d AND user_id = %d",
        $item_id,
        $user_id
    ));

    if ($bid_value == 0) {
        // Если ставка равна 0, удаляем запись о ставке пользователя
        if ($existing_bid) {
            $wpdb->delete($table_name, array('id' => $existing_bid->id));
            wp_send_json_success(array('message' => 'Your bid has been withdrawn.'));
        } else {
            wp_send_json_error('You have not placed any bid to withdraw.');
        }
    } else {
        // Если ставка больше 0, обновляем существующую ставку или создаем новую
        $data = array(
            'auction_id' => get_field('current_auction_id', $item_id), // ACF field relation to auction
            'item_id' => $item_id,
            'user_id' => $user_id,
            'bid_value' => $bid_value,
            'bid_time' => current_time('mysql')
        );
        $format = array('%d', '%d', '%d', '%f', '%s');

        if ($existing_bid) {
            $wpdb->update($table_name, $data, array('id' => $existing_bid->id), $format);
        } else {
            $wpdb->insert($table_name, $data, $format);
        }

        if ($wpdb->last_error) {
            wp_send_json_error('There was an error placing your bid.');
        } else {
            wp_send_json_success(array('message' => 'Your bid has been successfully placed.', 'bid_value' => $bid_value));
        }
    }
}

add_action('wp_ajax_place_bid', 'handle_bid_submission');
add_action('wp_ajax_nopriv_place_bid', 'handle_bid_submission');


function check_user_bid() {


    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to check your bid.');
    }

    $item_id = intval($_POST['item_id']);
    $user_id = get_current_user_id();

    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    $bid = $wpdb->get_row($wpdb->prepare(
        "SELECT bid_value FROM $table_name WHERE item_id = %d AND user_id = %d",
        $item_id,
        $user_id
    ));

    if ($bid) {
        wp_send_json_success(array('already_bid' => true, 'bid_value' => $bid->bid_value));
    } else {
        wp_send_json_success(array('already_bid' => false));
    }
}

add_action('wp_ajax_check_user_bid', 'check_user_bid');
add_action('wp_ajax_nopriv_check_user_bid', 'check_user_bid');


function mark_winner() {
    check_ajax_referer('mark_winner_nonce', 'security');

    $bid_id = isset($_POST['bid_id']) ? intval($_POST['bid_id']) : 0;
    $user_id = get_current_user_id(); // ID текущего пользователя
    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    // Получаем ID товара из ставки победителя
    $item_id = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM $table_name WHERE id = %d", $bid_id));

    // Сначала обновляем все ставки на этот товар, устанавливая winner в -1
    $wpdb->query($wpdb->prepare("UPDATE $table_name SET winner = -1 WHERE item_id = %d AND user_id != %d", $item_id, $user_id));

    // Теперь обновляем ставку победителя, устанавливая winner в 1
    $result = $wpdb->update($table_name, ['winner' => 1], ['id' => $bid_id]);

    if ($result !== false) {
        wp_send_json_success(__('User marked as winner successfully.', 'default'));
    } else {
        wp_send_json_error(__('Error marking user as winner.', 'default'));
    }
}
add_action('wp_ajax_mark_winner', 'mark_winner');

function unmark_winner() {
    check_ajax_referer('mark_winner_nonce', 'security');

    $bid_id = isset($_POST['bid_id']) ? intval($_POST['bid_id']) : 0;
    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    // Получаем ID товара из ставки победителя
    $item_id = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM $table_name WHERE id = %d", $bid_id));

    // Сбрасываем статус победителя у всех ставок на этот товар, устанавливая winner в 0
    $result = $wpdb->query($wpdb->prepare("UPDATE $table_name SET winner = 0 WHERE item_id = %d", $item_id));

    if ($result !== false) {
        wp_send_json_success(__('Winner unmarked successfully.', 'default'));
    } else {
        wp_send_json_error(__('Error unmarking the winner.', 'default'));
    }
}
add_action('wp_ajax_unmark_winner', 'unmark_winner');
