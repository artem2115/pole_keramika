<?php
// vendor/noumo/easyii/modules/completed/views/tags/create.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Создать тег';
?>

<?= $this->render('_menu') ?>

<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['class' => 'model-form']
]); ?>

<?= $form->field($model, 'name') ?>

<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>