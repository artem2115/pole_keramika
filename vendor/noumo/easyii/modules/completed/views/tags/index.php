<?php
// vendor/noumo/easyii/modules/completed/views/tags/index.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Теги проектов';
$module = 'completed';
?>

<?= $this->render('_menu') ?>

<?php if ($data->count > 0) : ?>
  <table class="table table-hover">
    <thead>
    <tr>
      <th width="50">#</th>
      <th>Название</th>
      <th width="100">Slug</th>
      <th width="80"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data->models as $item) : ?>
      <tr>
        <td><?= $item->primaryKey ?></td>
        <td>
          <a href="<?= Url::to(['/admin/' . $module . '/tags/edit', 'id' => $item->primaryKey]) ?>">
            <?= $item->name ?>
          </a>
        </td>
        <td><?= $item->slug ?></td>
        <td>
          <a href="<?= Url::to(['/admin/' . $module . '/tags/delete', 'id' => $item->primaryKey]) ?>"
             class="glyphicon glyphicon-remove confirm-delete"
             title="Удалить тег"></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?= yii\widgets\LinkPager::widget(['pagination' => $data->pagination]) ?>
<?php else : ?>
  <p>Теги не найдены</p>
<?php endif; ?>