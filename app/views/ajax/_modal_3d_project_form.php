<?php
// app/views/ajax/_modal_3d_project_form.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$modalClass = !empty($modal_class) ? $modal_class : 'm1';
?>
<div class="modalWrap">
  <div class="modal <?= $modalClass ?> fade" tabindex="-1" role="dialog">
    <div class="close"></div>
    <div class="mBody">
      <div class="modal-header">
        <h3>БЕСПЛАТНЫЙ 3D проект</h3>
        <p>Оставьте контакты — пришлём готовую визуализацию и расчёт стоимости в течение дня</p>
      </div>
      <div class="modal-body">
        <?php $form = ActiveForm::begin([
          'id' => 'form-3d-project',
          'action' => '/ajax/send-3d-project/',
          'enableClientValidation' => true,
          'options' => ['class' => 'f1'],
          'fieldConfig' => [
            'template' => "{label}\n{input}\n{hint}",
          ],
        ]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ваше имя']) ?>
        <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::class, ['mask' => '+7 (999) 999 99 99', 'options' => ['id' => 'phone_3d', 'placeholder' => '+7 (___) ___ __ __', 'maxlength' => 20]]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'example@mail.ru']) ?>
        <p class="form-agreement"><label class="w_check"><input class="ch_agree" type="checkbox"><var></var> Я согласен с <a href="<?= \yii\helpers\Url::to(['/agree'])?>">политикой обработки персональных данных</a></label></p>
        <?= Html::submitButton('Отправить', ['class' => 'btn1-b1', 'disabled' => true]) ?>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>