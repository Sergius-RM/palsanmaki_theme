<?php
/**
 * The template for displaying footer.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$copyright_data = get_field('copyright_data', 'option');

?>

<!-- Footer Area Start -->
<footer id="site-footer" class="site-footer" role="contentinfo">
    <div class="container-fluid">
        <div class="row">

            <!-- Branding Area Start -->
            <div class="col-12 col-xs-6 col-sm-12 col-md-3 col-xl-3 site-branding">
                <a href="/" class="footer-logo">
                    <?php
                    $header_logo = get_theme_mod('header_logo');
                    $img = wp_get_attachment_image_src($header_logo, 'full');
                    if ($img) :
                        ?>
                        <img src="<?php echo $img[0]; ?>" alt="">
                    <?php endif; ?>
                </a>
            </div>
            <!-- END Branding Area -->

            <!-- Footer Nav Area Start -->
            <div class="col-12 col-xs-6 col-sm-4 col-md-3 col-xl-3 footer_nav footer_contacts" role="navigation">
                <h3>
                    <?php _e( 'Yhteystiedot', 'default' ) ?>
                </h3>

                <?php if (have_rows('topbaremails', 'option')) { ?>
                    <?php while (have_rows('topbaremails', 'option')) {
                        the_row(); ?>
                            <a href="mailto:<?php the_sub_field('top_bar_email_link');?>" target="_blank">
                                <i class="bi bi-envelope-fill"></i> <?php the_sub_field('top_bar_email');?>
                            </a>
                    <?php } ?>
                <?php } ?>

                <?php if (have_rows('topbarphones', 'option')) { ?>
                    <?php while (have_rows('topbarphones', 'option')) {
                        the_row(); ?>
                            <a href="tel:<?php the_sub_field('top_bar_phone_link');?>" target="_blank">
                                <i class="bi bi-telephone-fill"></i><?php the_sub_field('top_bar_phone');?></a>
                    <?php } ?>
                <?php } ?>

                <?php if (have_rows('physical_adress', 'option')) {
                    while (have_rows('physical_adress', 'option')) {
                        the_row(); ?>
                            <div class="physical_adress">
                                <i class="bi bi-geo-alt-fill"></i> <?php the_sub_field('short_physical_adress');?>
                            </div>
                    <?php } ?>
                <?php } ?>

                <?php if( have_rows('social_links', 'option') ): ?>
                    <div class="footer_socials">
                    <?php while( have_rows('social_links', 'option') ) : the_row(); ?>
                        <?php   $field = get_sub_field_object('service_ico');
                                $options = $field['choices'];
                                $selected = get_sub_field('service_ico');?>
                        <a target="_blank" href="<?php the_sub_field('url'); ?>">
                            <i class="bi <?php the_sub_field('service_ico'); ?>"></i> <?php echo $options[ $selected ]; ?>
                        </a>
                    <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-xs-6 col-sm-4 col-md-3 col-xl-3 footer_nav" role="navigation">
                <?php wp_nav_menu( array( 'theme_location' => 'footer-1' ) ); ?>
            </div>

            <!-- Footer Nav Area -->

            <!-- Ordering Area Start -->
            <div class="col-12 col-xs-6 col-sm-12 col-md-3 col-xl-3 serice_menu">
            <?php get_template_part( 'template-parts/sections/section-service_menu' ); ?>
            </div>
            <!-- END Ordering Area -->

        </div>
    </div>

    <?php
    global $wp;
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    $current_slug = trim( parse_url( $current_url, PHP_URL_PATH ), '/' );

        if ($current_slug === 'tapahtumat'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            // Find the element
            var elem = document.querySelector('.tribe-common.tribe-events.tribe-events-view.tribe-events-view--month');

            // Check if the element exists
            if (elem) {
                // Add the classes
                elem.classList.add('tribe-common--breakpoint-xsmall', 'tribe-common--breakpoint-medium', 'tribe-common--breakpoint-full');
            }
        });
        </script>
    <?php endif;?>

</footer>
 <!-- Footer Area End -->

<!-- START Copyright Area -->
<div class="container-fluid footer_copyright">
    <div class="row align-items-center">
        <div class="col-12 col-sm-6 col-xl-6 footer_copyright_menu">
            <?php dynamic_sidebar( 'footer_bottom' ); ?>
        </div>
        <div class="col-12 col-sm-6 col-xl-6 copyright_data">
            <?php echo $copyright_data;?>
        </div>
    </div>
</div>
<!-- END Copyright Area -->