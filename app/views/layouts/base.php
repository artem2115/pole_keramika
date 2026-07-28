<?php

use yii\helpers\Html;

$asset = \app\assets\AppAsset::register($this);
if(isset($isHome))
  $canonical = Yii::$app->request->hostInfo;
else
  $canonical = Yii::$app->request->hostInfo . '/' . Yii::$app->request->pathInfo;
$this->beginPage() ?>
<?php $this->beginPage() ?>
  <!DOCTYPE html>
  <html lang="<?= Yii::$app->language ?>">
  <head>
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/><![endif]-->
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="shortcut icon" href="<?= $asset->baseUrl ?>/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= $asset->baseUrl ?>/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="//fonts.googleapis.com">
    <link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="canonical" href="<?= $canonical ?>">
    <?php $this->head() ?>
  </head>
  <body<?= !empty($this->context->bodyClass) ? ' class="' . $this->context->bodyClass . '"' : '' ?>>
  <?php $this->beginBody() ?>
  <?= $content ?>
  <?php if(isset($this->params['fancybox_asset'])){
    yii\easyii\assets\FancyboxAsset::register($this); ?>
    <link href="<?= \yii\helpers\Url::to('/css/jquery.fancybox.min.css') ?>" rel="stylesheet">
  <?php }
  $this->endBody() ?>
  </body>
  </html>
<?php $this->endPage() ?>