<?php
namespace yii\easyii\modules\collections\assets;

class FieldsAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@easyii/modules/collections/media';
  public $css = [
    'css/fields.css',
  ];
  public $js = [
    'js/fields.js'
  ];
  public $depends = [
    'yii\web\JqueryAsset',
  ];
}
