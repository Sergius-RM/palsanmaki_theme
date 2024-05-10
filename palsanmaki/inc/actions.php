<?php
/**
 * Actions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// без этого в админке просто не будет пункта "меню"
add_theme_support('menus');

function wpspec_menu_desc( $item_output, $item, $depth, $args ) {
    if ($item->description) {
        $item_output = str_replace( '</a>', '<span class="description">' . $item->description . '</span></a>', $item_output );
    }

    return $item_output;
}

add_filter( 'walker_nav_menu_start_el', 'wpspec_menu_desc', 10, 4 );

/**
 * Register navigation menus.
 */
register_nav_menus(
  [
    'menu-1' => esc_html__('Primary menu', 'greatcompany'),
    'footer-1' => esc_html__('Footer menu', 'greatcompany'),
  ]
);

register_sidebar(array(
    'name'          => __('Header Widget Area', 'wedding'),
    'id'            => 'header_1',
    'description'   => __('Appears on posts and pages in the sidebar.', 'wedding'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
));

register_sidebar(array(
    'name'          => __('Footer Copyright Widget Area', 'wedding'),
    'id'            => 'footer_bottom',
    'description'   => __('Appears on posts and pages in the sidebar.', 'wedding'),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
));

/**
 * WP Remove Guttenberg
 */

add_action('init', 'remove_guttenberg_from_pages', 10);
function remove_guttenberg_from_pages() {
    remove_post_type_support('page', 'editor');
}

/*-----------------------------------------------*
 * WP New Image Sizes
/*-----------------------------------------------*/
add_theme_support('custom-header');
// Add RSS feed links to <head> for posts and comments.
add_theme_support( 'automatic-feed-links' );
// Enable support for Post Thumbnails, and declare sizes.
add_theme_support( 'post-thumbnails' );
// Theme resize image
add_image_size( 'image_full'  , 1920, 1280, false ); //header carousel - aspect 3:2
add_image_size( 'image_blogpost', 1200 , 800, true ); //single blog - aspect 3:2
add_image_size( 'image_mediumM', 800 , 450, true ); //single Reference - aspect 16:9
add_image_size( 'image_small' , 480 , 320, true ); //blog masonry - aspect 3:2
add_image_size( 'image_reference' , 450 , 300, true ); //blog Overlay - aspect 3:2
add_image_size( 'image_allreferences' , 675 , 450, true ); //All references grid view - aspect 3:2
add_image_size( 'image_latestnews' , 390 , 260, true ); //Grid latestnews - aspect 3:2
add_image_size( 'image_team'  , 540 , 650, false );
add_image_size( 'image_square'  , 450 , 450, false );
add_image_size( 'image_thumb' , 100 , 100, false );

function my_pre_get_posts( $query ) {
    // do not modify queries in the admin
    if( is_admin() ) {
        return $query;
    }

    // only modify queries for 'event' post type
    if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'event' ) {

        $query->set('orderby', 'meta_value');
        $query->set('meta_key', 'start_date');
        $query->set('order', 'DESC');

    }

    // return
    return $query;

}

add_action('pre_get_posts', 'my_pre_get_posts');


/*-----------------------------------------------*
  Custom excerpt
/*-----------------------------------------------*/
function custom_excerpt($limit) {
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt) >= $limit) {
        array_pop($excerpt);
        $excerpt = implode(" ", $excerpt);
    } else {
        $excerpt = implode(" ", $excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`', '', $excerpt);
    return $excerpt;
}

