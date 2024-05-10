<?php
/**
 * Actions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function my_enqueue_media_library() {
    if (!did_action('wp_enqueue_media')) {
        wp_enqueue_media();
    }
}
add_action('wp_enqueue_scripts', 'my_enqueue_media_library');


/*
 * Enqueue WP Styles to Header Part.
*/
function theme_styles()
{
    // Регистрирую стили
    wp_register_style('info_style', get_template_directory_uri() . '/style.css', '', '1.1', 'all');
    wp_register_style('main_style', get_template_directory_uri() . '/assets/css/style.css', '', '1.1', 'all');
    wp_register_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css', '', '5.3', 'all');
    wp_register_style('bootstrap-icons', get_template_directory_uri() . '/assets/css/bootstrap-icons.css', '', '1.1', 'all');
    wp_register_style('fontawesome', get_template_directory_uri() . '/assets/css/font-awesome-5.9.0.min.css', '', '5.9.0', 'all');
    wp_register_style('leaflet', get_template_directory_uri() . '/assets/css/leaflet.css', '', '1', 'all');
    wp_register_style('tiny-slider', get_template_directory_uri() . '/assets/css/tiny-slider.css', '', '1', 'all');
    wp_register_style('fancybox', get_template_directory_uri() . '/assets/css/fancybox.css', '', '1', 'all');

    // Подключаю стили
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('bootstrap-icons');
    wp_enqueue_style('info_style');
    wp_enqueue_style('fontawesome');
    wp_enqueue_style('leaflet');
    wp_enqueue_style('tiny-slider');
    wp_enqueue_style('fancybox');
    wp_enqueue_style('main_style');

};
// Создаем экшн в котором подключаем скрипты подключенные внутри функции theme_styles
add_action('wp_enqueue_scripts', 'theme_styles');

/*
 * Enqueue WP JS scripts to Footer Part.
*/
function theme_script() {
    // Подключаю скрипты с аргументом 'defer'
    wp_enqueue_script('jquery_script', get_template_directory_uri() . '/assets/js/jquery-3.6.0.min.js', array(), null, false);

    wp_enqueue_script('leaflet', get_template_directory_uri() . '/assets/js/leaflet.js', array('jquery'), null, false);
    wp_enqueue_script('bootstrap_script', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_script('tiny-slider', get_template_directory_uri() . '/assets/js/tiny-slider.js', array('jquery'), null, true);
    wp_enqueue_script('fancybox', get_template_directory_uri() . '/assets/js/fancybox.umd.js', array('jquery'), null, false);
    wp_enqueue_script('custom_script', get_template_directory_uri() . '/assets/js/scripts.js', array('jquery'), null, true);

    // Локализация параметров для скрипта аукциона (bid)
    wp_localize_script('custom_script', 'auctionBidParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'bidNonce' => wp_create_nonce('bid_nonce'),
        'bidAlreadyPlacedText' => __('Huudettu', 'default'),
        'errorOccurredText' => __('Error occurred', 'default'),
        'bidSuccessText' => __('Your bid has been placed.', 'default')
    ));

    // Локализация параметров для скрипта аналитики аукциона
    wp_localize_script('custom_script', 'auctionAnalyticsParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mark_winner_nonce'),
        'winnerText' => __('Voittaja', 'default'),
        'markWinnerText' => __('Merkkaa voittajaksi', 'default'),
    ));

    // Локализация параметров для скрипта личного кабинета пользователя
    wp_localize_script('custom_script', 'user_account_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('update_user_info_nonce'),
        'formProcessingText' => __('Päivittää...', 'default'),
        'formPlacedText' => __('Päivitetty', 'default'),
    ));

    // Локализация параметров для скрипта личного кабинета пользователя
    wp_localize_script('custom_script', 'mass_upload_img', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));

    wp_localize_script('custom_script', 'ajax_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('handle_auction_item_nonce'),
    ));

    wp_localize_script('custom_script', 'sortableParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sortable_nonce'),
    ));

    // Добавление параметров для управления пользователями
    wp_localize_script('custom_script', 'userManagementParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'deleteNonce' => wp_create_nonce('delete_user_nonce'),
        'banNonce' => wp_create_nonce('ban_user_nonce'),
        'unbanNonce' => wp_create_nonce('unban_user_nonce'),
    ));

    wp_localize_script('custom_script', 'postageParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'emailNonce' => wp_create_nonce('send_winner_email_nonce'),
        // другие параметры
    ));

    wp_localize_script('custom_script', 'postageAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('save_postage_info_nonce')
    ));

}
add_action('wp_enqueue_scripts', 'theme_script');

function load_custom_wp_admin_style() {
    if ( is_user_logged_in() && current_user_can('manage_options') ) {
        // Проверка, находимся ли мы на нужной странице. Используйте is_page() с ID или слагом страницы.
        if ( is_page('user-account') || is_page_template('template-user-account.php') ) {
            // Замените 'your-stylesheet-path' и 'your-script-path' на актуальные пути.
            wp_enqueue_style('acf-input', ACF_URL . 'assets/css/acf-input.css', array('acf-global'), null, true);
            wp_enqueue_script('acf-input', ACF_URL . 'assets/js/acf-input.min.js', array('acf'), null, true);
        }
    }
}
add_action('wp_enqueue_scripts', 'load_custom_wp_admin_style');


function custom_login_styles() {
    wp_enqueue_style( 'login-styles', get_stylesheet_directory_uri() . '/assets/css/login-style.css' );
  }
  add_action( 'login_enqueue_scripts', 'custom_login_styles' );