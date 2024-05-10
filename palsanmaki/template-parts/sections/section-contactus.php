<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<!-- Contact US Section Start -->
<section class="contactus_section wrap_two_columns">
    <div class="container">
        <div class="row">
        <div class="col-sm-9 mx-auto">

        <div class="row mx-auto section_two_columns">

            <div class="col-sm-12 col-md-6 col-lg-6 contact_info">
                <?php if( get_sub_field('contact_info_title') ): ?>
                    <h3><?php the_sub_field('contact_info_title'); ?></h3>
                <?php endif;?>

                <?php if (have_rows('contact_address')) {
                    while (have_rows('contact_address')) {
                        the_row(); ?>
                            <div class="physical_adress">
                                <i class="bi bi-geo-alt-fill"></i> <?php the_sub_field('adress_item');?>
                            </div>
                    <?php } ?>
                <?php } ?>

                <?php if (have_rows('contact_phones')) { ?>
                    <?php while (have_rows('contact_phones')) {
                        the_row(); ?>
                            <a href="tel:<?php the_sub_field('phones_item_link');?>" target="_blank">
                                <i class="bi bi-telephone-fill"></i><?php the_sub_field('phones_item');?>
                            </a>
                    <?php } ?>
                <?php } ?>

                <?php if (have_rows('contact_emails')) { ?>
                    <?php while (have_rows('contact_emails')) {
                        the_row(); ?>
                            <a href="mailto:<?php the_sub_field('email_item_link');?>" target="_blank">
                                <i class="bi bi-envelope-fill"></i> <?php the_sub_field('email_item');?>
                            </a>
                    <?php } ?>
                <?php } ?>

                <?php if( have_rows('social_media_links') ): ?>
                    <div class="footer_socials">
                    <?php while( have_rows('social_media_links') ) : the_row(); ?>
                        <?php   $field = get_sub_field_object('service_ico');
                                $options = $field['choices'];
                                $selected = get_sub_field('service_ico');?>
                        <a target="_blank" href="<?php the_sub_field('link'); ?>">
                            <i class="bi <?php the_sub_field('service_ico'); ?>"></i> <?php echo $options[ $selected ]; ?>
                        </a>
                    <?php endwhile; ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 contactus_content">
                <?php if( get_sub_field('contactus_title') ): ?>
                    <h3><?php the_sub_field('contactus_title'); ?></h3>
                <?php endif;?>

                <?php if( get_sub_field('contactus_content') ): ?>
                    <p><?php the_sub_field('contactus_content'); ?></p>
                <?php endif;?>
            </div>

        </div>
        </div>
        </div>
    </div>
</section>
<!-- Contact US Section End -->