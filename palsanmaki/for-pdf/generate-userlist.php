<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
require_once get_template_directory() . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', TRUE);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$current_auctions = get_field('current_auction', 'option');

// Проверяем, что мы получили хотя бы один ID аукциона
if (empty($current_auctions)) {
    echo 'No active auctions found.';
    exit;
}

// Убедитесь, что у нас есть массив ID аукционов
if (!is_array($current_auctions)) {
    $current_auctions = [$current_auctions];
}

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auctions Data</title>
    <style>
                body { font-family: "DejaVu Sans", sans-serif; font-size: 12px;}
                h1 {font-size: 22px;}
                .pdf_userdata_item {page-break-before: always; pappdng}
                .pdf_userdata_item:first-child {page-break-before: auto; }
                .auction_data {font-weight: 600; margin-bottom: 30px; }
                .auction_data span {margin-right: 30px;}
                .userdata_item {width: 100%; padding: 30px; border: 1px solid #333; }
                td {width: 50%;}
                .maksetaan td {width: 100%;}
                td strong {min-width:100px; font-size: 11px; display:inline-block;}
                td span {width:50%; display:inline-block; border-bottom: 1px solid #333}
                .supplier_data {padding-bottom: 20px;}
                .asiakasnumero_id {margin-right: 30px;}
                .product_item td {padding-left:30px;}
                .product_item td:first-child {font-style: italic;}
                .total_price td {padding-top: 50px;}
                .product_item {background: #f7f7f7;}
                tr td:nth-child(2n+1) {padding-left: 20px;}
                .allekirjoitus td {padding: 50px 0 30px 0;}
            </style>
</head>
<body>';

// Перебираем все ID аукционов
foreach ($current_auctions as $auction_id) {
    // Получаем данные аукциона
    $title = get_the_title($auction_id);
    $date = get_field('auction_date', $auction_id);
    $time = get_field('auction_time', $auction_id);
    $location = get_field('auction_location', $auction_id);

    $unique_product_ids = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'product_id' AND p.post_parent = %d
        ORDER BY CAST(pm.meta_value AS UNSIGNED)
    ", $auction_id));

    $html .= '<div class="pdf_userdata_item" >';
    foreach ($unique_product_ids as $product_id) {

        if (!empty($product_id)) {
        $html .= '<div class="pdf_userdata_item" ><h1>' . get_the_title($auction_id) . '</h1>';
        $html .= '<p class="auction_data"><span>pvm: ' . $date . '</span><span>klo: ' . $time . '</span><span>paikka: ' . $location . '</span></p>';
        $html .= '<table class="userdata_item">
                    <tr><td class="supplier_data"><p><strong class="asiakasnumero_id">Asiakasnumero: </strong> ' . esc_html($product_id) . '</p></td>
                         <td class="supplier_data"><p><strong>Nimi:</strong> <span></span></p></td></tr>';

        $products = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = 'product_id' AND pm.meta_value = %s AND p.post_parent = %d
        ", $product_id, $auction_id));
        foreach ($products as $product) {
            $html .= '<tr class="product_item">
                        <td>' . esc_html($product->post_title) . '</td>
                        <td><p><strong>Hinta:</strong> <span></span></p></td>
                    </tr>';
        }

        $html .= '<tr class="total_price"><td><p><strong>Yhteensä:</strong> <span></span></p></td></tr>
                    <tr class="provisio"><td><p><strong>Provisio 33%:</strong> <span></span></p></td></tr>
                    <tr class="maksetaan"><td><p><strong>Maksetaan asiakkaalle:</strong> <span></span></p></td></tr>';
        $html .= '<tr class="allekirjoitus"><td><p><strong>Pvm:</strong> <span></span></p></td>
                    <td><p><strong>Allekirjoitus:</strong> <span></span></p></td></tr>';
        $html .= '</table>';
    }
    }
    $html .= '</div>';

}

$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("auctions-data.pdf", ["Attachment" => false]);

?>