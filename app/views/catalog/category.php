<?php
//app/views/catalog/category.php
/**
 * @var yii\web\View $this
 * * @var \yii\easyii\modules\catalog\models\Category $category
 * * @var \yii\data\ActiveDataProvider|null $dataProvider
 * * @var array $filterValues
 * * @var array $activeFilters
 * * @var array $filterCounts
 * * @var array $itemData
 * @var float $priceMin
 * @var float $priceMax
 * @var \yii\easyii\modules\catalog\models\Category[] $allCategories
 * @var \yii\easyii\modules\collections\models\Collection[]|null $collections
 * @var array|null $collectionPrices
 * @var array|null $brandsMap
 * @var array|null $activeBrand
 * @var string $view
 * @var int $visibleLimit
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $category ? $category->seo('title', $category->title) : 'Каталог';

// Значения по умолчанию для вкладки "Коллекции"
$brandsMap = $brandsMap ?? [];
$activeBrand = $activeBrand ?? [];
?>

<div class="b7">
  <div class="b7-l">
    <div class="b7-ch">
      <div class="h4">Заказать бесплатный дизайн проект</div>
      <p>Визуализируем вашу задумку за 30 минут бесплатно</p>
    </div>
    <button data-modal="order_design_project">Оставить заявку</button>
  </div>
  <div class="b7-r">
    <div class="b7-ch">
      <div class="h4">Хотите получить скидку?</div>
      <p>Визуализируем вашу задумку за 30 минут бесплатно</p>
    </div>
    <button data-modal="order_design_project">Оставить заявку</button>
  </div>
</div>

<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <?php if ($category): ?>
      <li><a href="<?= Url::to(['/catalog']) ?>">Каталог</a></li>
      <li><?= Html::encode($category->title) ?></li>
    <?php else: ?>
      <li>Каталог</li>
    <?php endif; ?>
  </ul>
</div>

<h1>
  <?= $category ? Html::encode($category->title) : 'Каталог' ?>
  <?php if ($view === 'tovary'): ?>
    <small><?= $totalCount ?? $dataProvider->totalCount ?> товаров</small>
  <?php else: ?>
    <small><?= count($collections ?? []) ?> коллекций</small>
  <?php endif; ?>
</h1>

<div class="c-wrap">
  <aside class="sidebar">
    <div class="f-r">
      <?= $this->render('_sidebar_categories', [
        'category' => $category,
        'allCategories' => $allCategories,
        'view' => $view,
      ]) ?>

      <?php if ($view === 'tovary'): ?>
        <?= $this->render('_sidebar_products', [
          'filterValues' => $filterValues,
          'activeFilters' => $activeFilters,
          'priceMin' => $priceMin,
          'priceMax' => $priceMax,
          'visibleLimit' => $visibleLimit,
          'filterCounts' => $filterCounts ?? [],
        ]) ?>
      <?php else: ?>
        <?= $this->render('_sidebar_collections', [
          'brandsMap' => $brandsMap,
          'activeBrand' => $activeBrand,
          'visibleLimit' => $visibleLimit,
        ]) ?>
      <?php endif; ?>
    </div>
  </aside>

  <div class="content">
    <div class="f-wrap">
      <div class="f-str">
        <div class="fs-1"><svg><use xlink:href="#filter"></use></svg> <span>Фильтр</span></div>
      </div>
      <div class="f-app">
        <div class="f-app-b"></div>
        <div class="f-app-footer">
          <button type="button" class="filter-submit-btn" id="filter-mobile-btn">Закрыть</button>
        </div>
      </div>
    </div>

    <?php if ($view === 'tovary'): ?>
      <?php // ================= ВКЛАДКА "ТОВАРЫ" ================= ?>

      <?php
      // Значения purpose для табов
      $purposeFilter = null;
      foreach ($filterValues as $f) {
        if ($f['field']->name === 'purpose') {
          $purposeFilter = $f;
          break;
        }
      }
      ?>
      <?php if ($purposeFilter): ?>
        <ul class="tabs2" data-filter-name="purpose">
          <?php foreach ($purposeFilter['values'] as $item): ?>
            <?php $checked = in_array($item['slug'], $activeFilters['purpose'] ?? []); ?>
            <li class="<?= $checked ? 'active' : '' ?>" data-value="<?= Html::encode($item['slug']) ?>">
              <?= Html::encode($item['label']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php
      // Размеры для табов
      $sizeFilter = null;
      foreach ($filterValues as $f) {
        if ($f['field']->name === 'size') {
          $sizeFilter = $f;
          break;
        }
      }
      ?>
      <?php if ($sizeFilter): ?>
        <ul class="tabs2 tabs2-2" data-filter-name="size">
          <?php foreach ($sizeFilter['values'] as $item): ?>
            <?php $checked = in_array($item['slug'], $activeFilters['size'] ?? []); ?>
            <li class="<?= $checked ? 'active' : '' ?>" data-value="<?= Html::encode($item['slug']) ?>">
              <?= Html::encode($item['label']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <ul class="tabs">
        <li class="active">
          <a href="<?= $category ? Url::to(['/catalog/' . $category->slug . '/tovary']) : Url::to(['/catalog']) ?>">Товары</a>
        </li>
        <li>
          <?php if ($category): ?>
            <a href="<?= Url::to(['/catalog/' . $category->slug . '/kollekcii']) ?>">Коллекции</a>
          <?php else: ?>
            <span>Коллекции</span>
          <?php endif; ?>
        </li>
      </ul>

      <div id="products-container">
        <?= $this->render('_products', [
          'category' => $category,
          'dataProvider' => $dataProvider,
          'itemData' => $itemData,
          'filterValues' => $filterValues,
          'activeFilters' => $activeFilters,
          'view' => 'tovary',
        ]) ?>
      </div>

    <?php else: ?>
      <?php // ================= ВКЛАДКА "КОЛЛЕКЦИИ" ================= ?>

      <ul class="tabs">
        <li><a href="<?= Url::to(['/catalog/' . $category->slug . '/tovary']) ?>">Товары</a></li>
        <li class="active"><a href="<?= Url::to(['/catalog/' . $category->slug . '/kollekcii']) ?>">Коллекции</a></li>
      </ul>

      <?php if (!empty($collections)): ?>
        <div class="prods-1-wrap">
          <div class="prods-1 prods-1-tile">
            <?php foreach ($collections as $col): ?>
              <div class="prod-1">
                <div class="p1-t">
                  <div class="i">
                    <a href="<?= Url::to(['/collection/' . $col->slug]) ?>">
                      <?php if ($col->image): ?>
                        <img src="<?= Image::thumb($col->image, 341, 240) ?>" alt="<?= Html::encode($col->title) ?>">
                      <?php else: ?>
                        <img src="/img/thg-1-1.jpg" alt="">
                      <?php endif; ?>
                    </a>
                  </div>
                  <div class="p-info">
                    <h4 class="p1-ttl">
                      <a href="<?= Url::to(['/collection/' . $col->slug]) ?>">
                        <?= Html::encode($col->title) ?>
                      </a>
                    </h4>
                    <?php if (!empty($collectionPrices[$col->collection_id])): ?>
                      <div class="sale2">
                        от <?= number_format($collectionPrices[$col->collection_id], 0, '.', ' ') ?> <span>₽</span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="<?= Url::to(['/collection/' . $col->slug]) ?>" class="btn1-b1">Подробнее</a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <p>По выбранным фильтрам коллекции не найдены</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>