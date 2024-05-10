<?php

// Register Auction Post Type
function create_auction_post_type() {
    $labels = array(
      'name' => __( 'Auctions' ),
      'singular_name' => __( 'Auction' ),
      'add_new' => __( 'New Auction' ),
      'add_new_item' => __( 'Add New Auction' ),
      'edit_item' => __( 'Edit Auction' ),
      'new_item' => __( 'New Auction' ),
      'view_item' => __( 'View Auction' ),
      'search_items' => __( 'Search auction' ),
      'not_found' =>  __( 'No auction Found' ),
      'not_found_in_trash' => __( 'No auction found in Trash' ),
      );
    $args = array(
      'labels' => $labels,
      'has_archive' => true,
      'public' => true,
      'hierarchical' => false,
      'menu_position' => 5,
      'menu_icon' => 'dashicons-hammer',
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'show_in_admin_bar'   => true,
      'can_export'          => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'page',
      'supports' => array(
        'title',
        'editor',
        'excerpt',
        'custom-fields',
        'thumbnail',
        'trackbacks',
        'page-attributes'
        ),
      );
    register_post_type( 'auction', $args );
  }
  add_action( 'init', 'create_auction_post_type' );

// Register Auction Items Post Type
function create_auction_items_post_type() {
  $labels = array(
    'name' => __( 'Auction products' ),
    'singular_name' => __( 'Auction product' ),
    'add_new' => __( 'New Auction product' ),
    'add_new_item' => __( 'Add New Auction product' ),
    'edit_item' => __( 'Edit Auction product' ),
    'new_item' => __( 'New Auction product' ),
    'view_item' => __( 'View Auction product' ),
    'search_items' => __( 'Search auction product' ),
    'not_found' =>  __( 'No auction product Found' ),
    'not_found_in_trash' => __( 'No auction product found in Trash' ),
    );
  $args = array(
    'labels' => $labels,
    'has_archive' => true,
    'public' => true,
    'hierarchical' => false,
    'menu_position' => 5,
    'menu_icon' => 'dashicons-tickets-alt',
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'can_export'          => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'custom-fields',
      'thumbnail',
      'trackbacks',
      'page-attributes'
      ),
    );
  register_post_type( 'auction_items', $args );
}
add_action( 'init', 'create_auction_items_post_type' );