<?php
/**
 * @var yii\web\View $this
 */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Корзина';
?>

<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li>Корзина</li>
  </ul>
</div>

<!-- Плашка уведомлений -->
<div id="basket-notifications"></div>

<!-- Состояние 1: пусто -->
<div class="bmes" id="basket-empty" style="display:none">
  <div class="bm-i1 bm-i1-1"><svg><use xlink:href="#cart"></use></svg></div>
  <h1>В корзине пока пусто</h1>
  <p>Добавьте товары из каталога, чтобы отправить заявку менеджеру.</p>
  <div class="bm-btn"><a class="btn1-b1" href="<?= Url::to(['/catalog']) ?>">Перейти в каталог</a></div>
</div>

<!-- Состояние 2: успех -->
<div class="bmes" id="basket-success" style="display:none">
  <div class="bm-i1 bm-i1-2"><svg><use xlink:href="#ok2"></use></svg></div>
  <h1>Заявка отправлена</h1>
  <p>Менеджер свяжется с вами по указанному телефону и уточнит детали заявки.</p>
  <p><small>Обсудим наличие, итоговую стоимость и получение товаров.</small></p>
  <div class="bm-btn"><a class="btn1-b1" href="<?= Url::to(['/catalog']) ?>">Перейти в каталог</a></div>
</div>

<!-- Состояние 3: корзина с товарами -->
<div id="basket-full" style="display:none">
  <h1>Корзина</h1>
  <p><span id="basket-count-text">0 товаров</span> · заявка менеджеру</p>

  <div class="b14">
    <div class="b14-l">
      <div id="basket-items-container"></div>

      <!-- Контакты -->
      <section class="checkout-block">
        <h3 class="h3">Контакты для заявки</h3>
        <p class="checkout-sub">Укажите имя и телефон — менеджер свяжется с вами.</p>

        <form class="checkout" id="basket-form" action="#" method="post" novalidate>
          <div class="checkout-row">
            <div class="f1">
              <label class="control-label" for="co-name">Ваше имя <span class="req">*</span></label>
              <input type="text" id="co-name" name="name" placeholder="Как к вам обращаться">
            </div>
            <div class="f1">
              <label class="control-label" for="co-phone">Телефон <span class="req">*</span></label>
              <input type="tel" id="co-phone" name="phone" placeholder="+7 (___) ___-__-__">
            </div>
          </div>

          <div class="f1">
            <label class="control-label" for="co-email">Email · необязательно</label>
            <input type="email" id="co-email" name="email" placeholder="example@mail.ru">
          </div>

          <div class="f1">
            <label class="control-label" for="co-comment">Комментарий · необязательно</label>
            <textarea id="co-comment" name="comment" rows="4" placeholder="Пожелания по товарам или получению"></textarea>
          </div>
        </form>
      </section>

      <!-- Получение -->
      <section class="checkout-block">
        <h3 class="h3">Получение товаров</h3>
        <p class="checkout-sub">Можно выбрать сейчас или уточнить с менеджером.</p>

        <div class="checkout-delivery" id="basket-delivery-switch">
          <button type="button" class="active" data-delivery="delivery">Доставка</button>
          <button type="button" data-delivery="pickup">Самовывоз</button>
        </div>

        <div class="checkout-delivery-fields">
          <div class="f1 active" data-delivery-field="delivery">
            <label class="control-label" for="co-address">Адрес доставки · необязательно</label>
            <input type="text" id="co-address" name="address" placeholder="Город, улица, дом, квартира">
          </div>

          <div class="f1" data-delivery-field="pickup">
            <label class="control-label" for="co-pickup">Магазин · необязательно</label>
            <div class="f1-sel">
              <select id="co-pickup" name="pickup">
                <option value="" disabled selected hidden>Выберите магазин</option>
                <?php foreach ($addresses as $address): ?>
                  <option value="<?= Html::encode($address->title) ?>">
                    <?= Html::encode($address->title) ?><?= $address->address ? ' — ' . Html::encode(strip_tags($address->address)) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div class="b14-r">
      <aside class="order" id="basket-order">
        <h3 class="h3">Ваша заявка</h3>

        <div class="order-summary">
          <div class="order-row">
            <span>Товары · <b id="order-count">0</b> позиции</span>
            <span id="order-subtotal">0 ₽</span>
          </div>
          <hr class="order-hr">
          <div class="order-total">
            <span class="order-total-l">Итого за товары</span>
            <div class="order-total-v" id="order-total">0 ₽</div>
            <p class="order-note">Стоимость доставки уточнит менеджер.</p>
          </div>
        </div>

        <div class="order-promo">
          <label class="control-label" for="promo">Промокод</label>
          <div class="order-promo-row">
            <input type="text" id="promo" name="promo" placeholder="Введите код">
            <button type="button" class="order-promo-btn">Применить</button>
          </div>
          <p class="order-promo-msg order-promo-msg-error"></p>
          <p class="order-promo-msg order-promo-msg-ok"></p>
        </div>

        <div class="order-row order-row-discount" style="display:none">
          <span>Скидка по промокоду</span>
          <span>−0 ₽</span>
        </div>

        <div class="order-agree">
          <label class="w_check">
            <input type="checkbox" name="agree" id="basket-agree">
            <span>Я согласен с обработкой персональных данных и <a href="#">политикой конфиденциальности</a></span>
          </label>
          <p class="order-agree-msg" id="agree-msg">Подтвердите согласие на обработку данных</p>
        </div>

        <button type="button" class="order-submit" id="basket-submit">Отправить заявку</button>

        <p class="order-foot">Менеджер уточнит наличие товаров, итоговую стоимость и условия получения.</p>
      </aside>
    </div>
  </div>
</div>