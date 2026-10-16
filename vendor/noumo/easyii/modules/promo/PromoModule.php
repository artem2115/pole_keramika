<?php
namespace yii\easyii\modules\promo;

class PromoModule extends \yii\easyii\components\Module
{
  public $settings = [
    // пусто
  ];

  public static $installConfig = [
    'title' => [
      'en' => 'Promo codes',
      'ru' => 'Промокоды',
    ],
    'icon' => 'gift',
    'order_num' => 130,
  ];
}