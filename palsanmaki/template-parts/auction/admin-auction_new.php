<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    <section class="auction_creating_section">
        <div class="container">
            <div class="row">

                <h1>
                    <?php the_title()?>
                </h1>

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
                                    <input type="date" name="auction_date" id="auction-date" value="<?php echo esc_attr($current_auction_date); ?>" required>

                                    <label for="auction-time">
                                        <?php _e( 'Aloitusaika', 'default' ) ?>
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
                                    <textarea name="auction_description" id="auction-description"><?php echo esc_textarea($description); ?></textarea>
                                </div>

                            </div>
                            <div class="col-12 col-sm-2">
                                <input type="submit" value="<?php echo $current_auction_id > 0 ? 'Päivitä' : 'Luo huutokauppa'; ?>">
                                <input type="hidden" name="current_auction_id" value="<?php echo esc_attr($current_auction_id); ?>">
                                <?php wp_nonce_field('auction_nonce_action', 'auction_nonce'); ?>

                                <a class="auction_list" href="<?php the_field('admin_auction_list_link', 'option');?>">
                                    <?php the_field('admin_auction_list', 'option');?>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <!-- Создание блока добавления продуктов по одному -->
        <div class="auction_products_grid container">
            <?php
                $current_auction_id = get_the_ID();

                $current_auction_title = get_the_title($current_auction_id);
                $current_auction_date = get_field('auction_date', $current_auction_id);
                $current_auction_time = get_field('auction_time', $current_auction_id);
                $current_auction_location = get_field('auction_location', $current_auction_id);
            ?>

            <h3>
                <?php _e('Uudet tuotteet', 'default'); ?>
            </h3>

            <div id="forms-container">

            </div>

            <div class="mass-upload-area">
                <div id="mass-image-upload-container">
                    <button id="mass-upload-button">
                        <?php _e('Lisää kuvia', 'default'); ?> <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>

            <div id="control_button_block" class="control_button_block">
                <div class="show-upload-area-button"><i class="bi bi-images"></i> <?php _e('Lisää kuvia', 'default'); ?></div>
                <div class="add-new-item-btn"><i class="bi bi-plus-circle"></i> <?php _e('Lisää uusi tuote', 'default'); ?></div>
                <button class="mass_prod_creation"><i class="bi bi-save"></i> <?php _e('Tallenna kaikki', 'default'); ?></button>
                <div id="progress-indicator"></div>
            </input>

            </div>

        </div>
    </section>

<script>
    jQuery(function ($) {
        // создание/обновление аукционов
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
                                $('#auction-form input[type="submit"]').val('Päivitä huutokauppa').removeClass('successfully')
                            }, 3000);
                        $('input[name="current_auction_id"]').val(response.data.auction_id);

                        // Делаем видимым блок добавления продуктов
                        $('.auction_products_grid').show();
                        $('input[name="auction_item_id"]').val(response.data.post_id);
                        // Заполняем форму данными, полученными в ответе, если нужно
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

        $(document).ready(function() {
            $('.auction_products_grid').hide();
        });

   // Генерация названия на основе индекса формы
   function generateTitle(index) {
        return index + '. HUUTOKAUPAN KOHDE';
    }

    // Обновление UI формы после сохранения
    function updateFormUI(form, response, index) {
        var submitButton = $(form).find('input[type="submit"]');
        var titleInput = $(form).find('input[name="auction_item_title"]');
        titleInput.val(generateTitle(index)); // Обновляем название с учетом сквозного индекса
        $(form).find('input[name="auction_item_id"]').val(response.data.post_id);
        var realPostIdContainer = $(form).find('.real-post-id');
        if (realPostIdContainer.length) {
            realPostIdContainer.text(response.data.post_id);
        }
        $(form).attr('data-id', response.data.post_id);
        submitButton.val('Päivitä').addClass('successfully').removeClass('create-btn').prop('disabled', false);
        setTimeout(function() {
            submitButton.val('<?php _e("Päivitä", "default"); ?>').addClass('create-btn').removeClass('successfully').prop('disabled', false);
        }, 3000);
    }

    function updateFormUI(form, response) {
        var submitButton = $(form).find('input[type="submit"]');
        $(form).find('input[name="auction_item_id"]').val(response.data.post_id);
        var realPostIdContainer = $(form).find('.real-post-id');
        if (realPostIdContainer.length) {
            realPostIdContainer.text(response.data.post_id);
        }
        $(form).attr('data-id', response.data.post_id);
            submitButton.val('<?php _e("Päivittää...", "default"); ?>').addClass('successfully').removeClass('successfully_save').removeClass('create-btn').prop('disabled', false);
        setTimeout(function() {
            submitButton.val('<?php _e("Päivitä", "default"); ?>').addClass('successfully_save').removeClass('successfully').prop('disabled', false);
        }, 3000);
    }

    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    // Асинхронная обработка формы
    async function processForm(form, index) {
        var titleInput = $(form).find('input[name="auction_item_title"]');
        if (!titleInput.val().trim()) {
            titleInput.val(generateTitle(index)); // Устанавливаем сгенерированное название, если поле пустое
        }

        // Обновленная часть для работы с изображениями
        var visibleImageIds = $(form).find('.image-preview-container img').map(function() {
            return $(this).data('id');
        }).get();

        // Получаем ID изображений из скрытого поля, если оно есть
        var hiddenImageIds = $(form).find('input[name="auction_item_image_ids"]').val();
        if (hiddenImageIds) {
            hiddenImageIds = hiddenImageIds.split(',');
            // Объединяем массивы, удаляя дубликаты
            var imageIds = Array.from(new Set([...visibleImageIds, ...hiddenImageIds]));
        } else {
            var imageIds = visibleImageIds;
        }

        // Преобразуем массив обратно в строку для передачи в FormData
        var imageIdsStr = imageIds.join(',');

        const formData = new FormData(form);
        formData.append('action', $(form).find('input[name="auction_item_id"]').val().length > 0 ? 'update_auction_item' : 'add_auction_item');
        formData.append('auction_item_image_ids', imageIdsStr);

        return new Promise((resolve, reject) => {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(form).find('input[type="submit"]').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        updateFormUI(form, response, index);
                        resolve();
                    } else {
                        reject(new Error('Error processing form'));
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 429) {
                        console.log('Too many requests, skipping this form.');
                        resolve(); // Продолжаем при 429 ошибке
                    } else {
                        reject(new Error('AJAX request failed'));
                    }
                }
            });
        });
    }

    // Обработка отправки формы
    $(document).on('submit', '.add-auction-item-form', function(e) {
        e.preventDefault();
        var form = this;
        var isUpdate = $(this).find('input[name="auction_item_id"]').val().length > 0;
        var imageIds = $(form).find('input[name="auction_item_image_ids"]').val();
        var formData = new FormData(form);
        formData.append('auction_item_image_ids', imageIds);

        processForm(form, isUpdate)
            .then(() => console.log('Form processed successfully'))
            .catch(error => console.error('Form processing error:', error));
    });

    // Массовое сохранение форм с индикатором прогресса
    $('.mass_prod_creation').click(async function(e) {
        e.preventDefault();
        var forms = $('.add-auction-item-form').toArray();
        $('#progress-indicator').show();
        let totalForms = forms.length;
        let index = 1;
        $(this).prop('disabled', true).addClass('successfully_save');

            for (let i = 0; i < totalForms; i += 15) {
                let batch = forms.slice(i, i + 15);
                for (let form of batch) {
                    try {
                        await processForm(form, index);
                        let progressPercent = Math.round((index / totalForms) * 100);
                        $('#progress-indicator').text(`Tallentaa... ${progressPercent}%`);
                    } catch (error) {
                        console.error('Form processing error:', error);
                        break;
                    }
                    index++;
                }
                await delay(1500); // Задержка между пакетами форм
            }

        $('#progress-indicator').hide();
        $(this).prop('disabled', false).removeClass('successfully_save');
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
                },
                error: function(xhr, status, error) {
                    alert('AJAX error occurred: ' + error);
                }
            });
        } else {
            form.remove();
        }
    });

    });
</script>

<script>
    // скрипт массовой загрузки изображений и создание форм
    jQuery(document).ready(function($) {
    $('#control_button_block').hide();

    $('#mass-upload-button').on('click', function(e) {
        e.preventDefault();

        var frame = wp.media({
            title: 'Valitse kuvat',
            button: { text: 'Lisää kuvia' },
            multiple: true
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var auctionId = $('input[name="current_auction_id"]').val();

            attachments.forEach(function(attachment, index) {
                var tempId = 'temp-' + Date.now() + '-' + index;
                var formHtml = '<form class="add-auction-item-form image-form" method="post" data-id="' + tempId + '" enctype="multipart/form-data">' +
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
                                                        '<input type="hidden" name="auction_item_image_id[]" value="' + attachment.id + '">' +
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
                                                '<input type="hidden" name="current_auction_id" value="' + auctionId + '">' +
                                                '<input type="hidden" name="action" value="add_auction_item_prod">' +
                                                '<input type="hidden" name="auction_item_id" value="' + tempId + ' ">' +
                                                '<input type="submit" value="Tallenna" class="create-btn">' +
                                                '<input type="button" value="Poista" class="delete-btn">' +
                                                '<input type="hidden" name="handle_auction_item_nonce" value="' + ajax_obj.nonce + '">' +
                                                '<div id="form-messages" style="display: none;"></div>' +
                                                '<div class="real-post-id"></div>'
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</form>';
                    $('#forms-container').append(formHtml);
                });
                $('.mass-upload-area').hide();
                $('#control_button_block').show();
            });

            frame.open();
        });

        $(document).ready(function() {
            $('.show-upload-area-button').on('click', function() {
                $('.mass-upload-area').show();
            });
        });
    });

</script>


<script>
    jQuery(document).ready(function($) {

        // Обработчик клика по инпуту для открытия медиа-библиотеки
    $(document).on('click', '.image-upload-wrapper', function(e) {
        e.preventDefault();

        var $wrapper = $(this);
        var $imageContainer = $wrapper.find('.image-preview-container');
        var $imageIdsInput = $wrapper.find('input[name="auction_item_image_ids"]');

        // Настройки медиа-библиотеки
        var mediaUploader = wp.media({
            title: 'Выберите изображения',
            button: {
                text: 'Использовать эти изображения'
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
        $(document).off('change', '.image-input').on('change', '.image-input', function(e) {
            const input = $(this);
            const previewContainer = input.closest('.image-upload-wrapper').find('.image-preview-container');
            previewContainer.empty();

            const loadingMessage = $('<div>').text('Ladataan kuvia...').css({
                'color': '#000',
                'font-size': '16px',
                'margin': '10px 0'
            });
            previewContainer.append(loadingMessage);

            const formData = new FormData();
            Array.from(e.target.files).forEach((file, index) => {
                formData.append('images[]', file);
            });

            formData.append('action', 'upload_auction_images');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                },
                success: function(response) {
                    loadingMessage.remove();

                    if (response.success) {
                        response.data.forEach(attachment => {
                            const img = $('<img>').attr({
                                'src': attachment.url,
                                'data-id': attachment.id,
                                'style': 'max-width: 70px; max-height: 70px;'
                            });
                            previewContainer.append(img);
                        });
                    } else {
                        console.error('Ошибка при загрузке изображений.');
                    }
                },
                error: function(xhr, status, error) {
                    loadingMessage.remove();
                    console.error('Ошибка AJAX запроса:', status, error);
                }
            });
        });
    }

    // Повторная инициализация сортировки и предварительного просмотра после добавления новой формы
    $('.add-new-item-btn').click(function() {
        const lastForm = $('.add-auction-item-form').last();
        const newItemForm = lastForm.clone().find("input[type=text], textarea, input[type=file]").val("").end();
        newItemForm.find('.image-preview-container').empty();
        newItemForm.find('input[name="auction_item_id"]').val("");
        newItemForm.find('.real-post-id').text('');
        newItemForm.attr('data-id', 'tempId');
        newItemForm.find('input[type="submit"]').val('<?php _e("Tallenna", "default"); ?>').removeClass('successfully').addClass('create-btn').prop('disabled', false);
        newItemForm.appendTo('#forms-container');

        initImagePreviews();
        initSortable();
    });

    function initSortable() {
        $(".image-preview-container").sortable({
            items: 'img',
            cursor: 'move',
            stop: function(event, ui) {
                var container = $(this);
                // updatePostThumbnail(container);
                updateImagesOrder(container);
            }
        }).disableSelection();
    }

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

    // Вызываем функции инициализации
    $(document).ready(function() {
        initImagePreviews();
        initSortable();
    });

});
</script>
