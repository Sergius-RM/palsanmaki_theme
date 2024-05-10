<?php
/**
 * ACF Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function numeric_posts_orderby($clauses) {
    global $wpdb;
    $clauses['orderby'] = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX($wpdb->posts.post_title, '.', 1), ' ', -1) AS UNSIGNED) ASC";
    return $clauses;
}
function custom_orderby_title_numeric($query) {
    if (!is_admin() && $query->is_main_query()) {
        $post_type = $query->get('post_type');
        if ($post_type == 'auction_items') {
            $query->set('orderby', 'meta_value_num');
            $query->set('meta_key', 'title_number');
        }
    }
}
add_filter('pre_get_posts', 'custom_orderby_title_numeric');
function save_title_number($post_id, $post, $update) {
    if ($post->post_type == 'auction_items' && $update) {
        $title = $post->post_title;
        $number = intval(trim(substr($title, 0, strpos($title, '.'))));
        update_post_meta($post_id, 'title_number', $number);
    }
}
add_action('save_post', 'save_title_number', 10, 3);

$admin_auction_list_link = get_field('admin_auction_list_link', 'option');


// В функциях.php вашей темы
add_action('wp_ajax_mass_add_auction_items', 'handle_mass_add_auction_items');

function handle_mass_add_auction_items() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Недостаточно прав для выполнения этой операции');
        return;
    }

    $attachments_ids = isset($_POST['attachments']) ? $_POST['attachments'] : array();

    $attachments_info = array();
    foreach ($attachments_ids as $attachment_id) {
        $attachment_url = wp_get_attachment_url($attachment_id);
        $attachment_thumb_url = wp_get_attachment_thumb_url($attachment_id);

        if ($attachment_url) {
            $attachments_info[] = array(
                'id' => $attachment_id,
                'url' => $attachment_url,
                'thumbnail_url' => $attachment_thumb_url ? $attachment_thumb_url : $attachment_url,
            );
        }
    }

    if (!empty($attachments_info)) {
        wp_send_json_success(array('attachments' => $attachments_info));
    } else {
        wp_send_json_error('Virhe ladattaessa kuvia');
    }
}


function handle_auction_form_submission() {
    // Проверяем, что запрос пришел с нужным действием
    if (isset($_POST['action']) && $_POST['action'] === 'create_or_update_auction') {
        // Проверяем nonce
        check_ajax_referer('auction_nonce_action', 'auction_nonce');

        $auction_id = isset($_POST['current_auction_id']) ? intval($_POST['current_auction_id']) : 0;
        $title = sanitize_text_field($_POST['auction_title']);
        $date = sanitize_text_field($_POST['auction_date']);
        $time = sanitize_text_field($_POST['auction_time']);
        $location = sanitize_text_field($_POST['auction_location']);
        $description = sanitize_textarea_field($_POST['auction_description']);

        $post_data = [
            'post_title'   => $title,
            'post_content' => $description,
            'post_type'    => 'auction',
            'post_status'  => 'publish',
        ];

        if ($auction_id > 0) {
            $post_data['ID'] = $auction_id;
        }

        $result_id = wp_insert_post($post_data, true);

        if (is_wp_error($result_id)) {
            wp_send_json_error('Ошибка при создании аукциона');
        } else {
            // Обновляем метаданные аукциона
            update_field('auction_date', $date, $result_id);
            update_field('auction_time', $time, $result_id);
            update_field('auction_location', $location, $result_id);
            wp_send_json_success([
                'auction_id' => $result_id,
                'title' => $title,
                'date' => $date,
                'time' => $time,
                'location' => $location,
                'description' => $description
            ]);
        }
    } else {
        wp_send_json_error('Неверное действие');
    }
}
add_action('wp_ajax_create_or_update_auction', 'handle_auction_form_submission');
add_action('wp_ajax_nopriv_create_or_update_auction', 'handle_auction_form_submission');


function handle_delete_auction() {
    global $wpdb; // Используем глобальный объект для работы с БД

    // Проверка nonce для безопасности
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_auction_nonce')) {
        wp_send_json_error('Nonce verification failed', 403);
        return;
    }

    // Получение ID аукциона
    $auction_id = isset($_POST['auction_id']) ? absint($_POST['auction_id']) : 0;

    // Проверка наличия аукциона
    if (!$auction_id || !get_post($auction_id)) {
        wp_send_json_error('Auction does not exist.');
        return;
    }

    // Находим все продукты, связанные с аукционом
    $args = array(
        'post_type' => 'auction_items',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'current_auction_id',
                'value' => $auction_id,
                'compare' => '='
            )
        )
    );
    $products = get_posts($args);

    // Удаление всех найденных продуктов
    foreach ($products as $product) {
        wp_delete_post($product->ID, true);
    }

    // Удаление записей о ставках из таблицы wp_auction_bids
    $wpdb->delete(
        $wpdb->prefix . 'auction_bids', // Полное имя таблицы с префиксом
        array('auction_id' => $auction_id), // Условия (где auction_id = $auction_id)
        array('%d') // Формат значения в условии
    );

    // Удаление аукциона
    wp_delete_post($auction_id, true);

    wp_send_json_success('Auction and related products and bids deleted successfully.');
}
add_action('wp_ajax_delete_auction', 'handle_delete_auction');



function remove_submenu_if_single_auction() {
    $current_auctions = get_field('current_auction', 'option');
    echo "<script>
        jQuery(document).ready(function($) {";
    if (count($current_auctions) < 2) {
        echo "$('.main_auction').each(function() {
                if ($(this).find('.sub-menu').length) {
                    $(this).find('.sub-menu').remove();
                }
            });";
    } else {
        echo "
            $('.main_auction > a').on('click', function(e) {
                e.preventDefault(); // Предотвращаем стандартное поведение ссылки
            });
            $('.main_auction').on('click', function(e) {
                // Проверяем, является ли нажатый элемент ссылкой
                if (!$(e.target).is('a')) {
                    var submenu = $(this).find('.sub-menu');
                    submenu.toggleClass('active_sub_menu'); // Переключаем класс active_sub_menu
                    submenu.slideToggle(); // Добавляем анимацию для отображения подменю
                }
            });";
    }
    echo "});
    </script>";
}
add_action('wp_footer', 'remove_submenu_if_single_auction');




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_mass_save_auction_items', 'handle_mass_save_auction_items');

function handle_mass_save_auction_items() {
    // Проверяем nonce и права пользователя (рекомендуется для безопасности)

    // Получаем данные
    $allFormsData = isset($_POST['allFormsData']) ? json_decode(stripslashes($_POST['allFormsData']), true) : array();

    $results = array();

    foreach ($allFormsData as $formData) {
        // Обрабатываем каждую форму
        $post_id = wp_insert_post(array(
            'post_title'    => sanitize_text_field($formData['auction_item_title']),
            'post_content'  => sanitize_textarea_field($formData['auction_item_description']),
            'post_type'     => 'auction_items',
            'post_status'   => 'publish',
            'meta_input'    => array(
                'product_id' => sanitize_text_field($formData['auction_item_sku']),
                // Можно добавить другие метаполя по необходимости
            ),
        ));

        // Проверяем на ошибки
        if (!is_wp_error($post_id)) {
            // Устанавливаем миниатюру, если указан ID изображения
            if (!empty($formData['auction_item_image_id'])) {
                set_post_thumbnail($post_id, absint($formData['auction_item_image_id']));
            }

            // Сохраняем current_auction_id как метаполе
            if (!empty($formData['current_auction_id'])) {
                update_post_meta($post_id, 'current_auction_id', absint($formData['current_auction_id']));
            }

            $results[] = array('post_id' => $post_id, 'status' => 'success');
        } else {
            $results[] = array('post_id' => 0, 'status' => 'error', 'message' => $post_id->get_error_message());
        }
    }

    // Возвращаем результат
    wp_send_json_success($results);
}


function upload_image_to_media_library() {
    if (!current_user_can('upload_files')) {
        wp_send_json_error(['message' => 'Недостаточно прав для выполнения операции']);
    }

    $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
    if (empty($image_url)) {
        wp_send_json_error(['message' => 'URL изображения не может быть пустым']);
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    // Download image to temporary file
    $tmp = download_url($image_url);
    if (is_wp_error($tmp)) {
        wp_send_json_error(['message' => 'Ошибка загрузки файла: ' . $tmp->get_error_message()]);
    }

    // Prepare an array of post data for the attachment.
    $file_array = [
        'name' => basename($image_url),
        'tmp_name' => $tmp,
    ];

    // Check for valid image
    $file_type = wp_check_filetype_and_ext($tmp, $image_url);
    if (!wp_match_mime_types('image', $file_type['type'])) {
        @unlink($tmp); // delete temporary file
        wp_send_json_error(['message' => 'Загружаемый файл не является допустимым изображением']);
    }

    // Upload the file into the WordPress Media Library.
    $attachment_id = media_handle_sideload($file_array, 0);

    // Check for handle sideload errors.
    if (is_wp_error($attachment_id)) {
        @unlink($file_array['tmp_name']); // delete temporary file
        wp_send_json_error(['message' => 'Ошибка при загрузке изображения: ' . $attachment_id->get_error_message()]);
    }

    $attachment_url = wp_get_attachment_url($attachment_id);

    wp_send_json_success(['attachment_id' => $attachment_id, 'url' => $attachment_url]);
}
add_action('wp_ajax_upload_image_to_media_library', 'upload_image_to_media_library');



function handle_add_auction_item_prod() {
    $post_id = isset($_POST['auction_item_id']) && $_POST['auction_item_id'] !== '' ? intval($_POST['auction_item_id']) : 0;
    $title = sanitize_text_field($_POST['auction_item_title']);
    $description = sanitize_textarea_field($_POST['auction_item_description']);
    $sku = sanitize_text_field($_POST['auction_item_sku']);
    $current_auction_id = absint($_POST['current_auction_id']);
    $image_id = isset($_POST['auction_item_image_id']) ? absint($_POST['auction_item_image_id']) : 0;
    $image_ids = explode(',', $_POST['image_ids']);


    $post_data = [
        'post_title'   => $title,
        'post_content' => $description,
        'post_type'    => 'auction_items',
        'post_status'  => 'publish',
        'meta_input'   => [
            'current_auction_id' => $current_auction_id,
            'product_id' => $sku,
        ],
    ];

    // Если post_id больше 0 и пост существует, обновляем его
    if ($post_id > 0 && get_post($post_id)) {
        $post_data['ID'] = $post_id;
        $updated_post_id = wp_update_post($post_data, true);
        if (is_wp_error($updated_post_id)) {
            wp_send_json_error(['message' => 'Ошибка при обновлении поста: ' . $updated_post_id->get_error_message()]);
            return;
        }
    } else {
        // Иначе создаем новый пост
        $post_id = wp_insert_post($post_data, true);
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Ошибка при создании поста: ' . $post_id->get_error_message()]);
            return;
        }
    }

    // Устанавливаем миниатюру, если есть $image_id
    if ($image_id > 0) {
        set_post_thumbnail($post_id, $image_id);
    }
    foreach ($image_ids as $index => $image_id) {
        if ($index === 0) {
            // Первое изображение делаем миниатюрой поста
            set_post_thumbnail($post_id, $image_id);
        } else {
            // Остальные изображения привязываем как вложения к посту
            wp_update_post([
                'ID' => $image_id,
                'post_parent' => $post_id
            ]);
        }
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

    wp_send_json_success(['auction_item_id' => $post_id]);
}

add_action('wp_ajax_add_auction_item_prod', 'handle_add_auction_item_prod');
add_action('wp_ajax_update_auction_item_prod', 'handle_add_auction_item_prod');



// Функция для удаления элемента аукциона
function handle_delete_auction_item() {

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

add_action('wp_ajax_delete_auction_item', 'handle_delete_auction_item');

add_action('wp_ajax_handle_update_images_order_and_thumbnail', 'handle_update_images_order_and_thumbnail');
function handle_update_images_order_and_thumbnail() {

    $postId = intval($_POST['post_id']);
    $imagesOrder = isset($_POST['images_order']) ? explode(',', $_POST['images_order']) : [];

    if (empty($imagesOrder)) {
        wp_send_json_error(['message' => 'Images order is empty.']);
        return;
    }

    if (current_user_can('edit_post', $postId)) {
        update_post_meta($postId, 'images_order', $imagesOrder);
        if (!empty($imagesOrder)) {
            set_post_thumbnail($postId, $imagesOrder[0]);
        }
        wp_send_json_success(['message' => 'Images order and thumbnail updated successfully.']);
    } else {
        wp_send_json_error(['message' => 'Error updating images order and thumbnail.']);
    }
}

add_action('wp_ajax_upload_auction_images', 'handle_upload_auction_images');
function handle_upload_auction_images() {

    $uploaded_images = [];
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    foreach ($_FILES['images']['name'] as $key => $value) {
        if ($_FILES['images']['name'][$key]) {
            $file = [
                'name'     => $_FILES['images']['name'][$key],
                'type'     => $_FILES['images']['type'][$key],
                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                'error'    => $_FILES['images']['error'][$key],
                'size'     => $_FILES['images']['size'][$key]
            ];
            $attachment_id = media_handle_sideload($file, 0);
            if (is_wp_error($attachment_id)) {
                wp_send_json_error('Ошибка при загрузке изображений.');
            } else {
                $uploaded_images[] = [
                    'id'  => $attachment_id,
                    'url' => wp_get_attachment_url($attachment_id)
                ];
            }
        }
    }

    wp_send_json_success($uploaded_images);
}


function get_highest_menu_order($current_auction_id) {
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

// обработчик перетаскивания и изменения порядка постов мышкой на тсранице Создания аукциона
add_action('wp_ajax_save_sorted_auction_items_order', 'handle_save_sorted_auction_items_order');
function handle_save_sorted_auction_items_order() {
    if (!check_ajax_referer('handle_auction_item_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $order = isset($_POST['order']) ? $_POST['order'] : array();
    if (!is_array($order)) {
        wp_send_json_error('Invalid order data');
        return;
    }

    foreach ($order as $menu_order => $item_id) {
        $item_id = intval($item_id);
        if (!$item_id) {
            continue;
        }
        wp_update_post([
            'ID' => $item_id,
            'menu_order' => $menu_order
        ]);
    }

    wp_send_json_success('Order updated successfully.');
}

// stop auction
function toggle_auction_status() {

    $auction_id = isset($_POST['auction_id']) ? intval($_POST['auction_id']) : 0;
    if (!$auction_id || !current_user_can('edit_post', $auction_id)) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }

    $stop_auction = get_field('stop_auction', $auction_id);
    $new_status = !$stop_auction;
    update_field('stop_auction', $new_status, $auction_id);

    global $wpdb;
    $items = get_posts([
        'post_type' => 'auction_items',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'current_auction_id',
                'value' => $auction_id,
                'compare' => '=',
            ],
        ],
    ]);

    foreach ($items as $item) {
        if ($new_status) { // Закрытие аукциона
            // Устанавливаем статус -2 для всех ставок без победителя
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `wp_auction_bids` SET `winner` = -2 WHERE `item_id` = %d AND `winner` = 0",
                    $item->ID
                )
            );
        } else { // Открытие аукциона
            // Возвращаем статус 0 для всех ставок с текущим статусом -2
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `wp_auction_bids` SET `winner` = 0 WHERE `item_id` = %d AND `winner` = -2",
                    $item->ID
                )
            );
        }
    }

    wp_send_json_success(['new_status' => $new_status]);
}
add_action('wp_ajax_toggle_auction_status', 'toggle_auction_status');


add_action('wp_ajax_save_current_auction', 'save_current_auction_callback');

function save_current_auction_callback() {
    // Получаем массив ID выбранных аукционов
    $auction_ids = isset($_POST['auction_id']) ? $_POST['auction_id'] : [];

    // Обновляем значение поля
    if (!empty($auction_ids) && is_array($auction_ids)) {
        update_field('current_auction', $auction_ids, 'option');
        wp_send_json_success('Аукционы успешно обновлены');
    } else {
        wp_send_json_error('ID аукционов не получены');
    }
}



