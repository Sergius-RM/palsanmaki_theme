<?php
/**
 * Template name: Admin Add New Auction
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

get_header();
?>

    <?php if (current_user_can('manage_options')):?>

        <?php get_template_part('template-parts/auction/admin', 'auction_new'); ?>

    <?php else:?>

        <script>
            // JavaScript для редиректа на главную страницу
            window.location.replace("<?php echo home_url(); ?>");
        </script>

    <?php endif;?>


<?php
get_footer();