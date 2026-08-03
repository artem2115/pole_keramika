<?php
// vendor/noumo/easyii/modules/completed/views/tags/edit.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Редактировать тег: ' . $model->name;
?>

<?= $this->render('_menu') ?>

<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['class' => 'model-form']
]); ?>

<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'slug') ?>

<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>