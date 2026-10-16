<?php
use yii\easyii\modules\promo\models\Promo;
use yii\easyii\widgets\DateTimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$module = $this->context->module->id;
?>

<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['class' => 'model-form']
]); ?>
  <div class="checkbox">
    <label>
      <?= Html::activeCheckbox($model, 'status', ['uncheck' => 0]) ?>
    </label>
  </div>
<?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'CERAM10']) ?>

  <div style="display: flex; gap: 16px;">
    <div style="width:50%;">
      <?= $form->field($model, 'type')->dropDownList(Promo::getTypes()) ?>
    </div>
    <div style="width:50%;">
      <?= $form->field($model, 'value')->textInput(['type' => 'number', 'step' => '0.01', 'min' => 0]) ?>
    </div>
  </div>

<?= $form->field($model, 'min_sum')->textInput(['type' => 'number', 'step' => '0.01', 'min' => 0, 'placeholder' => 'Не ограничено']) ?>

<?php
$dateFromEnabled = (bool)$model->date_from_enabled;
$dateToEnabled = (bool)$model->date_to_enabled;
?>

  <div class="form-group">
    <label class="control-label">Срок действия</label>

    <div style="margin-bottom: 10px;width:210px">
      <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
        <?= Html::activeCheckbox($model, 'date_from_enabled', [
          'uncheck' => 0,
          'label' => false,
          'id' => 'promo-date_from_enabled'
        ]) ?>
        <span>Действует с:</span>
      </label>
      <div class="date-from-wrapper" style="margin-top: 5px; <?= !$dateFromEnabled ? 'display: none;' : '' ?>">
        <?= $form->field($model, 'date_from')->widget(DateTimePicker::class)->label(false) ?>
      </div>
    </div>

    <div style="margin-bottom: 10px;width:210px">
      <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
        <?= Html::activeCheckbox($model, 'date_to_enabled', [
          'uncheck' => 0,
          'label' => false,
          'id' => 'promo-date_to_enabled'
        ]) ?>
        <span>Действует до:</span>
      </label>
      <div class="date-to-wrapper" style="margin-top: 5px; <?= !$dateToEnabled ? 'display: none;' : '' ?>">
        <?= $form->field($model, 'date_to')->widget(DateTimePicker::class)->label(false) ?>
      </div>
    </div>

    <p class="help-block">Если ни одна дата не выбрана — промокод бессрочный.</p>
  </div>

<?php
$this->registerJs('
  $("#promo-date_from_enabled").on("change", function() {
    $(".date-from-wrapper").toggle(this.checked);
  });
  $("#promo-date_to_enabled").on("change", function() {
    $(".date-to-wrapper").toggle(this.checked);
  });
');
?>

<?= $form->field($model, 'usage_limit')->textInput(['type' => 'number', 'min' => 1, 'placeholder' => 'Без лимита']) ?>

<?php if (!$model->isNewRecord): ?>
  <?= $form->field($model, 'usage_count')->textInput(['disabled' => true]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>