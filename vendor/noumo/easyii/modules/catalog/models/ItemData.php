<?php
namespace yii\easyii\modules\catalog\models;

use Yii;

class ItemData extends \yii\easyii\components\ActiveRecord
{

  public static function tableName()
  {
    return '{{%catalog_item_data}}';
  }
}