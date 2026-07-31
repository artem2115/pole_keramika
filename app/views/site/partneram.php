<?php
$this->title = $page->seo('title', $page->model->title);
?>
<div class="bc">
  <ul>
    <li><a href="/">Главная</a></li>
    <li><?= $page->model->title ?></li>
  </ul>
</div>
<h1><?= $page->seo('h1', $page->model->title) ?></h1>
<div class="b1 b1-2">
  <?php if ($page->model->image): ?>
  <div class="b1-l"><img src="<?= $page->model->getImageUrl() ?>" alt="<?= $page->model->title ?>"></div>
  <?php endif; ?>
  <div class="b1-r">
    <div class="h1"><?= $page->model->str1 ?></div>
    <?= $page->model->text ?>
    <div class="btns-1">
      <button class="btn1-b1" data-modal="order_design_project">Получить КП</button>
    </div>
  </div>
</div>
<?= $this->renderFile('@app/components/_offer.php', ['type' => 'partner']) ?>