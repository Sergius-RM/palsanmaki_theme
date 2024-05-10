<?php
/**
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// В functions.php
add_action('wp_ajax_update_user_info', 'update_user_info');

function update_user_info() {
    // Проверка nonce для безопасности
    check_ajax_referer('update_user_info_nonce', 'security');

    $user_id = get_current_user_id();

    if (isset($_POST['first_name'])) {
        wp_update_user([
            'ID' => $user_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'user_email' => sanitize_email($_POST['email']),
        ]);

        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
        update_user_meta($user_id, 'zip_code', sanitize_text_field($_POST['zip_code']));
        update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));

        wp_send_json_success([
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'phone' => get_user_meta($user_id, 'phone', true),
            'email' => get_userdata($user_id)->user_email,
            'address' => get_user_meta($user_id, 'address', true),
            'zip_code' => get_user_meta($user_id, 'zip_code', true),
            'city' => get_user_meta($user_id, 'city', true)
        ]);
    } else {
        wp_send_json_error('No data provided');
    }
}


// Функция отправки письма с подтверждением
function send_verification_email($user_email, $user_id) {
    $user = get_userdata($user_id);
    $password = get_user_meta($user_id, 'unverified_password', true); // Получаем временный пароль

    $verification_link = add_query_arg([
        'verify_email' => $user_email,
        'token' => sha1($user_id . $user_email . $password)
    ], home_url('/authorization/'));

    // Добавляем адрес электронной почты и пароль в тело письма
    $message = sprintf(
        "Hei,\n\nKlikkaa tätä linkkiä vahvistaaksesi sähköpostiosoitteesi: %s\n\nSähköposti: %s\nSalasana: %s",
        $verification_link,
        $user_email,
        $password
    );

    // Отправляем письмо
    wp_mail($user_email, 'Vahvista sähköpostiosoitteesi', $message);
}


// Функция для регистрации пользователя
function my_user_registration() {
    if (isset($_POST['email']) && !is_user_logged_in()) {
    $password = wp_generate_password();
    $user_login = sanitize_user($POST['first_name'] . '' . $_POST['last_name']);
    $user_email = sanitize_email($_POST['email']);    $user_data = [
        'user_login' => $user_login,
        'user_email' => $user_email,
        'user_pass' => $password,
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        // 'role' => 'subscriber' // Если нужно, установите роль пользователя
    ];

    $user_id = wp_insert_user($user_data);

        if (!is_wp_error($user_id)) {
            // Сохраняем дополнительные данные
            update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
            update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
            update_user_meta($user_id, 'zip_code', sanitize_text_field($_POST['zip_code']));
            update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
            update_user_meta($user_id, 'has_verified_email', false); // Устанавливаем флаг, что емейл не подтверждён

            // Отправляем письмо с подтверждением
            send_verification_email($user_email, $user_id);

            // Здесь не авторизуем пользователя и не перенаправляем
        } else {
            // Вывод ошибки создания пользователя
            echo '<div class="error_allert">' . $user_id->get_error_message() . '</div>';
        }
    }
}

// Функция для активации пользователя
function my_user_activation() {
    if (isset($_GET['verify_email']) && isset($_GET['token'])) {
        $user_email = $_GET['verify_email'];
        $user = get_user_by('email', $user_email);

        if ($user && sha1($user->ID . $user_email . get_user_meta($user->ID, 'unverified_password', true)) === $_GET['token']) {
            // Удаляем метаданные, которые использовались для подтверждения
            delete_user_meta($user->ID, 'unverified_password');
            delete_user_meta($user->ID, 'has_verified_email');

            // Обновляем роль пользователя на стандартную роль 'subscriber' или другую, которая позволяет доступ к сайту
            $user_id = wp_update_user(array('ID' => $user->ID, 'role' => 'subscriber'));

            // Проверяем, успешно ли обновлена роль пользователя
            if (!is_wp_error($user_id)) {
                // Перенаправляем на страницу входа с сообщением об успешной активации
                wp_redirect(home_url('/authorization/?verified=true'));
                exit;
            } else {
                // Обрабатываем возможные ошибки
                wp_redirect(home_url('/registration/?verification_failed=true'));
                exit;
            }
        } else {
            // Перенаправляем на страницу регистрации с ошибкой, если токен не совпадает
            wp_redirect(home_url('/registration/?verification_failed=true'));
            exit;
        }
    }
}
add_action('template_redirect', 'my_user_activation');


add_action('init', 'add_pending_verification_role');
function add_pending_verification_role() {
    add_role('pending_verification', __('Pending Verification'), array());
}

add_action('wp_scheduled_delete_pending_accounts', 'delete_pending_accounts_daily');

function delete_pending_accounts_daily() {
    $users = get_users(array(
        'role' => 'pending_verification',
        'fields' => array('ID', 'user_registered'),
    ));

    foreach ($users as $user) {
        $registered_date = strtotime($user->user_registered);
        $current_time = current_time('timestamp');
        $diff = $current_time - $registered_date;

        // Если аккаунту больше суток (86400 секунд)
        if ($diff > 86400 && !get_user_meta($user->ID, 'has_verified_email', true)) {
            require_once(ABSPATH.'wp-admin/includes/user.php');
            wp_delete_user($user->ID);
        }
    }
}

if (!wp_next_scheduled('wp_scheduled_delete_pending_accounts')) {
    wp_schedule_event(time(), 'daily', 'wp_scheduled_delete_pending_accounts');
}
