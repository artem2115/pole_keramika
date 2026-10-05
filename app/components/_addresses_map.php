<?php

use yii\easyii\modules\addresses\models\Addresses;
$addresses = Addresses::find()->where(['status' => Addresses::STATUS_ON])->all();
if (!empty($addresses)):
  \app\assets\MapAsset::register($this);
  ?>
  <h3 class="h1">Адреса магазинов</h3>
  <ul class="ul-5">
    <?php foreach ($addresses as $index => $address): ?>
      <li data-coords="<?= $address->getCoordsString() ?>">
        <b><?= $address->title ?></b>
        <i><?= $address->getCleanAddress() ?></i>
        <span><?= $address->getFirstPhone() ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
  <div id="yandex-map" class="map"></div>
<?php endif; ?>