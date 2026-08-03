<?php
//vendor/noumo/easyii/modules/completed/views/a/_form.php
use yii\easyii\helpers\Image;
use yii\easyii\widgets\DateTimePicker;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;
use yii\easyii\widgets\TagsInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$module = $this->context->module->id;
?>
<?php $form = ActiveForm::begin([
  'enableAjaxValidation' => true,
  'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>
<?= $form->field($model, 'title') ?>
<?php if($this->context->module->settings['enableThumb']) : ?>
  <?php if($model->image) : ?>
    <img src="<?= Image::thumb($model->image, 240) ?>">
    <a href="<?= Url::to(['/admin/' . $module . '/a/clear-image', 'id' => $model->completed_id]) ?>" class="text-danger confirm-delete" title="<?= Yii::t('easyii', 'Clear image') ?>"><?= Yii::t('easyii', 'Clear image') ?></a>
  <?php endif; ?>
  <?= $form->field($model, 'image')->fileInput() ?>
<?php endif; ?>
<?php if($this->context->module->settings['enableShort']) : ?>
  <?= $form->field($model, 'short')->textarea() ?>
<?php endif; ?>
<?= $form->field($model, 'text')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 400,
    'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'completed']),
    'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'completed']),
    'plugins' => ['fullscreen']
  ]
]) ?>

<?= $form->field($model, 'time')->widget(DateTimePicker::class); ?>

<?php if(IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
