<?php
namespace app\assets;
class MapAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@app/media';
  public $js = [
    '//api-maps.yandex.ru/2.1/?lang=ru_RU',
    'js/map.js'
  ];
  public $depends = ['app\assets\AppAsset'];
}