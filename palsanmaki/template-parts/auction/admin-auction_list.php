<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<!-- Hero Section Start -->
<section class="admin_auction_products_section">
    <div class="container">

        <?php
        // Получаем текущую дату
        $current_date = new DateTime();

        // Создаем объект WP_Query для предстоящих аукционов
        $args_upcoming = array(
            'post_type' => 'auction',
            'posts_per_page' => 25,
            'paged' => $paged,
            'meta_query' => array(
                array(
                    'key' => 'auction_date',
                    'value' => $current_date->format('Ymd'),
                    'compare' => '>=', // Сравниваем с текущей датой и более поздними
                    'type' => 'DATE',
                ),
            ),
            'order' => 'ASC',
            'orderby' => 'meta_value',
            'meta_key' => 'auction_date',
        );

        $query_upcoming = new WP_Query($args_upcoming);

        // Проверяем, есть ли предстоящие аукционы
        if ($query_upcoming->have_posts()) : ?>

            <h3>
                <?php _e( 'Tulevat huutokaupat', 'default' ) ?>
            </h3>

            <?php while ($query_upcoming->have_posts()) : $query_upcoming->the_post(); ?>

            <div class="auction_item current_auction_item">
                <div class="auction_data">
                    <span><i class="bi bi-calendar3"></i> <?php the_field('auction_date');?></span>
                    <span><i class="bi bi-clock"></i> <?php the_field('auction_time');?></span>
                    <span><i class="bi bi-geo-alt-fill"></i> <?php the_field('auction_location');?></span>
                </div>
                <div class="admin_auction_content">
                    <h3><?php the_title(); ?></h3>
                    <div class="admin_auction_controll_btn">
                        <a href="<?php the_permalink();?>" class="auction_details_link">
                            <i class="bi bi-eye-fill"></i> <?php _e( 'Katso', 'default' ) ?>
                        </a>

                        <?php
                            $admin_auction_details_link = get_field('admin_auction_details_link', 'option');

                            $separator = (parse_url($admin_auction_details_link, PHP_URL_QUERY)) ? '&' : '?';
                            $auction_id_link = add_query_arg(array('auction_id' => get_the_ID()), $admin_auction_details_link . $separator);
                        ?>
                        <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                            <i class="bi bi-pencil-square"></i> <?php _e('Muokkaa', 'default') ?>
                        </a>

                        <?php
                            $admin_analytics_link = get_field('admin_analytics_link', 'option');
                            $separator = (parse_url($admin_analytics_link, PHP_URL_QUERY)) ? '&' : '?';
                            $auction_id_link = add_query_arg(array('auction_id' => get_the_ID()), $admin_analytics_link . $separator);
                        ?>
                        <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                            <i class="bi bi-clipboard-data"></i> <?php the_field('admin_analytics', 'option');?>
                        </a>

                        <a class="auction_details_link stop-auction-button <?php if (get_field('stop_auction', $current_auction_id) == true ):?>successfully<?php endif;?>" data-auction-id="<?php echo get_the_ID(); ?>">
                            <i class="bi bi-x-circle"></i>
                            <?php if (get_field('stop_auction', $current_auction_id) == true ):?>
                                <?php _e( 'Valmis', 'default' ) ?>
                            <?php else:?>
                                <?php _e( 'Lopeta', 'default' ) ?>
                            <?php endif;?>
                        </a>

                        <a class="auction_details_link delete-auction-button" data-auction-id="<?php echo get_the_ID(); ?>" data-nonce="<?php echo wp_create_nonce('delete_auction_nonce'); ?>">
                            <i class="bi bi-trash-fill"></i> <?php _e( 'Poista', 'default' ) ?>
                        </a>

                    </div>
                </div>
            </div>

            <?php endwhile; ?>

            <?php // Выводим пагинацию
                the_posts_pagination(array(
                    'prev_text' => __('« Previous'),
                    'next_text' => __('Next »'),
                ));
            ?>

           <?php wp_reset_postdata();

        else :
            echo '<p>No upcoming auctions found.</p>';
        endif;
        ?>

        <?php
        // Создаем объект WP_Query для прошедших аукционов
        $args_past = array(
            'post_type' => 'auction',
            'posts_per_page' => 25,
            'paged' => $paged,
            'meta_query' => array(
                array(
                    'key' => 'auction_date',
                    'value' => $current_date->format('Ymd'),
                    'compare' => '<', // Сравниваем с текущей датой и более ранними
                    'type' => 'DATE',
                ),
            ),
            'order' => 'DESC',
            'orderby' => 'meta_value',
            'meta_key' => 'auction_date',
        );

        $query_past = new WP_Query($args_past);

        // Проверяем, есть ли прошедшие аукционы
        if ($query_past->have_posts()) : ?>

            <h3>
                <?php _e( 'Menneet', 'default' ) ?>
            </h3>

            <?php while ($query_past->have_posts()) : $query_past->the_post(); ?>

                <div class="auction_item">
                    <div class="auction_data">
                        <span><i class="bi bi-calendar3"></i> <?php the_field('auction_date');?></span>
                        <span><i class="bi bi-clock"></i> <?php the_field('auction_time');?></span>
                        <span><i class="bi bi-geo-alt-fill"></i> <?php the_field('auction_location');?></span>
                    </div>
                    <div class="admin_auction_content">
                        <h3><?php the_title(); ?></h3>
                        <div class="admin_auction_controll_btn">
                            <a href="<?php the_permalink();?>" class="auction_details_link">
                                <i class="bi bi-eye-fill"></i> <?php _e( 'Katso', 'default' ) ?>
                            </a>

                            <?php
                                $admin_auction_details_link = get_field('admin_auction_details_link', 'option');

                                $separator = (parse_url($admin_auction_details_link, PHP_URL_QUERY)) ? '&' : '?';
                                $auction_id_link = add_query_arg(array('auction_id' => get_the_ID()), $admin_auction_details_link . $separator);
                            ?>
                            <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                                <i class="bi bi-pencil-square"></i> <?php _e('Muokkaa', 'default') ?>
                            </a>

                            <?php
                                $admin_analytics_link = get_field('admin_analytics_link', 'option');
                                $separator = (parse_url($admin_analytics_link, PHP_URL_QUERY)) ? '&' : '?';
                                $auction_id_link = add_query_arg(array('auction_id' => get_the_ID()), $admin_analytics_link . $separator);
                            ?>
                            <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                                <i class="bi bi-clipboard-data"></i> <?php the_field('admin_analytics', 'option');?>
                            </a>

                            <a class="auction_details_link stop-auction-button <?php if (get_field('stop_auction', $current_auction_id) == true ):?>successfully<?php endif;?>" data-auction-id="<?php echo get_the_ID(); ?>">
                                <i class="bi bi-x-circle"></i>
                                <?php if (get_field('stop_auction', $current_auction_id) == true ):?>
                                    <?php _e( 'Valmis', 'default' ) ?>
                                <?php else:?>
                                    <?php _e( 'Lopeta', 'default' ) ?>
                                <?php endif;?>
                            </a>

                            <a class="auction_details_link delete-auction-button" data-auction-id="<?php echo get_the_ID(); ?>" data-nonce="<?php echo wp_create_nonce('delete_auction_nonce'); ?>">
                                <i class="bi bi-trash-fill"></i> <?php _e( 'Poista', 'default' ) ?>
                            </a>

                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

            <?php // Выводим пагинацию
                the_posts_pagination(array(
                    'prev_text' => __('« Previous'),
                    'next_text' => __('Next »'),
                ));
            ?>

            <?php wp_reset_postdata();

        else :
            echo '<p>No past auctions found.</p>';
        endif;
        ?>

    </div>
</section>
<!-- Hero Section End -->

<script>
jQuery(document).ready(function($) {

    $('.delete-auction-button').click(function(e) {
        e.preventDefault();
        var $button = $(this);
        var auctionId = $(this).data('auction-id');
        var nonce = $(this).data('nonce');

        $button.html('<i class="bi bi-trash-fill"></i> Poisto...').addClass('successfully');

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'delete_auction',
                    auction_id: auctionId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Virhe huutokaupan poistamisessa.');
                    }
                }
            });
    });

        $('.stop-auction-button').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var auctionId = button.data('auction-id');
            var nonce = button.data('nonce');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'toggle_auction_status',
                    auction_id: auctionId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        var newStatus = response.data.new_status;
                        button.text(newStatus ? '✓ Valmis' : '✓ Lopeta').toggleClass('successfully', newStatus);
                    } else {
                        alert('Virhe muutettaessa huutokaupan tilaa.');
                    }
                }
            });
        });

});
</script>
