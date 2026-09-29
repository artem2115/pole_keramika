<h1>Каталог</h1>
<ul>
  <?php foreach ($categories as $cat): ?>
    <li><a href="<?= \yii\helpers\Url::to(['/catalog/' . $cat->slug]) ?>"><?= $cat->title ?></a></li>
  <?php endforeach; ?>
</ul>