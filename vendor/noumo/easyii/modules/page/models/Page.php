<?php
namespace yii\easyii\modules\page\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;

class Page extends \yii\easyii\components\ActiveRecord
{
  public static function tableName()
  {
    return '{{%pages}}';
  }

  public function rules()
  {
    return [
      [['title', 'slug'], 'required'],
      [['title', 'text'], 'trim'],
      ['title', 'string', 'max' => 255],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
      [['created_at', 'updated_at'], 'integer']
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'text' => Yii::t('easyii', 'Text'),
      'slug' => Yii::t('easyii', 'Slug'),
    ];
  }

  public function behaviors()
  {
    return [
      'timestampBehavior' => TimestampBehavior::class,
      'seoBehavior' => SeoBehavior::class,
    ];
  }
}