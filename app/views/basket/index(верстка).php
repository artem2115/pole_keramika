<?php
//app/views/basket/index.php
?>
<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li>Корзина</li>
  </ul>
</div>
<!--В корзине пока пусто-->
<div class="bmes">
  <div class="bm-i1 bm-i1-1"><svg><use xlink:href="#cart"></use></svg></div>
  <h1>В корзине пока пусто</h1>
  <p>Добавьте товары из каталога, чтобы отправить заявку менеджеру.</p>
  <div class="bm-btn"><a class="btn1-b1" href="#">Перейти в каталог</a></div>
</div>
<!--/В корзине пока пусто-->
<!--Заявка отправлена-->
<div class="bmes">
  <div class="bm-i1 bm-i1-2"><svg><use xlink:href="#ok2"></use></svg></div>
  <h1>Заявка отправлена</h1>
  <p>Менеджер свяжется с вами по указанному телефону и уточнит детали заявки.</p>
  <p><small>Обсудим наличие, итоговую стоимость и получение товаров.</small></p>
  <div class="bm-btn"><a class="btn1-b1" href="#">Перейти в каталог</a></div>
</div>
<!--/Заявка отправлена-->
<!--01 · Корзина / доставка-->
<h1>Корзина</h1>
<p>2 товара · заявка менеджеру</p>
<div class="b14">
  <div class="b14-l">

    <div class="bt-unit">
      <div class="bt-u-1">
        <div class="bt-u-1-l"><img src="/img/prod-1.png" width="100" alt=""></div><!--размеры изображения 100х112-->
        <div class="bt-u-1-r">
          <h4><a href="#">Настенная плитка Unitile Муза беж</a></h4>
          <div class="bt-u-desc">250 × 400 мм · бежевый</div>
          <div class="bt-u-sale">2 500 ₽/м²</div>
        </div>
      </div>
      <div class="bt-u-2">
        <div class="bt-qty">
          <button type="button">−</button>
          <input type="text" value="12,5"/>
          <span class="meas">м²</span>
          <button type="button">+</button>
        </div>

        <div class="bt-sale">31 250 ₽</div>
      </div>

      <div class="bt-u-3">
        <button type="button"><svg><use xlink:href="#trash"></use></svg><span>Удалить</span></button>
      </div>
    </div>

    <!-- ===================== Контакты для заявки ===================== -->
    <section class="checkout-block">
      <h3 class="h3">Контакты для заявки</h3>
      <p class="checkout-sub">Укажите имя и телефон — менеджер свяжется с вами.</p>

      <form class="checkout" action="#" method="post" novalidate>
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

    <!-- ===================== Получение товаров ===================== -->
    <section class="checkout-block">
      <h3 class="h3">Получение товаров</h3>
      <p class="checkout-sub">Можно выбрать сейчас или уточнить с менеджером.</p>

      <div class="checkout-delivery">
        <button type="button" class="active" data-delivery="delivery">Доставка</button>
        <button type="button" data-delivery="pickup">Самовывоз</button>
      </div>

      <div class="checkout-delivery-fields">
        <!-- Доставка -->
        <div class="f1 active" data-delivery-field="delivery">
          <label class="control-label" for="co-address">Адрес доставки · необязательно</label>
          <input type="text" id="co-address" name="address" placeholder="Город, улица, дом, квартира">
        </div>

        <!-- Самовывоз -->
        <div class="f1" data-delivery-field="pickup">
          <label class="control-label" for="co-pickup">Магазин · необязательно</label>
          <div class="f1-sel">
            <select id="co-pickup" name="pickup">
              <option value="" disabled selected hidden>Вокзальная ул., 3А</option>
              <option value="vokzalnaya">Вокзальная ул., 3А</option>
              <option value="lenina">ул. Ленина, 15</option>
              <option value="mira">пр. Мира, 42</option>
              <option value="sovetskaya">ул. Советская, 8</option>
            </select>
          </div>
        </div>
      </div>
    </section>

  </div>
  <div class="b14-r">
    <aside class="order">
      <h3 class="h3">Ваша заявка</h3>

      <!-- Сводка -->
      <div class="order-summary">
        <div class="order-row">
          <span>Товары · <b>2</b> позиции</span>
          <span>32 362,84 ₽</span>
        </div>

        <!-- строка скидки — показывается только при применённом промокоде -->
        <div class="order-row order-row-discount">
          <span>Скидка по промокоду</span>
          <span>−3 236,28 ₽</span>
        </div>

        <hr class="order-hr">

        <div class="order-total">
          <span class="order-total-l">Итого за товары</span>
          <div class="order-total-v">32 362,84 ₽</div>
          <p class="order-note">Стоимость доставки уточнит менеджер.</p>
        </div>
      </div>

      <!-- Промокод -->
      <div class="order-promo">
        <label class="control-label" for="promo">Промокод</label>
        <div class="order-promo-row">
          <input type="text" id="promo" name="promo" placeholder="Введите код">
          <button type="button" class="order-promo-btn">Применить</button>
        </div>
        <p class="order-promo-msg order-promo-msg-error">Промокод не найден</p>
        <p class="order-promo-msg order-promo-msg-ok">Промокод CERAM10 применён. Скидка 10%.</p>
      </div>

      <!-- Плашка ошибки отправки -->
      <div class="order-alert">
        Не удалось отправить заявку. Проверьте соединение и попробуйте ещё раз.
      </div>

      <!-- Согласие -->
      <div class="order-agree">
        <label class="w_check">
          <input type="checkbox" name="agree">
          <span>Я согласен с обработкой персональных данных и <a href="#">политикой конфиденциальности</a></span>
        </label>
        <p class="order-agree-msg">Подтвердите согласие на обработку данных</p>
      </div>

      <!-- Кнопка отправки -->
      <button type="submit" class="order-submit">Отправить заявку</button>

      <p class="order-foot">Менеджер уточнит наличие товаров, итоговую стоимость и условия получения.</p>
    </aside>
  </div>
</div>
<!--/01 · Корзина / доставка-->
<!--
Состояния (что навешивать JS-ом)
Состояние	Класс на .order
Промокод применён	promo-applied
Промокод не найден	promo-error
Ошибка отправки	submit-error
Идёт отправка	submitting
Не отмечен чекбокс	agree-error
Например: <aside class="order promo-applied">.

Что где по состояниям
promo-applied: показываем .order-row-discount (зелёная строка скидки) + .order-promo-msg-ok (зелёная плашка). Кнопка «Убрать» — по тексту в HTML. JS просто меняет текст кнопки и вешает класс.

promo-error: красная рамка у input + показ .order-promo-msg-error.

submit-error: показ .order-alert + текст кнопки «Повторить отправку».

agree-error: показ .order-agree-msg, красный чекбокс (у тебя input[type=checkbox] + var — просто перекрашиваем через класс).

submitting: кнопка приглушена, pointer-events:none, текст «Отправляем…».
-->

