<?php
// vendor/noumo/easyii/modules/collections/views/a/_form.php

use yii\easyii\helpers\Image;
use yii\easyii\modules\catalog\models\Category;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;
use yii\easyii\widgets\TagsInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$module = $this->context->module->id;

// Список категорий
$categories = Category::find()->select(['category_id', 'title'])->orderBy(['title' => SORT_ASC])->all();
$categoriesList = \yii\helpers\ArrayHelper::map($categories, 'category_id', 'title');
?>
<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>

<?= $form->field($model, 'title') ?>

<?= $form->field($model, 'category_id')->dropDownList($categoriesList, ['prompt' => '— выберите категорию —']) ?>

<?php if ($this->context->module->settings['enableThumb']) : ?>
  <?php if ($model->image) : ?>
    <img src="<?= Image::thumb($model->image, 240) ?>">
    <a href="<?= Url::to(['/admin/' . $module . '/a/clear-image', 'id' => $model->collection_id]) ?>"
       class="text-danger confirm-delete"
       title="<?= Yii::t('easyii', 'Clear image') ?>">
      <?= Yii::t('easyii', 'Clear image') ?>
    </a>
  <?php endif; ?>
  <?= $form->field($model, 'image')->fileInput() ?>
<?php endif; ?>

<?= $form->field($model, 'description')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 300,
    'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'collections']),
    'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'collections']),
    'plugins' => ['fullscreen']
  ]
]) ?>

<?= $form->field($model, 'formats')->widget(TagsInput::class, [
  'options' => ['placeholder' => 'Введите формат и нажмите Enter (например: 25x40)']
])->hint('Введите форматы плитки, каждый через Enter') ?>

<?php if (IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>