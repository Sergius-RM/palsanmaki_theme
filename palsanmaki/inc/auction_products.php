<?php
/**
 * ACF Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Функция для добавления/обновления элемента аукциона
function prod_handle_auction_item() {
    // Проверяем права пользователя на создание/редактирование элементов
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }

    $auction_item_id = isset($_POST['auction_item_id']) ? absint($_POST['auction_item_id']) : 0;
    $auction_item_title = sanitize_text_field($_POST['auction_item_title']);
    $auction_item_description = sanitize_textarea_field($_POST['auction_item_description']);
    $current_auction_id = absint($_POST['current_auction_id']);
    $auction_item_sku = sanitize_text_field($_POST['auction_item_sku']);

    $post_data = [
        'ID'           => $auction_item_id,
        'post_title'   => $auction_item_title,
        'post_content' => $auction_item_description,
        'post_type'    => 'auction_items',
        'post_status'  => 'publish',
    ];

    // Создаем или обновляем пост
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to create or update the auction item.']);
        return;
    }

    update_post_meta($post_id, 'product_id', $auction_item_sku);

    if (isset($_POST['auction_item_image_ids'])) {
        $image_ids = $_POST['auction_item_image_ids'];
        foreach ($image_ids as $image_id) {
            // Привязываем каждый ID изображения к посту
            wp_update_post([
                'ID'          => intval($image_id),
                'post_parent' => $post_id
            ]);
        }
        // Устанавливаем первое изображение в качестве миниатюры поста
        set_post_thumbnail($post_id, intval($image_ids[0]));
    }
    if (!empty($_POST['auction_item_image_ids'])) {
        $image_ids = explode(',', $_POST['auction_item_image_ids']);
        if (!empty($image_ids) && is_array($image_ids)) {
            // Устанавливаем первое изображение в качестве миниатюры поста
            set_post_thumbnail($post_id, $image_ids[0]);

            // Присваиваем остальные изображения к посту
            foreach ($image_ids as $index => $attachment_id) {
                // Пропустим первое изображение, так как оно уже установлено как миниатюра
                if ($index == 0) continue;

                // Убедимся, что ID изображения является действительным числом
                $attachment_id = absint($attachment_id);
                if ($attachment_id > 0) {
                    // Обновляем пост с ID вложения
                    wp_update_post(array(
                        'ID'          => $attachment_id,
                        'post_parent' => $post_id
                    ));
                }
            }
        }
    }

    // Обновляем метаполя, если это необходимо
    update_post_meta($post_id, 'current_auction_id', $current_auction_id);

    wp_send_json_success(['message' => 'Auction item successfully created/updated.', 'post_id' => $post_id]);
}

add_action('wp_ajax_handle_auction_item', 'prod_handle_auction_item');


// Функция для удаления элемента аукциона
function prod_handle_delete_auction_item() {

    $auction_item_id = isset($_POST['auction_item_id']) ? absint($_POST['auction_item_id']) : 0;

    if (!current_user_can('delete_post', $auction_item_id)) { // Проверяем права пользователя на удаление поста
        wp_send_json_error(['message' => 'You are not allowed to delete this item.']);
        return;
    }

    $result = wp_delete_post($auction_item_id, true); // Удаляем пост

    if ($result) {
        wp_send_json_success(['message' => 'Auction item successfully deleted.']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete the auction item.']);
    }
}

add_action('wp_ajax_delete_auction_item', 'prod_handle_delete_auction_item');


// Функция для загрузки изображения
function prod_upload_auction_item_image($file_key, $post_id) {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attachment_id = media_handle_upload($file_key, $post_id);
    if (is_wp_error($attachment_id)) {
        // Обработка ошибки загрузки файла
        wp_send_json_error(['message' => 'Error uploading image.']);
    }

    return $attachment_id;
}

// Регистрация AJAX-действий для авторизованных пользователей
add_action('wp_ajax_add_auction_item', 'prod_handle_auction_item');
add_action('wp_ajax_update_auction_item', 'prod_handle_auction_item');
add_action('wp_ajax_add_auction_item', 'prod_handle_add_auction_item');
add_action('wp_ajax_update_auction_item', 'prod_handle_update_auction_item');


function prod_get_attachment_id_from_url($image_url) {
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
        if ($attachment) {
            return $attachment[0];
        }

    return null;
}

function prod_sideload_image_from_url($image_url, $post_id) {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Проверяем, есть ли уже вложение для этого URL
    $attachment_id = get_attachment_id_from_url($image_url);
    if ($attachment_id) {
        return $attachment_id;
    }

    // Загрузка и создание вложения
    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error($attachment_id->get_error_message());
        return;
    }

    return $attachment_id;
}

function sideload_image_from_url($image_url, $post_id) {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Проверяем, есть ли уже вложение для этого URL
    $attachment_id = get_attachment_id_from_url($image_url);
    if ($attachment_id) {
        return $attachment_id;
    }

    // Загрузка и создание вложения
    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error($attachment_id->get_error_message());
        return;
    }

    return $attachment_id;
}

add_action('wp_ajax_handle_update_post_thumbnail_prod', 'handle_update_post_thumbnail_prod');
function handle_update_post_thumbnail_prod() {
    error_log('AJAX request to update post thumbnail received');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

    if (!$post_id || !$image_id) {
        error_log("Missing Post ID or Image ID.");
        wp_send_json_error(['message' => 'Missing Post ID or Image ID.']);
        return;
    }

    if (set_post_thumbnail($post_id, $image_id)) {
        error_log("Thumbnail updated successfully for Post ID: {$post_id}.");
        wp_send_json_success(['message' => 'Thumbnail updated successfully.']);
    } else {
        error_log("Failed to update thumbnail for Post ID: {$post_id}.");
        wp_send_json_error(['message' => 'Failed to update thumbnail.']);
    }

}


function prod_get_highest_menu_order($current_auction_id) {
    global $wpdb;

    // Построение запроса к базе данных для получения наивысшего menu_order
    $query = $wpdb->prepare(
        "SELECT MAX(menu_order) FROM {$wpdb->posts}
         WHERE post_type = 'auction_items'
         AND ID IN (
             SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = 'current_auction_id'
             AND meta_value = %d
         )",
        $current_auction_id
    );

    // Получение результата
    $highest_menu_order = $wpdb->get_var($query);

    // Возврат наивысшего menu_order. Если нет элементов, вернуть 0.
    return ($highest_menu_order !== null) ? (int) $highest_menu_order : 0;
}

// обработчик перетаскивания и изменения порядка постов мышкой
add_action('wp_ajax_save_sorted_auction_items', 'prod_save_sorted_auction_items');
function prod_save_sorted_auction_items() {
    if (!check_ajax_referer('sortable_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce-vahvistus epäonnistui');
    }

    // Получение и обработка порядка
    $order = explode(',', $_POST['order']);
    foreach ($order as $index => $item_id) {
        wp_update_post(array(
            'ID' => intval($item_id),
            'menu_order' => $index
        ));
    }

    wp_send_json_success('Tilauksen päivitys onnistui');
}


function handle_update_images_order_and_thumbnail_prod() {
    error_log('AJAX request to update post thumbnail received');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

    set_post_thumbnail($post_id, $image_id);

}
add_action('wp_ajax_handle_update_images_order_and_thumbnail', 'handle_update_images_order_and_thumbnail_prod');



function prod_handle_delete_image() {
    // Проверяем, передан ли ID изображения
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

    // Проверяем права пользователя на удаление поста
    if (!current_user_can('delete_post', $image_id)) {
        wp_send_json_error('Недостаточно прав для выполнения этой операции.');
        return;
    }

    // Попытка удаления вложения (изображения)
    $result = wp_delete_attachment($image_id, true);

    if ($result) {
        wp_send_json_success('Изображение успешно удалено.');
    } else {
        wp_send_json_error('Произошла ошибка при попытке удаления изображения.');
    }
}

// Регистрация AJAX-обработчика для авторизованных пользователей
add_action('wp_ajax_handle_delete_image', 'prod_handle_delete_image');


