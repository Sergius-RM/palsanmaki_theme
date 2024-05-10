<?php
/**
 * Actions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Malicious URL Protection
if (strpos($_SERVER['REQUEST_URI'], "eval(") || strpos($_SERVER['REQUEST_URI'], "CONCAT") || strpos($_SERVER['REQUEST_URI'], "UNION+SELECT") || strpos($_SERVER['REQUEST_URI'], "base64")) {
  @header("HTTP/1.1 400 Bad Request");
  @header("Status: 400 Bad Request");
  @header("Connection: Close");
  @exit;
}

// Automatic spam protection
function true_stop_spam($commentdata)
{
  // we will hide the usual comment field using CSS
  $fake = trim($_POST['comment']);
  // filling it with robots will result in an error, the comment will not be sent
  if (!empty($fake)) {
      wp_die('spam comment!');
  }
  // then we will assign it the value of the comment field, which for people
  $_POST['comment'] = trim($_POST['true_comment']);

  return $commentdata;
}

add_filter('pre_comment_on_post', 'true_stop_spam');

// Prohibition of pingbacks and trackbacks on yourself
function true_disable_self_ping(&$links)
{
  foreach ($links as $l => $link) {
      if (0 === strpos($link, get_option('home'))) {
          unset($links[$l]);
      }
  }
}

add_action('pre_ping', 'true_disable_self_ping');

// Hiding the WordPress Version
function true_remove_wp_version_wp_head_feed()
{
  return '';
}

add_filter('the_generator', 'true_remove_wp_version_wp_head_feed');

// Allow download svg
function allow_type($type) {
  $type['svg'] = 'image/svg+xml';
  return $type;
}
add_filter('upload_mimes', 'allow_type');

function my_customize_register( $wp_customize ) {
  $wp_customize->add_setting('header_logo', array(
      'default' => '',
      'sanitize_callback' => 'absint',
  ));

  $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'header_logo', array(
      'section' => 'title_tagline',
      'label' => 'Footer Logo'
  )));

  $wp_customize->selective_refresh->add_partial('header_logo', array(
      'selector' => '.header-logo',
      'render_callback' => function() {
          $logo = get_theme_mod('header_logo');
          $img = wp_get_attachment_image_src($logo, 'full');
          if ($img) {
              return '<img src="' . $img[0] . '" alt="">';
          } else {
              return '';
          }
      }
  ));
}
add_action( 'customize_register', 'my_customize_register' );


remove_action( 'load-update-core.php', 'wp_update_plugins' );
add_filter( 'pre_site_transient_update_plugins', function() {
    return null;
});
wp_clear_scheduled_hook( 'wp_update_plugins' );

// Функция для изменения запроса поиска
function exclude_custom_post_type_from_search( $query ) {
  if ( is_admin() || ! $query->is_main_query() ) {
      return;
  }

  if ( $query->is_search() ) {
      $post_types = $query->get( 'post_type' );

      // Проверяем, является ли $post_types массивом
      if ( is_array( $post_types ) ) {
          // Удаляем кастомный тип записей "team" из массива
          $post_types = array_diff( $post_types, array( 'team' ) );
      } else {
          // Если $post_types не является массивом, преобразуем его в массив и удаляем "team"
          $post_types = array_diff( explode( ',', $post_types ), array( 'team' ) );
      }

      $query->set( 'post_type', $post_types );
  }
}
add_action( 'pre_get_posts', 'exclude_custom_post_type_from_search' );


function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
      show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');


add_action('wp_ajax_update_featured_products', 'update_featured_products_callback');
function update_featured_products_callback() {
    global $wpdb;

    if (empty($_POST['admin_products'])) {
        wp_send_json_error(['message' => 'Данные не переданы']);
        return;
    }

    $admin_products = $_POST['admin_products'];
    $post_id = 10;
    $successCount = 0;

    foreach ($admin_products as $index => $product_id) {
        $meta_key = 'sections_2_products_' . $index . '_product_item';
        $updated = $wpdb->update(
            $wpdb->postmeta,
            ['meta_value' => serialize([$product_id])],
            ['post_id' => $post_id, 'meta_key' => $meta_key]
        );
        if ($updated) {
            $successCount++;
        }
    }
    wp_send_json_success(['message' => 'Suositellut tuotteet päivitetty']);
}

function change_auction_menu_titles_and_add_script() {
  $current_auctions = get_field('current_auction', 'option');
  $auction1 = $current_auctions[0] ?? null;
  $auction2 = $current_auctions[1] ?? null;

  $auction_id1 = $auction1->ID ?? '';
  $auction_id2 = $auction2->ID ?? '';

  // Получаем названия аукционов
  $first_auction_title = $auction_id1 ? get_the_title($auction_id1) : '';
  $second_auction_title = $auction_id2 ? get_the_title($auction_id2) : '';

  // Добавляем скрипт для замены названий в меню
  echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
    var firstMenu = document.getElementById('menu-item-1029');
    var secondMenu = document.getElementById('menu-item-1018');

    if(firstMenu) {
      var firstLink = firstMenu.querySelector('a');
      if(firstLink) firstLink.textContent = '" . esc_js($first_auction_title) . "';
    }

    if(secondMenu) {
      var secondLink = secondMenu.querySelector('a');
      if(secondLink) secondLink.textContent = '" . esc_js($second_auction_title) . "';
    }
  });
  </script>";
}

add_action('wp_footer', 'change_auction_menu_titles_and_add_script');

function add_class_to_pagination($link) {
  $link = str_replace("prev page-numbers", "prev page-numbers custom-prev-class", $link);
  $link = str_replace("next page-numbers", "next page-numbers custom-next-class", $link);
  return $link;
}
add_filter('paginate_links_output', 'add_class_to_pagination');


//////////////////////////////////////////////////////////////////////

function disable_canonical_redirect_for_paged_auction($redirect_url) {
  if (is_paged()) {
      // Если мы находимся на странице кастомного типа записей 'auction' и это пагинированная страница
      return false; // Отменяем канонический редирект
  }
  return $redirect_url; // В противном случае возвращаем оригинальный URL для редиректа
}
add_filter('redirect_canonical', 'disable_canonical_redirect_for_paged_auction', 10, 2);


/**
 * Функция для проверки и обновления статуса аукционов
 */
function add_five_minutes_cron_interval( $schedules ) {
  $schedules['every_five_minutes'] = array(
      'interval' => 30 * 60, // Интервал в секундах.
      'display'  => esc_html__( 'Every Five Minutes' ),
  );
  return $schedules;
}
add_filter( 'cron_schedules', 'add_five_minutes_cron_interval' );

function check_and_update_auction_status() {
  $current_datetime = new DateTime('now', new DateTimeZone('Europe/Helsinki'));

  // Получаем все текущие аукционы
  $current_auctions = get_field('current_auction', 'option');

  foreach ($current_auctions as $auction_id) {
      $auction_date = get_field('auction_date', $auction_id);
      $auction_time = get_field('auction_time', $auction_id);

      $auction_datetime = DateTime::createFromFormat('d.m.Y H:i', $auction_date . ' ' . $auction_time, new DateTimeZone('Europe/Helsinki'));

      if ($auction_datetime < $current_datetime) {
          // Время аукциона истекло, обновляем статус
          update_field('stop_auction', true, $auction_id);
      }
  }
}
/**
* Планируем выполнение функции check_and_update_auction_status() каждый час
*/
if ( ! wp_next_scheduled( 'check_auction_status_every_five_minutes' ) ) {
  wp_schedule_event( time(), 'every_five_minutes', 'check_auction_status_every_five_minutes' );
}

add_action( 'check_auction_status_every_five_minutes', 'check_and_update_auction_status' );
