<?php
// vendor/noumo/easyii/modules/completed/views/a/_menu.php

use yii\helpers\Url;

$action = $this->context->action->id;
$module = $this->context->module->id;
?>
<ul class="nav nav-pills">
  <li <?= ($action === 'index') ? 'class="active"' : '' ?>>
    <a href="<?= $this->context->getReturnUrl(['/admin/' . $module]) ?>">
      <?php if($action == 'edit' || $action == 'photos') : ?>
        <i class="glyphicon glyphicon-chevron-left font-12"></i>
      <?php endif; ?>
      <?= Yii::t('easyii', 'List') ?>
    </a>
  </li>
  <li <?= ($action === 'create') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/' . $module . '/a/create']) ?>"><?= Yii::t('easyii', 'Create') ?></a>
  </li>
  <li>
    <a href="<?= Url::to(['/admin/' . $module . '/tags']) ?>">
      <i class="glyphicon glyphicon-tags"></i> Теги
    </a>
  </li>
</ul>
<br/>