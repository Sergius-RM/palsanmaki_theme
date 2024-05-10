<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<!-- Service Grid Area Start -->
<section class="service_grid">
    <div class="container">

        <div class="section-title text-center">
            <?php if( get_sub_field('h2_header') ): ?>
                <h2><?php echo get_sub_field('h2_header'); ?></h2>
            <?php endif; ?>
        </div>
        <div class="row">

        <?php if( have_rows('custom_pages') ): ?>
            <?php while( have_rows('custom_pages') ) : the_row(); ?>
                    <div class="col-12 col-sm-12 col-xl-4 post_item equal-height">
                        <div class="articles-item">
                            <div class="image">
                                <img src="<?php the_sub_field('image');?>" alt="<?php the_title(); ?>">
                            </div>

                            <div class="articles-content">

                                <h3>
                                    <?php the_sub_field('title');?>
                                </h3>

                                <p><?php the_sub_field('content');?></p>

                            </div>
                        </div>
                    </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php if( get_sub_field('link') ): ?>
            <a href="<?php the_sub_field('link');?>" id="<?php the_sub_field('link_id');?>" class="learn-more">
                <?php the_sub_field('link_text');?>
            </a>
        <?php endif; ?>

        </div>
    </div>
</div>
</section>
<!-- Blog Grid Area END  -->
