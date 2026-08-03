<?php
//vendor/noumo/easyii/modules/completed/views/a/_form.php
use yii\easyii\helpers\Image;
use yii\easyii\modules\completed\models\CompletedTag;
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

  <?php if($model->image) : ?>
    <img src="<?= Image::thumb($model->image, 240) ?>">
    <a href="<?= Url::to(['/admin/' . $module . '/a/clear-image', 'id' => $model->completed_id]) ?>" class="text-danger confirm-delete" title="<?= Yii::t('easyii', 'Clear image') ?>"><?= Yii::t('easyii', 'Clear image') ?></a>
  <?php endif; ?>
  <?= $form->field($model, 'image')->fileInput() ?>

  <?= $form->field($model, 'short')->textarea() ?>

<?= $form->field($model, 'text')->widget(Redactor::class, [
  'options' => [
    'minHeight' => 400,
    'imageUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'completed']),
    'fileUpload' => Url::to(['/admin/redactor/upload', 'dir' => 'completed']),
    'plugins' => ['fullscreen']
  ]
]) ?>
<?php
$allTags = CompletedTag::find()->orderBy(['name' => SORT_ASC])->all();
if (!empty($allTags)): ?>
  <div class="form-group">
    <label class="control-label">Теги</label>
    <div style="display:flex;flex-wrap:wrap;gap:8px;padding:8px 0;">
      <?php foreach ($allTags as $tag): ?>
        <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-weight:normal;">
          <input type="checkbox"
                 name="Completed[selectedTags][]"
                 value="<?= $tag->tag_id ?>"
            <?= in_array($tag->tag_id, $model->selectedTags) ? 'checked' : '' ?>>
          <?= $tag->name ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
<?= $form->field($model, 'time')->widget(DateTimePicker::class); ?>



<?php if(IS_ROOT) : ?>
  <?= $form->field($model, 'slug') ?>
  <?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Html::submitButton(Yii::t('easyii', 'Save'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>