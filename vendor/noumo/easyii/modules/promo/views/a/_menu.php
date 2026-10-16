<?php
use yii\helpers\Url;

$action = $this->context->action->id;
?>
<ul class="nav nav-pills">
  <li <?= ($action === 'index') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/promo']) ?>">
      <?php if ($action === 'edit') : ?>
        <i class="glyphicon glyphicon-chevron-left font-12"></i>
      <?php endif; ?>
      Список
    </a>
  </li>
  <li <?= ($action === 'create') ? 'class="active"' : '' ?>>
    <a href="<?= Url::to(['/admin/promo/a/create']) ?>">Создать</a>
  </li>
</ul>
<br/>