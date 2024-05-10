<?php
/**
 * Pagination Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Dompdf install
// Путь к файлу autoload.php относительно корневой директории сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/dompdf/vendor/autoload.php';


// Добавляем новый эндпоинт для создания PDF
function custom_pdf_endpoint() {
    add_rewrite_rule('^generate-pdf/([0-9]+)/?$', 'index.php?generate_pdf=1&auction_id=$matches[1]', 'top');
}
add_action('init', 'custom_pdf_endpoint');

// Добавляем новую переменную запроса для обработки PDF
function custom_pdf_query_vars($vars) {
    $vars[] = 'generate_pdf';
    $vars[] = 'auction_id';
    return $vars;
}
add_filter('query_vars', 'custom_pdf_query_vars');

// Перехватываем запрос и генерируем PDF
function custom_pdf_template_redirect() {
    $generate_pdf = get_query_var('generate_pdf');
    $auction_id = get_query_var('auction_id');

    if ($generate_pdf && $auction_id) {
        if (!current_user_can('manage_options')) {
            error_log('Пользователь не имеет прав администратора');
            exit; // Только администраторы могут скачивать PDF
        }

        // Получаем данные аукциона и ставок
        global $wpdb;
        $table_name = $wpdb->prefix . 'auction_bids';
        $auction_items = get_posts(array(
            'post_type' => 'auction_items',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'current_auction_id',
                    'value' => $auction_id,
                    'compare' => '='
                )
            )
        ));

        // Начинаем формировать HTML для PDF
        $html = '<h1>' . get_the_title($auction_id) . '</h1>';
        $html .= '<table>';
        foreach ($auction_items as $item) {
            $product_id = get_post_meta($item->ID, 'product_id', true);
            $bids = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE item_id = %d ORDER BY bid_value DESC LIMIT 5",
                $item->ID
            ));

            $html .= '<tr><td>' . get_the_title($item->ID) . '</td><td>' . $product_id . '</td></tr>';
            foreach ($bids as $bid) {
                $user_info = get_userdata($bid->user_id);
                $html .= '<tr><td>' . esc_html($user_info->display_name) . '</td><td>' . esc_html($bid->bid_value) . '€</td></tr>';
            }
        }
        $html .= '</table>';

        // Для отладки сохраняем HTML
        file_put_contents('last_pdf.html', $html);

        // Логируем данные о ставках
        error_log(print_r($bids, true));

        // Генерируем PDF из HTML
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Отправляем PDF в браузер
        $dompdf->stream('auction-' . $auction_id . '.pdf', array("Attachment" => 0));

        exit;
    }
}
add_action('template_redirect', 'custom_pdf_template_redirect');
