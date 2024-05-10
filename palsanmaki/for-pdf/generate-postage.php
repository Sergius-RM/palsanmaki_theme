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

    global $wpdb;
    $auction_id = intval($_GET['auction_id']);

    $date = get_field('auction_date', $auction_id);
    $time = get_field('auction_time', $auction_id);
    $location = get_field('auction_location', $auction_id);

    $table_bids = $wpdb->prefix . 'auction_bids';
    $table_users = $wpdb->base_prefix . 'users';
    $table_postage_info = $wpdb->prefix . 'auction_postage_info';

    $query = $wpdb->prepare("
        SELECT DISTINCT
            u.ID,
            u.display_name,
            u.user_email,
            um.meta_value AS address,
            um2.meta_value AS phone
        FROM $table_bids b
        INNER JOIN $table_users u ON b.user_id = u.ID
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'address'
        LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'phone'
        WHERE b.auction_id = %d AND b.winner = 1
        AND NOT EXISTS (
            SELECT 1 FROM {$wpdb->usermeta} um3 WHERE um3.user_id = u.ID AND um3.meta_key = 'banned' AND um3.meta_value = '1'
        )
        GROUP BY u.ID
        ORDER BY u.display_name ASC
    ", $auction_id);

    $winners = $wpdb->get_results($query);

    // Начало формирования HTML
    $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Postage List for Auction ' . $auction_id . '</title>
            <style>
                body { font-family: "DejaVu Sans", sans-serif; }
                h1 {font-size:18px; margin-bottom: 15px;}
                .auction_data {margin-bottom: 30px; padding-bottom: 20px; font-size: 12px; border-bottom:1px solid #333;}
                .auction_data span {margin-right:10px;}
                .winner-item {border:2px solid #e2e2e2; margin-bottom: 30px; padding:40px;}
                .winner-item, .user-info, .products-won { margin-bottom: 20px; }
                .total-info {font-size: 11px; margin-top: 10px;}
                .total-cost {font-size: 14px; margin-bottom: 20px;}
                .user-info h3 { font-size: 22px; margin: 0 0 10px 0; }
                .products-wоn h3 {margin-bottom: 10px; font-size: 11px;}
                .user-info {margin-bottom: 0px;}
                .user-data {padding-bottom: 20px; border-bottom:1px solid #333;}
                .user-data p {font-size: 12px; display: inline-block; margin-right: 10px; margin-bottom: 0; }
                .winner-product-info {border-bottom:1px solid #e4e4e4; margin-bottom: 4px; padding:15px 15px 10px 15px;}
                .winner-product-info .product-data {display: inline-block; margin-right: 20px; font-size: 11px;}
                .winner-product-info .product-data strong {font-size: 11px; font-style: italic;}
                .winner-product-info .product-data h4 {font-size: 14px; line-height: 1em; margin:0;}
                .agreement-info {font-size: 11px; font-style: italic; font-wight:600;}
                .agreement-info label {font-wight:600;}
                table { width: 100%; border-collapse: collapse; }
                td, th { padding: 8px; border: 1px solid #ddd; }
                hr {text-align:left; position: relative; left: 0px;}
                .winner-item {page-break-before: always; }
                .winner-item:first-child {page-break-before: auto; }
            </style>
        </head>
        <body>';


    $html .= '<div class="auction_postage_info_block ">';

    foreach ($winners as $winner) {

        $items_query = $wpdb->prepare("SELECT p.ID, p.post_title, b.bid_value, pm.meta_value AS product_id, pi.item_price, pi.fragile_add FROM {$wpdb->posts} p INNER JOIN $table_bids b ON p.ID = b.item_id LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'product_id' INNER JOIN $table_postage_info pi ON b.user_id = pi.user_id AND b.auction_id = pi.auction_id AND p.ID = pi.item_id WHERE b.user_id = %d AND b.winner = 1 AND b.auction_id = %d", $winner->ID, $auction_id);
        $items = $wpdb->get_results($items_query);

        // Фильтрация пользователей без товаров или с нулевыми значениями цены
        $items_with_price = array_filter($items, function ($item) {
            return $item->item_price > 0;
        });

        if (empty($items_with_price)) {
            continue; // Пропускаем пользователя, если у всех его товаров цена равна 0 или не указана
        }

        $html .= '<div class="winner-item">';
        
        $html .= '<h1>' . get_the_title($auction_id) . '</h1>';

        $html .= '<div class="auction_data">
                    
                    <span><strong>Pvm:</strong> ' . $date . '</span><br>
                    <span>Hirvaskankaan kauppahuone Oy 2913179-2</span>
                </div>';

        $html .= '<div class="user-info">
                <h3>' . esc_html($winner->display_name) . '</h3>
                <div class="user-data">
                    <p><strong>Osoite:</strong> ' . esc_html(get_user_meta($winner->ID, 'address', true)) . '</p>
                    <p><strong>Puhelin:</strong> ' . esc_html(get_user_meta($winner->ID, 'phone', true)) . '</p>
                    <p><strong>Sähköposti:</strong> ' . esc_html($winner->user_email) . '</p>
                        <br>
                    <p><strong>Postinumero:</strong> ' . esc_html(get_user_meta($winner->ID, 'zip_code', true)) . '</p>
                    <p><strong>Paikkakunta:</strong> ' . esc_html(get_user_meta($winner->ID, 'city', true)) . '</p>
                </div>
            </div>';

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, b.bid_value, pm.meta_value AS product_id,
            pi.item_price, pi.fragile_add
            FROM {$wpdb->posts} p
            INNER JOIN {$table_bids} b ON p.ID = b.item_id
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'product_id'
            INNER JOIN $table_postage_info pi ON b.user_id = pi.user_id AND b.auction_id = pi.auction_id AND p.ID = pi.item_id
            WHERE b.user_id = %d AND b.winner = 1 AND b.auction_id = %d
        ", $winner->ID, $auction_id));

        if ($items) {
            $html .= '<div class="products-wоn">
                        <h4>Voitetut huudot</h4>';

            $postageInfo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_postage_info WHERE user_id = %d AND auction_id = %d", $winner->ID, $auction_id), ARRAY_A);

            foreach ($items as $item) {
                $html .= '<div class="winner-product-info">
                            <div>
                                <div class="product-data"><strong>' . esc_html($item->product_id) . '</strong>
                                    <h4>' . esc_html($item->post_title) . '</h4>
                                </div>
                                <div class="product-data">Ennakkohuuto: ' . esc_html($item->bid_value) . ' €</div>
                            </div>
                            <div>
                                <div class="product-data">
                                    <strong>Hinta:</strong> <span> ' . esc_html($item->item_price) . ' €</span>
                                </div>
                                <div class="product-data">
                                    <strong>Särkyvä lisä:</strong> <span> ' . esc_html($item->fragile_add) . ' €<span>
                                </div>
                            </div>
                        </div>';
            }

            $html .= '</div>';
        }

        $termsAcceptedChecked = $postageInfo['terms_accepted'] == '1' ? 'checked' : '';
        $normalPostageChecked = $postageInfo['normal_postage'] == '1' ? 'checked' : '';
        $willPickupChecked = $postageInfo['will_pickup'] == '1' ? 'checked' : '';

        $html .= '<div class="total-info">
                    <strong>Hinnat yhteensä: </strong> <span class="items-total-price">' . esc_html($postageInfo['all_products_price']) . ' €</span><br>
                    <strong>Postimaksut: </strong> <span class="postage_cost">' . esc_html($postageInfo['postage_cost']) . ' €</span><br>
                    <strong>Postimaksut särkyvälisällä: </strong> <span class="postage-total">' . esc_html($postageInfo['postage-total']) . ' €</span><br><br>
                </div>
                <div class="total-cost">
                    <strong>Yhteensä normaali pakettina: <span class="summa_and_postage">' . esc_html($postageInfo['normal_postage_price']) . ' €</span></strong>
                </div>
                <div class="total-cost">
                    <strong>Yhteensä särykyvälisällä: <span class="grand-total">' . esc_html($postageInfo['total_price']) . ' €</span></strong>
                </div>';
        $html .= '<div class="agreement-info">
                <label>
                    Haen sen henkilökohtaisesti <input type="checkbox" name="will_pickup" ' . $willPickupChecked . ' disabled>
                </label>
                <label>
                    Normaali paketti <input type="checkbox" name="normal_postage" ' . $normalPostageChecked . ' disabled>
                </label>
                <label>
                    Särkyvälisällä <input type="checkbox" name="terms_accepted" ' . $termsAcceptedChecked . ' disabled>
                </label>
            </div>';

    $html .= '</div>';


    }

    $html .= '</div>';

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
