<?php
// vendor/noumo/easyii/modules/completed/views/tags/_menu.php

use yii\helpers\Url;

$action = $this->context->action->id;
?>
<ul class="nav nav-pills">
  <li <?= ($action === 'index') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/completed/tags']) ?>">
      <?php if ($action === 'edit') : ?>
        <i class="glyphicon glyphicon-chevron-left font-12"></i>
      <?php endif; ?>
      Список тегов
    </a>
  </li>
  <li <?= ($action === 'create') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/completed/tags/create']) ?>">Создать тег</a>
  </li>
</ul>
<br/>