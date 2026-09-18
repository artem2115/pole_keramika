<?php
namespace yii\easyii\modules\collections\models;

use Yii;

class ItemData extends \yii\easyii\components\ActiveRecord
{

  public static function tableName()
  {
    return '{{%collections_item_data}}';
  }
}