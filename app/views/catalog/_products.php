<?php
/**
 * @var \yii\easyii\modules\catalog\models\Category $category
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var array $itemData
 * @var array $filterValues
 * @var array $activeFilters
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\CategoryPager;

?>
<?= $this->render('_active_filters', [
  'filterValues' => $filterValues,
  'activeFilters' => $activeFilters,
]) ?>
<?php if ($dataProvider->totalCount > 0): ?>
  <div class="prods-1-wrap">
    <div class="prods-1 prods-1-tile">
      <?php foreach ($dataProvider->models as $item): ?>
        <?php
        $itemUnit = $itemData[$item->item_id]['unit'][0] ?? '';
        $itemSizes = $itemData[$item->item_id]['size'] ?? [];
        $itemUrl = $item->collection
          ? Url::to(['/collection/' . $item->collection->slug . '/' . $item->slug])
          : Url::to(['/catalog/' . $category->slug . '/' . $item->slug]);
        ?>
        <div class="prod-1">
          <div class="p1-t">
            <div class="i">
              <a href="<?= $itemUrl ?>">
                <?php if ($item->image): ?>
                  <img src="<?= Image::thumb($item->image, 341, 341) ?>" alt="<?= Html::encode($item->title) ?>">
                <?php endif; ?>
              </a>
              <?php if ($item->available > 0): ?>
                <span class="avail">В наличии</span>
              <?php else: ?>
                <span class="avail onorder">Под заказ</span>
              <?php endif; ?>
            </div>
            <div class="p-info">
              <div class="sale">
                <b><?= number_format($item->price, 0, '.', ' ') ?></b>
                <i>₽<?= $itemUnit ? '/' . $itemUnit : '' ?></i>
                <?php if ($item->old_price > $item->price && $item->old_price > 0): ?>
                  <span class="v1"><?= number_format($item->old_price, 0, '.', ' ') ?></span>
                  <span class="dt">-<?= round((1 - $item->price / $item->old_price) * 100) ?>%</span>
                <?php endif; ?>
              </div>
              <h4>
                <a href="<?= $itemUrl ?>"><?= Html::encode($item->title) ?></a>
              </h4>
              <?php if (!empty($itemSizes)): ?>
                <ul class="sizes">
                  <?php foreach ($itemSizes as $size): ?>
                    <li><?= Html::encode($size) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
          <button>В корзину</button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?= CategoryPager::widget([
    'pagination' => $dataProvider->pagination,
    'basePath' => '/catalog/' . $category->slug . '/tovary',
  ]) ?>
<?php else: ?>
  <p>Товары не найдены. Попробуйте изменить фильтры.</p>
<?php endif; ?>