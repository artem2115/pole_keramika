<?php
// app/views/completed/view.php

use yii\helpers\Url;
use yii\easyii\helpers\Image;

$this->title = $completed->seo('title', $completed->title);
$this->params['fancybox_asset'] = true;
?>
  <div class="bc">
    <ul>
      <li><a href="/">Главная</a></li>
      <li><a href="<?= Url::to(['/completed']) ?>">Реализованные проекты</a></li>
      <li><?= $completed->title ?></li>
    </ul>
  </div>
  <h1><?= $completed->title ?></h1>
  <div class="b6">
    <?php if ($completed->image): ?>
    <div class="b6-l">
      <img src="<?= $completed->image ?>" alt="<?= $completed->title ?>">
    </div>
    <?php endif; ?>
    <div class="b6-r">
      <div class="b6-r-t">
        <h2><?= $completed->subtitle ?></h2>
        <?php if ($completed->text): ?>
          <div class="h5">О проекте</div>
          <p><?= $completed->text ?></p>
        <?php endif; ?>
      </div>
      <?= $this->renderFile('@app/components/_strip.php') ?>
    </div>
  </div>
<?php if (!empty($completed->photos)): ?>
  <ul class="ul-g col-3">
    <?php foreach ($completed->photos as $photo): ?>
      <li>
        <a href="<?= $photo->image ?>" data-fancybox="gallery"<?= !empty($photo->description) ? ' data-caption="' . $photo->description . '"' : '' ?>>
          <img data-src="<?= Image::thumb($photo->image, 470, 470) ?>" alt="">
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif;
if (!empty($otherProjects)): ?>
  <div class="h2">Другие проекты</div>
  <ul class="ul-3">
  <?php foreach ($otherProjects as $project): ?>
    <li>
      <a href="<?= Url::to(['/completed/view', 'slug' => $project->slug]) ?>">
        <i><img data-src="<?= Image::thumb($project->image, 348, 232) ?>" alt="<?= $project->title ?>"></i>
        <h5><?= $project->title ?></h5>
      </a>
    </li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>