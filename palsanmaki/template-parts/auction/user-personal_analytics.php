<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$current_user_id = get_current_user_id();
global $wpdb;

// Получаем открытые ставки пользователя
$open_bids = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT item_id, MAX(bid_value) as bid_value, winner
        FROM {$wpdb->prefix}auction_bids
        WHERE user_id = %d AND winner = 0
        GROUP BY item_id
        ORDER BY bid_time DESC",
        $current_user_id
    )
);

// Получаем закрытые ставки пользователя
$closed_bids = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT item_id, auction_id, MAX(bid_value) as bid_value, winner
        FROM {$wpdb->prefix}auction_bids
        WHERE user_id = %d AND winner != 0
        GROUP BY item_id, auction_id
        ORDER BY auction_id DESC, item_id ASC",
        $current_user_id
    )
);
$bids_by_auction = array();
foreach ($closed_bids as $bid) {
    $bids_by_auction[$bid->auction_id][] = $bid;
}

// Функции для получения данных о продукте и аукционе
function get_auction_title($auction_id) {
    return get_the_title($auction_id);
}

function get_product_details($product_id) {
    return array(
        'title' => get_the_title($product_id),
        'custom_id' => get_field('product_id', $product_id) // Предполагается, что это ACF поле
    );
}

?>

<div class="container">
    <div class="row">
        <div class="col-12 col-sm-8">
            <div class="user_personal_analytics">
                <h3>
                    <?php _e( 'Avoimet huudot', 'default' ) ?>
                </h3>

                <?php foreach ($open_bids as $bid) :?>
                    <?php $product_details = get_product_details($bid->item_id); ?>
                    <div class="d-flex align-items-center justify-content-between data_analytics">
                        <div class="product_info">
                            <p>
                                <?php if (current_user_can('manage_options')):?>
                                    <?php echo $product_details['custom_id'];?>
                                <?php else:?>
                                    <?php echo get_the_ID();?>
                                <?php endif;?>
                            </p>
                            <h4>
                                <?php echo $product_details['title'];?>
                            </h4>
                        </div>
                        <div class="betting_price">
                            <?php echo $bid->bid_value; ?> €
                        </div>
                        <div class="betting_status">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="user_personal_analytics">
                <h3>
                    <?php _e( 'Päättyneet huudot', 'default' ) ?>
                </h3>

                <?php foreach ($bids_by_auction as $auction_id => $auction_bids): ?>
                    <div class="closed_bids_block" style="margin-bottom: 50px;">
                        <h4><?php echo get_auction_title($auction_id); ?></h4>

                        <?php foreach ($auction_bids as $bid): ?>
                            <?php $product_details = get_product_details($bid->item_id); ?>
                            <?php global $current_user; ?>
                            <?php
                            // если пользователь забанен, но ранее был отмечен как "победитель", его статус изменится на "проиграш"
                            if (get_user_meta($current_user->ID, 'banned', true)) {
                                $status_text = '<i class="bi bi-x-circle"></i> Hävitty';
                            } else {
                                $status_text = $bid->winner == 1 ? '<i class="bi bi-check-circle"></i> Voitettu' : '<i class="bi bi-x-circle"></i> Hävitty';
                            }
                            ?>

                            <div class="d-flex align-items-center justify-content-between data_analytics">
                                <div class="product_info">
                                    <p>
                                        <?php if (current_user_can('manage_options')): ?>
                                            <?php echo $product_details['custom_id']; ?>
                                        <?php else: ?>
                                            <?php echo get_the_ID(); ?>
                                        <?php endif; ?>
                                    </p>
                                    <h4><?php echo $product_details['title']; ?></h4>
                                </div>
                                <div class="betting_price"><?php echo $bid->bid_value; ?> €</div>
                                <div class="betting_status">
                                    <?php echo $status_text; ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>