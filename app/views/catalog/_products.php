<?php
/**
 * @var \yii\easyii\modules\catalog\models\Category $category
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var array $itemData
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
?>

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

  <?php
  $pagination = $dataProvider->pagination;
  $currentPage = $pagination->getPage() + 1;
  $pageCount = $pagination->getPageCount();

// Текущие GET-параметры, кроме служебных
  $currentParams = Yii::$app->request->get();
  unset($currentParams['page']);
  unset($currentParams['ajax']);
  unset($currentParams['category_slug']);
  unset($currentParams['collection_slug']);
  unset($currentParams['item_slug']);

// Базовый URL без номера страницы
  $basePath = '/catalog/' . $category->slug . '/tovary';

  $buildUrl = function($page) use ($basePath, $currentParams) {
    $url = $basePath . '/';
    if ($page > 1) {
      $url .= $page . '/';
    }
    if (!empty($currentParams)) {
      $url .= '?' . http_build_query($currentParams);
    }
    return $url;
  };
  ?>

  <?php if ($pageCount > 1): ?>
    <ul class="pgn">
      <?php if ($currentPage > 1): ?>
        <li class="prev"><a href="<?= $buildUrl($currentPage - 1) ?>">«</a></li>
      <?php else: ?>
        <li class="prev disabled"><span>«</span></li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pageCount; $i++): ?>
        <?php if ($i === $currentPage): ?>
          <li class="active"><a href="<?= $buildUrl($i) ?>"><?= $i ?></a></li>
        <?php else: ?>
          <li><a href="<?= $buildUrl($i) ?>"><?= $i ?></a></li>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($currentPage < $pageCount): ?>
        <li class="next"><a href="<?= $buildUrl($currentPage + 1) ?>">»</a></li>
      <?php else: ?>
        <li class="next disabled"><span>»</span></li>
      <?php endif; ?>
    </ul>
  <?php endif; ?>
<?php else: ?>
  <p>Товары не найдены. Попробуйте изменить фильтры.</p>
<?php endif; ?>