<?php
/**
 * @var yii\web\View $this
 * @var \yii\easyii\modules\catalog\models\Category|null $category
 * @var \yii\easyii\modules\collections\models\Collection[] $collections
 * @var array $collectionPrices
 * @var \yii\easyii\modules\catalog\models\Category[] $allCategories
 * @var array $stylesMap
 * @var array $activeStyle
 * @var int $visibleLimit
 * @var array|null $brandsMap
 * @var array|null $activeBrand
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $category
  ? $category->seo('title', $category->title . ' — коллекции')
  : 'Коллекции';

// Значения по умолчанию
$stylesMap = $stylesMap ?? [];
$activeStyle = $activeStyle ?? [];
$visibleLimit = $visibleLimit ?? 7;
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
    <li>
      <?php if ($category): ?>
        <a href="<?= Url::to(['/collections']) ?>">Коллекции</a>
      <?php else: ?>
        Коллекции
      <?php endif; ?>
    </li>
    <?php if ($category): ?>
      <li><?= Html::encode($category->title) ?></li>
    <?php endif; ?>
  </ul>
</div>

<h1>
  <?= $category ? Html::encode($category->title) : 'Коллекции' ?>
  <small><?= count($collections) ?> коллекций</small>
</h1>

<div class="c-wrap">
  <aside class="sidebar">
    <div class="f-r">
      <?= $this->render('@common/_sidebar_categories', [
        'category' => $category,
        'allCategories' => $allCategories,
        'view' => 'kollekcii',
      ]) ?>

      <?= $this->render('_sidebar_brands', [
        'brandsMap' => $brandsMap ?? [],
        'activeBrand' => $activeBrand ?? [],
        'visibleLimit' => $visibleLimit,
      ]) ?>

      <?= $this->render('_sidebar_styles', [
        'stylesMap' => $stylesMap,
        'activeStyle' => $activeStyle,
        'visibleLimit' => $visibleLimit,
      ]) ?>
    </div>
  </aside>

  <div class="content">
    <ul class="tabs">
      <li>
        <a href="<?= $category
          ? Url::to(['/catalog/' . $category->slug . '/tovary'])
          : Url::to(['/catalog']) ?>">Товары</a>
      </li>
      <li class="active">
        <a href="<?= $category
          ? Url::to(['/collections/' . $category->slug])
          : Url::to(['/collections']) ?>">Коллекции</a>
      </li>
    </ul>

    <?php if (!empty($collections)): ?>
      <div class="prods-1-wrap">
        <div class="prods-1 prods-1-tile">
          <?php foreach ($collections as $col): ?>
            <?php $colUrl = Url::to(['/collections/' . $col->category->slug . '/' . $col->slug]); ?>
            <div class="prod-1">
              <div class="p1-t">
                <div class="i">
                  <a href="<?= $colUrl ?>">
                    <?php if ($col->image): ?>
                      <img src="<?= Image::thumb($col->image, 341, 240) ?>" alt="<?= Html::encode($col->title) ?>">
                    <?php else: ?>
                      <img src="/img/thg-1-1.jpg" alt="">
                    <?php endif; ?>
                  </a>
                </div>
                <div class="p-info">
                  <h4 class="p1-ttl">
                    <a href="<?= $colUrl ?>"><?= Html::encode($col->title) ?></a>
                  </h4>
                  <?php if ($col->getStyleLabel()): ?>
                    <div class="p1-style"><?= Html::encode($col->getStyleLabel()) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($collectionPrices[$col->collection_id])): ?>
                    <div class="sale2">
                      от <?= number_format($collectionPrices[$col->collection_id], 0, '.', ' ') ?> <span>₽</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <a href="<?= $colUrl ?>" class="btn1-b1">Подробнее</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <p>Коллекции не найдены</p>
    <?php endif; ?>
  </div>
</div>