jQuery(function ($) {
  // Функция для добавления новой формы
  function addNewItemForm() {
      // Находим последнюю форму и клонируем её
      var lastForm = $('.add-auction-item-form').last();
      var newItemForm = lastForm.clone();

      // Очищаем значения в полях формы и устанавливаем уникальные идентификаторы
      var timestamp = Date.now();
      newItemForm.find('input[type="text"], textarea').val('');
      newItemForm.find('input[type="file"]').val('');
      newItemForm.find('.preview-image').removeAttr('src').hide();
      newItemForm.find('.image-input').attr('id', 'image-input-' + timestamp);
      newItemForm.find('.preview-image').attr('id', 'preview-image-' + timestamp);
      newItemForm.find('input[type="submit"]').val('<?php _e('Tallentaa', 'default'); ?>').removeClass('successfully').prop('disabled', false);

      // Убираем значения из скрытых полей, которые могут быть использованы для обновления и удаления
      newItemForm.find('input[name="auction_item_id"]').remove();
      newItemForm.find('input[name="action"]').val('add_auction_item');
      newItemForm.find('.update-btn').remove();
      newItemForm.find('.delete-btn').remove();

      // Добавляем кнопку удаления для новой формы
      var deleteButton = $('<input type="button" value="<?php _e('Poista', 'default'); ?>" class="delete-btn">');
      newItemForm.find('.auction_item_actions').append(deleteButton);

      // Вставляем новую форму в конец списка форм
      newItemForm.appendTo('#forms-container');
  }

  // Обработчик клика по кнопке "Добавить новый товар"
  $(document).on('click', '.add-new-item-btn', addNewItemForm);

  // Обработчик предварительного просмотра изображений
  $(document).on('change', '.image-input', function (e) {
      var inputId = $(this).attr('id');
      var previewId = inputId.replace('image-input', 'preview-image');
      var previewImage = $('#' + previewId);

      var file = e.target.files[0];
      if (file) {
          var reader = new FileReader();
          reader.onload = function (e) {
              previewImage.attr('src', e.target.result).show();
          };
          reader.readAsDataURL(file);
      }
  });

  // Обработчик для кнопок "Обновить"
  $(document).on('submit', '.add-auction-item-form', function (e) {
      e.preventDefault();

      // Собираем данные из формы
      var formData = new FormData(this);
      var submitButton = $(this).find('input[type="submit"]');
      var formAction = $(this).find('input[name="action"]').val();

      // Добавляем файл в formData, если это форма добавления или обновления
      if (formAction === 'add_auction_item' || formAction === 'update_auction_item') {
          var imageInput = $(this).find('.image-input');
          $.each(imageInput[0].files, function(i, file) {
              formData.append(imageInput.attr('name'), file);
          });
      }

      // Отправляем запрос на сервер
      $.ajax({
          type: 'POST',
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          data: formData,
          processData: false,
          contentType: false,
          cache: false,
          enctype: 'multipart/form-data',
          success: function (response) {
              if (response.success) {
                  // Обновляем интерфейс в зависимости от действия
                  if (formAction === 'add_auction_item') {
                      // Меняем кнопку на "Обновить" и добавляем класс, если продукт был добавлен
                                                  submitButton.val('<?php _e('Päivitä', 'default'); ?>').addClass('successfully').prop('disabled', true);
                  } else if (formAction === 'update_auction_item') {
                      // Меняем кнопку на "Обновить" и добавляем класс, если продукт был обновлен
                      submitButton.val('<?php _e('Päivitä', 'default'); ?>').addClass('successfully');
                  }

                  // Очищаем поле ввода файла и скрываем предпросмотр
                  imageInput.val('');
                  previewImage.removeAttr('src').hide();
              } else {
                  // Обработка случая, когда сохранение не удалось
                  console.error('Error:', response.data);
              }
              console.log(response); // Выводим ответ в консоль для отладки
          },
          error: function (error) {
              console.error('AJAX error:', error);
          }
      });
  });

  // Обработчик для кнопки "Удалить"
  $(document).on('click', '.delete-btn', function () {
      var form = $(this).closest('.add-auction-item-form');
      var itemId = form.find('input[name="auction_item_id"]').val();
      var nonce = form.find('input[name="handle_auction_item_nonce"]').val(); // Получаем значение nonce

      if (itemId) {
          // Отправляем AJAX запрос на удаление продукта
          $.ajax({
              type: 'POST',
              url: '<?php echo admin_url('admin-ajax.php'); ?>',
              data: {
                  action: 'delete_auction_item',
                  auction_item_id: itemId,
                  handle_auction_item_nonce: nonce, // Передаем nonce
              },
              success: function (response) {
                  if (response.success) {
                      // Удаляем форму продукта из DOM
                      form.remove();
                  } else {
                      // Обработка случая, когда удаление не удалось
                      console.error('Error:', response.data);
                  }
                  console.log(response); // Выводим ответ в консоль для отладки
              },
              error: function (error) {
                  console.error('AJAX error:', error);
              }
          });
      } else {
          // Если нет ID продукта, просто удаляем форму из DOM
          form.remove();
      }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  // Получаем все формы с классом 'image-form' при загрузке страницы
  var forms = document.querySelectorAll('.image-form');

  forms.forEach(function (form) {
      // Находим элементы внутри каждой формы
      var imageInput = form.querySelector('.image-input');
      var previewImage = form.querySelector('.preview-image');

      // Проверяем наличие элементов перед добавлением слушателя
      if (imageInput && previewImage) {
          imageInput.addEventListener('change', function (e) {
              var file = e.target.files[0];

              if (file) {
                  var reader = new FileReader();

                  reader.onload = function (e) {
                      previewImage.src = e.target.result;
                      previewImage.style.display = 'block';
                  }

                  reader.readAsDataURL(file);
              }
          });
      }
  });
});