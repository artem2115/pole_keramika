// app/raw/js/filter.js
// Фильтрация каталога
(function($) {
  'use strict';

  $(function() {
    var $content = $('.content');
    var $sidebar = $('.sidebar');
    if (!$content.length || !$sidebar.length) return;

    // ============================================================
    // Собираем параметры фильтров в массив {name, value}
    // ============================================================
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

      // Табы (purpose, size) — значения уже slug
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

    // ============================================================
    // Сборка query string с группировкой значений: key=v1,v2&key2=v3
    // ============================================================
    var buildQueryString = function(params) {
      var grouped = {};

      params.forEach(function(p) {
        var name = p.name.replace('[]', '');
        if (!grouped[name]) grouped[name] = [];
        grouped[name].push(p.value);
      });

      var parts = [];
      Object.keys(grouped).forEach(function(key) {
        parts.push(key + '=' + grouped[key].join(','));
      });

      return parts.join('&');
    };

    // ============================================================
    // Вкладка "Товары" — AJAX-фильтрация
    // ============================================================
    var isTovary = $('#products-container').length > 0;
    if (isTovary) {
      var pendingUpdate = null;
      var isUpdating = false;

      var updateProducts = function() {
        if (isUpdating) return;
        isUpdating = true;

        var params = collectFilters();
        var queryString = buildQueryString(params);
        var ajaxUrl = window.location.pathname +
          (queryString ? '?' + queryString + '&ajax=1' : '?ajax=1');

        $.ajax({
          url: ajaxUrl,
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
        var newUrl = window.location.pathname +
          (queryString ? '?' + queryString : '');
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
        var slugs = [];

        $sidebar.find('input[name="brand[]"]:checked').each(function() {
          slugs.push($(this).val());
        });

        var queryString = slugs.length ? 'brand=' + slugs.join(',') : '';
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.location.href = newUrl;
      });
    }

  });

})(jQuery);