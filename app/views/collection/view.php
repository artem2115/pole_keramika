<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $collection->title;
?>

<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li><a href="<?= Url::to(['/catalog']) ?>">Каталог</a></li>
    <?php if ($collection->category): ?>
      <li><a href="<?= Url::to(['/catalog/' . $collection->category->slug]) ?>"><?= Html::encode($collection->category->title) ?></a></li>
    <?php endif; ?>
    <li><?= Html::encode($collection->title) ?></li>
  </ul>
</div>

<h1><?= Html::encode($collection->title) ?></h1>

<?php if ($collection->description): ?>
  <div class="collection-description"><?= $collection->description ?></div>
<?php endif; ?>

<p>Товаров в коллекции: <?= count($items) ?></p>

<ul class="ul-9">
  <?php foreach ($items as $item): ?>
    <li>
      <a href="<?= Url::to(['/collection/' . $collection->slug . '/' . $item->slug]) ?>">
        <?= Html::encode($item->title) ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>