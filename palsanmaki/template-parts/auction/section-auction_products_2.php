<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$current_auctions = get_field('current_auction', 'option');
$product_item = $current_auctions[1] ?? null;
?>

<!-- Current Auction Section Start -->
<section class="auction_products_section">
    <div class="container">
        <div class="row">
            <?php
                $post_object = get_post($product_item);
                setup_postdata($GLOBALS['post'] =& $post_object);
                ?>
                    <div class="col-12 col-sm-9 mx-auto auction_content">
                        <?php the_content(); ?>

                        <?php if (get_field('about_auction_link', 'option')):?>
                            <a class="about_auction_btn" href="<?php the_field('about_auction_link', 'option');?>">
                                <?php the_field('about_auction_name', 'option');?>
                            </a>
                        <?php endif;?>

                        <?php if (current_user_can('manage_options')):?>
                            <?php
                                $admin_auction_details_link = get_field('admin_auction_details_link', 'option');

                                $separator = (parse_url($admin_auction_details_link, PHP_URL_QUERY)) ? '&' : '?';
                                $auction_id_link = add_query_arg(array('auction_id' => get_the_ID()), $admin_auction_details_link . $separator);
                            ?>
                            <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_manage">
                                <?php the_field('admin_auction_details', 'option');?>
                            </a>

                            <a class="auction_manage" href="<?php the_field('admin_auction_list_link', 'option');?>">
                                <?php the_field('admin_auction_list', 'option');?>
                            </a>
                        <?php endif;?>

                    </div>
                <?php
                wp_reset_postdata();
            ?>
        </div>
    </div>

    <div class="auction_products_grid container">
        <div class="row">
        <?php
            $post_object = get_post($product_item);
            setup_postdata($GLOBALS['post'] =& $post_object);
            ?>

            <?php
            $per_page= 30;
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $current_auction_id = get_the_ID();
            add_filter('posts_clauses', 'numeric_posts_orderby');
            $args = array(
                'post_type'      => 'auction_items',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                // 'orderby' => 'menu_order',
                'orderby' => 'title_number',
                'order' => 'ASC',
                'meta_query'     => array(
                    array(
                        'key'     => 'current_auction_id',
                        'value'   => $current_auction_id,
                        'compare' => '=',
                    ),
                ),
            );

            $query_auction_items = new WP_Query($args);
            remove_filter('posts_clauses', 'numeric_posts_orderby');

            if ($query_auction_items->have_posts()) :
                while ($query_auction_items->have_posts()) : $query_auction_items->the_post();

                    $item_id = get_the_ID();
                    $winner_query = $wpdb->prepare("SELECT winner FROM {$wpdb->prefix}auction_bids WHERE item_id = %d AND winner != 0", $item_id);
                    $winner_status = $wpdb->get_var($winner_query);
                    ?>
                    <div id="product-<?php the_ID(); ?>" class="col-12 col-sm-6 col-md-4 auction_product_item equal-height">
                        <div class="venietka_hr_blue"></div>
                        <?php
                $post_id = get_the_ID();

                        // Проверяем, есть ли выделенное изображение (миниатюра) у поста
                    if (has_post_thumbnail($post_id)) {

                        $thumbnail_id = get_post_thumbnail_id($post_id);
                        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'full'); // Или другой размер, если нужно
                        // Выводим миниатюру поста как первое изображение в галерее
                        echo '<div class="gallery">';
                        echo '<a data-fancybox="gallery-' . esc_attr($post_id) . '" href="' . esc_url($thumbnail_url) . '">';
                        echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title($thumbnail_id)) . '" />';
                        echo '</a>';
                    } else {
                        echo '<div class="gallery">';
                    }?>

                    <?php  // Получаем все присоединенные изображения к посту
                        $images = get_attached_media('image', $post_id); ?>

                            <div class="gallery_icons">
                                <i class="bi bi-search"></i>
                                <?php if (count($images) > 0): ?>
                                    <i class="bi bi-images"></i>
                                <?php endif;?>
                            </div>
                    <?php
                    // Проверяем, есть ли дополнительные изображения
                    if (count($images) > 0) {
                        foreach ($images as $image) {
                            // Пропускаем миниатюру поста, если она уже выведена
                            if ($image->ID == $thumbnail_id) continue;

                            $image_url = wp_get_attachment_image_url($image->ID, 'full');
                            echo '<a data-fancybox="gallery-' . esc_attr($post_id) . '" href="' . esc_url($image_url) . '" data-src="' . esc_url($image_url) . '"></a>';
                        }
                    }

                    echo '</div>';
                ?>
                        <script>
                            Fancybox.bind('[data-fancybox]', {
                            //
                            });
                        </script>

                        <?php if (current_user_can('manage_options')):?>
                            <!-- <span class="product_id">
                                < ?php the_field('product_id');?>
                            </span> -->
                        <?php else:?>
                            <!-- <span class="product_id">
                                < ?php echo get_the_ID();?>
                            </span> -->
                        <?php endif;?>

                        <h3><?php the_title(); ?></h3>
                        <div class="product_description"><?php the_content(); ?></div>

                        <?php if (!current_user_can('manage_options')): ?>

                            <?php if (get_field('stop_auction', $current_auction_id) == true ):?>
                                <div class="lot_closed">
                                    <?php _e('Huudot loppuneet', 'default'); ?>
                                </div>
                            <?php elseif (get_field('stop_auction', $current_auction_id) == false ):?>
                                <?php if (is_user_logged_in() && $winner_status === null): ?>
                                    <div class="bid-form-container">
                                        <form class="bid-form" data-item-id="<?php the_ID(); ?>">
                                            <input class="bid_input" type="number" name="bid_value" min="0" placeholder="<?php _e('Summa...', 'default'); ?>" required>
                                            <input type="submit" value="<?php _e('HUUDA!', 'default'); ?>">
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <a class="lot_closed" href="<?php the_field('user_authorization_link', 'option');?>">
                                        <?php _e('Kirjaudu sisään', 'default'); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif;?>

                        <?php else: ?>
                            <p class="not_for_admin">
                                <?php _e('Järjestelmänvalvojat eivät huuda', 'default'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php
                endwhile;

            // Добавление пагинации
            if ($query_auction_items->max_num_pages > 1) {
                echo paginate_links(array(
                    'total' => $query_auction_items->max_num_pages,
                    'current' => $paged,
                    'type' => 'list',
                    'prev_text'    => __('« Edellinen'),
                    'next_text'    => __('Seuraava »'),
                ));
                $pagination_links = paginate_links();

                if ($pagination_links) {
                    echo '<nav class="auction-pagination">' . $pagination_links . '</nav>';
                }
            } ?>
            <?php else:
                ?>
                <p><?php _e('Täällä ei ole vielä tuotteita.', 'default'); ?></p>

            <?php endif; ?>

            <?php wp_reset_postdata(); ?>

        <?php wp_reset_postdata(); ?>

        </div>
    </div>

</section>
<!-- Current Auction Section End -->