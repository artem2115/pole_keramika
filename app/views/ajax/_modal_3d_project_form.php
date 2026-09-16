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
      </div>
      <div class="modal-body">
        <?php $form = ActiveForm::begin([
          'id' => 'form-3d-project',
          'action' => '/ajax/send-3d-project/',
          'enableClientValidation' => true,
          'options' => ['class' => 'form-3d'],
        ]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ваше имя'])->label(false) ?>
        <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::class, ['mask' => '+7 (999) 999 99 99', 'options' => ['id' => 'phone_app', 'placeholder' => $model->getAttributeLabel('phone'), 'maxlength' => 20]])->label(false) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'example@mail.ru'])->label(false) ?>
        <?= Html::submitButton('Отправить', ['class' => 'btn1-b1']) ?>
        <p class="form-agreement">
          Нажимая на кнопку «Отправить», вы даёте <a href="#">согласие на обработку</a> своих персональных данных
        </p>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>