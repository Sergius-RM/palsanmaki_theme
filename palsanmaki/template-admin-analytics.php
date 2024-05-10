<?php
/**
 * Template name: Analytics
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

 get_header();

$current_auction_id = isset($_GET['auction_id']) ? absint($_GET['auction_id']) : 0;

$current_auction_title = get_the_title($current_auction_id);
$current_auction_date = get_field('auction_date', $current_auction_id);
$current_auction_time = get_field('auction_time', $current_auction_id);
$current_auction_location = get_field('auction_location', $current_auction_id);

if (current_user_can('manage_options') && $current_auction_id):

    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bids';

    $auction_items = get_posts(array(
        'post_type' => 'auction_items',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num', // Сортировка по числовому полю
        'meta_key' => 'title_number', // Имя числового поля
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'current_auction_id',
                'value' => $current_auction_id,
                'compare' => '='
            )
        )
    )); ?>

    <div class="auction_analytics_area container">

        <div class="auction_products_grid container d-flex align-items-center justify-content-between">
            <div class="auction_products_hero">
                <h1><?php echo esc_html($current_auction_title); ?></h1>

                <div class="auction_data">
                    <span><i class="bi bi-calendar3"></i> <?php echo esc_html($current_auction_date); ?></span>
                    <span><i class="bi bi-clock"></i> <?php echo esc_html($current_auction_time); ?></span>
                    <span><i class="bi bi-geo-alt-fill"></i> <?php echo esc_html($current_auction_location); ?></span>
                </div>
            </div>
            <div class="auction_products_btn d-flex">

                <?php
                    $admin_mailing_list_link = get_field('admin_mailing_list_link', 'option');

                    $separator = (parse_url($admin_mailing_list_link, PHP_URL_QUERY)) ? '&' : '?';
                    $auction_id_link = add_query_arg(array('auction_id' => $current_auction_id), $admin_mailing_list_link . $separator);
                ?>
                <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                    <i class="bi bi-envelope"></i> <?php the_field('admin_mailing_list', 'option');?>
                </a>

                <a href="/for-pdf/generate-pdf.php?auction_id=<?php echo $current_auction_id; ?>" class="auction_details_link" target="_blank">
                    <i class="bi bi-printer-fill"></i> <?php _e('Tulosta lista', 'default'); ?>
                </a>

            </div>
        </div>

        <?php foreach ($auction_items as $item):
            setup_postdata($item);
            $product_id = get_post_meta($item->ID, 'product_id', true);
        ?>

                <div class="row auction_analitics_item">

                    <div class="col-12 col-sm-8 d-flex">
                        <div class="row">
                            <div class="col-12 col-sm-6 auction_item_image">
                                <img src="<?php echo get_the_post_thumbnail_url($item->ID, 'full'); ?>" alt="Image preview" style="display: block; width: 100%; height: auto;">
                            </div>
                            <div class="col-12 col-sm-6 auction_item_info">
                                    <p><?php _e('Tuotetunnus:', 'default'); ?> <?php echo get_the_ID();?></p>
                                <?php if (get_post_meta($item->ID, 'product_id', true)) :?>
                                    <p><?php _e('Asiakasnumero:', 'default'); ?> <?php echo $product_id; ?></p>
                                <?php endif;?>

                                <h4><?php echo get_the_title($item->ID); ?></h4>
                                <p><?php echo apply_filters('the_content', get_post_field('post_content', $item->ID)); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 analytics_user_bids">
                        <?php
                            $bids = $wpdb->get_results($wpdb->prepare(
                                "SELECT ab.* FROM $table_name ab
                                INNER JOIN $wpdb->users u ON ab.user_id = u.ID
                                LEFT JOIN $wpdb->usermeta um ON ab.user_id = um.user_id AND um.meta_key = 'banned'
                                WHERE item_id = %d AND (um.meta_value IS NULL OR um.meta_value != '1')
                                ORDER BY ab.bid_value DESC, ab.winner DESC
                                LIMIT 5",
                                $item->ID
                            ));
                        ?>

                        <?php if ($bids):?>
                            <h4>
                                <?php _e('Ennakkkotarjoukset', 'de fault'); ?>
                            </h4>
                            <ol>
                                <?php $is_first = true;?>
                                <?php foreach ($bids as $bid): ?>
                                   <?php $user_info = get_userdata($bid->user_id);
                                   $user_info = get_userdata($bid->user_id);
                                   $winner_status = $bid->winner;
                                   $bid_id = $bid->id;
                                ?>

                                    <li class="bid-item" data-bid-id="<?php echo $bid_id; ?>" data-winner-status="<?php echo $winner_status; ?>">
                                        <?php echo esc_html($user_info->display_name);?> - <?php echo esc_html($bid->bid_value);?> €

                                        <?php if ($winner_status == -2):?>

                                            <?php if ($is_first): ?>
                                                <span class="lot_closed"><?php _e('Ei voittajaa', 'default'); ?></span>
                                                <?php $is_first = false; ?>
                                            <?php endif;?>

                                        <?php else:?>

                                            <?php if ($is_first): ?>
                                                <button class='mark-winner <?php echo ($bid->winner ? " winner_chosen'" : "'") ?>' data-bid-id='<?php echo $bid->id;?>'>
                                                    <?php echo ($bid->winner ? __('Voittaja', 'default') : __('Merkkaa voittajaksi', 'default')); ?>
                                                </button>
                                                <?php $is_first = false; ?>
                                            <?php endif;?>

                                        <?php endif;?>

                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php else: ?>
                            <p><?php _e('Ei huutoja', 'default'); ?></p>
                        <?php endif; ?>
                    </div>

                </div>

        <?php endforeach; ?>
        <?php wp_reset_postdata(); ?>

    </div>

    <?php else:?>

        <script>
            window.location.replace("<?php echo home_url(); ?>");
        </script>

    <?php endif;?>

<?php
get_footer();