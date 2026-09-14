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
<?php if(!empty($contacts)): ?>
  <ul class="ul-8">
    <?php foreach ($contacts as $contact): ?>
      <li>
        <a href="<?= $contact['link'] ?? '#' ?>">
          <i class="i-<?= $contact['icon'] ?? '' ?>">
            <svg width="<?= $contact['width'] ?? 20 ?>" height="<?= $contact['height'] ?? 20 ?>">
              <use xlink:href="#<?= $contact['icon'] ?? '' ?>"></use>
            </svg>
          </i>
          <b><?= $contact['title'] ?? '' ?></b>
          <em><?= $contact['subtitle'] ?? '' ?></em>
          <span><?= $contact['description'] ?? '' ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
<?= $this->renderFile('@app/components/_addresses_map.php') ?>