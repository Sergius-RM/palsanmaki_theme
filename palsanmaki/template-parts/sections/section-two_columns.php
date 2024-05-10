<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<!-- Two Columns Section Start -->
<section class="two_columns_section wrap_two_columns">
    <div class="container">
        <div class="row">
        <div class="col-sm-9 mx-auto">

        <div class="row align-items-center mx-auto section_two_columns">

            <div class="col-sm-6 col-md-4 col-lg-4 two_columns_image <?php if ( get_sub_field('swap_blocks') == true ) { echo 'right_side'; } ?>">
                <?php if ( get_sub_field('image') ):?>
                    <?php $quick_order_image = get_sub_field('image');?>
                    <img src="<?php echo $quick_order_image;?>">
                <?php endif; ?>
            </div>

            <div class="col-sm-6 col-md-8 col-lg-8 two_columns_content <?php if ( get_sub_field('swap_blocks') == true ) { echo 'order-first'; } ?>">

                <h2><?php the_sub_field('h2_header'); ?></h2>

                <?php if (get_sub_field('header_subtitle')):?>
                    <h4><?php the_sub_field('header_subtitle'); ?></h4>
                <?php endif;?>

            </div>

            <div class="content">
                <?php if (get_sub_field('content')):?>
                    <?php the_sub_field('content'); ?>
                <?php endif;?>

                <?php if (get_sub_field('enable_cta_button')):?>
                    <a class="learn-more" target="_blank" <?php if (get_sub_field('button_id')):?>id="<?php the_sub_field('button_id'); ?>"<?php endif;?> href="<?php the_sub_field('button_link'); ?>">
                        <?php the_sub_field('button_text'); ?>
                    </a>
                <?php endif;?>
            </div>

        </div>
    </div>
    </div>
    </div>
</section>
<!-- Two Columns Section End -->