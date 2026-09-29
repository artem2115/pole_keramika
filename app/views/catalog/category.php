<?php foreach ($dataProvider->models as $item): ?>
  <?php
  // URL товара: если есть коллекция — через /collection/, иначе — /catalog/{category}/{item}/
  if ($item->collection) {
    $itemUrl = \yii\helpers\Url::to(['/collection/' . $item->collection->slug . '/' . $item->slug]);
  } else {
    $itemUrl = \yii\helpers\Url::to(['/catalog/' . $category->slug . '/' . $item->slug]);
  }
  ?>
  <div>
    <a href="<?= $itemUrl ?>"><?= $item->title ?></a>
  </div>
<?php endforeach; ?>