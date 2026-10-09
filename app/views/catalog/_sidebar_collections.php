<?php
/**
 * @var array $brandsMap
 * @var array $activeBrand
 * @var int $visibleLimit
 */

use yii\helpers\Html;

if (empty($brandsMap)) return;

$totalBrands = count($brandsMap);
?>

<div class="sb1">
  <div class="sb1-ttl"><b>Бренд</b><i></i></div>
  <div class="sb1-b">
    <ul class="filter-list" data-visible="<?= $visibleLimit ?>">
      <?php $i = 0; foreach ($brandsMap as $slug => $data): ?>
        <li class="<?= $i >= $visibleLimit ? 'filter-hidden' : '' ?>">
          <label>
            <input type="checkbox"
                   name="brand[]"
                   value="<?= Html::encode($slug) ?>"
              <?= in_array($slug, $activeBrand) ? 'checked' : '' ?>>
            <span><?= Html::encode($data['value']) ?> (<?= count($data['collection_ids']) ?>)</span>
          </label>
        </li>
        <?php $i++; endforeach; ?>
    </ul>
    <?php if ($totalBrands > $visibleLimit): ?>
      <button type="button" class="filter-more">
        Показать ещё (<?= $totalBrands - $visibleLimit ?>)
      </button>
    <?php endif; ?>
  </div>
</div>