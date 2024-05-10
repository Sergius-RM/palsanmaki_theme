<?php
/**
 * Template Name: Auction Post
 * Template Post Type: auction
 * The template for displaying all single posts
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>

    <?php get_template_part('template-parts/auction/single', 'hero_auction'); ?>
    <?php get_template_part('template-parts/auction/single', 'auction_products'); ?>

<?php get_footer();