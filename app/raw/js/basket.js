// app/raw/js/basket.js
// Корзина: localStorage + синхронизация с сервером
(function($) {
  'use strict';

  var STORAGE_KEY = 'pk_basket';

  // ============================================================
  // Работа с localStorage
  // ============================================================

  var Basket = {
    getRaw: function() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return [];
        var data = JSON.parse(raw);
        if (!data || !Array.isArray(data.items)) return [];
        return data.items;
      } catch (e) {
        console.error('Basket: ошибка чтения localStorage', e);
        return [];
      }
    },

    setRaw: function(items) {
      try {
        var currentPromo = this.getPromo();
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
          items: items,
          promo: currentPromo
        }));
        $(document).trigger('basket:changed');
      } catch (e) {
        console.error('Basket: ошибка записи localStorage', e);
      }
    },

    has: function(itemId) {
      return this.getRaw().some(function(item) {
        return item.item_id === itemId;
      });
    },

    add: function(itemId, qty) {
      itemId = parseInt(itemId, 10);
      qty = parseFloat(qty) || 1;

      if (!itemId || itemId <= 0) return false;
      if (this.has(itemId)) return false;

      var items = this.getRaw();
      items.push({ item_id: itemId, qty: qty });
      this.setRaw(items);
      return true;
    },

    updateQty: function(itemId, qty) {
      itemId = parseInt(itemId, 10);
      qty = parseFloat(qty);

      if (!itemId || !qty || qty <= 0) return false;

      var items = this.getRaw();
      for (var i = 0; i < items.length; i++) {
        if (items[i].item_id === itemId) {
          items[i].qty = qty;
          this.setRaw(items);
          return true;
        }
      }
      return false;
    },

    remove: function(itemId) {
      itemId = parseInt(itemId, 10);
      var items = this.getRaw().filter(function(item) {
        return item.item_id !== itemId;
      });
      this.setRaw(items);
    },

    clear: function() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ items: [], promo: '' }));
        $(document).trigger('basket:changed');
      } catch (e) {
        console.error('Basket: ошибка очистки', e);
      }
    },

    getCount: function() {
      return this.getRaw().length;
    },

    // Промокод
    getPromo: function() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return '';
        var data = JSON.parse(raw);
        return data.promo || '';
      } catch (e) {
        return '';
      }
    },

    setPromo: function(code) {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        var data = raw ? JSON.parse(raw) : {};
        if (!data.items) data.items = [];
        data.promo = code || '';
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        $(document).trigger('basket:changed');
      } catch (e) {
        console.error('Basket: ошибка записи промокода', e);
      }
    },

    clearPromo: function() {
      this.setPromo('');
    },

    applyServerResponse: function(response) {
      var items = this.getRaw();

      if (response.removed && response.removed.length) {
        items = items.filter(function(item) {
          return response.removed.indexOf(item.item_id) === -1;
        });
      }

      this.setRaw(items);
    }
  };

  // ============================================================
  // Синхронизация с сервером
  // ============================================================

  var isSyncing = false;
  var pendingSync = null;

  function syncBasket(callback) {
    var raw = Basket.getRaw();

    if (!raw.length) {
      if (typeof callback === 'function') {
        callback({
          success: true,
          items: [],
          removed: [],
          changed_prices: [],
          total: 0,
          count: 0,
          discount: 0,
          total_with_discount: 0,
          promo_applied: null,
          promo_error: null
        });
      }
      updateCounter(0);
      return;
    }

    if (isSyncing) {
      pendingSync = callback;
      return;
    }
    isSyncing = true;

    $.ajax({
      url: '/basket/sync/',
      type: 'POST',
      dataType: 'json',
      data: {
        items: raw,
        promo: Basket.getPromo(),
        _csrf: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response && response.success) {
          Basket.applyServerResponse(response);

          if (response.removed && response.removed.length) {
            showNotification('Некоторые товары больше не доступны и были удалены из корзины', 'warning');
          }
          if (response.changed_prices && response.changed_prices.length) {
            showNotification('Цены на некоторые товары изменились', 'info');
          }

          if (response.promo_error && Basket.getPromo()) {
            Basket.clearPromo();
            showNotification(response.promo_error, 'warning');
          }

          updateCounter(response.count);
          refreshCartButtons();

          if (typeof callback === 'function') {
            callback(response);
          }
        } else {
          console.error('Basket sync error:', response);
          if (typeof callback === 'function') {
            callback({ success: false });
          }
        }
      },
      error: function(xhr) {
        console.error('Basket sync AJAX error:', xhr.responseText);
        if (typeof callback === 'function') {
          callback({ success: false });
        }
      },
      complete: function() {
        isSyncing = false;

        if (pendingSync) {
          var cb = pendingSync;
          pendingSync = null;
          syncBasket(cb);
        }
      }
    });
  }

  // ============================================================
  // Счётчик в шапке
  // ============================================================

  function updateCounter(count) {
    if (typeof count === 'undefined') {
      count = Basket.getCount();
    }

    var $cart = $('.cart1');
    if (!$cart.length) return;

    var $link = $cart.find('a').first();
    if (!$link.length) return;

    var $counter = $link.find('.cart-count');
    if (!$counter.length) {
      $counter = $('<span class="cart-count"></span>');
      $link.append($counter);
    }

    $counter.text(count > 0 ? count : '0');
    $cart.toggleClass('has-items', count > 0);
  }

  // ============================================================
  // Обновление кнопок «В корзину»
  // ============================================================

  function refreshCartButtons() {
    $('.add-to-cart').each(function() {
      var $btn = $(this);
      var itemId = parseInt($btn.data('item-id'), 10);
      if (!itemId) return;

      if (Basket.has(itemId)) {
        if (!$btn.hasClass('in-basket')) {
          $btn
            .addClass('in-basket')
            .data('original-text', $btn.text())
            .text('В корзине');
        }
      } else {
        if ($btn.hasClass('in-basket')) {
          var originalText = $btn.data('original-text') || 'В корзину';
          $btn
            .removeClass('in-basket')
            .text(originalText);
        }
      }
    });
  }

  // ============================================================
  // Уведомления
  // ============================================================

  function showNotification(message, type) {
    type = type || 'info';

    var $container = $('#basket-notifications');
    if (!$container.length) {
      $container = $('<div id="basket-notifications-global"></div>').appendTo('body');
    }

    var $el = $('<div class="basket-notice basket-notice-' + type + '"></div>')
      .text(message)
      .appendTo($container);

    setTimeout(function() {
      $el.fadeOut(300, function() { $(this).remove(); });
    }, 5000);
  }

  // ============================================================
  // Рендер корзины
  // ============================================================

  function showState(state) {
    $('#basket-empty').hide();
    $('#basket-full').hide();
    $('#basket-success').hide();

    if (state === 'empty') {
      $('#basket-empty').show();
    } else if (state === 'full') {
      $('#basket-full').show();
    } else if (state === 'success') {
      $('#basket-success').show();
    }
  }

  function renderBasket(response) {
    var items = response.items || [];

    if (!items.length) {
      showState('empty');
      return;
    }

    showState('full');

    var $container = $('#basket-items-container');
    $container.empty();

    items.forEach(function(item) {
      $container.append(renderBasketItem(item));
    });

    $('#basket-count-text').text(items.length + ' ' + pluralize(items.length, 'товар', 'товара', 'товаров'));
    $('#order-count').text(items.length);
    $('#order-subtotal').text(formatPrice(response.total) + ' ₽');

    // Промокод
    renderPromo(response);

    // Итоговая сумма
    var finalTotal = response.total_with_discount !== undefined
      ? response.total_with_discount
      : response.total;
    $('#order-total').text(formatPrice(finalTotal) + ' ₽');
  }

  function renderBasketItem(item) {
    var imgSrc = item.image || '/img/prod-1.png';
    var sizesText = item.sizes && item.sizes.length ? item.sizes.join(', ') : '';
    var unit = escapeHtml(item.unit || '');

    var $li = $('<div class="bt-unit"></div>');
    $li.attr('data-item-id', item.item_id);

    $li.append('\
      <div class="bt-u-1">\
        <div class="bt-u-1-l"><img src="' + imgSrc + '" width="100" alt=""></div>\
        <div class="bt-u-1-r">\
          <h4><a href="' + (item.url || '#') + '">' + escapeHtml(item.title) + '</a></h4>\
          <div class="bt-u-desc">' + escapeHtml(sizesText) + '</div>\
          <div class="bt-u-sale">' + formatPrice(item.price) + ' ₽/' + unit + '</div>\
        </div>\
      </div>\
    ');

    $li.append('\
      <div class="bt-u-2">\
        <div class="bt-qty">\
          <button type="button" class="qty-minus">−</button>\
          <input type="text" value="' + item.qty + '" class="qty-input"/>\
          <span class="meas">' + unit + '</span>\
          <button type="button" class="qty-plus">+</button>\
        </div>\
        <div class="bt-sale">' + formatPrice(item.sum) + ' ₽</div>\
      </div>\
    ');

    $li.append('\
      <div class="bt-u-3">\
        <button type="button" class="remove-item"><svg><use xlink:href="#trash"></use></svg><span>Удалить</span></button>\
      </div>\
    ');

    return $li;
  }

  // Отрисовка состояния промокода
  function renderPromo(response) {
    var $discountRow = $('.order-row-discount');
    var $promoInput = $('#promo');
    var $promoBtn = $('.order-promo-btn');
    var $promoMsgOk = $('.order-promo-msg-ok');
    var $promoMsgError = $('.order-promo-msg-error');
    var $order = $('#basket-order');

    if (response.promo_applied) {
      // Промокод применён
      $discountRow.show().find('span:last').text('−' + formatPrice(response.discount) + ' ₽');
      $promoMsgOk.text(response.promo_applied).show();
      $promoMsgError.hide();
      $promoInput.val(response.promo_code || Basket.getPromo());
      $promoBtn.text('Убрать');
      $order.addClass('promo-applied').removeClass('promo-error');
    } else {
      $discountRow.hide();
      $promoMsgOk.hide();
      $promoMsgError.hide();
      $promoBtn.text('Применить');
      $order.removeClass('promo-applied promo-error');
    }
  }

  // ============================================================
  // Утилиты
  // ============================================================

  function formatPrice(value) {
    var num = Math.round(parseFloat(value) || 0);
    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function pluralize(n, one, few, many) {
    var mod10 = n % 10;
    var mod100 = n % 100;

    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
    return many;
  }

  function getStep(current) {
    if (current < 1) return 0.5;
    if (current < 10) return 1;
    if (current < 100) return 5;
    return 10;
  }

  // ============================================================
  // Обработчик: добавление в корзину
  // ============================================================

  $(document).on('click', '.add-to-cart', function(e) {
    e.preventDefault();

    var $btn = $(this);
    var itemId = parseInt($btn.data('item-id'), 10);
    if (!itemId) return;

    if (Basket.has(itemId)) {
      window.location.href = '/basket/';
      return;
    }

    if (Basket.add(itemId, 1)) {
      showNotification('Товар добавлен в корзину', 'success');

      $btn
        .addClass('in-basket')
        .data('original-text', $btn.text())
        .text('В корзине');

      updateCounter();
      syncBasket();
    }
  });

  // ============================================================
  // Обработчики: изменение количества, удаление
  // ============================================================

  $(document).on('click', '.bt-unit .qty-minus', function() {
    var $row = $(this).closest('.bt-unit');
    var itemId = parseInt($row.data('item-id'), 10);
    var $input = $row.find('.qty-input');
    var current = parseFloat($input.val()) || 0;

    var step = getStep(current);
    var next = Math.max(step, Math.round((current - step) * 100) / 100);

    if (next === current) return;

    $input.val(next);
    Basket.updateQty(itemId, next);
    syncBasket(function(response) {
      renderBasket(response);
    });
  });

  $(document).on('click', '.bt-unit .qty-plus', function() {
    var $row = $(this).closest('.bt-unit');
    var itemId = parseInt($row.data('item-id'), 10);
    var $input = $row.find('.qty-input');
    var current = parseFloat($input.val()) || 0;

    var step = getStep(current);
    var next = Math.round((current + step) * 100) / 100;

    $input.val(next);
    Basket.updateQty(itemId, next);
    syncBasket(function(response) {
      renderBasket(response);
    });
  });

  $(document).on('change blur', '.bt-unit .qty-input', function() {
    var $row = $(this).closest('.bt-unit');
    var itemId = parseInt($row.data('item-id'), 10);
    var value = parseFloat($(this).val()) || 0;

    if (value <= 0) {
      value = getStep(value);
      $(this).val(value);
    }

    Basket.updateQty(itemId, value);
    syncBasket(function(response) {
      renderBasket(response);
    });
  });

  $(document).on('click', '.bt-unit .remove-item', function() {
    var $row = $(this).closest('.bt-unit');
    var itemId = parseInt($row.data('item-id'), 10);

    Basket.remove(itemId);
    syncBasket(function(response) {
      renderBasket(response);
    });
  });

  // ============================================================
  // Промокод
  // ============================================================

  $(document).on('click', '.order-promo-btn', function(e) {
    e.preventDefault();

    var currentPromo = Basket.getPromo();

    // Если промокод уже применён — "Убрать"
    if (currentPromo) {
      Basket.clearPromo();
      syncBasket(function(response) {
        renderBasket(response);
        showNotification('Промокод снят', 'info');
      });
      return;
    }

    // Применяем новый
    var code = $.trim($('#promo').val());
    if (!code) {
      $('.order-promo-msg-error').text('Введите промокод').show();
      return;
    }

    Basket.setPromo(code);
    syncBasket(function(response) {
      if (response.promo_error) {
        $('.order-promo-msg-error').text(response.promo_error).show();
        $('.order-promo-msg-ok').hide();
        $('#basket-order').addClass('promo-error').removeClass('promo-applied');
        Basket.clearPromo();
      } else if (response.promo_applied) {
        showNotification(response.promo_applied, 'success');
      }
      renderBasket(response);
    });
  });

  // ============================================================
  // Переключатель Доставка / Самовывоз
  // ============================================================

  $(document).on('click', '#basket-delivery-switch button', function() {
    var $btn = $(this);
    var mode = $btn.data('delivery');

    $('#basket-delivery-switch button').removeClass('active');
    $btn.addClass('active');

    $('[data-delivery-field]').removeClass('active');
    $('[data-delivery-field="' + mode + '"]').addClass('active');
  });

  // ============================================================
  // Отправка заявки
  // ============================================================

  $(document).on('click', '#basket-submit', function(e) {
    e.preventDefault();

    var $btn = $(this);
    var $agree = $('#basket-agree');
    var $agreeMsg = $('#agree-msg');

    // Валидация согласия
    if (!$agree.is(':checked')) {
      $agreeMsg.addClass('active');
      return;
    }
    $agreeMsg.removeClass('active');

    // Данные формы
    var name = $.trim($('#co-name').val());
    var phone = $.trim($('#co-phone').val());
    var email = $.trim($('#co-email').val());
    var comment = $.trim($('#co-comment').val());

    if (!name) {
      showNotification('Укажите ваше имя', 'error');
      $('#co-name').focus();
      return;
    }
    if (!phone) {
      showNotification('Укажите телефон', 'error');
      $('#co-phone').focus();
      return;
    }

    var phoneDigits = phone.replace(/\D/g, '');
    if (phoneDigits.length < 11) {
      showNotification('Введите телефон полностью', 'error');
      $('#co-phone').focus();
      return;
    }

    // Способ получения
    var deliveryType = $('#basket-delivery-switch button.active').data('delivery') || 'delivery';
    var address = $.trim($('#co-address').val());
    var pickup = $('#co-pickup').val() || '';

    // Состав корзины
    var rawItems = Basket.getRaw();
    if (!rawItems.length) {
      showNotification('Корзина пуста', 'error');
      return;
    }

    // Блокируем кнопку
    $btn.prop('disabled', true).text('Отправляем…');

    $.ajax({
      url: '/basket/submit/',
      type: 'POST',
      dataType: 'json',
      data: {
        items: rawItems,
        promo: Basket.getPromo(),
        name: name,
        phone: phone,
        email: email,
        comment: comment,
        delivery_type: deliveryType,
        address: address,
        pickup: pickup,
        agree: 1,
        _csrf: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response && response.success) {
          Basket.clear();
          updateCounter(0);
          refreshCartButtons();
          showState('success');
        } else {
          var msg = (response && response.message) ? response.message : 'Ошибка отправки';
          showNotification(msg, 'error');
          $btn.prop('disabled', false).text('Отправить заявку');
        }
      },
      error: function(xhr) {
        console.error('Basket submit error:', xhr.responseText);
        showNotification('Ошибка отправки. Попробуйте ещё раз', 'error');
        $btn.prop('disabled', false).text('Отправить заявку');
      }
    });
  });

  // ============================================================
  // Синхронизация между вкладками
  // ============================================================

  window.addEventListener('storage', function(e) {
    if (e.key === STORAGE_KEY) {
      updateCounter();
      refreshCartButtons();
      $(document).trigger('basket:changed');

      if ($('#basket-full').length) {
        syncBasket(function(response) {
          renderBasket(response);
        });
      }
    }
  });

  // ============================================================
  // Экспорт
  // ============================================================

  window.PKBasket = Basket;
  window.PKBasket.sync = syncBasket;
  window.PKBasket.notify = showNotification;
  window.PKBasket.updateCounter = updateCounter;
  window.PKBasket.refreshButtons = refreshCartButtons;
  window.PKBasketRender = renderBasket;
  window.PKBasketShowState = showState;

  // ============================================================
  // Инициализация
  // ============================================================

  $(function() {
    updateCounter();
    refreshCartButtons();

    // Маска на телефон
    if ($.fn.inputmask) {
      $('#co-phone').inputmask('+7 (999) 999 99 99', {
        placeholder: '+7 (___) ___ __ __',
        showMaskOnHover: false,
        clearIncomplete: false
      });
    }

    if ($('#basket-full').length) {
      syncBasket(function(response) {
        renderBasket(response);
      });
    }
  });

})(jQuery);