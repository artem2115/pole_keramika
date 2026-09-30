<?php
use yii\easyii\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;

/**
* @var yii\web\View $this
* @var \yii\easyii\modules\collections\models\Collection $collection
* @var \yii\easyii\modules\catalog\models\Item[] $items
* @var string[] $images
* @var array $itemData
* @var \yii\easyii\modules\collections\models\Collection[] $similarCollections
*/

$hasGallery = count($images) > 1;

$this->title = $collection->seo('title', $collection->title);
?>
<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li><a href="<?= Url::to(['/catalog']) ?>">Каталог</a></li>
    <?php if ($collection->category): ?>
      <li>
        <a href="<?= Url::to(['/catalog/' . $collection->category->slug]) ?>">
          <?= Html::encode($collection->category->title) ?>
        </a>
      </li>
    <?php endif; ?>
    <li><?= Html::encode($collection->title) ?></li>
  </ul>
</div>
<h1><?= $collection->seo('h1', $collection->title) ?></h1>
<div class="b8 b8-2">
  <?php if (!empty($images)): ?>
    <div class="b8-1c sl-2-rev">
      <figure class="sl-2 sl-2-for">
        <?php foreach ($images as $image): ?>
          <div class="sl-2-s">
            <img src="<?= Image::thumb($image, 985, 697) ?>" alt="<?= Html::encode($collection->title) ?>">
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
    <div class="b10">
      <?php $minPrice = $collection->getMinPrice(); ?>
      <?php if ($minPrice > 0): ?>
        <div class="sale3">от <?= number_format($minPrice, 0, '.', ' ') ?> руб.</div>
      <?php endif; ?>
      <?php $formats = $collection->getFormatsList(); ?>
      <?php if (!empty($formats)): ?>
        <div class="h5">Форматы плитки</div>
        <ul class="sizes">
          <?php foreach ($formats as $format): ?>
            <li><?= Html::encode($format) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if ($collection->description): ?>
        <div class="h5">О коллекции</div>
        <p><?= $collection->short ?></p>
      <?php endif; ?>
      <button class="btn1-b1" data-modal="order_design_project">Заказать дизайн проект</button>
      <button class="btn1-b2" data-scroll-to="#collection-items">Смотреть плитку</button>
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
<?php if ($collection->category): ?>
<div class="ttl2">
  <div class="h2">Состав коллекции</div>
  <div class="b9">
    <a href="<?= Url::to(['/catalog/' . $collection->category->slug]) ?>">
      <i>
        <?php if ($collection->category->image): ?>
          <img src="<?= Image::thumb($collection->category->image, 131, 90) ?>" alt="">
        <?php else: ?>
          <img src="/img/131x90.png" alt="">
        <?php endif; ?>
      </i>
      <div class="b9-r">
        <h6><?= Html::encode($collection->category->title) ?></h6>
      </div>
    </a>
  </div>
</div>
<?php endif;
if (!empty($items)): ?>
<ul class="ul-9" id="collection-items">
  <?php foreach ($items as $item): ?>
    <?php
    $itemSizes = $itemData[$item->item_id]['size'] ?? [];
    $itemUnit = $itemData[$item->item_id]['unit'][0] ?? '';
    ?>
    <li>
      <i>
        <?php if ($item->image): ?>
          <img src="<?= Image::thumb($item->image, 300, 300) ?>" alt="<?= Html::encode($item->title) ?>">
        <?php endif; ?>
      </i>
      <div class="ul-9-b">
        <div class="sale">
          <b><?= number_format($item->price, 0, '.', ' ') ?></b>
          <i>₽<?= $itemUnit ? '/' . $itemUnit : '' ?></i>
        </div>
        <h4>
          <a href="<?= Url::to(['/collection/' . $collection->slug . '/' . $item->slug]) ?>">
            <?= Html::encode($item->title) ?>
          </a>
        </h4>
        <?php if (!empty($itemSizes)): ?>
          <ul class="sizes">
            <?php foreach ($itemSizes as $size): ?>
              <li><?= Html::encode($size) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif;
if ($collection->description): ?>
<article>
  <h3>Описание коллекции</h3>
  <?= $collection->description ?>
</article>
<?php endif;
if (!empty($similarCollections)): ?>
<div class="ttl">
  <h3>Похожие коллекции</h3>
  <div class="sl-arrs prods-1-arrs"></div>
</div>
<div class="prods-1-wrap">
  <figure class="prods-1 prods-2-sl">
    <?php foreach ($similarCollections as $similar): ?>
      <div class="prod-1">
        <div class="p1-t">
          <div class="i">
            <a href="<?= Url::to(['/collection/' . $similar->slug]) ?>">
              <?php if ($similar->image): ?>
                <img src="<?= Image::thumb($similar->image, 341, 240) ?>" alt="<?= Html::encode($similar->title) ?>">
              <?php else: ?>
                <img src="/img/thg-1-1.jpg" alt="">
              <?php endif; ?>
            </a>
          </div>
          <div class="p-info">
            <div class="sale2">
              от <?= number_format($similar->getMinPrice(), 0, '.', ' ') ?> <span>₽</span>
            </div>
            <h4 class="p1-ttl">
              <a href="<?= Url::to(['/collection/' . $similar->slug]) ?>">
                <?= Html::encode($similar->title) ?>
              </a>
            </h4>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </figure>
</div>
<?php endif; ?>