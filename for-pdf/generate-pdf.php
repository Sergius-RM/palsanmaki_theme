<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php'); // Для доступа к функциям WordPress
require_once get_template_directory() . '/dompdf/autoload.inc.php'; // Подключение Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// Инициализация Dompdf с опциями для поддержки кириллицы
$options = new Options();
$options->set('isRemoteEnabled', TRUE); // Включить загрузку удаленных изображений
$options->set('defaultFont', 'DejaVu Sans'); // Шрифт для поддержки кириллицы

$dompdf = new Dompdf($options);

// Проверка, есть ли ID аукциона
if (isset($_GET['auction_id']) && is_numeric($_GET['auction_id'])) {
    $auction_id = intval($_GET['auction_id']);

    $date = get_field('auction_date', $auction_id);
    $time = get_field('auction_time', $auction_id);
    $location = get_field('auction_location', $auction_id);

    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';
    $auction_items = get_posts([
        'post_type' => 'auction_items',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num', // Сортировка по числовому полю
        'meta_key' => 'title_number', // Имя числового поля
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => 'current_auction_id',
                'value' => $auction_id,
                'compare' => '='
            ]
        ],
        'post__in' => $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT item_id FROM $table_name WHERE auction_id = %d",
            $auction_id
        ))
    ]);

    // Начало формирования HTML
    $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: "DejaVu Sans", sans-serif;
                }
                h1 {
                    color: #000;
                    font-size: 22px;
                }
                .auction_data {
                    font-size: 12px;
                    margin-bottom:30px;
                }
                .auction_data span {
                    margin-right: 30px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, tr, td {
                    border: 0;
                }
                tr, td {
                    padding: 10px 5px;
                    text-align: left;
                    vertical-align: top;
                }
                tr {
                    border-bottom: 1px solid #e4e4e4;
                    margin-bottom: 30px;
                }
1
                .coll-1 {
                    width: 60%;
                }
                .coll-1 p {
                    font-size: 12px;
                    margin: 0;
                    margin-bottom: 5px;
                }
                .product_id {
                    font-size: 11px !important;
                    font-style: italic;
                    margin-bottom: 5px;
                    line-height: 1
                }
                .description {
                    font-size: 11px !important;
                }
                h4 {
                    font-size: 14px;
                    margin: 15px 0 10px 0;
                    line-height: 14px;
                }

                .coll-2 {
                    width: 40%;
                }
                ol {
                    padding-left: 20px;
                    margin: 0;
                }
                ol li {
                    font-size: 11px;
                    font-weight: 500;
                    line-height: 14px;
                    margin-bottom: 5px;
                }
            </style>
        </head>
        <body>';
    $html .= '<h1>' . get_the_title($auction_id) . '</h1>';

    $html .= '<div class="auction_data">
                    <span><strong>Date:</strong> ' . $date . '</span>
                    <span><strong>Time:</strong> ' . $time . '</span>
                    <span><strong>Location:</strong> ' . $location . '</span>
                </div>';

    $html .= '<table>';
    foreach ($auction_items as $item) {
        $item_id = $item->ID;
        $product_id = get_post_meta($item->ID, 'product_id', true);
        $thumbnail_url = get_the_post_thumbnail_url($item->ID); // Получение полного URL изображения
        $description = get_post_field('post_content', $item->ID);
        $bids = $wpdb->get_results($wpdb->prepare(
            "SELECT ab.* FROM $table_name ab
            LEFT JOIN $wpdb->usermeta um ON ab.user_id = um.user_id AND um.meta_key = 'banned'
            WHERE item_id = %d AND (um.meta_value IS NULL OR um.meta_value != '1')
            ORDER BY ab.bid_value DESC, ab.winner DESC
            LIMIT 5",
            $item->ID
        ));

        // Вставка изображения и текста в HTML
        $html .= '<tr><td class="coll-1"><p class="product_id">Tuotetunnus: ' . $item_id . '</p><p class="product_id">Asiakasnumero: ' . $product_id . '</p><h4>' . get_the_title($item->ID) . '</h4><p class="description">' . $description . '</p></td>';
        $html .= '<td class="coll-2"><ol>';
        foreach ($bids as $bid) {
            $user_info = get_userdata($bid->user_id);
            $winner_status = $bid->winner; // Получение статуса победителя для текущей ставки

            // Формирование строки ставки с учетом статуса победителя
            $bid_line = esc_html($user_info->display_name) . ' - ' . esc_html($bid->bid_value) . '€';

            // Добавление специфической информации в зависимости от статуса победителя
            if ($winner_status == 1) {
                $bid_line .= ' (' . __('Voittaja', 'default') . ')';
            }

            $html .= '<li>' . $bid_line . '</li>';
        }
        $html .= '</ol></tr>';
    }
    $html .= '</table>';
    $html .= '</body></html>';

    // Генерация PDF
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("auction-$auction_id.pdf", ["Attachment" => false]);
    exit;
} else {
    echo 'Huutokaupan tunnusta ei ole määritetty';
}
?>
