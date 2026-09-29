<h1><?= $item->title ?></h1>
<p>Цена: <?= $item->price ?></p>
<p>Коллекция: <?= $item->collection ? $item->collection->title : '—' ?></p>