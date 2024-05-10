jQuery.noConflict(); (function($) {

window.addEventListener('scroll', function() {
    var header = document.querySelector('.header_area');
    if (window.scrollY > 140) {
        header.classList.add('header_sticky');
    } else {
        header.classList.remove('header_sticky');
    }
});

  ///tiny-slider
  $('.slide_list').each(function (index, element) {
    var slider = tns({
      container: element,
      autoHeight: true,
      items: 1,
      loop: true,
      swipeAngle: false,
      speed: 500,
      mouseDrag: true,
     // autoplay: true,
      controls: true,
      nav: true,
      navPosition: "bottom",
    });
  });

    document.addEventListener('DOMContentLoaded', function () {
      const topLevelItems = document.querySelectorAll('.menu-item-has-children');

      topLevelItems.forEach(function (topLevelItem) {
          const subMenus = topLevelItem.querySelectorAll('.sub-menu');
          const triggerElement = topLevelItem.querySelector('.menu-trigger'); // Находим .main_auction внутри пункта меню

          topLevelItem.addEventListener('click', function (event) {
              const target = event.target;

              // Также проверяем, что target это пункт меню с классом main_auction
              if (target === triggerElement || topLevelItem.classList.contains('menu-trigger')) {
                  event.preventDefault();

                  if (window.innerWidth <= 845) {
                      const isSubMenuOpen = subMenus[0].style.display === 'block';

                      closeOtherSubMenus(topLevelItem);

                      if (isSubMenuOpen) {
                          subMenus.forEach(function (subMenu) {
                              subMenu.style.display = 'none';
                          });
                      } else {
                          subMenus.forEach(function (subMenu) {
                              subMenu.style.display = 'block';
                          });
                      }
                  }
              }
          }, { passive: true });

          topLevelItem.addEventListener('mouseenter', function (event) {
              if (window.innerWidth > 845) {
                  const isSubMenuOpen = subMenus[0].style.display === 'block';

                  closeOtherSubMenus(topLevelItem);

                  if (!isSubMenuOpen) {
                      subMenus.forEach(function (subMenu) {
                          subMenu.style.display = 'block';
                      });
                  }
              }
          }, { passive: true });

          subMenus.forEach(function (subMenu) {
              subMenu.style.display = 'none';
          });
      });

      function closeOtherSubMenus(currentTopLevelItem) {
          topLevelItems.forEach(function (item) {
              if (item !== currentTopLevelItem) {
                  const subMenus = item.querySelectorAll('.sub-menu');
                  subMenus.forEach(function (subMenu) {
                      subMenu.style.display = 'none';
                  });
              }
          });
      }

  }, { passive: true });
   // Добавляем флаг passive

})(jQuery);

jQuery(document).ready(function($) {
  // Проверяем статус каждой ставки и обновляем текст кнопки
  $('.bid-item').each(function() {
      var $item = $(this);
      var bidId = $item.data('bid-id');
      var winnerStatus = $item.data('winner-status');

      if (winnerStatus === '1') {
          $item.find('.mark-winner').addClass('winner_chosen').text(auctionAnalyticsParams.winnerText);
      }
  });

  $(document).on('click', '.mark-winner', function() {
    var $button = $(this);
    var bidId = $button.data('bid-id');
    var isWinnerChosen = $button.hasClass('winner_chosen');

    $.ajax({
        url: auctionAnalyticsParams.ajaxurl,
        type: 'POST',
        data: {
            action: isWinnerChosen ? 'unmark_winner' : 'mark_winner',
            bid_id: bidId,
            security: auctionAnalyticsParams.nonce
        },
        success: function(response) {
            if (response.success) {
                if (isWinnerChosen) {
                    $button.removeClass('winner_chosen').text(auctionAnalyticsParams.markWinnerText);
                } else {
                    $button.addClass('winner_chosen').text(auctionAnalyticsParams.winnerText);
                }
            } else {
                alert(response.data);
            }
        },
        error: function() {
            alert('An error occurred, please try again.');
        }
    });
    });

});

jQuery(document).ready(function($) {
  $('.bid-form').on('submit', function(e) {
      e.preventDefault();

      var form = $(this);
      var bidValueInput = form.find('input[name="bid_value"]');
      var bidValue = bidValueInput.val();
      var itemId = form.data('item-id');
      var submitBtn = form.find('input[type="submit"]');

      $.ajax({
          url: auctionBidParams.ajaxurl,
          type: 'POST',
          data: {
              action: 'place_bid',
              item_id: itemId,
              bid_value: bidValue,
              security: auctionBidParams.bidNonce
          },
          success: function(response) {
              if (response.success) {
                bidValueInput.val(response.data.bid_value).prop('disabled', false);
                submitBtn.val('Huudettu').prop('disabled', false);
                setTimeout(function() {
                    submitBtn.val('HUUDA!').prop('disabled', false);
                }, 3000);
                form.find('.bid-feedback').text(response.data.message).css('color', 'green');
              } else {
                  form.find('.bid-feedback').text(response.data).css('color', 'red');
              }
          },
          error: function(xhr, status, error) {
              form.find('.bid-feedback').text(auctionBidParams.errorOccurredText).css('color', 'red');
              console.error('AJAX error:', status, error);
          }
      });
  });

  // Проверка текущей ставки пользователя для каждого товара при загрузке страницы
  $('.bid-form').each(function() {
      var form = $(this);
      var itemId = form.data('item-id');
      var bidValueInput = form.find('input[name="bid_value"]');
      var submitBtn = form.find('input[type="submit"]');

      $.ajax({
          url: auctionBidParams.ajaxurl,
          type: 'POST',
          dataType: 'json',
          data: {
              action: 'check_user_bid',
              item_id: itemId,
              security: auctionBidParams.checkBidNonce
          },
          success: function(response) {
              if (response.success) {
                  if (response.data.already_bid) {
                      bidValueInput.val(response.data.bid_value).prop('disabled', false);
                      submitBtn.val(auctionBidParams.bidAlreadyPlacedText).prop('disabled', false);
                  }
              }
          },
          error: function(xhr, status, error) {
              console.error('AJAX error:', status, error);
          }
      });
  });
});

jQuery(document).ready(function($) {
  $('#form').on('submit', function(e) {
      e.preventDefault();

      var submitButton = $(this).find('button[type="submit"]');
      var originalButtonText = submitButton.text();

      var formData = $(this).serialize();

      $.ajax({
          url: user_account_params.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: formData + '&security=' + user_account_params.nonce,
          beforeSend: function() {
              submitButton.text(user_account_params.formProcessingText).prop('disabled', true);
          },
          success: function(response) {
              if(response.success) {
                  submitButton.text(user_account_params.formPlacedText);
              } else {
                  submitButton.text(originalButtonText);
                  console.error('Something went wrong:', response.data);
              }
              submitButton.prop('disabled', false);
          },
          error: function(response) {
              submitButton.text(originalButtonText);
              console.error('AJAX error:', response);
              submitButton.prop('disabled', false);
          }
      });

  });


    $(window).on('load', function() {
        var hash = window.location.hash;
        if (hash && $(hash).length) {
            $('html, body').animate({
                scrollTop: $(hash).offset().top - 400
              }, 0);
            }
        });
    // jQuery(document).ready(function($) {
    //     $("#forms-container").sortable({
    //         placeholder: "ui-state-highlight",
    //         update: function(event, ui) {
    //             var sortedIDs = $(this).sortable("toArray", {attribute: "data-id"});
    //             $.ajax({
    //                 url: sortableParams.ajaxurl,
    //                 type: 'POST',
    //                 data: {
    //                     action: 'save_sorted_auction_items',
    //                     order: sortedIDs.join(','),
    //                     nonce: sortableParams.nonce
    //                 },
    //                 success: function(response) {
    //                     if (response.success) {
    //                         console.log('Order updated successfully');
    //                     } else {
    //                         console.error('Failed to update order:', response);
    //                     }
    //                 },
    //                 error: function(xhr, status, error) {
    //                     console.error('AJAX Error:', xhr, status, error);
    //                 }
    //             });
    //         }
    //     });
    // });

    // скрипты управления пользователями
    jQuery(document).ready(function($) {
        // Обработка кнопки Delete без перезагрузки страницы
        $('.admin_user_management_area').on('click', '.delete', function() {
            if (!confirm("Are you sure you want to delete this user?")) return;
            var userId = $(this).data('id');
            var $userRow = $(this).closest('li');
            $.ajax({
                type: 'POST',
                url: userManagementParams.ajaxurl,
                data: {
                    action: 'delete_user',
                    user_id: userId,
                    nonce: userManagementParams.deleteNonce
                },
                success: function(response) {
                    if (response.success) {
                        $userRow.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }
            });
        });

        // Обработка кнопки Ban и Unban без перезагрузки страницы
        $('.admin_user_management_area').on('click', '.ban, .unban', function() {
            var userId = $(this).data('id');
            var $userRow = $(this).closest('li');
            var action = $(this).hasClass('ban') ? 'ban_user' : 'unban_user';
            var nonce = action === 'ban_user' ? userManagementParams.banNonce : userManagementParams.unbanNonce;

            $.ajax({
                type: 'POST',
                url: userManagementParams.ajaxurl,
                data: {
                    action: action,
                    user_id: userId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Toggle button class and text based on current action
                        if (action === 'ban_user') {
                            $userRow.find('.ban').removeClass('ban').addClass('unban').text('Unban');
                            $userRow.find('.user_status').html('<span class="user-status-banned">Banned</span>');
                        } else {
                            $userRow.find('.unban').removeClass('unban').addClass('ban').text('Ban');
                            $userRow.find('.user_status').text('');
                        }
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }
            });
        });
    });


    jQuery(document).ready(function($) {
        function savePostageInfo(data) {
            $.post(postageAjax.ajaxurl, data, function(response) {
                if(response.success) {
                    console.log('Data saved successfully');
                } else {
                    console.error('Failed to save data');
                }
            });
        }

        jQuery(document).ready(function($) {
            $('.save-info-button').on('click', function() {
                var $thisButton = $(this);
                var $winnerItem = $(this).closest('.winner-item');
                var userId = $winnerItem.data('user-id');
                var auctionId = $winnerItem.data('auction-id');
                var itemsData = [];
                var totalInfo = {
                    items_total_price: $winnerItem.find('.items-total-price').text().replace(' €', ''),
                    postage_cost: $winnerItem.find('input[name="postage_cost"]').val(),
                    postage_total: $winnerItem.find('.postage-total').text().replace(' €', ''),
                    summa_and_postage: $winnerItem.find('.summa_and_postage').text().replace(' €', ''),
                    grand_total: $winnerItem.find('.grand-total').text().replace(' €', '')
                };

                $winnerItem.find('.winner-product-info').each(function() {
                    var itemId = $(this).data('item-id');
                    var itemPrice = parseFloat($(this).find('input[name="item_price"]').val()) || 0;
                    var fragileAdd = parseFloat($(this).find('input[name="fragile_add"]').val()) || 0;
                    var termsAccepted = $(this).find('input[name="terms_accepted"]').is(':checked') ? 1 : 0;
                    var willPickup = $(this).find('input[name="will_pickup"]').is(':checked') ? 1 : 0;
                    var normalPostage = $(this).find('input[name="normal_postage"]').is(':checked') ? 1 : 0;
                    itemsData.push({
                        item_id: itemId,
                        item_price: itemPrice,
                        fragile_add: fragileAdd,
                        terms_accepted: termsAccepted,
                        will_pickup: willPickup,
                        normal_postage: normalPostage
                    });
                });
                // Отправляем данные на сервер
                $.ajax({
                    url: postageAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_postage_info',
                        nonce: postageAjax.nonce,
                        user_id: userId,
                        auction_id: auctionId,
                        items: itemsData,
                        total_info: totalInfo
                    },
                    success: function(response) {
                        if(response.success) {
                            console.log('Data saved successfully');
                            // Меняем текст кнопки
                            $thisButton.html('<i class="bi bi-cash-stack"></i> Päivitetty');
                            // Добавляем класс
                            $thisButton.addClass('updated_btn');
                            // Возможно, вы захотите сделать это временным, например, возвращая кнопку к исходному состоянию через несколько секунд
                            setTimeout(function() {
                                $thisButton.html('<i class="bi bi-cash-stack"></i> Tallenna');
                                $thisButton.removeClass('updated_btn');
                            }, 3000); // 3 секунды
                        } else {
                            console.error('Failed to save data', response);
                        }
                    },
                    error: function(error) {
                        console.error('An error occurred', error);
                    }
                });
            });
        });

    });

});
