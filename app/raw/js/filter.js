// app/raw/js/filter.js
// AJAX-фильтрация каталога
(function($) {
  'use strict';

  $(function() {
    var $content = $('.content');
    var $sidebar = $('.sidebar');
    if (!$content.length || !$sidebar.length) return;

    // ============================================================
    // Вкладка "Товары" — AJAX-фильтрация
    // ============================================================
    var isTovary = $('#products-container').length > 0;
    if (isTovary) {
      var pendingUpdate = null;
      var isUpdating = false;

      var collectFilters = function() {
        var params = [];

        // Цена
        var priceFrom = $sidebar.find('[name="price_from"]').val();
        var priceTo = $sidebar.find('[name="price_to"]').val();
        if (priceFrom) params.push({name: 'price_from', value: priceFrom});
        if (priceTo) params.push({name: 'price_to', value: priceTo});

        // Хит
        if ($sidebar.find('[name="is_hit"]').is(':checked')) {
          params.push({name: 'is_hit', value: 1});
        }

        // Чекбоксы (кроме is_hit)
        $sidebar.find('input[type="checkbox"]:checked').each(function() {
          var name = $(this).attr('name');
          if (name === 'is_hit') return;
          params.push({name: name, value: $(this).val()});
        });

        // Табы (purpose, size)
        $content.find('.tabs2').each(function() {
          var $tabs = $(this);
          var name = $tabs.data('filter-name');
          if (!name) return;

          $tabs.find('li.active').each(function() {
            params.push({
              name: name + '[]',
              value: $(this).data('value')
            });
          });
        });

        return params;
      };

      var updateProducts = function() {
        if (isUpdating) return;
        isUpdating = true;

        var params = collectFilters();
        var ajaxParams = params.slice();
        ajaxParams.push({name: 'ajax', value: 1});

        $.ajax({
          url: window.location.pathname,
          data: $.param(ajaxParams),
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

        // Обновляем URL без ajax=1
        var queryString = $.param(params);
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.history.replaceState({}, '', newUrl);
      };

      var scheduleUpdate = function() {
        clearTimeout(pendingUpdate);
        pendingUpdate = setTimeout(updateProducts, 150);
      };

      // Цена — по blur
      $sidebar.on('blur', '[name="price_from"], [name="price_to"]', scheduleUpdate);

      // Хит — по change
      $sidebar.on('change', '[name="is_hit"]', scheduleUpdate);

      // Чекбоксы фильтров — по change
      $sidebar.on('change', 'input[type="checkbox"]:not([name="is_hit"])', scheduleUpdate);

      // Табы — по клику (toggle + отложенный вызов)
      $content.on('click', '.tabs2 li', function() {
        $(this).toggleClass('active');
        scheduleUpdate();
      });
    }

    // ============================================================
    // Вкладка "Коллекции" — фильтр по бренду с перезагрузкой
    // ============================================================
    var $brandCheckboxes = $sidebar.find('input[name="brand[]"]');
    if ($brandCheckboxes.length) {
      $sidebar.on('change', 'input[name="brand[]"]', function() {
        var params = [];

        $sidebar.find('input[name="brand[]"]:checked').each(function() {
          params.push({name: 'brand[]', value: $(this).val()});
        });

        var queryString = $.param(params);
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.location.href = newUrl;
      });
    }

  });

})(jQuery);