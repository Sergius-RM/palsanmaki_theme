<?php
/**
 * The site's entry point.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();

//get_sidebar();
?>

<?php get_template_part( 'template-parts/main-sections' ); ?>

<?php
get_footer();