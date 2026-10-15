<?php
namespace app\assets;

class AppAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@app/media';
  public $css = [
    'css/style.css',
  ];
  public $js = [
    'js/slick1.8.0.js',
    'js/site.js',
    'js/basket.js',
  ];
  public $depends = [
    'app\assets\IESupportAsset',
    'yii\web\JqueryAsset',
  ];
}