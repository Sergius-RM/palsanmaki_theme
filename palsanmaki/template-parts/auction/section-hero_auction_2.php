<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$current_auctions = get_field('current_auction', 'option');
$product_item = $current_auctions[1] ?? null;
?>

<!-- Hero Section Start -->
<section class="auction-hero-section">

</section>

<section class="auction-data-section">

    <div class="hero-container container">
        <div class="row">

            <h1>
                <?php the_title(); ?>
            </h1>

            <?php
                $post_object = get_post($product_item);
                setup_postdata($GLOBALS['post'] =& $post_object);

                $thumbnail_url = get_the_post_thumbnail_url($product_item, 'thumbnail');
                $post_permalink = get_permalink($product_item);
                ?>

                <div class="auction_data">
                    <?php the_field('auction_date');?> - <?php the_field('auction_location');?>
                </div>

                <?php if (get_field('stop_auction') == true ):?>
                    <div class="time_before_start">
                        <?php echo __('Ennakkohuudot sulkeutuneet', 'default'); ?>
                    </div>
                <?php elseif (get_field('stop_auction') == false ):?>
                    <div class="time_before_start">
                        <?php
                            $auction_date = get_field('auction_date');
                            $auction_time = get_field('auction_time');
                            $timezone = new DateTimeZone('Europe/Helsinki');

                            $auction_datetime = DateTime::createFromFormat('d.m.Y H:i', $auction_date . ' ' . $auction_time, $timezone);
                            $current_datetime = new DateTime('now', $timezone);
                            $interval = $current_datetime->diff($auction_datetime);

                            if ($auction_datetime <= $current_datetime) {
                                echo __('Ennakkohuudot sulkeutuneet', 'default');
                                if (!get_field('stop_auction', $current_auction_id)) {
                                    update_field('stop_auction', true, $current_auction_id);
                                }
                            } else {
                                echo __('Ennakkohuutoa jäljellä: ', 'default') . $interval->format('%a pv %h h %i min');
                            }
                        ?>
                    </div>
                <?php endif;?>

                <?php
                wp_reset_postdata();
            ?>

        </div>
    </div>
</section>
<!-- Hero Section End -->