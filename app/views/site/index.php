<?php

use yii\easyii\modules\article\api\Article;
use yii\easyii\modules\carousel\api\Carousel;
use yii\easyii\modules\gallery\api\Gallery;
use yii\easyii\modules\guestbook\api\Guestbook;
use yii\easyii\modules\news\api\News;
use yii\easyii\modules\text\api\Text;
use yii\helpers\Html;
$this->title = $page->seo('title', $page->model->title);


?>


<div class="b1">
  <div class="b1-l"><img src="/img/3d-project.png" alt=""></div>
  <div class="b1-r">
    <div class="h1">Бесплатный 3D проект для вашего дома</div>
    <p>Воплотим в реальность даже самые смелые задумки, поможем определиться с концепцией</p>
    <div class="btns-1">
      <button class="btn1-b1" data-modal="order_design_project">Заказать дизайн проект</button>
      <button class="btn1-b3" data-modal="order_call">Заказать звонок <svg><use xlink:href="#call"></use></svg></button>
    </div>
  </div>
</div>

<ul class="ul-1">
  <li>
    <i><svg><use xlink:href="#eye"></use></svg></i>
    <div><a href="#">Бесплатный 3D проект</a> по вашей планировке</div>
  </li>
  <li>
    <i><svg><use xlink:href="#ware"></use></svg></i>
    <div>Крупнейший склад плитки в Чите</div>
  </li>
  <li>
    <i><svg><use xlink:href="#delivery"></use></svg></i>
    <div>Доставка в день покупки</div>
  </li>
  <li>
    <i><svg><use xlink:href="#map"></use></svg></i>
    <div><a href="#">5 магазинов</a> в Чите</div>
  </li>
  <li>
    <i><svg><use xlink:href="#check"></use></svg></i>
    <div>Нашей сети более 13 лет</div>
  </li>
</ul>

<ul class="ul-2">
  <li>
    <a href="#">
      <span>Плитка ПВХ</span>
      <i><img src="/img/p-1.png" alt=""></i>
    </a>
  </li>
  <li>
    <a href="#">
      <span>Ламинат</span>
      <i><img src="/img/p-2.png" alt=""></i>
    </a>
  </li>
  <li>
    <a href="#">
      <span>Плитка</span>
      <i><img src="/img/p-2.png" alt=""></i>
    </a>
  </li>
  <li>
    <a href="#">
      <span>Смеси, затирки, клей и др.</span>
      <i><img src="/img/p-2.png" alt=""></i>
    </a>
  </li>
  <li>
    <a href="#">
      <span>Доп товары</span>
      <i><img src="/img/p-2.png" alt=""></i>
    </a>
  </li>
</ul>

<div class="ttl">
  <h1>Плитка под любую текстуру</h1>
  <div class="sl-arrs sl-1-arrs"></div>
</div>

<figure class="sl-1">
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-1.png" alt="">
      <span><i>Бетон</i></span>
    </a>
  </div>
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-2.png" alt="">
      <span><i>Мрамор</i></span>
    </a>
  </div>
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-1.png" alt="">
      <span><i>Камень</i></span>
    </a>
  </div>
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-2.png" alt="">
      <span><i>Дерево</i></span>
    </a>
  </div>
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-1.png" alt="">
      <span><i>Другая</i></span>
    </a>
  </div>
  <div class="sl-1-s">
    <a href="#">
      <img src="/img/sl-1-2.png" alt="">
      <span><i>Третья</i></span>
    </a>
  </div>
</figure>

<ul class="tabs">
  <li class="active">Акции</li>
  <li>Хиты продаж</li>
  <li>Новинки</li>
</ul>

<div class="prods-1-wrap">
  <figure class="prods-1 prods-1-sl">
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>2500</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка деревянная стеклянная мягкая столбы весовые</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li><li>260<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>2545</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>3200</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>7600</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>1689</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
    <div class="prod-1">
      <div class="p1-t">
        <div class="i">
          <img src="/img/prod-1.png" alt="">
          <span class="avail">В наличии</span>
        </div>
        <div class="p-info">
          <div class="sale">
            <b>5316</b> <i>₽/м²</i>
            <span class="v1">2500</span>
            <span class="dt">-25%</span>
          </div>
          <h4><a href="#">Настенная плитка Unitile Муза беж низ елка</a></h4>
          <ul class="sizes">
            <li>250<span>x</span>400</li>
          </ul>
          <ul>
            <li>Толщина: <span>10 мм</span></li>
            <li>Штук в упаковке: <span>2 шт</span></li>
          </ul>
        </div>
      </div>
      <button>В корзину</button>
    </div>
  </figure>
</div>
<div class="prods-1-arrs"></div>
<div class="h1">Реализованные проекты</div>
<div class="ar-h1">Для вдохновения</div>
<ul class="ul-3">
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <i><img src="/img/232x232.png" alt=""></i>
      <h5>Современный санузел с акцентом на раковину</h5>
    </a>
  </li>
  <li>
    <a href="#">
      <b>Смотреть все</b>
      <i><svg><use xlink:href="#arr_rt"></use></svg></i>
    </a>
  </li>
</ul>

<div class="b2">
  <div class="b2-l">
    <img src="/img/614x457.png" alt="">
  </div>
  <div class="b2-r">
    <div class="h2">Лучший проект - это проект сделанный для вашего дома</div>
    <p>3D проект бесплатно для вашего дома, на основе ваших пожеланий, размеров комнаты и примеров, которые вам нравятся</p>
    <button>Хочу 3D-проект</button>
  </div>
</div>

<div class="b3">
  <div class="b3-l">
    <h2>Поле Керамика</h2>
    <ul class="ul-4">
      <li>Склад в Чите</li>
      <li>Доставка по Забайкальскому краю</li>
      <li>Беспроцентная рассрочка</li>
    </ul>
  </div>
  <div class="b3-r">
    <img src="/img/video.png" alt="">
  </div>
</div>

<h3 class="h1">Адреса магазинов</h3>

<ul class="ul-5">
  <li>
    <b>Вокзальная ул., 3А</b>
    <span>+7 (924) 818-00-36</span>
  </li>
  <li>
    <b>ул. Курнатовского, 25</b>
    <span>+7 (3022) 50-09-01</span>
  </li>
  <li>
    <b>Объездное ш., 24А, стр. 3</b>
    <span>+7 (924) 818-00-59</span>
  </li>
  <li>
    <b>Вокзальная ул., 3А</b>
    <span>+7 (924) 818-00-36</span>
  </li>
</ul>

<div class="map"><img src="/img/ya_karta.jpg" alt=""></div>

<h4 class="h1">Читайте отзывы о нас!</h4>
<!--тут отзывы-->

<?= $this->renderFile('@app/components/_offer.php', []) ?>