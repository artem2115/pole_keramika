<?php
//vendor/noumo/easyii/modules/catalog/views/items/_form.php
use yii\easyii\helpers\Image;
use yii\easyii\widgets\DateTimePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\easyii\widgets\Redactor;
use yii\easyii\widgets\SeoForm;

$settings = $this->context->module->settings;
$module = $this->context->module->id;
$collections = \yii\easyii\modules\collections\models\Collection::find()
  ->where(['category_id' => $model->category_id])
  ->orderBy(['title' => SORT_ASC])
  ->all();
$collectionsList = \yii\helpers\ArrayHelper::map($collections, 'collection_id', 'title');
?>

<?php $form = ActiveForm::begin([
  'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form']
]); ?>
<?= $form->field($model, 'collection_id')->dropDownList($collectionsList, ['prompt' => '— без коллекции —']) ?>
<?= $form->field($model, 'title') ?>
<?php if($settings['itemThumb']) : ?>
  <?php if($model->image) : ?>
    <img src="<?= Image::thumb($model->image, 240) ?>">
    <a href="<?= Url::to(['/admin/' . $module . '/items/clear-image', 'id' => $model->primaryKey]) ?>" class="text-danger confirm-delete" title="<?= Yii::t('easyii', 'Clear image') ?>"><?= Yii::t('easyii', 'Clear image') ?></a>
  <?php endif; ?>
  <?= $form->field($model, 'image')->fileInput() ?>
<?php endif; ?>
<?= $dataForm ?>
<?php if($settings['itemDescription']) : ?>
  <?= $form->field($model, 'description')->widget(Redactor::class, [
    'options' => [
      'minHeight' => 400,
      'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'catalog'], true),
      'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'catalog'], true),
      'plugins' => ['fullscreen']
    ]
  ]) ?>
<?php endif; ?>

<?= $form->field($model, 'available') ?>
<?= $form->field($model, 'price') ?>
<?= $form->field($model, 'old_price') ?>
<?= $form->field($model, 'discount') ?>

<?= $form->field($model, 'time')->widget(DateTimePicker::class); ?>

<?php if(IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>