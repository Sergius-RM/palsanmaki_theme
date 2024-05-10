
<!-- Hero Section Start -->
<section class="hero-section">

    <div class="hero-container container">
        <div class="row mx-auto slider slide_list">

            <?php if( have_rows('hero_slider') ): ?>
                <?php while( have_rows('hero_slider') ) : the_row(); ?>

                    <div class="slide_item <?php if (get_sub_field('enable_content')):?>double_image_item<?php endif;?>" <?php if (get_sub_field('enable_content')):?>style=" max-height: none"<?php endif;?>>
                        <div class="banner_overlay"></div>
                        <?php if ( get_sub_field('image') ):?>
                            <?php $image_item = get_sub_field('image');?>

                            <?php if (get_sub_field('enable_content')):?>
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-6 slider_img_area">
                                        <img src="<?php echo $image_item;?>">
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-6 slider_content_area">
                                        <div class="slider_content">
                                            <h2><?php the_sub_field('title'); ?></h2>
                                            <p class="slider_content_text">
                                                <?php the_sub_field('text'); ?>
                                            </p>
                                        </div>
                                        <?php if (get_sub_field('enable_cta_button')):?>
                                            <a class="learn-more" target="_blank" <?php if (get_sub_field('button_id')):?>id="<?php the_sub_field('button_id'); ?>"<?php endif;?> href="<?php the_sub_field('button_link'); ?>">
                                                <?php the_sub_field('button_text'); ?>
                                            </a>
                                        <?php endif;?>
                                    </div>
                                </div>
                            <?php else:?>
                                <img src="<?php echo $image_item;?>">
                            <?php endif;?>

                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- Hero Section End -->