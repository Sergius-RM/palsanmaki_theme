<?php
/**
 * Template name: User Mmanagement
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

    <section class="admin_user_management_area">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-8">
                    <h1><?php the_title();?></h1>

                    <input type="text" id="user-search-input" placeholder="<?php esc_attr_e('Etsi käyttäjä...', 'default'); ?>">

                    <?php
                    $paged = max(1, get_query_var('paged'));
                    $args = array(
                        'role__not_in' => ['Administrator', 'pending_verification'], // Исключаем администраторов
                        'number' => 50, // Выводим по 50 пользователей
                        'orderby' => 'display_name', // Сортировка по алфавиту
                        'order' => 'ASC',
                        'paged' => $paged,
                    );

                    // Запрос пользователей
                    $user_query = new WP_User_Query($args);
                    $users = $user_query->get_results();
                    ?>

                    <ul id="user-list">
                        <?php foreach ($users as $user) : ?>
                            <li class="user_info_item">
                                <div class="user_name" data-bs-toggle="collapse" data-bs-target="#collapseUser<?php echo $user->ID;?>" aria-expanded="false" aria-controls="collapseUser<?php echo $user->ID;?>">
                                    <?php echo esc_html($user->display_name); ?>
                                    <div id="collapseUser<?php echo $user->ID;?>" class="collapse_user_data collapse ">
                                        <?php
                                            $phone_number = get_user_meta($user->ID, 'phone', true);
                                            $email = $user->user_email;
                                            $address = get_user_meta($user->ID, 'address', true);
                                            $postal_code = get_user_meta($user->ID, 'zip_code', true);
                                            $city = get_user_meta($user->ID, 'city', true);
                                        ?>
                                        <div>
                                            <strong><?php _e( 'ID: ', 'default' ) ?></strong> <?php echo $user->ID;?>
                                        </div>
                                        <div>
                                            <strong><?php _e( 'Sähköposti: ', 'default' ) ?></strong> <a href="mailto:<?php echo $email;?>"><?php echo $email;?></a>
                                        </div>
                                        <div>
                                            <strong><?php _e( 'Puhelinnumero: ', 'default' ) ?></strong> <a href="tel:<?php echo $phone_number;?>"><?php echo $phone_number;?></a>
                                        </div>
                                        <div>
                                            <strong><?php _e( 'Osoite: ', 'default' ) ?></strong> <?php echo $address;?>
                                        </div>
                                        <div>
                                            <strong><?php _e( 'Postinumero: ', 'default' ) ?></strong> <?php echo $postal_code;?>
                                        </div>
                                        <div>
                                            <strong><?php _e( 'Paikkakunta: ', 'default' ) ?></strong> <?php echo $city;?>
                                        </div>
                                    </div>
                                </div>

                                <div class="user_status">
                                    <?php if (get_user_meta($user->ID, 'banned', true)) : ?>
                                        <span class="user-status-banned"><?php _e('Banned', 'default'); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="user_ctrl_btn">
                                <button class="<?php echo (get_user_meta($user->ID, 'banned', true)) ? 'unban' : 'ban'; ?>" data-id="<?php echo esc_attr($user->ID); ?>">
                                    <i class="bi <?php echo (get_user_meta($user->ID, 'banned', true)) ? 'bi-unlock-fill' : 'bi-lock-fill'; ?>"></i>
                                    <span><?php _e((get_user_meta($user->ID, 'banned', true)) ? 'Unban' : 'Ban', 'default'); ?></span>
                                </button>


                                    <button class="delete" data-id="<?php echo esc_attr($user->ID); ?>">
                                        <i class="bi bi-ban"></i><i class="bi bi-trash-fill"></i> <?php _e('Poista', 'default'); ?>
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php
                    // Пагинация
                    $total_users = $user_query->get_total();
                    $total_pages = intval($total_users / 50) + ($total_users % 50 > 0 ? 1 : 0);

                        // Получаем ссылки пагинации
                        $pagination_links = paginate_links(array(
                            'base' => get_pagenum_link(1) . '%_%',
                            'format' => 'page/%#%',
                            'current' => $paged,
                            'total' => $total_pages,
                            'type' => 'array', // Получаем пагинацию в виде массива
                        ));

                        if ( ! empty( $pagination_links ) ) {
                            echo '<ul class="page-numbers">';
                            foreach ( $pagination_links as $link ) {
                                // Обертываем каждую ссылку в li
                                echo '<li>' . $link . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <script>
    jQuery(document).ready(function($) {
        $('#user-search-input').keyup(debounce(function() {
            var searchValue = $(this).val().toLowerCase();
            $('#user-list li').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1)
            });
        }, 500));
    });

    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    </script>

    <?php else:?>

        <script>
            window.location.replace("<?php echo home_url(); ?>");
        </script>

    <?php endif;?>


<?php
get_footer();

