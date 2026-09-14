<?php
// app/views/completed/index.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = $page->seo('title', 'Реализованные проекты');
?>
<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li>Реализованные проекты</li>
  </ul>
</div>
<h1><?= $this->title ?></h1>
<h2 class="h2-1">Узор и фактура</h2>
<?php if (!empty($tags)): ?>
  <ul class="tabs2">
    <li class="<?= !$activeTag ? 'active' : '' ?>"><a href="<?= Url::to(['/completed']) ?>">Все</a></li>
    <?php foreach ($tags as $tag): ?>
      <li class="<?= $activeTag === $tag->slug ? 'active' : '' ?>">
        <a href="<?= Url::to(['/completed/index', 'tag' => $tag->slug]) ?>"><?= Html::encode($tag->name) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif;
if($dataProvider->totalCount > 0): ?>
  <ul class="ul-3">
    <?php foreach ($dataProvider->models as $item): ?>
      <li>
        <a href="<?= Url::to(['/completed/view', 'slug' => $item->slug]) ?>">
          <i>
            <?php if ($item->image): ?>
              <img src="<?= $item->image ?>" alt="<?= Html::encode($item->title) ?>">
            <?php else: ?>
              <img src="/img/232x232.png" alt="<?= Html::encode($item->title) ?>">
            <?php endif; ?>
          </i>
          <h5><?= Html::encode($item->title) ?></h5>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?= LinkPager::widget([
    'pagination' => $dataProvider->pagination,
    'options' => ['class' => 'pgn'],
    'linkContainerOptions' => ['class' => ''],
    'prevPageCssClass' => 'prev',
    'nextPageCssClass' => 'next',
    'activePageCssClass' => 'active',
    'disabledPageCssClass' => 'disabled',
    'prevPageLabel' => '«',
    'nextPageLabel' => '»',
    'maxButtonCount' => 7,
  ]) ?>
<?php else: ?>
  <p>Проекты не найдены</p>
<?php endif; ?>