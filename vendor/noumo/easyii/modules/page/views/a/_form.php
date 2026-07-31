<?php
// vendor/noumo/easyii/modules/page/views/a/_form.php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;

?>

<?php if ($model->slug === 'page-contacts'): ?>
  <ul class="nav nav-pills">
    <li>
      <?= Html::a(
        '<i class="glyphicon glyphicon-earphone"></i> Редактировать контакты',
        ['contacts', 'id' => $model->primaryKey],
      ) ?></li>
  </ul>
  <br>
<?php endif; ?>

<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['class' => 'model-form', 'enctype' => 'multipart/form-data']
]); ?>

<?= $form->field($model, 'title') ?>

<?php if ($model->slug === 'page-partneram'): ?>
  <?= $form->field($model, 'str1')->textInput(['maxlength' => 1024]) ?>
<?php endif; ?>

<?= $form->field($model, 'text')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 400,
    'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'pages']),
    'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'pages']),
    'plugins' => ['fullscreen']
  ]
]) ?>

<?php if ($model->image): ?>
  <div class="form-group">
    <label>Текущее изображение</label>
    <div>
      <img src="<?= $model->getImageUrl() ?>" alt="<?= $model->title ?>" style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
    </div>
  </div>
<?php endif; ?>

<?= $form->field($model, 'image')->fileInput(['accept' => 'image/*']) ?>

<?php if(IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>