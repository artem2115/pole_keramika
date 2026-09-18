<?php
namespace yii\easyii\modules\collections;

class CollectionsModule extends \yii\easyii\components\Module
{
  public $settings = [
    'enableThumb' => true,
    'enablePhotos' => true,
    'enableShort' => true,
    'shortMaxLength' => 256,
  ];

  public static $installConfig = [
    'title' => [
      'en' => 'Collections',
      'ru' => 'Коллекции',
    ],
    'icon' => 'th-large',
    'order_num' => 110,
  ];
}