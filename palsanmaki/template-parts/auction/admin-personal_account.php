<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
    <div class="user_tab_two_coll">

        <div class="admin_control_btn">

            <h3><?php _e( 'Nopea pääsy', 'default' ) ?></h3>

            <a class="auction_manage" href="<?php the_field('admin_auction_list_link', 'option');?>">
                <i class="bi bi-list-task"></i> <?php the_field('admin_auction_list_account', 'option');?>
            </a>

            <a href="<?php the_field('admin_auction_new_link', 'option');?>" class="add_new_auction">
                <i class="bi bi-file-earmark-plus"></i> <?php the_field('admin_auction_new_account', 'option');?>
            </a>

            <?php
                $product_items = get_field('current_auction', 'option');

                if ($product_items) {
                    $first_product_item = $product_items[0];
                    $post_id = $first_product_item->ID;

                    $admin_auction_details_link = get_field('admin_auction_details_link', 'option');
                    $separator = (parse_url($admin_auction_details_link, PHP_URL_QUERY) ? '&' : '?');
                    $auction_id_link = add_query_arg(['auction_id' => $post_id], $admin_auction_details_link . $separator);
                ?>
                    <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                        <i class="bi bi-pencil-square"></i> <?php the_field('admin_auction_details_account', 'option');?>
                    </a>

                <?php
                    $admin_analytics_link = get_field('admin_analytics_link', 'option');
                    $separator = (parse_url($admin_analytics_link, PHP_URL_QUERY)) ? '&' : '?';
                    $auction_id_link = add_query_arg(['auction_id' => $post_id], $admin_analytics_link . $separator);
                ?>
                <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                    <i class="bi bi-clipboard-data"></i> <?php the_field('admin_analytics_account', 'option');?>
                </a>

                <?php
                    $admin_mailing_list_link = get_field('admin_mailing_list_link', 'option');
                    $separator = (parse_url($admin_mailing_list_link, PHP_URL_QUERY)) ? '&' : '?';
                    $auction_id_link = add_query_arg(['auction_id' => $post_id], $admin_mailing_list_link . $separator);
                ?>
                <a href="<?php echo esc_url($auction_id_link); ?>" class="auction_details_link">
                    <i class="bi bi-envelope"></i> <?php the_field('admin_mailing_list_account', 'option');?>
                </a>

                <?php
                    $print_userlist_link = '/for-pdf/generate-userlist.php';
                    $separator = (parse_url($print_userlist_link, PHP_URL_QUERY)) ? '&' : '?';
                    $userlist_link = add_query_arg(['auction_id' => $post_id], $print_userlist_link . $separator);
                ?>
                <a href="<?php echo esc_url($userlist_link); ?>" class="auction_details_link" target="_blank">
                    <i class="bi bi-printer-fill"></i> <?php the_field('admin_winner_list_pdf', 'option');?>
                </a>
            <?php } ?>

            <a class="user_management" href="<?php the_field('admin_user_management_link', 'option');?>">
                <i class="bi bi-people-fill"></i> <?php the_field('admin_user_management_account', 'option');?>
            </a>

            <a class="user_management" href="<?php the_field('general_analytics_link', 'option');?>">
                <i class="bi bi-info-circle"></i> <?php the_field('general_analytics', 'option');?>
            </a>

        </div>
        <div>
            <?php
            if ( is_user_logged_in() && current_user_can('administrator') ) {

                // Получаем ключ поля для 'current_auction'
                $field_key = "field_65b7fac09c9e9"; // Замените на ключ вашего поля 'current_auction'
                $field = get_field_object($field_key);

                if( $field ) {
                    echo '<div class="acf-form-wrapper">';
                        acf_form(array(
                            'field_groups' => array($field['ID']),
                            'fields' => array($field_key),
                            'post_id' => 'option',
                            'posts_per_page' => -1,
                            'form' => true,
                            'return' => add_query_arg( 'updated', 'true', get_permalink() ),
                            'submit_value' => 'Tallenna'
                        ));
                    echo '</div>';
                }
            }
            ?>
        </div>

    </div>

    <?php get_template_part('template-parts/auction/admin', 'admins_recommend'); ?>

<script>
jQuery(document).ready(function($) {
    // Обработка отправки формы
    $('input.acf-button').click(function(e) {
        e.preventDefault();

        // Получаем ID аукциона из скрытого поля формы
        var auction_ids = [];
        $('input[name="acf[field_65b7fac09c9e9][]"]').each(function() {
            auction_ids.push($(this).val());
        });
        var auction_name = $('.acf-rel-item.acf-rel-item-remove').first().text().trim();
        auction_name = auction_name.replace(/[\s\S]*?([^\s]+[\s]*[^\s]+).*$/, "$1");
        // Проверяем, что ID аукциона существует
        if (auction_ids.length === 0) {
            alert('Huutokauppoja ei ole valittu');
            return;
        }

        // Отправляем AJAX запрос на сохранение
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'save_current_auction',
                auction_id: auction_ids
            },
            success: function(response) {
                if (response.success) {
                    var messageBlock = $('<div class="success-message" style="display: none; margin-top: 30px;"></div>').text('Huutokaupat päivitetty');
                    $('input.acf-button').after(messageBlock);
                    messageBlock.fadeIn();

                    // Убираем сообщение через 10 секунд
                    setTimeout(function() {
                        messageBlock.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 10000);

                } else {
                    alert('Ошибка: ' + response.data);
                }
            },
            complete: function() {
                // Вернуть исходный текст кнопки, если была логика индикации загрузки
            }
        });
    });
});

</script>