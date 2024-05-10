<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$current_auction_id = isset($_GET['auction_id']) ? absint($_GET['auction_id']) : 0;
$auction_page = get_post($current_auction_id);

$current_auction_title = get_the_title($current_auction_id);
$current_auction_date = get_field('auction_date', $current_auction_id);

$current_auction_time = get_field('auction_time', $current_auction_id);
$current_auction_location = get_field('auction_location', $current_auction_id);
$auction_content = apply_filters('the_content', $auction_page->post_content);
$description = wp_strip_all_tags($auction_content);

add_filter('posts_clauses', 'numeric_posts_orderby');
$existing_products = new WP_Query(array(
    'post_type' => 'auction_items',
    'posts_per_page' => -1,
    // 'orderby' => 'menu_order',
    'orderby' => 'title_number',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'current_auction_id',
            'value' => $current_auction_id,
            'compare' => '=',
        ),
    ),
));

remove_filter('posts_clauses', 'numeric_posts_orderby');
?>

    <div class="auction_products_grid container">

        <h1><?php echo esc_html($current_auction_title); ?></h1>

        <div class="auction_data" data-bs-toggle="collapse" data-bs-target="#collapseAuctionUpdate" aria-expanded="false" aria-controls="collapseAuctionUpdate">
            <span><i class="bi bi-calendar3"></i> <?php echo esc_html($current_auction_date); ?></span>
            <span><i class="bi bi-clock"></i> <?php echo esc_html($current_auction_time); ?></span>
            <span><i class="bi bi-geo-alt-fill"></i> <?php echo esc_html($current_auction_location); ?></span>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div id="collapseAuctionUpdate" class="auction_creating_section collapse ">
            <form id="auction-form" method="post">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-sm-10">

                            <div class="d-flex">
                                <label for="auction-title">
                                    <?php _e( 'Otsikko', 'default' ) ?>
                                </label>
                                <input type="text" name="auction_title" id="auction-title" value="<?php echo esc_attr($current_auction_title); ?>" required>

                                <label for="auction-date">
                                    <?php _e( 'Päivämäärä', 'default' ) ?>
                                </label>
                                <?php   list($day, $month, $year) = explode('.', $current_auction_date);
                                        $formatted_date = $year . '-' . $month . '-' . $day;?>
                                <input type="date" name="auction_date" id="auction-date" value="<?php echo esc_attr($formatted_date); ?>" required>

                                <label for="auction-time">
                                    <?php _e( 'Lopetusaika', 'default' ) ?>
                                </label>
                                <input type="time" name="auction_time" id="auction-time" value="<?php echo esc_attr($current_auction_time); ?>" required>

                                <label for="auction-location">
                                    <?php _e( 'Osoite', 'default' ) ?>
                                </label>
                                <input type="text" name="auction_location" id="auction-location" value="<?php echo esc_attr($current_auction_location); ?>" required>
                            </div>

                            <div class="d-flex">
                                <label for="auction-description">
                                    <?php _e( 'Kuvaus', 'default' ) ?>
                                </label>
                                <textarea name="auction_description" class="auction-description" id="auction-description"><?php echo esc_textarea($description); ?></textarea>
                            </div>

                        </div>
                        <div class="col-12 col-sm-2">
                            <input type="submit" value="<?php echo $current_auction_id > 0 ? 'Päivitä' : 'Luo huutokauppa'; ?>">
                            <input type="hidden" name="current_auction_id" value="<?php echo esc_attr($current_auction_id); ?>">
                            <?php wp_nonce_field('auction_nonce_action', 'auction_nonce'); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <h3><?php _e('Uudet tuotteet', 'default'); ?></h3>

        <div id="forms-container">
            <?php if ($existing_products->have_posts()): ?>
                <?php while ($existing_products->have_posts()): $existing_products->the_post(); ?>
                    <form class="add-auction-item-form image-form" data-id="<?php echo get_the_ID();?>" method="post" enctype="multipart/form-data">
                        <div class="container">
                            <div class="row">

                                <div class="col-12 col-sm-4">
                                    <div class="image-upload-wrapper">
                                        <label><?php _e('Lisää kuvia', 'default'); ?>
                                            <input type="file" class="image-input" name="auction_item_images[]" multiple>
                                            <i class="bi bi-download"></i>
                                            <input type="hidden" name="images_order" value="">
                                            <input type="hidden" name="auction_item_image_ids" value="">
                                        </label>
                                        <div class="image-preview-container">
                                            <?php
                                            // Получаем ID миниатюры поста
                                            $thumbnail_id = get_post_thumbnail_id($post->ID);

                                            // Если миниатюра существует, выводим ее
                                            if ($thumbnail_id) {
                                                $thumbnail_url = wp_get_attachment_url($thumbnail_id);
                                                echo '<img src="' . esc_url($thumbnail_url) . '" data-id="' . esc_attr($thumbnail_id) . '" alt="">';
                                            }

                                            $attachment_ids = get_posts([
                                                'post_parent' => get_the_ID(),
                                                'post_type' => 'attachment',
                                                'numberposts' => -1,
                                                'post_status' => 'inherit',
                                                'post_mime_type' => 'image',
                                                'fields' => 'ids',
                                            ]);

                                            foreach ($attachment_ids as $attachment_id) {
                                                $image_url = wp_get_attachment_url($attachment_id);
                                                echo '<img src="' . esc_url($image_url) . '" data-id="' . esc_attr($attachment_id) . '" alt="" style="max-width: 70px; max-height: 70px;">';
                                            }
                                            ?>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 auction_item_info">
                                    <label for="auction_item_title_<?php the_ID(); ?>">
                                        <?php _e('Tuotenimi', 'default'); ?>
                                        <input type="text" name="auction_item_title" value="<?php echo esc_attr(get_the_title()); ?>">
                                    </label>
                                    <label for="auction_item_sku_<?php the_ID(); ?>"><?php _e('', 'default'); ?>
                                        <?php _e('Asiakasnumero', 'default'); ?>
                                        <input type="text" name="auction_item_sku" value="<?php echo esc_attr(get_post_meta(the_field('product_id'), 'sku', true)); ?>">
                                    </label>
                                    <label for="auction_item_description_<?php the_ID(); ?>">
                                        <?php _e('Tuotekuvaus', 'default'); ?>
                                        <textarea name="auction_item_description"><?php echo esc_textarea(get_the_content()); ?></textarea>
                                    </label>
                                </div>
                                <div class="col-12 col-sm-2 auction_item_actions">
                                    <input type="hidden" name="current_auction_id" value="<?php echo esc_attr($current_auction_id); ?>">
                                    <input type="hidden" name="action" value="update_auction_item">
                                    <input type="hidden" name="auction_item_id" value="<?php the_ID(); ?>">
                                    <input type="submit" value="<?php _e('Päivitä', 'default'); ?>" class="update-btn">
                                    <input type="button" value="<?php _e('Poista', 'default'); ?>" class="delete-btn">
                                    <div class="real-post-id"></div>
                                    <?php wp_nonce_field('handle_auction_item_nonce', 'handle_auction_item_nonce'); ?>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php endif; ?>

        </div>

        <div class="mass-upload-area" style="display:none;">
            <div id="mass-image-upload-container">
                <button id="mass-upload-button">
                    <?php _e('Lisää kuvia', 'default'); ?> <i class="bi bi-download"></i>
                </button>
            </div>
        </div>

        <div id="control_button_block" class="control_button_block">
            <div class="show-upload-area-button"><i class="bi bi-images"></i> <?php _e('Lisää kuvia', 'default'); ?></div>
            <div class="add-new-item-btn"><i class="bi bi-plus-circle"></i> <?php _e('Lisää uusi tuote', 'default'); ?></div>
            <button class="mass_prod_creation"><i class="bi bi-arrow-repeat"></i> <?php _e('Päivitä kaikki', 'default'); ?></button>
            <div id="progress-indicator"></div>
        </div>

        </div>

    </div>

<script>
    jQuery(function ($) {
    // обновление данных аукциона
    $('#auction-form').on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'create_or_update_auction');

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#auction-form input[type="submit"]').val('Huutokauppa päivitetty').addClass('successfully');
                    setTimeout(function() {
                        $('#auction-form input[type="submit"]').val('Päivitä huutokauppa').removeClass('successfully');
                    }, 3000);
                    $('input[name="current_auction_id"]').val(response.data.auction_id);

                    // Обновляем информацию в блоках
                    $('h1').text(response.data.title);

                        // Форматируем дату
                    var date = new Date(response.data.date);
                    var day = date.getDate();
                    var month = date.getMonth() + 1;
                    var year = date.getFullYear();
                    var formattedDate = day + '.' + month + '.' + year;
                    $('.auction_data span').eq(0).html('<i class="bi bi-calendar3"></i> ' + formattedDate);
                    $('.auction_data span').eq(1).html('<i class="bi bi-clock"></i> ' + response.data.time);
                    $('.auction_data span').eq(2).html('<i class="bi bi-geo-alt-fill"></i> ' + response.data.location);

                    // Заполняем форму данными, полученными в ответе
                    $('#auction-title').val(response.data.title);
                    $('#auction-date').val(response.data.date);
                    $('#auction-time').val(response.data.time);
                    $('#auction-location').val(response.data.location);
                    $('#auction-description').val(response.data.description);
                }
            },
            error: function (error) {
                console.error('Ошибка AJAX запроса: ', error);
            }
        });
    });
});
</script>

<script>
jQuery(document).ready(function($) {

    // Обработчик клика по кнопке "Lisää kuvia" для массовой загрузки изображений
    $('#mass-upload-button').on('click', function(e) {
        e.preventDefault();

        // Настройки медиа-библиотеки WordPress
        var mediaUploader = wp.media({
            title: 'Valitse kuvat huutokaupalle',
            button: {
                text: 'Lisää kaikki valitut kuvat'
            },
            multiple: true
        });

        // После выбора изображений
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').map(function(attachment) {
                attachment = attachment.toJSON();
                return attachment;
            });

            // Создаем формы для каждого изображения
            $.each(attachments, function(index, attachment) {
                var formHTML = createAuctionItemForm(attachment, index); // Функция для создания HTML формы
                $('#forms-container').append(formHTML);
            });

            // После добавления всех форм, инициализируем предпросмотр и сортировку изображений
            $('.mass-upload-area').hide();
            initImagePreviews();
            initSortable();
        });

        // Открытие медиа-библиотеки
        mediaUploader.open();
    });

    // Функция для создания HTML формы аукционного элемента
    function createAuctionItemForm(attachment, index) {
        var currentAuctionId = "<?php echo $current_auction_id; ?>";
        var tempId = 'temp-' + Date.now() + '-' + index;
        var formHtml = '<form class="add-auction-item-form image-form" method="post" data-id="' + currentAuctionId + '" enctype="multipart/form-data">' +
                                    '<div class="container">' +
                                        '<div class="row">' +
                                            '<div class="col-12 col-sm-4">' +
                                                '<div class="image-upload-wrapper">' +
                                                    '<label>Lisää kuvia' +
                                                        '<input type="file" class="image-input" name="auction_item_images[]" multiple>' +
                                                        '<i class="bi bi-download"></i>' +
                                                        '<input type="hidden" name="images_order" value="">' +
                                                        '<input type="hidden" name="auction_item_image_ids" value="">' +
                                                    '</label>' +
                                                    '<div class="image-preview-container">' +
                                                        '<img src="' + attachment.url + '" data-id="' + attachment.id + '" style="max-width: 70px; max-height: 70px;">' +
                                                        '<input type="hidden" name="auction_item_image_url[]" value="' + attachment.url + '">' +
                                                        '<input type="hidden" name="auction_item_image_ids" value="' + attachment.id + '">' +
                                                        '<input type="text" name="auction_item_image_id_text[]" value="' + attachment.id + '" style="display:none;">' +
                                                        '<div class="reaAttachId">' + attachment.id + '</div>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                            '<div class="col-12 col-sm-6 auction_item_info">' +
                                                '<label>Tuotenimi' +
                                                    '<input type="text" name="auction_item_title">' +
                                                '</label>' +
                                                '<label>Asiakasnumero' +
                                                    '<input type="text" name="auction_item_sku">' +
                                                '</label>' +
                                                '<label>Tuotekuvaus' +
                                                    '<textarea name="auction_item_description"></textarea>' +
                                                '</label>' +
                                            '</div>' +
                                            '<div class="col-12 col-sm-2 auction_item_actions">' +
                                                '<input type="hidden" name="current_auction_id" value="' + currentAuctionId + '">' +
                                                '<input type="hidden" name="action" value="add_auction_item">' +
                                                '<input type="hidden" name="auction_item_id" value="' + tempId + ' ">' +
                                                '<input type="submit" value="Tallenna" class="create-btn">' +
                                                '<input type="button" value="Poista" class="delete-btn">' +
                                                '<div class="real-post-id"></div>'
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</form>';

            return formHtml;

    }

    // Обработчик клика по инпуту для открытия медиа-библиотеки
    $(document).on('click', '.image-upload-wrapper', function(e) {
        e.preventDefault();

        var $wrapper = $(this);
        var $imageContainer = $wrapper.find('.image-preview-container');
        var $imageIdsInput = $wrapper.find('input[name="auction_item_image_ids"]');

        // Настройки медиа-библиотеки
        var mediaUploader = wp.media({
            title: 'Valitse kuvat',
            button: {
                text: 'Käytä näitä kuvia'
            },
            multiple: true
        });

        // При выборе изображений
        mediaUploader.on('select', function() {
            var selection = mediaUploader.state().get('selection');
            var imageIds = [];
            $imageContainer.empty(); // Очистить контейнер

            selection.each(function(attachment) {
                attachment = attachment.toJSON();
                // Добавляем каждое изображение в контейнер предпросмотра
                $imageContainer.append('<img src="' + attachment.url + '" style="max-width: 70px; max-height: 70px;">');
                imageIds.push(attachment.id); // Сохраняем ID для отправки на сервер
            });

        // Заполнение скрытого поля с ID изображений
        if (imageIds.length > 0) {
            $imageIdsInput.val(imageIds.join(','));
        }
    });

    // Открытие медиа-библиотеки
    mediaUploader.open();
    });

    // Инициализация предварительного просмотра изображений
    function initImagePreviews() {
        // Обработчик изменения для каждого input
        $(document).off('change', '.image-input').on('change', '.image-input', function(e) {
            const input = $(this);
            const previewContainer = input.closest('.image-upload-wrapper').find('.image-preview-container');
            previewContainer.empty(); // Очищаем предыдущие миниатюры

            // Добавляем каждое выбранное изображение в контейнер предпросмотра
            Array.from(e.target.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // Создаем элемент img и добавляем его в контейнер предпросмотра
                        const img = $('<img>').attr('src', event.target.result).css({ maxWidth: '70px', maxHeight: '70px' });
                        previewContainer.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    $('.add-new-item-btn').click(function() {
        var lastForm = $('.add-auction-item-form').last();
        var newForm;
        var currentAuctionId = "<?php echo $current_auction_id; ?>"; // Создаем уникальный ID для новой формы

        // Проверяем, есть ли формы на странице
        if (lastForm.length > 0) {
            // Клонируем последнюю форму, если она существует, и очищаем все поля ввода
            newForm = lastForm.clone().find("input[type=text], textarea, input[type=file]").val("").end();
            newForm.find('.image-preview-container').empty(); // Очищаем контейнер предварительного просмотра изображений
        } else {
            // Создаем новую форму с нуля, если форм для клонирования нет
            newForm = $('<form class="add-auction-item-form image-form" method="post" data-id="' + currentAuctionId + '" enctype="multipart/form-data">' +
                '<div class="container">' +
                '<div class="row">' +
                '<div class="col-12 col-sm-4">' +
                '<div class="image-upload-wrapper">' +
                '<label>Lisää kuvia' +
                '<input type="file" class="image-input" name="auction_item_images[]" multiple>' +
                '<i class="bi bi-download"></i>' +
                '<input type="hidden" name="images_order" value="">' +
                '<input type="hidden" name="auction_item_image_ids" value="">' +
                '</label>' +
                '<div class="image-preview-container">' +
                // Место для миниатюр, если они будут
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col-12 col-sm-6 auction_item_info">' +
                '<label>Tuotenimi' +
                '<input type="text" name="auction_item_title">' +
                '</label>' +
                '<label>Asiakasnumero' +
                '<input type="text" name="auction_item_sku">' +
                '</label>' +
                '<label>Tuotekuvaus' +
                '<textarea name="auction_item_description"></textarea>' +
                '</label>' +
                '</div>' +
                '<div class="col-12 col-sm-2 auction_item_actions">' +
                '<input type="hidden" name="current_auction_id" value="' + currentAuctionId + '">' +
                '<input type="hidden" name="action" value="add_auction_item">' +
                '<input type="submit" value="Tallenna" class="create-btn">' +
                '<input type="button" value="Poista" class="delete-btn">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</form>');
        }

        newForm.find('input[name="auction_item_id"]').val(""); // Очищаем скрытое поле ID продукта, если оно есть
        newForm.find('.real-post-id').text(''); // Очищаем текстовое поле с реальным ID поста, если оно есть
        newForm.attr('data-id', currentAuctionId); // Обновляем атрибут data-id формы на новый уникальный ID
        newForm.find('input[type="submit"]').val('<?php _e("Tallenna", "default"); ?>').removeClass('successfully').addClass('create-btn').prop('disabled', false); // Обновляем кнопку отправки формы

        $('#forms-container').append(newForm); // Добавляем новую форму в контейнер

        // Переинициализация обработчиков для новой формы
        initImagePreviews();
        initSortable();
    });

    // Соритровка миниатюр
    function initSortable() {
        $(".image-preview-container").sortable({
            items: 'img',
            cursor: 'move',
            stop: function(event, ui) {
                var container = $(this);
                updateImagesOrder(container);
            }
        }).disableSelection();
    }

    // Обновление миниатюры после сортировки
    function updatePostThumbnail(firstImageId, postId) {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'handle_update_images_order_and_thumbnail',
                post_id: postId.toString(),
                image_id: firstImageId.toString(), // Преоб
            },
            success: function(response) {
                if (response.success) {
                    alert('Thumbnail updated successfully');
                    console.log('Thumbnail updated successfully');
                    console.log("Post ID: " + postId + ", First Image ID: " + firstImageId);
                } else {
                    alert('Error updating thumbnail');
                    console.error('Error updating thumbnail');
                    console.log("Post ID: " + postId + ", First Image ID: " + firstImageId);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', status, error);
            }
        });
    }

    // Вызываем функции инициализации
    $(document).ready(function() {
        initImagePreviews();
        initSortable();
    });

        // Генерация названия для формы
        function generateTitle(index) {
        return index + '. HUUTOKAUPAN KOHDE';
    }

    // Асинхронное сохранение формы
async function saveFormAsync(form, index) {
    var titleInput = $(form).find('input[name="auction_item_title"]');
    if (!titleInput.val().trim()) {
        titleInput.val(generateTitle(index + 1)); // Индексация начинается с 1
    }

    const formData = new FormData(form);
    const submitButton = $(form).find('input[type="submit"]');
    const isUpdateAction = submitButton.hasClass('update-btn');
    var postId = $(form).find('.real-post-id').text();
    if (postId) {
        formData.append('auction_item_id', postId);
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                submitButton.prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    // Обновление UI формы в соответствии с ответом сервера
                    $(form).find('input[name="auction_item_id"]').val(response.data.post_id);
                    var realPostIdContainer = $(form).find('.real-post-id');
                    if (realPostIdContainer.length) {
                        realPostIdContainer.text(response.data.post_id);
                    }
                    $(form).attr('data-id', response.data.post_id);
                    submitButton.val('<?php _e("Päivittää...", "default"); ?>').addClass('successfully').removeClass('create-btn').prop('disabled', false);
                    setTimeout(function () {
                        submitButton.val('<?php _e("Päivitä", "default"); ?>').addClass('create-btn').removeClass('successfully').prop('disabled', false);
                    }, 1000);
                    resolve();
                } else {
                    reject(new Error('Error saving form'));
                }
            },
            error: function (xhr, status, error) {
                // Проверяем статус ошибки
                if (xhr.status === 429) {
                    // Если ошибка 429 (Too Many Requests), игнорируем ее и продолжаем загрузку
                    console.log('Too many requests, skipping this form.');
                    submitButton.prop('disabled', false);
                    resolve(); // Разрешаем промис, чтобы продолжить загрузку
                } else {
                    // Для других ошибок выводим уведомление и отклоняем промис
                    alert('AJAX error: ' + error);
                    submitButton.prop('disabled', false);
                    reject(new Error('AJAX request failed'));
                }
            }
        });
    });
}

// Обработка отправки формы
$(document).on('submit', '.add-auction-item-form', function (e) {
    e.preventDefault();
    var form = this;
    var index = $('.add-auction-item-form').index(form);
    var imageIds = $(form).find('input[name="auction_item_image_ids"]').val();
    var formData = new FormData(form);
    formData.append('auction_item_image_ids', imageIds);
    saveFormAsync(form, index).then(() => {
        console.log('Form processed successfully');
    }).catch(error => {
        console.error('Form processing error:', error);
    });


});

// Массовое сохранение форм
$('.mass_prod_creation').on('click', async function (e) {
    e.preventDefault();
    var forms = $('.add-auction-item-form');
    var savedFormsCount = 0;
    $('#progress-indicator').show().text('Tallenna... 0%');
    $(this).prop('disabled', true).addClass('successfully_save');

    // Разбиваем формы на партии по 30 штук
    var formBatches = [];
    for (let i = 0; i < forms.length; i += 20) {
        formBatches.push(forms.slice(i, i + 20));
    }

    for (let batchIndex = 0; batchIndex < formBatches.length; batchIndex++) {
        var batch = formBatches[batchIndex];
        for (let i = 0; i < batch.length; i++) {
            try {
                await saveFormAsync(batch[i], i);
                savedFormsCount++;
                var progress = Math.round((savedFormsCount / forms.length) * 100);
                $('#progress-indicator').text(`Tallenna... ${progress}%`);
            } catch (error) {
                console.error('Error during form save', error);
                break;
            }
        }
    }

    $('#progress-indicator').hide();
    $(this).prop('disabled', false).removeClass('successfully_save');
});

    $(document).ready(function() {
        $('.show-upload-area-button').on('click', function() {
            $('.mass-upload-area').show();
        });
    });

    // Обработчик для кнопки "Удалить"
    $(document).on('click', '.delete-btn', function() {
        var form = $(this).closest('.add-auction-item-form');
        var itemId = form.find('input[name="auction_item_id"]').val();
        var nonce = $('#_wpnonce').val(); // Используйте nonce из формы или страницы

        if (itemId) {
            if (!confirm('Haluatko varmasti poistaa tämän kohteen?')) return;

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'delete_auction_item',
                    auction_item_id: itemId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        form.remove();
                    } else {
                        alert('Error occurred while deleting the item: ' + response.data.message);
                    }
                }
            });
        } else {
            form.remove();
        }
    });

    // Обработка клика по кнопке удаления
    $(document).on('click', '.btn-delete-image', function() {
        var attachmentId = $(this).data('id');
        var container = $(this).closest('.image-upload-wrapper').find('.image-preview-container');

        if (confirm('Вы уверены, что хотите удалить это изображение?')) {
            deleteImage(attachmentId, function() {
                container.find('img[data-id="' + attachmentId + '"]').remove();
                container.find('.btn-delete-image[data-id="' + attachmentId + '"]').remove();
            });
        }
    });

    // Функция удаления изображения
    function deleteImage(attachmentId, callback) {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'handle_delete_image',
                image_id: attachmentId,
            },
            success: function(response) {
                if (response.success) {
                    console.log('Изображение успешно удалено.'); // Логирование в консоль
                    if (typeof callback === "function") {
                        callback(); // Вызов функции обратного вызова
                    }
                } else {
                    console.error('Ошибка при удалении изображения: ', response); // Логирование ошибки
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX запроса: ', xhr, status, error); // Логирование ошибки AJAX запроса
            }
        });
    }

    // Добавление кнопок удаления к каждому изображению (предполагается, что это уже сделано в вашем текущем коде)
    $('.image-preview-container img').each(function() {
        var attachmentId = $(this).data('id');
        // Убедитесь, что кнопка добавляется непосредственно после <img>, чтобы оба элемента можно было легко удалить
        $(this).after('<button type="button" class="btn-delete-image" data-id="' + attachmentId + '"><i class="bi bi-x-circle-fill"></i></button>');
    });


    function updateImagesOrder(container) {
        var imagesOrder = container.find('img').map(function() {
            return $(this).data('id');
        }).get().join(',');

        var postId = container.closest('form').data('id');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'handle_update_images_order_and_thumbnail',
                post_id: postId,
                images_order: imagesOrder,
            },
            success: function(response) {
                if (response.success) {
                    // alert('Order and thumbnail updated successfully');
                } else {
                    alert('Error updating order and thumbnail');
                }
            }
        });
    }

    // Функция для удаления дубликатов изображений и кнопок удаления
    function removeDuplicateImagesAndButtons() {
        $('.image-preview-container').each(function() {
            var seenImages = {};
            var seenButtons = {};

            // Итерация по изображениям внутри контейнера
            $(this).find('img').each(function() {
                var img = $(this);
                var dataId = img.data('id');

                if (seenImages[dataId]) {
                    img.remove();
                } else {
                    seenImages[dataId] = true;
                }
            });

            // Итерация по кнопкам удаления внутри контейнера
            $(this).find('.btn-delete-image').each(function() {
                var btn = $(this);
                var dataId = btn.data('id');

                if (seenButtons[dataId]) {
                    btn.remove();
                } else {
                    seenButtons[dataId] = true;
                }
            });
        });
    }

    removeDuplicateImagesAndButtons();
});

</script>