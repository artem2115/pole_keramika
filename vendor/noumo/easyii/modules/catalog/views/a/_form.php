<?php
// vendor/noumo/easyii/modules/catalog/views/a/_form.php

use yii\easyii\helpers\Image;
use yii\easyii\widgets\SeoForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$class = $this->context->categoryClass;
$settings = $this->context->module->settings;
?>
<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['enctype' => 'multipart/form-data']
]); ?>
<?= $form->field($model, 'title') ?>

<?php if(!empty($parent)) : ?>
  <div class="form-group field-category-title required">
    <label for="category-parent" class="control-label"><?= Yii::t('easyii', 'Parent category') ?></label>
    <select class="form-control" id="category-parent" name="parent">
      <option value="" class="smooth"><?= Yii::t('easyii', 'No') ?></option>
      <?php foreach($class::find()->sort()->asArray()->all() as $node) : ?>
        <option
          value="<?= $node['category_id'] ?>"
          <?php if($parent == $node['category_id']) echo 'SELECTED' ?>
          style="padding-left: <?= $node['depth'] * 20 ?>px;"
        ><?= $node['title'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>
<?php endif; ?>

<?php if($settings['categoryThumb']) : ?>
  <?php if($model->image) : ?>
    <img src="<?= Image::thumb($model->image, 240) ?>">
    <a href="<?= Url::to(['/admin/' . $this->context->moduleName . '/a/clear-image', 'id' => $model->primaryKey]) ?>" class="text-danger confirm-delete" title="<?= Yii::t('easyii', 'Clear image') ?>"><?= Yii::t('easyii', 'Clear image') ?></a>
  <?php endif; ?>
  <?= $form->field($model, 'image')->fileInput() ?>
<?php endif; ?>

<?php if(IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>

<?php if (!$model->isNewRecord): ?>
  <?php
  $collections = $model->getCollections()
    ->orderBy(['title' => SORT_ASC])
    ->all();
  ?>
  <hr>
  <div class="panel panel-info">
    <div class="panel-heading">
      <strong>Коллекции в этой категории</strong>
    </div>
    <div class="panel-body">
      <?php if (!empty($collections)): ?>
        <ul style="margin-bottom:15px;">
          <?php foreach ($collections as $col): ?>
            <li style="padding:4px 0;">
              <a href="<?= Url::to(['/admin/collections/a/edit', 'id' => $col->collection_id]) ?>">
                <?= Html::encode($col->title) ?>
              </a>
              <?php if ($col->status == 0): ?>
                <span class="label label-default">выключена</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="color:#999;">В этой категории пока нет коллекций</p>
      <?php endif; ?>

      <?= Html::a(
        '<i class="glyphicon glyphicon-plus"></i> Создать коллекцию',
        ['/admin/collections/a/create', 'category_id' => $model->primaryKey],
        ['class' => 'btn btn-success btn-sm']
      ) ?>
    </div>
  </div>
<?php endif; ?>