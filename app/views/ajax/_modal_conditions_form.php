<?php
// app/views/ajax/_modal_conditions_form.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

$modalClass = !empty($modal_class) ? $modal_class : 'm1';
?>
<div class="modalWrap">
  <div class="modal <?= $modalClass ?> fade" tabindex="-1" role="dialog">
    <div class="close"></div>
    <div class="mBody">
      <div class="modal-header">
        <h3>Партнёрские условия</h3>
        <p>Обновлённые условия, помощь и выгода для строителей и дизайнеров интерьера</p>
      </div>
      <div class="modal-body">
        <?php $form = ActiveForm::begin([
          'id' => 'form-conditions',
          'action' => '/ajax/send-conditions/',
          'enableClientValidation' => true,
          'options' => ['class' => 'f1'],
          'fieldConfig' => [
            'template' => "{label}\n{input}\n{hint}",
          ],
        ]); ?>
        <?= $form->field($model, 'name')->textInput([
          'maxlength' => true,
          'placeholder' => 'Ваше имя',
        ]) ?>
        <?= $form->field($model, 'phone')->widget(MaskedInput::class, [
          'mask' => '+7 (999) 999 99 99',
          'options' => [
            'id' => 'phone_conditions',
            'placeholder' => '+7 (___) ___ __ __',
            'maxlength' => 20,
          ],
        ]) ?>

        <?= $form->field($model, 'email')->textInput([
          'maxlength' => true,
          'placeholder' => 'example@mail.ru',
        ]) ?>
        <p class="form-agreement">
          <label class="w_check">
            <input class="ch_agree" type="checkbox"><var></var>Я согласен с <a href="<?= Url::to(['/agree']) ?>">политикой обработки персональных данных</a>
          </label>
        </p>
        <?= Html::submitButton('Получить условия', ['class' => 'btn1-b1', 'disabled' => true]) ?>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>