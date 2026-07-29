<?php
$this->title = $page->seo('title', $page->model->title);
?>
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


<h3 class="h1">Адреса магазинов</h3>
<ul class="ul-5">
  <li>
    <b>Вокзальная ул., 3А</b>
    <span>+7 (924) 818-00-36</span>
  </li>
  <li>
    <b>ул. Курнатовского, 25</b>
    <span>+7 (3022) 50-09-01</span>
  </li>
  <li>
    <b>Объездное ш., 24А, стр. 3</b>
    <span>+7 (924) 818-00-59</span>
  </li>
  <li>
    <b>Вокзальная ул., 3А</b>
    <span>+7 (924) 818-00-36</span>
  </li>
</ul>

<div class="map"><img src="/img/ya_karta.jpg" alt=""></div>
