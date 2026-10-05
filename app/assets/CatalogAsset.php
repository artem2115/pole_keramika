<?php
namespace app\assets;
class CatalogAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@app/media';
  public $js = ['js/filter.js'];
  public $depends = ['app\assets\AppAsset'];
}