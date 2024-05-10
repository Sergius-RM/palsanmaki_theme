<?php
/**
 * The template for displaying footer.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$content_type = get_sub_field( 'content_type');
?>

<!-- Info Card Area Start -->
<section class="info_card_section">
    <div class="container">
        <div class="row mx-auto">

            <?php if (get_sub_field( 'info_card_title')): ?>
                <h2><?php the_sub_field('info_card_title');?></h2>
            <?php endif;?>

            <?php if ($content_type == 'text'):?>

                <div class="info_card_content">
                    <?php the_sub_field('info_card_content');?>
                </div>

            <?php elseif( $content_type == 'block'): ?>

                <?php while( have_rows('content_blocks') ) : the_row(); ?>
                    <div class="col-12 col-sm-12 col-md-6 col-xl-4 event_item equal-height">
                        <div class="border_v"></div>
                        <div class="event_info">
                            <div class="date">
                                <?php the_sub_field('date');?>
                            </div>
                            <div class="title">
                                <?php the_sub_field('title');?>
                            </div>
                            <div class="subtitle">
                                <?php the_sub_field('subtitle');?>
                            </div>
                        </div>
                        <div class="border_v"></div>
                    </div>
                <?php endwhile; ?>

            <?php elseif( $content_type == 'event'): ?>

                <?php
                    $args = array(
                        'post_type'      => 'tribe_events',
                        'posts_per_page' => 3,
                        'meta_key'       => '_EventStartDate',
                        'orderby'        => 'meta_value',
                        'order'          => 'ASC',
                        'meta_query'     => array(
                            array(
                                'key'     => '_EventStartDate',
                                'value'   => date('Y-m-d H:i:s'), // текущая дата и время
                                'compare' => '>=', // выбираем события, начинающиеся с текущей даты или позже
                                'type'    => 'DATETIME'
                            )
                        )
                    );
                ?>

                <?php $query = new WP_Query($args);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post(); ?>

                        <div class="col-12 col-sm-12 col-xl-4 event_item event_calendar_item equal-height">
                            <div class="event_info">
                                <div class="date">
                                    <?php echo tribe_get_start_date(null, false, 'j.n.Y');?>
                                </div>
                                <div class="title category">
                                    <?php $event_cats = get_the_terms(get_the_ID(), 'tribe_events_cat');
                                        if ($event_cats && !is_wp_error($event_cats)) {
                                            $event_cat_links = array();
                                            foreach ($event_cats as $cat) {
                                                $event_cat_links[] = '<a href="' . esc_url(get_term_link($cat)) . '" rel="tag">' . esc_html($cat->name) . '</a>';
                                            }
                                            echo implode(', ', $event_cat_links);
                                        }; ?>
                                </div>
                                <div class="subtitle">
                                    <a href="<?php the_permalink();?>"><?php echo get_the_title();?></a>
                                </div>
                            </div>
                        </div>

                    <?php }
                    wp_reset_postdata();
                } else { ?>
                    <h3>
                        <?php _e('Tulevia tapahtumia ei löytynyt', 'default'); ?>
                    </h3>
                <?php }
                ?>

            <?php endif; ?>

            <?php if (get_sub_field('enable_cta_button')):?>
                <a class="cta_btn" <?php if (get_sub_field('button_id')):?>id="<?php the_sub_field('link_id'); ?>"<?php endif;?> href="<?php the_sub_field('button_link');?>"><?php the_sub_field('button_text');?></a>
            <?php endif;?>

            <div class="border_btn_venietka"></div>
        </div>
        <div class="venietka_bg_down"></div>
    </div>

</section>
<!-- Info Card Area End -->