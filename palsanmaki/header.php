<?php
/**
 * The template for displaying the header
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// if (current_user_can('manage_options')) {
//     if ( is_page('user-account') || is_page_template('template-user-account.php') ) {
//         acf_form_head();
//     }
// }
?>
<!DOCTYPE html>
<html
    <?php language_attributes(); ?>
    class="no-js"
>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <?php $viewport_content = apply_filters('hello_elementor_viewport_content', 'width=device-width, initial-scale=1'); ?>
        <meta
            name="viewport"
            content="<?php echo esc_attr($viewport_content); ?>"
        >
        <link rel="profile" href="https://gmpg.org/xfn/11">
        <?php wp_head(); ?>
<?php
    if (current_user_can('manage_options')) {
        if ( is_page('user-account') || is_page_template('template-user-account.php') ) {
            acf_form_head();
        }
    }
 ?>
        <?php if (  get_field( 'google_analytics', 'option') ) :?>
            <?php $headcode = get_field('google_analytics', 'option');
                print $headcode;?>
        <?php endif ?>

    </head>

    <body <?php body_class();?>>

        <?php if (  get_field( 'google_analytics_body', 'option') ) :?>
            <?php $bodycode = get_field('google_analytics_body', 'option');
                print $bodycode; ?>
        <?php endif ?>

        <?php wp_body_open();
            get_template_part('template-parts/header');
        ?>
