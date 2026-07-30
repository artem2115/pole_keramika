(function() {
  'use strict';

  var mapInstance = null;
  var placemarks = [];
  var isMapReady = false;

  function initMap() {
    var $mapContainer = $('#yandex-map');
    if (!$mapContainer.length) {
      return;
    }

    if ($mapContainer.data('initialized')) {
      return;
    }

    var $mapItems = $('.ul-5 li[data-lat][data-lng]');
    if (!$mapItems.length) {
      $mapContainer.html('<div style="background:#f5f5f5;display:flex;align-items:center;justify-content:center;height:100%;font-family:sans-serif;color:#999;">Нет адресов для отображения на карте</div>');
      return;
    }

    var mapCoords = [];
    $mapItems.each(function(index) {
      var $item = $(this);
      var lat = parseFloat($item.data('lat'));
      var lng = parseFloat($item.data('lng'));
      if (lat && lng) {
        mapCoords.push({
          lat: lat,
          lon: lng,
          title: $item.find('b').text().trim(),
          address: $item.find('i').text().trim().replace(/\s+/g, ' '),
          phone: $item.find('span').text().trim(),
          index: index,
          $item: $item
        });
      }
    });

    if (!mapCoords.length) {
      $mapContainer.html('<div style="background:#f5f5f5;display:flex;align-items:center;justify-content:center;height:100%;font-family:sans-serif;color:#999;">Нет координат для отображения на карте</div>');
      return;
    }

    var windowWidth = $(window).width();
    var mapWidth = Math.min(windowWidth - 60, 1400);

    $mapContainer.css({
      'width': '100%',
      'max-width': mapWidth + 'px',
      'height': '400px',
      'min-height': '400px',
      'display': 'block',
      'position': 'relative',
      'background': '#f5f5f5',
      'border-radius': '8px',
      'overflow': 'hidden',
      'margin': '0 auto'
    });

    mapInstance = new ymaps.Map('yandex-map', {
      center: [mapCoords[0].lat, mapCoords[0].lon],
      zoom: 14,
      controls: ['zoomControl', 'fullscreenControl']
    });

    mapInstance.behaviors.disable('scrollZoom');

    var placemarkOptions = {
      iconLayout: 'default#image',
      iconImageHref: '/i/placemark.svg',
      iconImageSize: [40, 40],
      iconImageOffset: [-20, -40]
    };

    for (var i = 0; i < mapCoords.length; i++) {
      var c = mapCoords[i];
      var placemark = new ymaps.Placemark(
        [c.lat, c.lon],
        {
          balloonContent: '<aside>' + c.title + '<br>' + c.address + '<br>' + c.phone + '</aside>'
        },
        placemarkOptions
      );

      placemark._index = i;
      placemark._coords = [c.lat, c.lon];

      mapInstance.geoObjects.add(placemark);
      placemarks.push(placemark);
    }

    if (mapCoords.length > 1) {
      mapInstance.setBounds(mapInstance.geoObjects.getBounds());
    }

    // Ждем полной загрузки карты
    mapInstance.events.once('actionend', function() {
      isMapReady = true;
      console.log('Карта полностью загружена');
    });

    // Также помечаем как готовую через таймаут
    setTimeout(function() {
      isMapReady = true;
    }, 1000);

    setTimeout(function() {
      mapInstance.container.fitToViewport();
    }, 300);

    $mapContainer.data('initialized', true);

    // ====== ОБРАБОТЧИК КЛИКА ПО АДРЕСАМ ======
    $('.ul-5 li[data-lat][data-lng]').on('click', function() {
      var $this = $(this);
      var lat = parseFloat($this.data('lat'));
      var lng = parseFloat($this.data('lng'));

      if (!lat || !lng || !mapInstance) {
        return;
      }

      // Если карта еще не готова, ждем
      if (!isMapReady) {
        var waitInterval = setInterval(function() {
          if (isMapReady && mapInstance) {
            clearInterval(waitInterval);
            centerMapOnPoint($this, lat, lng);
          }
        }, 100);
        return;
      }

      centerMapOnPoint($this, lat, lng);
    });
  }

  function centerMapOnPoint($item, lat, lng) {
    $('.ul-5 li').removeClass('active');
    $item.addClass('active');

    // Используем panTo для более плавного перемещения
    mapInstance.panTo([lat, lng], {
      zoom: 16,
      duration: 500
    });

    setTimeout(function() {
      for (var j = 0; j < placemarks.length; j++) {
        var coords = placemarks[j].geometry.getCoordinates();
        if (coords[0] === lat && coords[1] === lng) {
          placemarks[j].balloon.open();
          break;
        }
      }
    }, 400);

    setTimeout(function() {
      $('html, body').animate({
        scrollTop: $('#yandex-map').offset().top - 100
      }, 400);
    }, 300);
  }
  function checkYmaps() {
    if (typeof ymaps !== 'undefined' && ymaps.Map && typeof ymaps.ready === 'function') {
      ymaps.ready(initMap);
      return true;
    }
    return false;
  }

  if (checkYmaps()) {
    return;
  }

  var checkInterval = setInterval(function() {
    if (checkYmaps()) {
      clearInterval(checkInterval);
    }
  }, 300);

  setTimeout(function() {
    if (!document.querySelector('#yandex-map ymaps')) {
      clearInterval(checkInterval);
      var $mapContainer = $('#yandex-map');
      if ($mapContainer.length) {
        $mapContainer.html('<div style="background:#f5f5f5;display:flex;align-items:center;justify-content:center;height:100%;font-family:sans-serif;color:#999;">Карта временно недоступна</div>');
      }
    }
  }, 10000);

})();