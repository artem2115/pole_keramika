<?php

use yii\easyii\modules\shopcart\api\Shopcart;
use yii\easyii\modules\subscribe\api\Subscribe;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\widgets\Menu;
$isHome = Yii::$app->controller->id === Yii::$app->defaultRoute && Yii::$app->controller->action->id === Yii::$app->controller->defaultAction;
?>
<?php $this->beginContent('@app/views/layouts/base.php', ['isHome' => $isHome]); ?>
<main>
<header>
  <div class="c">
    <div class="ht">
      <div class="logo"><a href="/"><img src="/i/logo.webp" alt="Поле Керамика"></a></div>
      <div class="htc">
        <nav>
          <ul>
            <li><a href="#">Каталог</a></li>
            <li><a href="#">Акции</a></li>
            <li><a href="/completed/">Реализованные проекты</a></li>
            <li><a href="/partneram/">Партнерам</a></li>
            <li><a href="/about/">О нас</a>
              <ul>
                <li><a href="/contacts/">Контакты</a></li>
                <li><a href="#">Пункт 2</a></li>
                <li><a href="#">Пункт 3</a></li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
      <div class="htr">
        <div class="ph1">
          <a href="tel:+79248180034">
            <svg><use xlink:href="#m-ph"></use></svg>
            <span>+7 924 818-00-34</span></a>
        </div>
        <div class="cart1">
          <a href="#"><span>Корзина</span> <svg><use xlink:href="#cart"></use></svg></a>
        </div>
        <button data-modal="order_call">заказать звонок</button>
      </div>
    </div>
    <div class="hb">
      <ul>
        <li><a href="#">Керамическая плитка</a></li>
        <li><a href="#">Керамогранит</a></li>
        <li><a href="#">Ламинат</a></li>
        <li><a href="#">Клеи, затирки, смеси и др.</a></li>
        <li><a href="#">Бесплатный дизайн проект</a></li>
      </ul>
    </div>
  </div>
</header>
  <div class="c">
<?= $content ?>
  </div>
</main>
<footer>
  <div class="c">
    <div class="ft">
      <div class="ft-1">
        <div class="f-logo"><img src="/i/logo.webp" alt="Поле Керамика"></div>
        <div class="f-tel"><a href="tel:+79248180034">+7 924 818-00-34</a></div>
        <button data-modal="order_call">Заказать звонок</button>
        <div class="f-email"><a href="mailto:info@mail.ru">info@mail.ru</a></div>
        <mark>г. Чита</mark>
      </div>
      <div class="ft-2">
        <h6>Каталог</h6>
        <ul>
          <li><a href="#">Керамическая плитка</a></li>
          <li><a href="#">Керамогранит</a></li>
          <li><a href="#">Ламинат</a></li>
          <li><a href="#">Смеси, затирки, клей и др.</a></li>
        </ul>
      </div>
      <div class="ft-3">
        <ul>
          <li><a href="#">Наши магазины</a></li>
          <li><a href="#">О компании</a></li>
          <li><a href="#">Новости и блог</a></li>
          <li><a href="#">Акции</a></li>
          <li><a href="#">Реализованные проекты</a></li>
          <li><a href="#">Партнерам</a></li>
        </ul>
      </div>
      <div class="ft-4">
        <div class="b5">
          <strong>Бесплатный дизайн-проект</strong>
          <button>Оставить заявку</button>
        </div>
        <ul class="soc">
          <li><a href="#"><svg><use xlink:href="#tg"></use></svg></a></li>
          <li><a href="#"><svg><use xlink:href="#max"></use></svg></a></li>
          <li><a href="#"><svg><use xlink:href="#vk"></use></svg></a></li>
          <li><a href="#"><svg><use xlink:href="#wapp"></use></svg></a></li>
        </ul>
      </div>
    </div>
    <div class="fb">
      <div class="fb-l">
        <mark><?= date('Y') ?> &copy; ООО “Поле Керамика”</mark>
        <a href="#">Политика конфиденциальности</a>
      </div>
      <div class="fb-r">
        <a href="#"><img src="/i/bigart.webp" alt="Создание сайта - Bigart"></a>
      </div>
    </div>
  </div>
</footer>
<?php $this->endContent(); ?>
