<?php
/**
 * Template name: Auction page
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

get_header();

?>

    <?php get_template_part('template-parts/auction/section', 'hero_auction'); ?>

    <?php get_template_part('template-parts/auction/section', 'auction_products'); ?>

<?php
get_footer();