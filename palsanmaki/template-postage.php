<?php
/**
 * Template name: Postage page
 */

get_header();

global $wpdb;

$current_auction_id = isset($_GET['auction_id']) ? absint($_GET['auction_id']) : 0;

$current_auction_title = get_the_title($current_auction_id);
$current_auction_date = get_field('auction_date', $current_auction_id);
$current_auction_time = get_field('auction_time', $current_auction_id);
$current_auction_location = get_field('auction_location', $current_auction_id);

    // Подготовка запроса к базе данных для получения сохраненных данных
    $tableName = $wpdb->prefix . 'auction_postage_info';
    $query = $wpdb->prepare("
        SELECT * FROM `$tableName`
        WHERE auction_id = %d
    ", $current_auction_id);

    // сбор данных (если есть) для заполнения формы
    $savedItemsData = $wpdb->get_results($query, OBJECT_K);
    $userItemsData = [];
    foreach ($savedItemsData as $data) {
        $userId = $data->user_id;
        if (!isset($userItemsData[$data->user_id])) {
            $userItemsData[$userId] = [
                'items' => [],
                'total_info' => [
                    'item_price' => '',
                    'fragile_add' => '',
                    'postage_cost' => '',
                    'terms_accepted' => $data->terms_accepted,
                    'normal_postage' => $data->normal_postage,
                    'will_pickup' => $data->will_pickup
                ]
            ];
        }
        $userItemsData[$data->user_id]['items'][$data->item_id] = $data;
        $userItemsData[$data->user_id]['total_info']['postage_cost'] = $data->postage_cost;
    }

    // Запрос для получения победителей, которые не забанены
    $table_bids = $wpdb->prefix . 'auction_bids'; // замените на имя вашей таблицы сделок
    $table_users = $wpdb->base_prefix . 'users';
    $winners_data = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT u.ID, u.user_email, u.display_name
         FROM {$table_bids} b
         INNER JOIN {$table_users} u ON b.user_id = u.ID
         WHERE b.auction_id = %d AND b.winner = 1
         ORDER BY u.ID ASC", $current_auction_id));
    
    $userItems = [];
    $winnerItemNumbers = [];
    
    foreach ($winners_data as $winner) {
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, b.bid_value, pm.meta_value as product_id
             FROM {$wpdb->posts} p
             INNER JOIN {$table_bids} b ON p.ID = b.item_id
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'product_id'
             WHERE b.user_id = %d AND b.winner = 1 AND b.auction_id = %d",
             $winner->ID, $current_auction_id
        ));
    
        $userItems[$winner->ID] = $items;
    
        // Вычисляем минимальный номер товара для пользователя
        $minItemNumber = PHP_INT_MAX;
        foreach ($items as $item) {
            $itemNumber = intval(explode('. ', $item->post_title)[0]);
            $minItemNumber = min($minItemNumber, $itemNumber);
        }
        $winnerItemNumbers[$winner->ID] = $minItemNumber;
    }
    
    // Сортируем массив $winners_data на основе минимального номера товара
    usort($winners_data, function ($a, $b) use ($winnerItemNumbers) {
        return $winnerItemNumbers[$a->ID] <=> $winnerItemNumbers[$b->ID];
    });

?>

    <?php if (current_user_can('manage_options')):?>

    <div class="auction_postage_area container">
        <div class="auction_products_grid container d-flex align-items-center justify-content-between">

            <div class="auction_products_hero">
                <h1><?php echo esc_html($current_auction_title); ?></h1>

                <div class="auction_data">
                    <span><i class="bi bi-calendar3"></i> <?php echo esc_html($current_auction_date); ?></span>
                    <span><i class="bi bi-clock"></i> <?php echo esc_html($current_auction_time); ?></span>
                    <span><i class="bi bi-geo-alt-fill"></i> <?php echo esc_html($current_auction_location); ?></span>
                    <span class="postage_comment" data-bs-toggle="collapse" data-bs-target="#collapseAuctionUpdate" aria-expanded="false" aria-controls="collapseAuctionUpdate">
                        <i class="bi bi-chat-left-dots"></i></i><?php _e('Kommentoi postiin', 'default'); ?>
                    </span>
                </div>
            </div>
            <div class="auction_products_btn d-flex">
                <button class="auction_details_link mass_invoice_mailing">
                    <i class="bi bi-envelope"></i> <?php _e('Laskujen ryhmälähetys', 'default'); ?>
                </button>
                <a href="/for-pdf/generate-postage.php?auction_id=<?php echo $current_auction_id; ?>" class="auction_details_link" target="_blank">
                    <i class="bi bi-printer-fill"></i> <?php _e('Tulosta postituslista', 'default'); ?>
                </a>

            </div>
        </div>

        <div class="auction_postage_info_block container">
            <div class="row">
                <div class="col-12 col-sm-9">

                    <div id="collapseAuctionUpdate" class="control_button_block collapse ">
                        <div id="postage_comment_area" class="postage_comment_area">
                            <?php $content = the_field('postage_comment'); ?>
                            <?php wp_editor($content, 'postage_comment_area'); ?>
                        </div>
                        <div class="postage_comment_update"><i class="bi bi-arrow-repeat"></i> <?php _e('Päivitä', 'default'); ?></div>
                    </div>

                    <?php foreach ($winners_data as $current_user): ?>
                        <?php
                            $savedData = $userItemsData[$current_user->ID] ?? null;
                            $userId = $current_user->ID;
                            $userActionData = $userItemsData[$userId]['total_info'];

                            $willPickupChecked = $userActionData['will_pickup'] == '1' ? 'checked' : '';
                            $normalPostageChecked = $userActionData['normal_postage'] == '1' ? 'checked' : '';
                            $termsAcceptedChecked = $userActionData['terms_accepted'] == '1' ? 'checked' : '';
                        ?>
                        <?php
                            $phone_number = get_user_meta($current_user->ID, 'phone', true);
                            $email = $current_user->user_email;
                            $address = get_user_meta($current_user->ID, 'address', true);
                            $user_id = $current_user->ID;

                            $won_items = $wpdb->get_results($wpdb->prepare(
                                "SELECT p.ID, p.post_title, b.bid_value
                                 FROM {$wpdb->posts} p
                                 INNER JOIN {$table_bids} b ON p.ID = b.item_id
                                 WHERE b.user_id = %d AND b.winner = 1 AND b.auction_id = %d",
                                 $current_user->ID, $current_auction_id
                            ));

                            // вывод номеров продуктов
                            foreach ($won_items as &$item) {
                                $number = intval(explode('.', $item->post_title)[0]);
                                // echo ' ' . $number . ' ';
                            }
                            unset($item);

                        ?>

                        <?php 
                        $getUserAnswer = '';
                            if ($userActionData['will_pickup'] == '1' || $userActionData['normal_postage'] == '1' || $userActionData['terms_accepted'] == '1') {
                            $getUserAnswer = 'is_get_user_answer';
                            };?>

                        <div class="winner-item <?php echo $getUserAnswer;?>" data-user-id="<?php echo esc_attr($user_id); ?>" data-auction-id="<?php echo esc_attr($current_auction_id); ?>">
                            <div class="user-info" data-bs-toggle="collapse" data-bs-target="#collapseWinner<?php echo $current_user->ID; ?>" aria-expanded="false" aria-controls="collapseWinner<?php echo $current_user->ID; ?>">
                                <h3 class="d-flex justify-content-between align-items-center">
                                    <?php echo esc_html($current_user->display_name); ?>
                                    <i class="bi bi-chevron-down"></i>
                                </h3>
                            </div>
                            <div id="collapseWinner<?php echo $current_user->ID; ?>" class="collapse">
                                <div class="d-flex user-data">
                                    <div class="user_contact_item">
                                        <i class="bi bi-geo-alt-fill"></i> <?php echo esc_html($address); ?>
                                    </div>
                                    <div class="user_contact_item">
                                        <a href="tel:<?php echo esc_html($phone_number); ?>">
                                            <i class="bi bi-telephone-fill"></i> <?php echo esc_html($phone_number); ?>
                                        </a>
                                    </div>
                                    <div class="user_contact_item">
                                        <a href="mailto:<?php echo esc_html($email); ?>">
                                            <i class="bi bi-envelope-fill"></i> <?php echo esc_html($email); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="products-won">
                                    <h3><?php _e('Voitetut huudot', 'default'); ?></h3>
                                    <?php foreach ($won_items as $item): ?>
                                        <?php
                                            // Найдем сохраненные данные для этого элемента, если они есть
                                            $itemData = array_values(array_filter($savedData['items'] ?? [], function($savedItem) use ($item) {
                                                return $savedItem->item_id == $item->ID;
                                            }))[0] ?? null;
                                        ?>
                                        <div class="winner-product-info" data-item-id="<?php echo esc_attr($item->ID); ?>">
                                            <div class="d-flex align-items-end product-head">
                                                <div class="product-title">
                                                    <span><?php echo esc_html($item->product_id); ?></span>
                                                    <strong><?php echo esc_html($item->post_title); ?></strong>
                                                </div>
                                                <div class="product-bid">
                                                    <strong>
                                                        <?php _e('Ennakkohuuto: ', 'default'); ?> <?php echo esc_html($item->bid_value); ?> €
                                                    </strong>
                                                </div>
                                            </div>
                                            <div class="product-price">
                                                <label for="item_price">
                                                    <?php _e('Hinta', 'default'); ?>
                                                    <input type="text" name="item_price" value="<?php echo esc_attr($itemData->item_price ?? ''); ?>">
                                                </label>
                                                <label for="fragile_add">
                                                    <?php _e('Särkyvä lisä', 'default'); ?>
                                                    <input type="text" name="fragile_add" value="<?php echo esc_attr($itemData->fragile_add ?? ''); ?>">
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="total-info">
                                    <p>
                                        <strong><?php _e('Tuotteet yhteensä:', 'default'); ?></strong> <span class="items-total-price">0 €</span>
                                    </p>

                                    <div class="postage_cost">
                                        <label for="postage_cost"><?php _e('Postimaksut', 'default'); ?></label>
                                        <input type="text" name="postage_cost" value="<?php echo esc_attr($savedData['total_info']['postage_cost'] ?? ''); ?>">
                                    </div>

                                    <p>
                                        <strong><?php _e('Postimaksut särkyvälisällä:', 'default'); ?></strong> <span class="postage-total">0 €</span>
                                    </p>

                                    <hr width="250px">

                                    <div class="postage_result">
                                        <span>
                                            <p>
                                                <strong><?php _e('Yhteensä normaali pakettina:', 'default'); ?></strong> <span class="summa_and_postage">0 €</span>
                                            </p>
                                            <p>
                                                <strong><?php _e('Yhteensä särkyvälisällä:', 'default'); ?></strong> <span class="grand-total">0 €</span>
                                            </p>
                                        </span>
                                        <span>
                                            <button class="auction_details_link save-info-button">
                                                <i class="bi bi-cash-stack"></i> <?php _e('Tallenna', 'default'); ?>
                                            </button>

                                            <?php $invoice_sent = get_user_meta($user_id, 'invoice_sent_for_auction_' . $current_auction_id, true);?>
                                            <?php if ($invoice_sent || $userActionData['will_pickup'] == '1' || $userActionData['normal_postage'] == '1' || $userActionData['terms_accepted'] == '1'): ?>
                                                <button class="auction_details_link send_personal_invoice successfully">
                                                    <i class="bi bi-envelope"></i> <?php _e('Kirje lähetetty', 'default'); ?>
                                                </button>
                                            <?php else:?>
                                                <button class="auction_details_link send_personal_invoice">
                                                    <i class="bi bi-envelope"></i> <?php _e('Lähetä asiakkaalle sähköposti', 'default'); ?>
                                                </button>
                                            <?php endif;?>
                                        </span>
                                    </div>
                                </div>
                                <div class="agreement-info">

                                    <label>
                                        <?php _e('Haen sen henkilökohtaisesti', 'default'); ?>
                                        <input type="checkbox" name="will_pickup" <?php echo $willPickupChecked; ?> disabled>
                                    </label>

                                    <label>
                                        <?php _e('Normaali paketti', 'default'); ?>
                                        <input type="checkbox" name="normal_postage" <?php echo $normalPostageChecked; ?> disabled>
                                    </label>

                                    <label>
                                        <?php _e('Särkyvälisällä', 'default'); ?>
                                        <input type="checkbox" name="terms_accepted" <?php echo $termsAcceptedChecked; ?> disabled>
                                    </label>

                                    <?php
                                    $comment = $wpdb->get_var($wpdb->prepare(
                                        "SELECT user_comment FROM `{$wpdb->prefix}auction_postage_info` WHERE user_id = %d AND auction_id = %d",
                                        $user_id,
                                        $current_auction_id
                                    ));

                                    // Выводим комментарий
                                    if ($comment) {
                                        echo '<div class="user-comment">Kommentti: ' . esc_textarea($comment) . '</div>';
                                    };?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- скрипт рассчета стомости -->
    <script>
        jQuery(document).ready(function($) {
            function calculateTotals() {
                $('.winner-item').each(function() {
                    var itemsTotalPrice = 0;
                    var postageTotal = 0;
                    var grandTotal = 0;

                    $(this).find('.winner-product-info').each(function() {
                        var itemPrice = parseFloat($(this).find('input[name="item_price"]').val()) || 0;
                        var fragileAdd = parseFloat($(this).find('input[name="fragile_add"]').val()) || 0;

                        itemsTotalPrice += itemPrice;
                        postageTotal += fragileAdd;
                    });

                    // Добавляем стоимость обычной доставки
                    var basePostage = parseFloat($(this).find('input[name="postage_cost"]').val()) || 0;
                    postageTotal += basePostage;

                    summaAndPostage = itemsTotalPrice + basePostage;
                    grandTotal = itemsTotalPrice + postageTotal;

                    $(this).find('.items-total-price').text(itemsTotalPrice.toFixed(2) + ' €');
                    $(this).find('.postage-total').text(postageTotal.toFixed(2) + ' €');
                    $(this).find('.summa_and_postage').text(summaAndPostage.toFixed(2) + ' €');
                    $(this).find('.grand-total').text(grandTotal.toFixed(2) + ' €');
                });
            }

            // Обработчик для полей ввода стоимости товара и доставки
            $(document).on('input', 'input[name="item_price"], input[name="fragile_add"], input[name="postage_cost"]', function() {
                calculateTotals();
            });

            // Инициализируем расчеты при загрузке страницы
            calculateTotals();
        });
    </script>

    <?php else:?>

        <script>
            window.location.replace("<?php echo home_url(); ?>");
        </script>

    <?php endif;?>

    <script>
    // Скрывает элементы из поля редактора
    document.addEventListener('DOMContentLoaded', function() {
        var element = document.getElementById('wp-postage_comment_area-editor-tools');
        var element2 = document.getElementById('wp-postage_comment_area-editor-container');
        if (element) {
            element.style.display = 'none';
            element.parentNode.removeChild(element);
            element2.parentNode.removeChild(element2);
        }
    })
    // Скрипт обновления произвольного поля комментария
    jQuery(document).ready(function($) {
        $('.postage_comment_update').on('click', function() {
            var postId = <?php echo json_encode(get_the_ID()); ?>;
            var button = $(this);
            var commentContent = tinyMCE.get('postage_comment_area').getContent();
            button.addClass('successfully').text('<?php _e('Päivittää...', 'default'); ?>');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    'action': 'save_postage_comment',
                    'post_id': postId,
                    'postage_comment': commentContent
                },
                success: function(response) {
                    if (tinyMCE.get('postage_comment_area')) {
                        tinyMCE.get('postage_comment_area').setContent(response);
                    } else {
                        $('#postage_comment_area').html(response);
                    }
                },
                complete: function() {
                    setTimeout(function() {
                        button.removeClass('successfully').text('<?php _e('Päivitä', 'default'); ?>');
                    }, 3000);
                }
            });
        });
    });
</script>
<script>
// скрипт индивидуальной рассылки счетов
jQuery(document).ready(function($) {
    $('.send_personal_invoice').click(function(e) {
        e.preventDefault();
        var $thisButton = $(this);
        var userId = $(this).closest('.winner-item').data('user-id');
        var auctionId = $(this).closest('.winner-item').data('auction-id');
        var postId = <?php echo json_encode(get_the_ID()); ?>;
        var itemsData = $(this).closest('.winner-item').find('.winner-product-info').map(function() {
            return {
                item_id: $(this).data('item-id'),
                item_title: $(this).find('.product-title strong').text(),
                item_price: $(this).find('input[name="item_price"]').val(),
                fragile_add: $(this).find('input[name="fragile_add"]').val()
            };
        }).get();

        var itemsTotalPrice = $(this).closest('.winner-item').find('.items-total-price').text().trim().replace(' €', '');
        var postageCost = $(this).closest('.winner-item').find('input[name="postage_cost"]').val().trim();
        var postageTotal = $(this).closest('.winner-item').find('.postage-total').text().trim().replace(' €', '');
        var summaAndPostage = $(this).closest('.winner-item').find('.summa_and_postage').text().trim().replace(' €', '');
        var grandTotal = $(this).closest('.winner-item').find('.grand-total').text().trim().replace(' €', '');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'send_personal_invoice_ajax',
                post_id: postId,
                user_id: userId,
                auction_id: auctionId,
                items: itemsData,
                items_total_price: itemsTotalPrice,
                postage_cost: postageCost,
                postage_total: postageTotal,
                summa_and_postage: summaAndPostage,
                grand_total: grandTotal
            },
            success: function(response) {
                if (response.success) {
                    $thisButton.text('Kirje lähetetty').addClass('successfully');
                    console.log('Ответ сервера:', response.data);
                } else {
                    console.error('Ошибка:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX запроса:', status, error);
            }
        });
    });


// скрипт массовой рассылки счетов
$('.mass_invoice_mailing').click(function(e) {
        e.preventDefault();
        var auctionId = '<?php echo $current_auction_id; ?>';
        var postId = <?php echo json_encode(get_the_ID()); ?>;
        // Собираем данные каждого победителя аукциона
        var winnersData = [];
        $('.winner-item').each(function() {
            var userId = $(this).data('user-id');
            var itemsData = $(this).find('.winner-product-info').map(function() {
                return {
                    item_id: $(this).data('item-id'),
                    item_title: $(this).find('.product-title strong').text(),
                    item_price: $(this).find('input[name="item_price"]').val(),
                    fragile_add: $(this).find('input[name="fragile_add"]').val()
                };
            }).get();

            var itemsTotalPrice = $(this).find('.items-total-price').text().trim().replace(' €', '');
            var postageCost = $(this).find('input[name="postage_cost"]').val().trim();
            var postageTotal = $(this).find('.postage-total').text().trim().replace(' €', '');
            var summaAndPostage = $(this).find('.summa_and_postage').text().trim().replace(' €', '');
            var grandTotal = $(this).find('.grand-total').text().trim().replace(' €', '');

            winnersData.push({
                user_id: userId,
                items: itemsData,
                items_total_price: itemsTotalPrice,
                postage_cost: postageCost,
                postage_total: postageTotal,
                summa_and_postage: summaAndPostage,
                grand_total: grandTotal
            });
        });

        // Отправляем данные AJAX-запросом
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'send_mass_personal_invoices_ajax',
                post_id: postId,
                auction_id: auctionId,
                winners_data: winnersData
            },
            success: function(response) {
                if (response.success) {
                    alert('Kirjeet on lähetetty onnistuneesti kaikille voittajille');
                } else {
                    alert('Sähköpostien lähettämisessä tapahtui virhe.');
                    console.error('Ошибка AJAX запроса: ' + error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX запроса: ' + error);
            }
        });
    });

});

</script>
<?php
get_footer();