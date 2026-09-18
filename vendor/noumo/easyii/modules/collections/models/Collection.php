<?php
// vendor/noumo/easyii/modules/collections/models/Collection.php

namespace yii\easyii\modules\collections\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\helpers\Slug;
use yii\easyii\models\Photo;
use yii\helpers\StringHelper;

class Collection extends \yii\easyii\components\ActiveRecord
{
  const STATUS_OFF = 0;
  const STATUS_ON = 1;

  public $formatsArray = [];

  public static function tableName()
  {
    return '{{%catalog_collections}}';
  }

  public function rules()
  {
    return [
      [['title', 'category_id'], 'required'],
      [['title', 'short'], 'trim'],
      ['title', 'string', 'max' => 255],
      ['category_id', 'integer'],
      ['image', 'image'],
      [['description'], 'safe'],
      [['status', 'order_num', 'time'], 'integer'],
      ['time', 'default', 'value' => time()],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
      ['status', 'default', 'value' => self::STATUS_ON],
      ['formats', 'safe'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'category_id' => 'Категория',
      'image' => Yii::t('easyii', 'Image'),
      'description' => 'Описание',
      'formats' => 'Форматы плитки',
      'formatsArray' => 'Форматы плитки',
      'slug' => Yii::t('easyii', 'Slug'),
      'time' => Yii::t('easyii', 'Date'),
    ];
  }

  public function behaviors()
  {
    return [
      'timestampBehavior' => TimestampBehavior::class,
      'seoBehavior' => SeoBehavior::class,
    ];
  }

  public function getPhotos()
  {
    return $this->hasMany(Photo::class, ['item_id' => 'collection_id'])
      ->where(['class' => self::class])
      ->sort();
  }

  public function getCategory()
  {
    return $this->hasOne(\yii\easyii\modules\catalog\models\Category::class,
      ['category_id' => 'category_id']);
  }

  public function getItems()
  {
    return $this->hasMany(\yii\easyii\modules\catalog\models\Item::class,
      ['collection_id' => 'collection_id']);
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      // Обработка форматов — TagsInput приходит как строка с запятыми или как массив
      if (is_array($this->formats)) {
        $formats = array_filter(array_map('trim', $this->formats));
        $this->formats = json_encode(array_values($formats), JSON_UNESCAPED_UNICODE);
      } elseif (is_string($this->formats) && !empty($this->formats)) {
        $formats = array_filter(array_map('trim', explode(',', $this->formats)));
        $this->formats = json_encode(array_values($formats), JSON_UNESCAPED_UNICODE);
      } elseif (empty($this->formats)) {
        $this->formats = '[]';
      }

      if (!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']) {
        @unlink(Yii::getAlias('@webroot') . $this->oldAttributes['image']);
      }

      Slug::createSlug($this);

      return true;
    } else {
      return false;
    }
  }

  public function afterFind()
  {
    parent::afterFind();

    // Декодируем форматы в строку для TagsInput
    $this->formatsArray = [];
    if (!empty($this->formats)) {
      $decoded = json_decode($this->formats, true);
      if (is_array($decoded)) {
        $this->formatsArray = $decoded;
        // Перезаписываем formats строкой для TagsInput
        $this->formats = implode(',', $decoded);
      }
    }
  }

  public function afterDelete()
  {
    parent::afterDelete();

    if ($this->image) {
      @unlink(Yii::getAlias('@webroot') . $this->image);
    }

    foreach ($this->getPhotos()->all() as $photo) {
      $photo->delete();
    }
  }
}