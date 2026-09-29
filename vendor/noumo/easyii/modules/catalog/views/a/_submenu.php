<?php
use yii\helpers\Url;

$action = $this->context->action->id;
$module = $this->context->module->id;
?>
<ul class="nav nav-tabs">
  <li <?= ($action === 'edit') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/' . $module . '/a/edit', 'id' => $model->primaryKey]) ?>">
      <?= Yii::t('easyii/catalog', 'Edit category') ?>
    </a>
  </li>
  <?php if (IS_ROOT) : ?>
    <li <?= ($action === 'fields') ? 'class="active"' : '' ?>>
      <a href="<?= Url::to(['/admin/' . $module . '/a/fields', 'id' => $model->primaryKey]) ?>">
        <?= Yii::t('easyii/catalog', 'Fields') ?>
      </a>
    </li>
  <?php endif; ?>
  <?php if ($this->context->module->settings['itemsInFolder']) : ?>
    <li <?= ($action === 'index') ? 'class="active"' : '' ?>>
      <a href="<?= Url::to(['/admin/' . $module . '/items/index', 'id' => $model->primaryKey]) ?>">
        <span class="glyphicon glyphicon-list"></span> <?= Yii::t('easyii', 'Items') ?>
      </a>
    </li>
  <?php endif; ?>
</ul>
<br>