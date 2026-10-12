<?php
//app/views/catalog/_active_filters.php
/**
 * @var array $filterValues
 * @var array $activeFilters
 */

use yii\helpers\Html;

// Собираем активные фильтры для отображения
$activeItems = [];

if (!empty($activeFilters)) {
  foreach ($activeFilters as $fieldName => $slugs) {
    if (empty($slugs)) continue;

    // Цена от
    if ($fieldName === 'price_from') {
      $activeItems[] = [
        'label' => 'Цена от ' . number_format((float)$slugs, 0, '.', ' '),
        'field' => 'price_from',
        'slug' => $slugs,
      ];
      continue;
    }

    // Цена до
    if ($fieldName === 'price_to') {
      $activeItems[] = [
        'label' => 'Цена до ' . number_format((float)$slugs, 0, '.', ' '),
        'field' => 'price_to',
        'slug' => $slugs,
      ];
      continue;
    }

    // Хит
    if ($fieldName === 'is_hit') {
      $activeItems[] = [
        'label' => 'Только хиты',
        'field' => 'is_hit',
        'slug' => '1',
      ];
      continue;
    }

    // Обычные фильтры — ищем label по slug
    $fieldTitle = '';
    $fieldValues = [];

    foreach ($filterValues as $filter) {
      if ($filter['field']->name === $fieldName) {
        $fieldTitle = $filter['field']->title;
        $fieldValues = $filter['values'];
        break;
      }
    }

    foreach ($slugs as $slug) {
      $label = $slug;
      foreach ($fieldValues as $value) {
        if ($value['slug'] === $slug) {
          $label = $value['label'];
          break;
        }
      }

      $activeItems[] = [
        'label' => ($fieldTitle ? $fieldTitle . ': ' : '') . $label,
        'field' => $fieldName,
        'slug' => $slug,
      ];
    }
  }
}

if (!empty($activeItems)): ?>
  <div class="active-filters">
    <div class="active-filters-list">
      <?php foreach ($activeItems as $item): ?>
        <button type="button"
                data-filter-field="<?= Html::encode($item['field']) ?>"
                data-filter-slug="<?= Html::encode($item['slug']) ?>">
          <span><?= Html::encode($item['label']) ?></span>
          <i>✕</i>
        </button>
      <?php endforeach; ?>
    </div>
    <button type="button" class="active-filters-reset" id="active-filters-reset">
      Сбросить все
    </button>
  </div>
<?php endif; ?>