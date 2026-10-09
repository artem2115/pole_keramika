// app/raw/js/filter.js
// Фильтрация каталога
(function($) {
  'use strict';

  $(function() {
    var $content = $('.content');
    var $sidebar = $('.sidebar');
    if (!$content.length || !$sidebar.length) return;

    // ============================================================
    // Утилиты
    // ============================================================

    var isMobile = function() {
      return window.innerWidth < 961;
    };

    var getFilterRoot = function() {
      if (isMobile()) {
        var $appB = $('.f-app-b');
        if ($appB.length) return $appB;
      }
      return $sidebar;
    };

    var collectFilters = function() {
      var params = [];
      var $root = getFilterRoot();

      // Цена
      var $priceFrom = $root.find('[name="price_from"]').first();
      var $priceTo = $root.find('[name="price_to"]').first();
      var priceFrom = $priceFrom.val();
      var priceTo = $priceTo.val();
      var defaultMin = String($priceFrom.data('min'));
      var defaultMax = String($priceTo.data('max'));

      if (priceFrom && String(priceFrom) !== defaultMin) {
        params.push({name: 'price_from', value: priceFrom});
      }
      if (priceTo && String(priceTo) !== defaultMax) {
        params.push({name: 'price_to', value: priceTo});
      }

      // Хит
      if ($root.find('[name="is_hit"]').is(':checked')) {
        params.push({name: 'is_hit', value: 1});
      }

      // Чекбоксы
      $root.find('input[type="checkbox"]:checked').each(function() {
        var name = this.name;
        if (name === 'is_hit') return;
        params.push({name: name, value: this.value});
      });

      return params;
    };

    var buildQueryString = function(params) {
      var grouped = {};

      params.forEach(function(p) {
        var name = p.name.replace('[]', '');
        (grouped[name] = grouped[name] || []).push(p.value);
      });

      return Object.keys(grouped).map(function(key) {
        return key + '=' + grouped[key].join(',');
      }).join('&');
    };

    // Сформировать URL: путь + query (если есть)
    var buildUrl = function(params) {
      var queryString = params.length ? buildQueryString(params) : '';
      return window.location.pathname + (queryString ? '?' + queryString : '');
    };

    // ============================================================
    // Вкладка "Товары" — AJAX-фильтрация, пагинация, синхронизация табов
    // ============================================================
    var isTovary = $('#products-container').length > 0;
    if (isTovary) {
      var pendingUpdate = null;
      var isUpdating = false;
      var hasChanges = false;
      var initialState = null;

      var getFilterState = function() {
        var $root = getFilterRoot();
        var state = {
          price_from: $root.find('[name="price_from"]').first().val() || '',
          price_to: $root.find('[name="price_to"]').first().val() || '',
          is_hit: $root.find('[name="is_hit"]').is(':checked') ? '1' : '',
          checkboxes: []
        };

        $root.find('input[type="checkbox"]:checked').each(function() {
          if (this.name === 'is_hit') return;
          state.checkboxes.push(this.name + ':' + this.value);
        });
        state.checkboxes.sort();

        return JSON.stringify(state);
      };

      var updateButtonState = function() {
        if (!initialState) {
          hasChanges = false;
          $('#filter-mobile-btn').text('Закрыть').removeClass('has-changes');
          return;
        }

        var changed = getFilterState() !== initialState;

        if (changed !== hasChanges) {
          hasChanges = changed;
          $('#filter-mobile-btn')
            .text(changed ? 'Применить' : 'Закрыть')
            .toggleClass('has-changes', changed);
        }
      };

      var resetChanged = function() {
        hasChanges = false;
        initialState = getFilterState();
        $('#filter-mobile-btn').text('Закрыть').removeClass('has-changes');
      };

      // Синхронизация табов с чекбоксами
      var syncTabsFromCheckboxes = function() {
        var $root = getFilterRoot();

        $content.find('.tabs2').each(function() {
          var $tabs = $(this);
          var name = $tabs.data('filter-name');
          if (!name) return;

          $tabs.find('li').each(function() {
            var $li = $(this);
            var $checkbox = $root.find('input[name="' + name + '[]"][value="' + $li.data('value') + '"]');
            $li.toggleClass('active', $checkbox.is(':checked'));
          });
        });
      };

      var loadProducts = function(url) {
        if (isUpdating) return;
        isUpdating = true;

        var sep = url.indexOf('?') !== -1 ? '&' : '?';

        $.ajax({
          url: url + sep + 'ajax=1',
          dataType: 'html',
          success: function(html) {
            $('#products-container').html(html);
          },
          error: function(xhr) {
            console.error('Ошибка фильтрации:', xhr.responseText);
          },
          complete: function() {
            isUpdating = false;
          }
        });

        window.history.replaceState({}, '', url);
      };

      var updateProducts = function() {
        loadProducts(buildUrl(collectFilters()));
      };

      var scheduleUpdate = function() {
        clearTimeout(pendingUpdate);
        pendingUpdate = setTimeout(updateProducts, 150);
      };

      var onFilterChange = function() {
        isMobile() ? updateButtonState() : scheduleUpdate();
      };

      // --- Обработчики ---

      $(document).on('blur', 'input[name="price_from"], input[name="price_to"]', onFilterChange);
      $(document).on('change', 'input[name="is_hit"]', onFilterChange);

      $(document).on('change', 'input[type="checkbox"][name$="[]"]', function() {
        syncTabsFromCheckboxes();
        onFilterChange();
      });

      // Клик по табу → переключаем чекбокс
      $(document).on('click', '.tabs2 li', function() {
        var $li = $(this);
        var name = $li.closest('.tabs2').data('filter-name');
        if (!name) return;

        var $checkbox = getFilterRoot().find('input[name="' + name + '[]"][value="' + $li.data('value') + '"]');
        if ($checkbox.length) {
          $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        }
      });

      // Пагинация через AJAX
      $(document).on('click', '#products-container .pgn a', function(e) {
        e.preventDefault();
        var href = this.getAttribute('href');
        if (href) loadProducts(href);
      });

      // Мобильная модалка
      var closeMobileFilter = function() {
        $('body').removeClass('f-open');
        $('.fs-1').removeClass('active');
        $('.f-app').removeClass('active');
      };

      $(document).on('click', '#filter-mobile-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);

        if ($btn.hasClass('has-changes')) {
          updateProducts();
          resetChanged();
        }
        closeMobileFilter();
      });

      $(document).on('click', '.fs-1', function() {
        initialState = getFilterState();
        hasChanges = false;
        $('#filter-mobile-btn').text('Закрыть').removeClass('has-changes');
      });

      // Инициализация
      syncTabsFromCheckboxes();


      // Клик по активному фильтру — снимаем его
      $(document).on('click', '.active-filters-list button', function() {
        var field = $(this).data('filter-field');
        var slug = $(this).data('filter-slug');
        var $root = getFilterRoot();

        // Цена
        if (field === 'price_from' || field === 'price_to') {
          var $input = $root.find('input[name="' + field + '"]').first();
          var dataAttr = field === 'price_from' ? 'min' : 'max';
          $input.val($input.data(dataAttr));
        }
        // Хит
        else if (field === 'is_hit') {
          $root.find('input[name="is_hit"]').prop('checked', false);
        }
        // Чекбоксы
        else {
          var $checkbox = $root.find('input[name="' + field + '[]"][value="' + slug + '"]');
          if ($checkbox.length) {
            $checkbox.prop('checked', false);
          }
        }

        syncTabsFromCheckboxes();
        updateProducts();
      });

      // Клик по "Сбросить все"
      $(document).on('click', '#active-filters-reset', function() {
        var $root = getFilterRoot();

        // Сбрасываем чекбоксы
        $root.find('input[type="checkbox"]').prop('checked', false);

        // Сбрасываем цену к дефолту
        $root.find('input[name="price_from"]').each(function() {
          $(this).val($(this).data('min'));
        });
        $root.find('input[name="price_to"]').each(function() {
          $(this).val($(this).data('max'));
        });

        // Синхронизируем табы
        syncTabsFromCheckboxes();

        // Применяем
        updateProducts();
      });
    }

    // ============================================================
    // Вкладка "Коллекции" — фильтр по бренду с перезагрузкой
    // ============================================================
    if ($sidebar.find('input[name="brand[]"]').length) {
      $(document).on('change', 'input[name="brand[]"]', function() {
        // На вкладке "Товары" работает AJAX — редирект не нужен
        if ($('#products-container').length) return;

        var slugs = [];

        $sidebar.find('input[name="brand[]"]:checked').each(function() {
          slugs.push(this.value);
        });

        var queryString = slugs.length ? 'brand=' + slugs.join(',') : '';
        window.location.href = window.location.pathname + (queryString ? '?' + queryString : '');
      });
    }

  });

})(jQuery);