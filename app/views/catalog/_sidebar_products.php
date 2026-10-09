<?php
/**
 * @var array $filterValues
 * @var array $activeFilters
 * @var array $filterCounts
 * @var float $priceMin
 * @var float $priceMax
 * @var int $visibleLimit
 */

use yii\helpers\Html;

// Значения цены
$priceFromValue = $activeFilters['price_from'] ?? ($priceMin > 0 ? (int)$priceMin : '');
$priceToValue = $activeFilters['price_to'] ?? ($priceMax > 0 ? (int)$priceMax : '');
?>

  <div class="sb1">
    <div class="sb1-ttl">
      <b>Цена, ₽</b>
    </div>
    <div class="sb1-b">
      <div class="price-sel">
        <div class="ps">
          <i>от</i>
          <input type="text"
                 name="price_from"
                 value="<?= Html::encode($priceFromValue) ?>"
                 data-min="<?= (int)$priceMin ?>"
                 data-max="<?= (int)$priceMax ?>">
        </div>
        <div class="ps-mdash">&mdash;</div>
        <div class="ps">
          <i>до</i>
          <input type="text"
                 name="price_to"
                 value="<?= Html::encode($priceToValue) ?>"
                 data-min="<?= (int)$priceMin ?>"
                 data-max="<?= (int)$priceMax ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="filter-toggle">
    <span class="label">Хит</span>
    <label class="switch">
      <input type="checkbox" name="is_hit" value="1" <?= !empty($activeFilters['is_hit']) ? 'checked' : '' ?>>
      <span class="slider"></span>
    </label>
  </div>

<?php
foreach ($filterValues as $filter):
  if ($filter['field']->name === 'purpose') continue;
  $totalValues = count($filter['values']);
  ?>
  <div class="sb1">
    <div class="sb1-ttl">
      <b><?= Html::encode($filter['field']->title) ?></b>
      <i></i>
    </div>
    <div class="sb1-b">
      <ul class="filter-list" data-visible="<?= $visibleLimit ?>">
        <?php foreach ($filter['values'] as $index => $item): ?>
          <li class="<?= $index >= $visibleLimit ? 'filter-hidden' : '' ?>">
            <label>
              <input type="checkbox"
                     name="<?= Html::encode($filter['field']->name) ?>[]"
                     value="<?= Html::encode($item['slug']) ?>"
                     data-slug="<?= Html::encode($item['slug']) ?>"
                <?= in_array($item['slug'], $activeFilters[$filter['field']->name] ?? []) ? 'checked' : '' ?>>
              <?php if ($item['hex']): ?>
                <i class="sb-clr" style="background:<?= Html::encode($item['hex']) ?>"></i>
              <?php endif; ?>
              <span><?= Html::encode($item['label']) ?>
                <?php
                // Ищем счётчик для этого значения
                $count = null;
                if (!empty($filterCounts[$filter['field']->name])) {
                  // Для цвета — сверяем по оригинальному значению (с HEX)
                  $originalValue = $item['value'];
                  $count = $filterCounts[$filter['field']->name][$originalValue] ?? 0;
                }
                ?>
                <?php if ($count !== null): ?>
                  (<?= $count ?>)
                <?php endif; ?>
</span>
            </label>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php if ($totalValues > $visibleLimit): ?>
        <button type="button" class="filter-more">
          Показать ещё (<?= $totalValues - $visibleLimit ?>)
        </button>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>