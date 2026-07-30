<?php
// vendor/noumo/easyii/modules/addresses/views/a/_form.php

use yii\easyii\widgets\DateTimePicker;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$module = $this->context->module->id;

$this->registerJs('
    // Телефоны
    var phoneIndex = ' . count($model->phoneNumbers ?: [0]) . ';
    if (phoneIndex === 0) phoneIndex = 1;
    
    $("#add-phone").click(function() {
        var phoneHtml = \'<div class="input-group phone-item" style="margin-top:5px;">\' +
            \'<input type="text" name="Addresses[phoneNumbers][]" class="form-control" placeholder="+7 123 456 78 90">\' +
            \'<span class="input-group-btn">\' +
                \'<button class="btn btn-danger remove-phone" type="button"><i class="glyphicon glyphicon-remove"></i></button>\' +
            \'</span>\' +
        \'</div>\';
        $("#phones-container").append(phoneHtml);
        return false;
    });
    
    $(document).on("click", ".remove-phone", function() {
        $(this).closest(".phone-item").remove();
        return false;
    });
    
    // Email
    var emailIndex = ' . count($model->emailAddresses ?: [0]) . ';
    if (emailIndex === 0) emailIndex = 1;
    
    $("#add-email").click(function() {
        var emailHtml = \'<div class="input-group email-item" style="margin-top:5px;">\' +
            \'<input type="text" name="Addresses[emailAddresses][]" class="form-control" placeholder="info@mail.ru">\' +
            \'<span class="input-group-btn">\' +
                \'<button class="btn btn-danger remove-email" type="button"><i class="glyphicon glyphicon-remove"></i></button>\' +
            \'</span>\' +
        \'</div>\';
        $("#emails-container").append(emailHtml);
        return false;
    });
    
    $(document).on("click", ".remove-email", function() {
        $(this).closest(".email-item").remove();
        return false;
    });
', \yii\web\View::POS_READY);
?>

<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>

<?= $form->field($model, 'title') ?>

<?= $form->field($model, 'address')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 100,
    'buttons' => ['html', 'unorderedlist', 'br','italic', 'bold'],
    'plugins' => []
  ]
]) ?>

  <div class="form-group">
    <label class="control-label">Телефоны</label>
    <div id="phones-container">
      <?php if (!empty($model->phoneNumbers)): ?>
        <?php foreach ($model->phoneNumbers as $phone): ?>
          <div class="input-group phone-item" style="margin-top:5px;">
            <input type="text" name="Addresses[phoneNumbers][]" class="form-control" value="<?= $phone ?>" placeholder="+7 123 456 78 90">
            <span class="input-group-btn">
                        <button class="btn btn-danger remove-phone" type="button"><i class="glyphicon glyphicon-remove"></i></button>
                    </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="input-group phone-item" style="margin-top:5px;">
          <input type="text" name="Addresses[phoneNumbers][]" class="form-control" placeholder="+7 123 456 78 90">
          <span class="input-group-btn">
                    <button class="btn btn-danger remove-phone" type="button"><i class="glyphicon glyphicon-remove"></i></button>
                </span>
        </div>
      <?php endif; ?>
    </div>
    <button id="add-phone" class="btn btn-success btn-sm" style="margin-top:5px;">
      <i class="glyphicon glyphicon-plus"></i> Добавить телефон
    </button>
    <div class="help-block">Вводите номер в формате: +7 123 456 78 90</div>
  </div>

  <div class="form-group">
    <label class="control-label">Email адреса</label>
    <div id="emails-container">
      <?php if (!empty($model->emailAddresses)): ?>
        <?php foreach ($model->emailAddresses as $email): ?>
          <div class="input-group email-item" style="margin-top:5px;">
            <input type="text" name="Addresses[emailAddresses][]" class="form-control" value="<?= $email ?>" placeholder="info@mail.ru">
            <span class="input-group-btn">
                        <button class="btn btn-danger remove-email" type="button"><i class="glyphicon glyphicon-remove"></i></button>
                    </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="input-group email-item" style="margin-top:5px;">
          <input type="text" name="Addresses[emailAddresses][]" class="form-control" placeholder="info@mail.ru">
          <span class="input-group-btn">
                    <button class="btn btn-danger remove-email" type="button"><i class="glyphicon glyphicon-remove"></i></button>
                </span>
        </div>
      <?php endif; ?>
    </div>
    <button id="add-email" class="btn btn-success btn-sm" style="margin-top:5px;">
      <i class="glyphicon glyphicon-plus"></i> Добавить email
    </button>
  </div>

<?= $form->field($model, 'mode')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 100,
    'buttons' => ['html', 'unorderedlist', 'br', 'italic', 'bold'],
    'plugins' => []
  ]
]) ?>

<?= $form->field($model, 'coordinates')->textInput([
  'maxlength' => true,
  'placeholder' => '55.753215, 37.622504'
])->hint('Координаты для Яндекс.Карты. Формат: широта, долгота. Например: 55.753215, 37.622504') ?>

<?= $form->field($model, 'text')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 200,
    'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'addresses']),
    'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'addresses']),
    'plugins' => ['fullscreen']
  ]
]) ?>

<?= $form->field($model, 'time')->widget(DateTimePicker::class); ?>

<?php if (IS_ROOT): ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>