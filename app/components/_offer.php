<?php
/**
 * Блок с карточками для партнеров
 *
 * @var string $type - тип блока: 'main' или 'partner'
 */

$type = $type ?? 'main';

// Настройки для разных типов страниц
$settings = [
  'main' => [
    'blockClass' => 'b4 b4-1',
    'showTitle' => true,
    'title' => 'Поле Керамика надежный партнер',
    'buttons' => [
      'Получить КП',
      'Получить условия'
    ],
    'images' => [
      '/i/b4-1.webp',
      '/i/b4-2.webp'
    ]
  ],
  'partner' => [
    'blockClass' => 'b4 b4-2',
    'showTitle' => false,
    'title' => '',
    'buttons' => [
      'Получить КП',
      'Получить условия'
    ],
    'images' => [
      '/i/b4-1.webp',
      '/i/b4-2.webp'
    ]
  ]
];

$config = $settings[$type] ?? $settings['main'];
?>

<div class="<?= $config['blockClass'] ?>">
  <?php if ($config['showTitle']): ?>
    <div class="b4-ttl">
      <h4><?= $config['title'] ?></h4>
    </div>
  <?php endif; ?>

  <div class="b4-ch">
    <div class="b4-l">
      <div class="b4-ch-t">
        <h5>Для застройщиков и крупных объектов в Забайкальском крае</h5>
        <p>Оптовые поставки керамической плитки и керамогранита</p>
      </div>
      <button><?= $config['buttons'][0] ?></button>
    </div>
    <div class="b4-r"><img src="<?= $config['images'][0] ?>" alt=""></div>
  </div>

  <div class="b4-ch">
    <div class="b4-l">
      <div class="b4-ch-t">
        <h5>Для строителей и дизайнеров интерьера</h5>
        <p>Обновленные условия, помощь и выгода! (текст - оффер для партнеров)</p>
      </div>
      <button><?= $config['buttons'][1] ?></button>
    </div>
    <div class="b4-r"><img src="<?= $config['images'][1] ?>" alt=""></div>
  </div>
</div>