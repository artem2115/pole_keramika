<?php
//app/views/catalog/item.php
/**
 * @var yii\web\View $this
 * @var \yii\easyii\modules\catalog\models\Category $category
 * @var \yii\easyii\modules\collections\models\Collection|null $collection
 * @var \yii\easyii\modules\catalog\models\Item $item
 * @var string[] $images
 * @var array $characteristics
 * @var \yii\easyii\modules\catalog\models\Item[] $collectionItems
 * @var array $itemData
 */

use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;

$this->params['fancybox_asset'] = true;
$this->title = $item->seo('title', $item->title);

$hasGallery = count($images) > 1;
$itemUnit = $itemData[$item->item_id]['unit'][0] ?? $item->getDataValue('unit');
?>

  <div class="bc">
    <ul>
      <li><a href="/">Главная</a></li>
      <li><a href="<?= Url::to(['/catalog']) ?>">Каталог</a></li>
      <?php if ($collection && $collection->category): ?>
        <li>
          <a href="<?= Url::to(['/catalog/' . $collection->category->slug]) ?>">
            <?= Html::encode($collection->category->title) ?>
          </a>
        </li>
        <li>
          <a href="<?= Url::to(['/collections/' . $collection->category->slug . '/' . $collection->slug]) ?>">
            <?= Html::encode($collection->title) ?>
          </a>
        </li>
      <?php elseif ($category): ?>
        <li>
          <a href="<?= Url::to(['/catalog/' . $category->slug]) ?>">
            <?= Html::encode($category->title) ?>
          </a>
        </li>
      <?php endif; ?>
      <li><?= Html::encode($item->title) ?></li>
    </ul>
  </div>

  <h1><?= $item->seo('h1', $item->title) ?></h1>

  <div class="b8 b8-b1">
    <?php if (!empty($images)): ?>
      <div class="b8-1c">
        <figure class="sl-2 sl-2-for">
          <?php foreach ($images as $image): ?>
            <div class="sl-2-s">
              <a href="<?= $image ?>" data-fancybox="ct" data-caption="<?= Html::encode($item->title ?? '') ?>"><img src="<?= Image::thumb($image, 523, 370) ?>" alt="<?= Html::encode($item->title) ?>"></a>
            </div>
          <?php endforeach; ?>
        </figure>
        <?php if ($hasGallery): ?>
          <div class="sl-3-w">
            <figure class="sl-3 sl-3-nav">
              <?php foreach ($images as $image): ?>
                <div class="sl-3-s">
                  <img src="<?= Image::thumb($image, 53, 37) ?>" alt="">
                </div>
              <?php endforeach; ?>
            </figure>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="b8-2c">
      <div class="h3">Характеристики</div>
      <?php if (!empty($characteristics)): ?>
        <table>
          <?php foreach ($characteristics as $title => $value): ?>
            <tr>
              <th><?= Html::encode($title) ?></th>
              <td><?= Html::encode($value) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php if ($collection): ?>
        <div class="b9">
          <a href="<?= Url::to(['/collections/' . $collection->category->slug . '/' . $collection->slug]) ?>">
            <i>
              <?php if ($collection->image): ?>
                <img src="<?= Image::thumb($collection->image, 131, 90) ?>" alt="">
              <?php else: ?>
                <img src="/img/131x90.png" alt="">
              <?php endif; ?>
            </i>
            <div class="b9-r">
              <h6><?= Html::encode($collection->title) ?></h6>
              <b>К коллекции <svg><use xlink:href="#arr_rt2"></use></svg></b>
            </div>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div class="b8-2c">
      <div class="b10">
        <div class="sale3">
          <?= number_format($item->price, 0, '.', ' ') ?> ₽<?= $itemUnit ? '/' . $itemUnit : '' ?>
        </div>
        <button class="btn1-b1">В корзину</button>
        <button class="btn1-b2">Помощь в подборе</button>
      </div>
      <div class="b11">
        <a href="#">
          <b>Рассчитать количество</b>
          <svg><use xlink:href="#arr_rt2"></use></svg>
        </a>
      </div>
      <div class="strip">
        <div class="in-str">
          <h4>Бесплатный 3D проект</h4>
          <p>Визуализируем вашу задумку за 30 минут бесплатно</p>
          <button class="btn1-b5" data-modal="order_design_project">Заказать 3D-тур</button>
        </div>
      </div>
    </div>
  </div>

<?php if ($collection && !empty($collectionItems)): ?>
  <div class="ttl2">
    <div class="h2">Другие товары из коллекции</div>
    <div class="b9">
      <a href="<?= Url::to(['/collections/' . $collection->category->slug . '/' . $collection->slug]) ?>">
        <i>
          <?php if ($collection->image): ?>
            <img src="<?= Image::thumb($collection->image, 131, 90) ?>" alt="">
          <?php else: ?>
            <img src="/img/131x90.png" alt="">
          <?php endif; ?>
        </i>
        <div class="b9-r">
          <h6><?= Html::encode($collection->title) ?></h6>
        </div>
      </a>
    </div>
  </div>

  <ul class="ul-9">
    <?php foreach ($collectionItems as $cItem): ?>
      <?php
      $cItemUnit = $itemData[$cItem->item_id]['unit'][0] ?? '';
      ?>
      <li>
        <i>
          <?php if ($cItem->image): ?>
            <img src="<?= Image::thumb($cItem->image, 300, 300) ?>" alt="<?= Html::encode($cItem->title) ?>">
          <?php endif; ?>
        </i>
        <div class="ul-9-b">
          <div class="sale">
            <b><?= number_format($cItem->price, 0, '.', ' ') ?></b>
            <i>₽<?= $cItemUnit ? '/' . $cItemUnit : '' ?></i>
          </div>
          <h4>
            <a href="<?= Url::to(['/collections/' . $collection->category->slug . '/' . $collection->slug . '/' . $cItem->slug]) ?>">
              <?= Html::encode($cItem->title) ?>
            </a>
          </h4>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if ($item->description): ?>
  <article class="item-description">
    <h3>Описание товара</h3>
    <div class="item-description-body">
      <?= $item->description ?>
    </div>
    <div class="more"><span>Развернуть описание</span></div>
  </article>
<?php endif; ?>