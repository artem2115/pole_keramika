<?php
use yii\easyii\modules\promo\models\Promo;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Промокоды';
$module = $this->context->module->id;
?>

<?= $this->render('_menu') ?>

<?php if ($data->count > 0): ?>
  <table class="table table-hover">
    <thead>
    <tr>
      <th width="50">#</th>
      <th>Код</th>
      <th>Скидка</th>
      <th width="120">Мин. сумма</th>
      <th width="180">Срок</th>
      <th width="100">Использований</th>
      <th width="80">Статус</th>
      <th width="80"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data->models as $item): ?>
      <tr data-id="<?= $item->primaryKey ?>">
        <td><?= $item->primaryKey ?></td>
        <td>
          <a href="<?= Url::to(['/admin/' . $module . '/a/edit', 'id' => $item->primaryKey]) ?>">
            <b><?= Html::encode($item->code) ?></b>
          </a>
        </td>
        <td><?= Html::encode($item->getDiscountLabel()) ?></td>
        <td><?= $item->min_sum > 0 ? number_format($item->min_sum, 0, '.', ' ') . ' ₽' : '—' ?></td>
        <td>
          <?php if ($item->date_from || $item->date_to): ?>
            <?= $item->date_from ? date('d.m.Y', $item->date_from) : '∞' ?>
            —
            <?= $item->date_to ? date('d.m.Y', $item->date_to) : '∞' ?>
          <?php else: ?>
            ∞
          <?php endif; ?>
        </td>
        <td>
          <?= $item->usage_count ?>
          <?= $item->usage_limit !== null ? ' / ' . $item->usage_limit : '' ?>
        </td>
        <td class="status">
          <?= Html::checkbox('', $item->status == Promo::STATUS_ON, [
            'class' => 'switch',
            'data-id' => $item->primaryKey,
            'data-link' => Url::to(['/admin/' . $module . '/a']),
          ]) ?>
        </td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <a href="<?= Url::to(['/admin/' . $module . '/a/delete', 'id' => $item->primaryKey]) ?>"
               class="btn btn-default confirm-delete">
              <span class="glyphicon glyphicon-remove"></span>
            </a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?= yii\widgets\LinkPager::widget(['pagination' => $data->pagination]) ?>
<?php else: ?>
  <p>Промокодов нет</p>
<?php endif; ?>