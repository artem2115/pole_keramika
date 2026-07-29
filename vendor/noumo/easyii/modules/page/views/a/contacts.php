<?php
// vendor/noumo/easyii/modules/page/views/a/contacts.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Управление контактами: ' . $page->title;

$this->registerJs('
    var contactIndex = ' . count($models) . ';
    
    $("#add-contact").click(function() {
        var template = $(".contact-item").first().clone();
        template.find("input").each(function() {
            var name = $(this).attr("name");
            $(this).attr("name", name.replace(/\[\d+\]/, "["+contactIndex+"]"));
            $(this).val("");
        });
        template.find(".contact-number").text(contactIndex + 1);
        template.show();
        $("#contacts-container").append(template);
        contactIndex++;
        return false;
    });
    
    $(document).on("click", ".remove-contact", function() {
        if ($(".contact-item:visible").length > 1) {
            $(this).closest(".contact-item").remove();
            reindexContacts();
        } else {
            alert("Должен быть хотя бы один контакт");
        }
        return false;
    });
    
    function reindexContacts() {
        $(".contact-item:visible").each(function(index) {
            $(this).find(".contact-number").text(index + 1);
            $(this).find("input").each(function() {
                var name = $(this).attr("name");
                $(this).attr("name", name.replace(/\[\d+\]/, "["+index+"]"));
            });
        });
        contactIndex = $(".contact-item:visible").length;
    }
', \yii\web\View::POS_READY);
?>

  <ul class="nav nav-pills">
    <li>
      <a href="/admin/page/a/edit/<?= $page->page_id ?>/">Назад</a></li>
  </ul>
  <br>

  <h1><?= Html::encode($this->title) ?></h1>

  <div class="alert alert-info">
    Страница: <strong><?= Html::encode($page->title) ?></strong> (slug: <?= Html::encode($page->slug) ?>)
  </div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
  <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('error')): ?>
  <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

  <div id="contacts-container">
    <?php foreach ($models as $index => $model): ?>
      <div class="contact-item" style="border:1px solid #ddd; padding:15px; margin-bottom:15px; background:#f9f9f9; border-radius:4px;">
        <div style="margin-bottom:15px;">
          <strong>Контакт <span class="contact-number"><?= $index + 1 ?></span></strong>
          <button class="btn btn-danger btn-xs pull-right remove-contact" type="button">
            <i class="glyphicon glyphicon-remove"></i> Удалить
          </button>
        </div>

        <div class="row">
          <div class="col-md-3">
            <?= $form->field($model, "[{$index}]icon")->textInput(['placeholder' => 'call2']) ?>
          </div>
          <div class="col-md-1">
            <?= $form->field($model, "[{$index}]width")->textInput(['type' => 'number', 'placeholder' => '20']) ?>
          </div>
          <div class="col-md-1">
            <?= $form->field($model, "[{$index}]height")->textInput(['type' => 'number', 'placeholder' => '20']) ?>
          </div>
          <div class="col-md-7">
            <?= $form->field($model, "[{$index}]link")->textInput(['placeholder' => 'tel:+79248180034']) ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <?= $form->field($model, "[{$index}]title")->textInput(['placeholder' => '+7 924 818-00-34']) ?>
          </div>
          <div class="col-md-4">
            <?= $form->field($model, "[{$index}]subtitle")->textInput(['placeholder' => 'Контакт-центр']) ?>
          </div>
          <div class="col-md-4">
            <?= $form->field($model, "[{$index}]description")->textInput(['placeholder' => 'Ежедневно с 08:00 до 23:00 (МСК)']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="form-group">
    <?= Html::button('<i class="glyphicon glyphicon-plus"></i> Добавить контакт', [
      'id' => 'add-contact',
      'class' => 'btn btn-success'
    ]) ?>

    <?= Html::submitButton('<i class="glyphicon glyphicon-save"></i> Сохранить', [
      'class' => 'btn btn-primary'
    ]) ?>
  </div>

<?php ActiveForm::end(); ?>