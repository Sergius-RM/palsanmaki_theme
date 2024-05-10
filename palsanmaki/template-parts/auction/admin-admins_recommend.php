<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$post_id = 10;

$saved_products = array(
    1 => get_post_meta($post_id, 'sections_2_products_0_product_item', true),
    2 => get_post_meta($post_id, 'sections_2_products_1_product_item', true),
    3 => get_post_meta($post_id, 'sections_2_products_2_product_item', true),
);

foreach ($saved_products as &$products) {
    $products = maybe_unserialize($products);
    if (!is_array($products)) {
        $products = [];
    }
}

$product_items = get_field('current_auction', 'option');

?>

<div class="row admin_recommended_products">
    <?php
        $admin_id = 1;

        $arr = array(1, 2, 3);
        foreach ($arr as &$value) :
            $admin_recommend = 'admin_recommend_' . $admin_id;
            $checked_products = $saved_products[$admin_id] ?? [];
            if($value > 3) break; ?>

        <div id="<?php echo $admin_recommend;?>" class="col-12 col-sm-4 admin_recommend_item <?php echo $admin_recommend;?>">
            <h4>
                <?php if ($admin_id == 1):?>
                    <?php _e('Aki suosittelee tuotetta', 'default'); ?>
                <?php elseif ($admin_id == 2):?>
                    <?php _e('Heli suosittelee tuotetta', 'default'); ?>
                <?php elseif ($admin_id == 3):?>
                    <?php _e('Markku suosittelee tuotetta', 'default'); ?>
                <?php endif;?>
            </h4>
            <div class="admin_recommend_list">
                <?php
                    foreach ($product_items as $product_item) :

                        $post_object = get_post($product_item);
                        setup_postdata($GLOBALS['post'] =& $post_object);
                        ?>

                        <?php
                        $current_auction_id = get_the_ID();

                        $args = array(
                            'post_type'      => 'auction_items',
                            'posts_per_page' => -1,
                            'orderby' => 'meta_value_num', // Сортировка по числовому полю
                            'meta_key' => 'title_number', // Имя числового поля
                            'order' => 'ASC',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'current_auction_id',
                                    'value'   => $current_auction_id,
                                    'compare' => '=',
                                ),
                            ),
                        ); ?>

                        <?php $query_auction_items = new WP_Query($args);

                        if ($query_auction_items->have_posts()) :
                            while ($query_auction_items->have_posts()) : $query_auction_items->the_post();
                            $post_id = get_the_ID(); ?>

                                <label>
                                    <img src="<?php the_post_thumbnail_url(); ?>" alt="">
                                    <input type="radio" name="admin_products[<?php echo $admin_id;?>][]" value=" <?php echo get_the_ID();?>" <?php if (in_array(get_the_ID(), $checked_products)) : ?>checked<?php endif; ?>>
                                    <span><?php the_title(); ?></span>
                                </label><br>

                            <?php endwhile; ?>
                        <?php endif; ?>
                    <?php wp_reset_postdata(); ?>

                <?php endforeach; ?>
            </div>
        </div>

        <?php $admin_id++; ?>
    <?php endforeach; ?>

    <button id="save-admin-products" class="auction_details_link">
        <?php _e('Tallenna suositellut tuotteet', 'default'); ?>
    </button>
</div>

<script>
jQuery(document).ready(function($) {
    $('#save-admin-products').on('click', function(e) {
        e.preventDefault();
        var adminProducts = [];
        $('div[id^="admin_recommend_"]').each(function(index) {
            var productId = $(this).find('input[type="radio"]:checked').val();
            if (productId) {
                adminProducts.push(productId.trim());
            }
        });

        console.log('Отправляемые данные:', adminProducts);

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_featured_products',
                admin_products: adminProducts,
            },
            success: function(response) {
                console.log('Ответ сервера:', response);
                var messageBlock = $('<div/>', {
                    'class': 'success-message',
                    'text': response.data.message
                }).insertAfter('#save-admin-products');

                setTimeout(function() {
                    messageBlock.fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Ошибка AJAX запроса:', textStatus, errorThrown);
                alert('Ошибка при отправке запроса.');
            }
        });
    });
});
</script>
