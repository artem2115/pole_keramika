//app/raw/js/site.js
//сохранение свг спрайта в localStorage
(function(window, document){
  'use strict';

  const file = '/i/sprite.svg';
  const revision = '0.1';
  const LS = window.localStorage;

  if(!document.createElementNS) return;

  const insertSVG = (data) => {
    document.body.insertAdjacentHTML('afterbegin', data);
  };

  if(LS){
    const cachedRev = LS.getItem('svgRev');
    const cachedData = LS.getItem('svgData');

    if(cachedData && cachedRev === revision){
      insertSVG(cachedData);
      return;
    }
  }

  fetch(file)
    .then(res => {
      if(!res.ok) throw new Error('SVG file not found');
      const contentType = res.headers.get('content-type');
      if(!contentType || !contentType.includes('image/svg')){
        throw new Error('Invalid content type');
      }
      return res.text();
    })
    .then(data => {
      insertSVG(data);
      if(LS){
        try {
          LS.setItem('svgData', data);
          LS.setItem('svgRev', revision);
        } catch(e) {}
      }
    })
    .catch(err => console.error('SVG sprite error:', err));

})(window, document);

// Основной код
$(function(){

  const body = $('body');
  const header = $("header");
  const ul_3 = $('.ul-3');
  const sl_1 = $('.sl-1');
  const prods_1 = $('.prods-1-sl');
  const prods_2 = $('.prods-2-sl');
  const sl_9 = $('.sl-9');
  const sl_2_for = $('.sl-2-for');
  const word_click = 'click';
  const word_active = 'active';
  const word_openModal = 'openModal';
  const cover = $('<div id="cover"/>').appendTo('body');
  const preArrow = '<button class="slick-next"><svg><use xlink:href="#arr-l"></use></svg></button>';
  const nextArrow = '<button class="slick-prev"><svg><use xlink:href="#arr-r"></use></svg></button>';

  const CLASSES = {
    mNav: 'mNav',
    openModal: 'openModal',
    active: 'active',
    hovered: 'hovered',
    covNav: 'cov_nav'
  };

  function isVisible(elem){
    // Проверяем сам элемент
    const rect = elem.getBoundingClientRect();
    const windowHeight = document.documentElement.clientHeight;
    const windowWidth = document.documentElement.clientWidth;

    if (
      rect.width > 0 &&
      rect.height > 0 &&
      rect.top < windowHeight + 200 &&
      rect.bottom > -200 &&
      rect.left < windowWidth + 200 &&
      rect.right > -200
    ) {
      return true;
    }

    // Проверяем ближайшего родителя (на случай если img не отрендерен)
    let parent = elem.parentElement;
    while (parent) {
      const parentRect = parent.getBoundingClientRect();
      if (
        parentRect.width > 0 &&
        parentRect.height > 0 &&
        parentRect.top < windowHeight + 200 &&
        parentRect.bottom > -200 &&
        parentRect.left < windowWidth + 200 &&
        parentRect.right > -200
      ) {
        return true;
      }
      parent = parent.parentElement;
    }

    return false;
  }

  function showVisible(forceAll){
    const elems = document.querySelectorAll('iframe[data-src], img[data-src], img[data-srcset]');
    let anyLoaded = false;

    elems.forEach(elem => {
      if(!forceAll && !isVisible(elem)) return;

      anyLoaded = true;

      const src = elem.getAttribute('data-src');
      if(src){
        elem.src = src;
        elem.removeAttribute('data-src');
      }

      const srcset = elem.getAttribute('data-srcset');
      if(srcset){
        elem.srcset = srcset;
        elem.removeAttribute('data-srcset');
      }
    });

    // Если после проверки ни один не загрузился — пробуем загрузить все
    if (!anyLoaded && !forceAll && elems.length > 0) {
      showVisible(true);
    }
  }

  window.addEventListener('scroll', function() { showVisible(false); });
  window.addEventListener('resize', function() { showVisible(false); });
  window.addEventListener('load', function() {
    setTimeout(function() { showVisible(false); }, 100);
    setTimeout(function() { showVisible(false); }, 500);
    setTimeout(function() { showVisible(false); }, 1000);
  });
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() { showVisible(false); }, 100);
    setTimeout(function() { showVisible(false); }, 500);
  });

  function addOpenModalClass(){
    const pagePosition = window.scrollY;
    document.body.classList.add(word_openModal);
    document.body.dataset.position = pagePosition;
    document.body.style.top = -pagePosition + 'px';
  }

  function removeOpenModalClass(){
    const pagePosition = parseInt(document.body.dataset.position, 10) || 0;
    body.removeClass(word_openModal);
    window.scrollTo({top: pagePosition, left: 0, behavior: 'instant'});
    document.body.removeAttribute('style');
    delete document.body.dataset.position;
  }

  $('input[type="checkbox"]:not(#availability)').after('<var></var>');

  if(window.innerWidth < 1166){
    header.addClass('out');
    $('.htr').append('<div class="tNav"><i></i></div><div class="mob_nav"><div class="mn_body"></div></div>');
    var mn_body = $('.mn_body');
    mn_body.append($('header nav'));
    $('.mn_body nav > ul > li').each(function() {
      if ($(this).find('ul').length) {
        $(this).addClass('has-submenu');
      }
    });
    $('nav > ul > li span a', mn_body).on(word_click, function(e){
      let $this = $(this);
      e.preventDefault();
      $this.parents('li').toggleClass(word_active);
    })
  }

  if(window.innerWidth < 681){
    $('.mn_body').append($('.htr [data-modal=order_call]'))
  }

  $(document).on(word_click, '.tNav', function(){
    body.toggleClass(CLASSES.mNav);
    cover.toggleClass(CLASSES.covNav).fadeToggle();

    if(body.hasClass(CLASSES.mNav)){
      addOpenModalClass();
    } else {
      removeOpenModalClass();
    }
  });

  $(document).on(word_click, '.mn_body nav > ul > li.has-submenu > a', function(e) {
    e.preventDefault();
    const $li = $(this).parent();
    $li.toggleClass('open');
    $li.siblings().removeClass('open');
  });

  cover.on(word_click, function(){
    if(body.hasClass(CLASSES.mNav)){
      body.removeClass(CLASSES.mNav);
      cover.removeClass(CLASSES.covNav).fadeOut();
      removeOpenModalClass();
    }
  });

  if(sl_1.length){
    sl_1.slick({
      autoplay: true,
      slidesToShow: 5,
      slidesToScroll: 1,
      autoplaySpeed: 400000,
      centerPadding: '0',
      appendArrows: '.sl-1-arrs',
      prevArrow: preArrow,
      nextArrow: nextArrow,
      responsive: [
        { breakpoint: 1691, settings: {slidesToShow: 5} },
        { breakpoint: 1481, settings: {slidesToShow: 4} },
        { breakpoint: 1245, settings: {slidesToShow: 3} },
        { breakpoint: 781, settings: {slidesToShow: 2} },
        { breakpoint: 500, settings: { centerMode:true,centerPadding: '30px', slidesToShow: 1 } }
      ]
    });
  }

  if(prods_1.length){
    prods_1.slick({
      autoplay: true,
      slidesToShow: 5,
      slidesToScroll: 1,
      autoplaySpeed: 400000,
      centerPadding: '0',
      appendArrows: '.prods-1-arrs',
      prevArrow: preArrow,
      nextArrow: nextArrow,
      responsive: [
        { breakpoint: 1691, settings: {slidesToShow: 5} },
        { breakpoint: 1481, settings: {slidesToShow: 4} },
        { breakpoint: 1245, settings: {slidesToShow: 3} },
        { breakpoint: 741, settings: { centerMode: true,centerPadding: '30px', slidesToShow: 2 } },
        { breakpoint: 620, settings: {centerMode: true,centerPadding: '50px', slidesToShow: 1 } },
        { breakpoint: 350, settings: {centerMode: true,centerPadding: '10px', slidesToShow: 1 } }
      ]
    });
  }

  if(prods_2.length){
    prods_2.slick({
      autoplay: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      autoplaySpeed: 400000,
      centerPadding: '0',
      appendArrows: '.prods-1-arrs',
      prevArrow: preArrow,
      nextArrow: nextArrow,
      responsive: [
        { breakpoint: 985, settings: {slidesToShow: 3} },
        { breakpoint: 770, settings: {centerMode:true, centerPadding: '30px', slidesToShow: 1 } },
        { breakpoint: 410, settings: { centerPadding: '20px', slidesToShow: 1 } }
      ]
    });
  }

  if(sl_9.length){
    sl_9.slick({
      autoplay: true,
      slidesToShow: 6,
      slidesToScroll: 1,
      autoplaySpeed: 400000,
      centerPadding: '0',
      appendArrows: '.sl-9-arrs',
      prevArrow: preArrow,
      nextArrow: nextArrow,
      responsive: [
        { breakpoint: 1166, settings: {slidesToShow: 5} },
        { breakpoint: 1026, settings: {slidesToShow: 4} },
        { breakpoint: 781, settings: {slidesToShow: 3 } },
        { breakpoint: 621, settings: {slidesToShow: 2 ,centerMode:true,centerPadding: '30px',} },
        { breakpoint: 481, settings: {slidesToShow: 1 ,centerMode:true,centerPadding: '50px',} },
      ]
    });
  }

  if (sl_2_for.length) {
    sl_2_for.slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: true,
      asNavFor: '.sl-3-nav'
    });

    $('.sl-3-nav').each(function() {
      const $this = $(this);
      const $parent = $this.closest('.b8-1c');
      const isReversed = $parent.hasClass('sl-2-rev');
      const isMobile = window.innerWidth < 521;

      $this.slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        asNavFor: $this.closest('.b8-1c').find('.sl-2-for'),
        vertical: isMobile ? false : !isReversed,
        focusOnSelect: true,
        responsive: [
          { breakpoint: 411, settings: {slidesToShow: 3} },
          { breakpoint: 371, settings: {slidesToShow: 2} }
        ]
      });
    });
  }

  const $menu = $('.htc nav > ul');

  if ($menu.length) {
    if (!$menu.data('hover-bg-initialized')) {
      $menu.data('hover-bg-initialized', true);

      const $hoverBg = $('<div>').addClass('hover-bg').appendTo($menu);
      const $menuItems = $menu.children('li').children('a');

      if ($menuItems.length) {
        function updateHoverBg($link) {
          if (!$link || !$link.length) return;
          const position = $link.position();
          const width = $link.outerWidth();
          $hoverBg.css({ left: position.left, width: width }).addClass('active');
          $menuItems.removeClass('hovered');
          $link.addClass('hovered');
        }

        function positionSubmenus() {
          $('.has-submenu').each(function() {
            const $li = $(this);
            const $submenu = $li.find('> ul');
            if ($submenu.length) {
              $submenu.css({ 'left': $li.position().left + 'px' });
            }
          });
        }

        $('.htc nav > ul > li').each(function() {
          if ($(this).find('ul').length) {
            $(this).addClass('has-submenu');
          }
        });

        positionSubmenus();

        $menu.on('mouseleave', function(e) {
          const $relatedTarget = $(e.relatedTarget);
          if (!$relatedTarget.closest('.htc nav').length) {
            $hoverBg.removeClass('active');
            $menuItems.removeClass('hovered');
          }
        });

        $menuItems.on('mouseenter', function() {
          updateHoverBg($(this));
        });

        $menu.on('mouseenter', 'ul li', function(e) {
          e.stopPropagation();
          const $parentLink = $(this).closest('.htc nav > ul > li').children('a').first();
          if ($parentLink.length) {
            updateHoverBg($parentLink);
          }
        });

        let resizeTimeout;
        $(window).on('resize', function() {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(function() {
            const $hoveredItem = $menuItems.filter('.hovered');
            if ($hoveredItem.length) updateHoverBg($hoveredItem);
            positionSubmenus();
          }, 100);
        });

        const $menuItemsWithSubmenu = $('.has-submenu');
        let hideTimeout;

        $menuItemsWithSubmenu.on('mouseenter', function() {
          clearTimeout(hideTimeout);
          const $submenu = $(this).find('> ul');
          if ($submenu.length) {
            const liLeft = $(this).position().left;
            $submenu.css('left', liLeft + 'px');
            $submenu.stop(true, true).css({
              'opacity': 1,
              'visibility': 'visible',
              'transform': 'translateY(0)'
            });
          }
        });

        $menuItemsWithSubmenu.on('mouseleave', function() {
          const $submenu = $(this).find('> ul');
          if ($submenu.length) {
            hideTimeout = setTimeout(function() {
              $submenu.stop(true, true).css({
                'opacity': 0,
                'visibility': 'hidden',
                'transform': 'translateY(10px)'
              });
            }, 200);
          }
        });
      }
    }
  }

  $('h5', ul_3).each(function() {
    if ($(this).text().length > 60) {
      $(this).addClass('ft1');
    }
  });

  $(document).on(word_click, '.sb1-ttl', function() {
    if (!$(this).find('i').length) return;

    const $block = $(this).closest('.sb1');
    const $content = $block.find('.sb1-b');

    if ($block.hasClass('closed')) {
      $content.stop(true).animate({ height: $content[0].scrollHeight }, 300, function() {
        $content.css('height', 'auto');
        $block.removeClass('closed');
      });
    } else {
      const currentHeight = $content.height();
      $content.css('height', currentHeight + 'px');
      $content.stop(true).animate({ height: 0, paddingTop: 0, paddingBottom: 0 }, 300, function() {
        $block.addClass('closed');
      });
    }
  });

  $('.i-thg').each(function() {
    const $thg = $(this);
    const count = $thg.find('img').length;
    if (count <= 1) return;

    const $zones = $('<div>').addClass('i-thg-zones');
    const $dots = $('<div>').addClass('i-thg-dots');

    for (let i = 0; i < count; i++) {
      $zones.append($('<span>').attr('data-index', i));
      $dots.append($('<span>').attr('data-index', i).toggleClass('active', i === 0));
    }

    $thg.append($zones, $dots);
  });

  function switchThgImage($container, index) {
    $container.find('img').removeClass('active').eq(index).addClass('active');
    $container.find('.i-thg-dots span').removeClass('active').eq(index).addClass('active');
  }

  $(document).on('mouseenter', '.i-thg .i-thg-zones span', function() {
    switchThgImage($(this).closest('.i-thg'), $(this).data('index'));
  });

  $(document).on(word_click, '.i-thg .i-thg-dots span', function() {
    switchThgImage($(this).closest('.i-thg'), $(this).data('index'));
  });


  if (window.innerWidth < 961) {
    if ($('.f-r').length) {
      $('.f-app-b').html($('.f-r'));
      $('.f-app').prepend('<div class="f-close"></div>');

      $(document).on(word_click, '.fs-1', function() {
        const $fs = $('.fs-1');
        const $fApp = $('.f-app');

        if ($fs.hasClass('active')) {
          body.removeClass('f-open');
          $fs.removeClass('active');
          $fApp.removeClass('active');
        } else {
          body.addClass('f-open');
          $fs.addClass('active');
          $fApp.addClass('active');
        }
      });

      $(document).on(word_click, '.f-close', function() {
        body.removeClass('f-open');
        $('.fs-1').removeClass('active');
        $('.f-app').removeClass('active');
      });
    }
  }

  if(!sessionStorage.getItem('hb-scrolled')){
    const $ul = $('.hb ul');
    if($ul[0].scrollWidth > $ul.width()){
      $ul.addClass('hb-hint');
    }
    sessionStorage.setItem('hb-scrolled', '1');
  }

  $(document).on(word_click, '[data-modal]', function(e){
    e.preventDefault();
    var data_modal = $(this).attr('data-modal');
    if(data_modal === 'order_design_project'){//Заказать дизайн проект
      $.ajax({
        url: '/ajax/get-modal-3d-project',
        type: 'POST',
        dataType: 'html',
        data: {
          _csrf: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(html){
          $('body').append(html);
          get_modal($('.modal'));
          // Вставляем модалку в body
          //$('body').append(response);
          // Открываем
          //$('.modal-3d').fadeIn(300);
          //$('body').addClass('openModal');
        },
        error: function(xhr){
          console.error('Ошибка загрузки модалки:', xhr.responseText);
        }
      });
    }
    if(data_modal === 'get_3d_project'){
      //здесь код вызова модалки с формой заказа бесплатного 3д проекта
    }
  });

  function get_modal(modal){
    cover.fadeIn();
    modal.fadeIn();
    addOpenModalClass();
  }
  function closeModal(){
    cover.removeClass(word_active).fadeOut();
    $('.modalWrap').fadeOut(function(){
      $(this).remove()
    });

    if(body.hasClass(word_openModal)){
      removeOpenModalClass()
    }
  }
  
  body.on(word_click, '#cover,.mn_top i, .close, #close-modal', function(){
    closeModal();
  });
  body.on(word_click, '.modalWrap', function(e){
    if(!$(e.target).closest('.modal').length){
      closeModal();
    }
  });
  $(document).on('keydown', function(e){
    if(e.keyCode === 27){
      closeModal();
    }
  });
  
});