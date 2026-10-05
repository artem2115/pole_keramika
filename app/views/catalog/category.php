<?php
/**
 * @var yii\web\View $this
 * @var \yii\easyii\modules\catalog\models\Category $category
 * @var \yii\data\ActiveDataProvider|null $dataProvider
 * @var array $filterValues
 * @var array $activeFilters
 * @var array $itemData
 * @var \yii\easyii\modules\catalog\models\Category[] $allCategories
 * @var \yii\easyii\modules\collections\models\Collection[]|null $collections
 * @var array|null $collectionPrices
 * @var array|null $brandsMap
 * @var array|null $activeBrand
 * @var string $view
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = $category->seo('title', $category->title);

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
    <li><a href="<?= Url::to(['/catalog']) ?>">Каталог</a></li>
    <li><?= Html::encode($category->title) ?></li>
  </ul>
</div>

<h1>
  <?= Html::encode($category->title) ?>
  <?php if ($view === 'tovary'): ?>
    <small><?= $dataProvider->totalCount ?> товаров</small>
  <?php else: ?>
    <small><?= count($collections ?? []) ?> коллекций</small>
  <?php endif; ?>
</h1>

<div class="c-wrap">
  <aside class="sidebar">
    <div class="f-r">
      <?php // Блок категорий — общий для обеих вкладок ?>
      <?php if (!empty($allCategories)): ?>
        <div class="sb1">
          <div class="sb1-ttl">
            <b>Категория</b>
            <i></i>
          </div>
          <div class="sb1-b">
            <ul>
              <?php foreach ($allCategories as $cat): ?>
                <li>
                  <a class="<?= $cat->category_id === $category->category_id ? 'active' : '' ?>"
                     href="<?= Url::to(['/catalog/' . $cat->slug . '/' . ($view === 'kollekcii' ? 'kollekcii' : 'tovary')]) ?>">
                    <?= Html::encode($cat->title) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($view === 'tovary'): ?>
        <?php // ================= ФИЛЬТРЫ ТОВАРОВ ================= ?>

        <div class="sb1">
          <div class="sb1-ttl">
            <b>Цена, ₽</b>
          </div>
          <div class="sb1-b">
            <div class="price-sel">
              <div class="ps">
                <i>от</i>
                <input type="text" name="price_from" value="<?= Html::encode($activeFilters['price_from'] ?? '') ?>">
              </div>
              <div class="ps-mdash">&mdash;</div>
              <div class="ps">
                <i>до</i>
                <input type="text" name="price_to" value="<?= Html::encode($activeFilters['price_to'] ?? '') ?>">
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

        <?php foreach ($filterValues as $filter): ?>
          <?php if ($filter['field']->name === 'purpose') continue; // Назначение выводим как табы ?>
          <div class="sb1">
            <div class="sb1-ttl">
              <b><?= Html::encode($filter['field']->title) ?></b>
              <i></i>
            </div>
            <div class="sb1-b">
              <ul>
                <?php foreach ($filter['values'] as $value): ?>
                  <?php
                  $label = $value;
                  $hex = '';
                  if ($filter['field']->name === 'color' && strpos($value, ':') !== false) {
                    list($label, $hex) = explode(':', $value, 2);
                  }
                  $checked = in_array($value, $activeFilters[$filter['field']->name] ?? []);
                  ?>
                  <li>
                    <label>
                      <input type="checkbox"
                             name="<?= Html::encode($filter['field']->name) ?>[]"
                             value="<?= Html::encode($value) ?>"
                        <?= $checked ? 'checked' : '' ?>>
                      <?php if ($hex): ?>
                        <i class="sb-clr" style="background:<?= Html::encode($hex) ?>"></i>
                      <?php endif; ?>
                      <span><?= Html::encode($label) ?></span>
                    </label>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endforeach; ?>

      <?php else: ?>
        <?php // ================= ФИЛЬТРЫ КОЛЛЕКЦИЙ ================= ?>

        <?php if (!empty($brandsMap)): ?>
          <div class="sb1">
            <div class="sb1-ttl">
              <b>Бренд</b>
              <i></i>
            </div>
            <div class="sb1-b">
              <ul>
                <?php foreach ($brandsMap as $brand => $collectionIds): ?>
                  <li>
                    <label>
                      <input type="checkbox"
                             name="brand[]"
                             value="<?= Html::encode($brand) ?>"
                        <?= in_array($brand, $activeBrand) ? 'checked' : '' ?>>
                      <span><?= Html::encode($brand) ?></span>
                    </label>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </aside>

  <div class="content">
    <div class="f-wrap">
      <div class="f-str">
        <div class="fs-1"><svg><use xlink:href="#filter"></use></svg> <span>Фильтр</span></div>
      </div>
      <div class="f-app"><div class="f-app-b"></div></div>
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
          <?php foreach ($purposeFilter['values'] as $value): ?>
            <?php $checked = in_array($value, $activeFilters['purpose'] ?? []); ?>
            <li class="<?= $checked ? 'active' : '' ?>" data-value="<?= Html::encode($value) ?>">
              <?= Html::encode($value) ?>
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
          <?php foreach ($sizeFilter['values'] as $value): ?>
            <?php $checked = in_array($value, $activeFilters['size'] ?? []); ?>
            <li class="<?= $checked ? 'active' : '' ?>" data-value="<?= Html::encode($value) ?>">
              <?= Html::encode($value) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <ul class="tabs">
        <li class="active"><a href="<?= Url::to(['/catalog/' . $category->slug . '/tovary']) ?>">Товары</a></li>
        <li><a href="<?= Url::to(['/catalog/' . $category->slug . '/kollekcii']) ?>">Коллекции</a></li>
      </ul>

      <div id="products-container">
        <?= $this->render('_products', [
          'category' => $category,
          'dataProvider' => $dataProvider,
          'itemData' => $itemData,
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