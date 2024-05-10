<?php
/**
 * Template name: Admin Auction Details
 */

get_header();

?>

<?php if (current_user_can('manage_options')): ?>

    <?php get_template_part('template-parts/auction/admin', 'auction_details'); ?>

<?php else:?>

    <script>
        window.location.replace("<?php echo home_url(); ?>");
    </script>

<?php endif;?>


<?php get_footer();
