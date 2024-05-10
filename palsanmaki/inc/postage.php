<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


add_action('wp_ajax_save_postage_info', 'save_postage_info');

function save_postage_info() {
    check_ajax_referer('save_postage_info_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
    }
    global $wpdb;
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$auctionId = isset($_POST['auction_id']) ? intval($_POST['auction_id']) : 0;
$itemsData = isset($_POST['items']) ? $_POST['items'] : array();
$totalInfo = isset($_POST['total_info']) ? $_POST['total_info'] : array();

$tableName = $wpdb->prefix . 'auction_postage_info';

foreach ($itemsData as $item) {
    $itemId = intval($item['item_id']);
    $itemPrice = floatval($item['item_price']);
    $fragileAdd = floatval($item['fragile_add']);
    $willPickup = intval($item['will_pickup']);
    $normalPostage = intval($item['normal_postage']);
    $termsAccepted = intval($item['terms_accepted']);

    $existing_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$tableName} WHERE auction_id = %d AND user_id = %d AND item_id = %d",
        $auctionId, $userId, $itemId
    ));

    $data = array(
        'auction_id' => $auctionId,
        'user_id' => $userId,
        'item_id' => $itemId,
        'item_price' => $itemPrice,
        'fragile_add' => $fragileAdd,
        'will_pickup' => $willPickup,
        'normal_postage' => $normalPostage,
        'terms_accepted' => $termsAccepted,
        // Add additional fields if needed
    );
    $format = array('%d', '%d', '%d', '%f', '%f', '%d', '%d');

    if ($existing_id) {
        $wpdb->update($tableName, $data, array('id' => $existing_id), $format, array('%d'));
    } else {
        $wpdb->insert($tableName, $data, $format);
    }
}

// Update the total info
$wpdb->update(
    $tableName,
    array(
        'postage_cost' => floatval($totalInfo['postage_cost']),
        'normal_postage_price' => floatval($totalInfo['summa_and_postage']),
        'total_price' => floatval($totalInfo['grand_total']),
        'postage-total' => floatval($totalInfo['postage_total']),
        'all_products_price' => floatval($totalInfo['items_total_price']),
    ),
    array('user_id' => $userId, 'auction_id' => $auctionId),
    array('%f', '%f'),
    array('%d', '%d')
);

wp_send_json_success('Data saved successfully.');
}


// отправка писем
function send_invoice_email($user_id, $auction_id, $email_content) {
    // Подготовка данных для отправки
    $user_info = get_userdata($user_id);
    $email_to = $user_info->user_email;
    $subject = 'Lasku huutokauppaan osallistumisesta';

    // Формирование URL для подтверждения
    $accept_url = home_url('/vahvistus/?action=accept_terms&user_id=' . $user_id . '&auction_id=' . $auction_id);
    $pickup_url = home_url('/vahvistus/?action=will_pickup&user_id=' . $user_id . '&auction_id=' . $auction_id);

    // Добавление ссылок в содержимое письма
    $message = $email_content;
    $message .= "\n\nVahvista käyttöehdot: <a href='" . $accept_url . "'>Asiakas on hyväksynyt</a>";
    $message .= "\nVahvista nouto: <a href='" . $pickup_url . "'>Asiakas noutaa tuotteет</a>";
    $message .= "\Kiitos!";

    // Логирование содержимого письма перед отправкой
    error_log("Отправка письма на $email_to: $message");

    // Отправка письма
    wp_mail($email_to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}



// Функция отправки писем пользователям
function send_emails_to_winners($winners, $auction_id) {
    foreach ($winners as $winner) {
        $confirm_url = home_url('/vahvistus/?action=accept_terms&user_id=' . $winner->ID . '&auction_id=' . $auction_id);
        $pickup_url = home_url('/vahvistus/?action=confirm_pickup&user_id=' . $winner->ID . '&auction_id=' . $auction_id);

        $subject = "Your auction winnings";
        $message = "Dear {$winner->display_name},\n\nHere are your auction winnings...\n\n";
        $message .= "Please confirm your agreement: <a href='{$confirm_url}'>Asiakas on hyväksynyt</a>\n";
        $message .= "Please confirm your pickup: <a href='{$pickup_url}'>Asiakas noutaa tuotteet</a>\n\n";
        $message .= "Thank you!";

        wp_mail($winner->user_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
}

// скрипт индивидуальной рассылки счетов
function send_personal_invoice_ajax_handler() {
    $user_id = intval($_POST['user_id']);
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $auction_id = intval($_POST['auction_id']);
    $items = $_POST['items'];
    $itemsTotalPrice = $_POST['items_total_price'];
    $postageCost = $_POST['postage_cost'];
    $postageTotal = $_POST['postage_total'];
    $normalPostage = $_POST['summa_and_postage'];
    $grandTotal = $_POST['grand_total'];

    $user_info = get_userdata($user_id);
    $user_name = $user_info->display_name;
    $user_email = $user_info->user_email;

    $pickup_url = home_url('/vahvistus/?action=will_pickup&user_id=' . $user_id . '&auction_id=' . $auction_id);
    $normal_postage_url = home_url('/vahvistus/?action=normal_postage&user_id=' . $user_id . '&auction_id=' . $auction_id);
    $accept_url = home_url('/vahvistus/?action=accept_terms&user_id=' . $user_id . '&auction_id=' . $auction_id);

    $postage_comment = get_field('postage_comment', $post_id, false);
    $postage_comment = html_entity_decode(stripslashes($postage_comment));

    // Формирование содержимого письма
    $email_content = '<h1>Hei!</h1>';
    $email_content .= '<p>Teille tuli seuraavat tavarat huutokaupasta, onneksi olkoon!</p>';
    $email_content .= '<ul>';

    foreach ($items as $item) {
        $email_content .= '<li>' . esc_html($item['item_title']) . ': Hinta - ' . esc_html($item['item_price']) . '€</li>';
    }

    $email_content .= '</ul>
                       <div style="margin-bottom:30px;">
                       <h3><strong>KLIKKAA ALLA OLEVA LINKKI JONKA TOIMITUKSEN VALITSET!</strong></h3>
                       <p style="margin-bottom:10px;"><strong>Hinnat yhteensä noudettuna:</strong> ' . $itemsTotalPrice . '€</p>
                       <a href="' . $pickup_url . '">Haen sen henkilökohtaisesti</a>
                       </div>

                       <div style="margin-bottom:30px;">
                       <p style="margin-bottom:10px;"><strong>Hinnat yhteensä normaalipakettina:</strong> ' . $normalPostage . '€</p>
                        <a href="' . $normal_postage_url . '">Lähetä tilaukseni yksinkertaisena pakettina</a>
                        </div>

                        <div>
                        <p style="margin-bottom:10px;"><strong>Hinnat yhteensä särkyvälisällä:</strong> ' . $grandTotal . '€</p>
                        <a href="' . $accept_url . '">Lähetä tilaukseni herkänä</a>
                        </div>

                        <div>
                        <p style="margin-bottom:10px;">Laskun tulisi olla maksettu kolmen päivän kuluessa.<br>
                        Voitte myös maksaa tuotteet käteisellä noudettaessa.<br>
                        Olemme paikalla huutokaupalla osoite Tervatehtaantie 93, 41290 Kangashäkki</p>
                        </div>

                        <div>' . $postage_comment . '</div>
                        ';

    $subject = "Sinut on valittu Huutokauppa Palsanmäen voittajaksi!"; // Тема письма
    $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Huutokauppa Palsanmäki <noreply@infodoza.com>'); // Заголовки

    // Пытаемся отправить письмо
    $mail_sent = wp_mail($user_email, $subject, $email_content, $headers);

    // Проверяем, было ли письмо отправлено успешно
    if ($mail_sent) {
        // Если письмо отправлено, обновляем метаполе пользователя
        update_user_meta($user_id, 'invoice_sent_for_auction_' . $auction_id, true);

        // Возвращаем успешный ответ
        wp_send_json_success([
            'message' => $email_content,
            'userName' => $user_name,
            'userEmail' => $user_email,
            'subject' => $subject,
            'from' => 'noreply@infodoza.com'
        ]);

    } else {
        // Если письмо не отправлено, возвращаем ошибку
        wp_send_json_error([
            'message' => 'Failed to send invoice.'
        ]);
    }

    wp_die();
}
add_action('wp_ajax_send_personal_invoice_ajax', 'send_personal_invoice_ajax_handler');


// скрипт массовой рассылки счетов
function send_mass_personal_invoices_ajax_handler() {
    // Проверяем, переданы ли данные победителей
    if (!isset($_POST['winners_data']) || !is_array($_POST['winners_data'])) {
        wp_send_json_error('No winners data provided.');
        wp_die();
    }

    $winners_data = $_POST['winners_data'];
    $auction_id = intval($_POST['auction_id']);
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $emails_sent = 0; // Счётчик успешно отправленных писем

    $postage_comment = get_field('postage_comment', $post_id, false);
    $postage_comment = html_entity_decode(stripslashes($postage_comment));

    foreach ($winners_data as $winner_data) {
        $user_id = intval($winner_data['user_id']);
        $user_info = get_userdata($user_id);
        if (!$user_info) continue;

        $filtered_items = array_filter($winner_data['items'], function($item) {
            return floatval($item['item_price']) > 0;
        });
        // Пропускаем пользователя, если у него нет товаров с ненулевой ценой
        if (empty($filtered_items)) continue;

        $user_name = $user_info->display_name;
        $user_email = $user_info->user_email;

        // Формирование содержимого письма
        $email_content = "<h1>Hei!</h1>
                            <p>Teille tuli seuraavat tavarat huutokaupasta, onneksi olkoon!</p>
                            <ul>";
        foreach ($winner_data['items'] as $item) {
            $email_content .= "<li>{$item['item_title']}: Hinta - {$item['item_price']}€</li>";
        }
        $email_content .= "</ul>";

        // Добавляем ссылки для подтверждения
        $pickup_url = home_url("/vahvistus/?action=will_pickup&user_id={$user_id}&auction_id={$auction_id}");
        $normal_postage_url = home_url('/vahvistus/?action=normal_postage&user_id=' . $user_id . '&auction_id=' . $auction_id);
        $accept_url = home_url("/vahvistus/?action=accept_terms&user_id={$user_id}&auction_id={$auction_id}");

        $email_content .= "
                        <div style='margin-bottom:30px;'>
                        <h3><strong>KLIKKAA ALLA OLEVA LINKKI JONKA TOIMITUKSEN VALITSET!</strong></h3>
                        <p style='margin-bottom:10px;'><strong>Hinnat yhteensä noudettuna:</strong> {$winner_data['items_total_price']}€</p>
                        <a href='{$pickup_url}'>Haen sen henkilökohtaisesti</a>
                        </div>

                        <div style='margin-bottom:30px;'>
                        <p style='margin-bottom:10px;'><strong>Hinnat yhteensä normaalipakettina:</strong> {$winner_data['summa_and_postage']}€</p>
                        <a href='{$normal_postage_url}'>Lähetä tilaukseni yksinkertaisena pakettina</a>
                        </div>

                        <div>
                        <p style='margin-bottom:10px;'><strong>Hinnat yhteensä särkyvälisällä:</strong> {$winner_data['grand_total']}€</p>
                        <a href='{$accept_url}'>Lähetä tilaukseni herkänä</a>
                        </div>

                        <div>
                        <p style='margin-bottom:10px;'>Laskun tulisi olla maksettu kolmen päivän kuluessa.<br>
                        Voitte myös maksaa tuotteet käteisellä noudettaessa.<br>
                        Olemme paikalla huutokaupalla osoite Tervatehtaantie 93, 41290 Kangashäkki</p>
                        </div>

                        <div>{$postage_comment}</div>
                        ";
        // Отправка письма
        $subject = "Sinut on valittu Huutokauppa Palsanmäen voittajaksi!";
        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Huutokauppa Palsanmäki <noreply@infodoza.com>');
        if (wp_mail($user_email, $subject, $email_content, $headers)) {
            $emails_sent++;
        }
    }

    wp_send_json_success(['emails_sent' => $emails_sent]);
    wp_die();
}
add_action('wp_ajax_send_mass_personal_invoices_ajax', 'send_mass_personal_invoices_ajax_handler');


// Функция для обновления поля комментария письма
add_action('wp_ajax_save_postage_comment', 'save_postage_comment_callback');
function save_postage_comment_callback() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $new_comment = isset($_POST['postage_comment']) ? $_POST['postage_comment'] : '';

    if (function_exists('update_field')) {
        update_field('field_65eb284a47b73', $new_comment, $post_id);
    } else {
        $wpdb->update(
            $wpdb->postmeta,
            array('meta_value' => $new_comment),
            array('post_id' => $post_id, 'meta_key' => 'postage_comment')
        );
    }

    echo $new_comment;
    wp_die();
}

function save_user_comment() {
    // Проверка на валидность nonce
    if (!isset($_POST['user_comment_nonce_field']) || !wp_verify_nonce($_POST['user_comment_nonce_field'], 'user_comment_nonce')) {
        wp_die('Security check failed');
    }

    // Проверка на наличие нужных данных
    if (isset($_POST['user_comment'], $_POST['user_id'], $_POST['auction_id'])) {
        global $wpdb;
        $tableName = $wpdb->prefix . 'auction_postage_info';
        $user_id = intval($_POST['user_id']);
        $auction_id = intval($_POST['auction_id']);
        $comment = sanitize_textarea_field($_POST['user_comment']);

        // Проверка, существует ли уже запись для этого пользователя и аукциона
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM `$tableName` WHERE user_id = %d AND auction_id = %d",
            $user_id,
            $auction_id
        ));

        if ($existing) {
            // Обновление комментария, если запись существует
            $wpdb->update($tableName, ['user_comment' => $comment], ['user_id' => $user_id, 'auction_id' => $auction_id]);
        } else {
            // Вставка новой записи, если таковая отсутствует
            $wpdb->insert($tableName, [
                'user_id' => $user_id,
                'auction_id' => $auction_id,
                'user_comment' => $comment
            ]);
        }

        // Перенаправляем пользователя обратно на страницу
        wp_redirect($_SERVER['HTTP_REFERER'] . '&comment_saved=1');
        exit;
    }
}
add_action('admin_post_save_user_comment', 'save_user_comment');
add_action('admin_post_nopriv_save_user_comment', 'save_user_comment');

