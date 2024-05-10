<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
$current_auctions = get_field('current_auction', 'option');
$current = $current_auctions[0] ?? null;
$current2 = $current_auctions[1] ?? null;
?>

<!-- Featured Products Area Start -->
<section class="featured_products_section">
    <div class="container">

        <div class="section-title text-center">
            <?php if( get_sub_field('title') ): ?>
                <h2><?php echo get_sub_field('title'); ?></h2>
            <?php endif; ?>
        </div>
        <div class="row">

        <?php if( have_rows('products') ): ?>
            <?php while( have_rows('products') ) : the_row(); ?>

                <div class="col-12 col-sm-4 col-md-4 col-xl-4 product_item_area equal-height">

                        <?php if( get_sub_field('person_img') ): ?>
                            <div class="image">
                                <img class="person_img" src="<?php the_sub_field('person_img');?>">
                                <?php if( get_sub_field('pop_up') ): ?>
                                    <img class="pop_up_item" src="<?php the_sub_field('pop_up');?>">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        $product_items = get_sub_field('product_item');
                        if ($product_items) :
                            foreach ($product_items as $product_item) :
                                $thumbnail_url = get_the_post_thumbnail_url($product_item->ID);
                                $post_permalink = get_permalink($product_item->ID);
                                $auction_id = get_field('current_auction_id', $product_item->ID);
                                $auction_page_url = get_permalink($auction_id);
                                $product_title = get_the_title($product_item->ID);
                                $product_number = (int) substr($product_title, 0, strpos($product_title, '.'));
                        ?>
                                <div class="product_item">
                                    <div class="frame"></div>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title($product_item->ID)); ?>">
                                </div>

                        <?php
                            if ($auction_id == $current->ID) {
                                $auction_page_url = '/huutokauppa/';
                            } else if ( $auction_id == $current2->ID) {
                                $auction_page_url = '/sunnuntain_huutokauppa/';
                            } else {
                                $auction_page_url = $auction_page_url;
                            }

                            if ( wp_is_mobile() ) {
                                $items_per_page = 20;
                            } else {
                                $items_per_page = 30;
                            }

                            $pagination_page = ceil($product_number / $items_per_page);

                            if ($pagination_page > 1) {
                                $anchor_link = $auction_page_url . 'page/' . $pagination_page . '/#product-' . $product_item->ID;
                            } else {
                                $anchor_link = $auction_page_url . '#product-' . $product_item->ID;
                            }
                            ?>

                            <a href="<?php echo $anchor_link; ?>" class="learn-more">
                                <?php _e( 'HUUDA', 'default' ) ?>
                            </a>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php endwhile; ?>
        <?php endif; ?>

        </div>
    </div>
</div>
</section>
<!-- Featured Products Area END  -->
