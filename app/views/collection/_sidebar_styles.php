<?php
/**
 * @var array $stylesMap
 * @var array $activeStyle
 * @var int $visibleLimit
 */

use yii\helpers\Html;

if (empty($stylesMap)) return;

$total = count($stylesMap);
?>

<div class="sb1">
  <div class="sb1-ttl">
    <b>Стиль</b>
    <i></i>
  </div>
  <div class="sb1-b">
    <ul class="filter-list" data-visible="<?= $visibleLimit ?>">
      <?php $i = 0; foreach ($stylesMap as $slug => $data): ?>
        <li class="<?= $i >= $visibleLimit ? 'filter-hidden' : '' ?>">
          <label>
            <input type="checkbox"
                   name="style"
                   value="<?= Html::encode($slug) ?>"
              <?= in_array($slug, $activeStyle) ? 'checked' : '' ?>>
            <span><?= Html::encode($data['label']) ?> (<?= $data['count'] ?>)</span>
          </label>
        </li>
        <?php $i++; endforeach; ?>
    </ul>
    <?php if ($total > $visibleLimit): ?>
      <button type="button" class="filter-more">
        Показать ещё (<?= $total - $visibleLimit ?>)
      </button>
    <?php endif; ?>
  </div>
</div>