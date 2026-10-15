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

  /**
   * Доступные стили коллекций
   */
  public static $styles = [
    'classic' => 'Классика',
    'loft' => 'Лофт',
    'modern' => 'Модерн',
    'provence' => 'Прованс',
    'scandi' => 'Скандинавский',
    'contemporary' => 'Современный',
    'eco' => 'Экостиль',
    'minimalism' => 'Минимализм',
  ];

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
      ['short', 'string', 'max' => 1024],
      ['style', 'string', 'max' => 64],
      ['style', 'in', 'range' => array_keys(self::$styles)],
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
      'style' => 'Стиль',
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

  /**
   * Все форматы (размеры) из товаров коллекции
   * @return array
   */
  public function getFormatsList()
  {
    $sizes = \yii\easyii\modules\catalog\models\ItemData::find()
      ->alias('d')
      ->innerJoin(['i' => \yii\easyii\modules\catalog\models\Item::tableName()], 'i.item_id = d.item_id')
      ->where(['i.collection_id' => $this->collection_id])
      ->andWhere(['d.name' => 'size'])
      ->andWhere(['i.status' => \yii\easyii\modules\catalog\models\Item::STATUS_ON])
      ->select(['d.value'])
      ->distinct()
      ->column();

    return array_filter($sizes);
  }

  /**
   * Минимальная цена среди товаров коллекции
   * @return float
   */
  public function getMinPrice()
  {
    return (float) \yii\easyii\modules\catalog\models\Item::find()
      ->where(['collection_id' => $this->collection_id])
      ->andWhere(['status' => \yii\easyii\modules\catalog\models\Item::STATUS_ON])
      ->andWhere(['>', 'price', 0])
      ->min('price');
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      // Генерация short из description, если short не задан
      if (empty($this->short) && !empty($this->description)) {
        $cleanText = strip_tags($this->description);
        $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);
        $cleanText = trim($cleanText);

        $this->short = StringHelper::truncate($cleanText, 200, '…');
      }

      // Обработка форматов
      if (is_array($this->formats)) {
        $formats = array_filter(array_map('trim', $this->formats));
        $this->formats = json_encode(array_values($formats), JSON_UNESCAPED_UNICODE);
      } elseif (is_string($this->formats) && !empty($this->formats)) {
        $formats = array_filter(array_map('trim', explode(',', $this->formats)));
        $this->formats = json_encode(array_values($formats), JSON_UNESCAPED_UNICODE);
      } elseif (empty($this->formats)) {
        $this->formats = '[]';
      }

      if ($insert && empty($this->time)) {
        $this->time = time();
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


  /**
   * Все изображения коллекции: главная image + photos
   * @return array
   */
  public function getAllImages()
  {
    $images = [];

    // Сначала главное изображение (если есть)
    if (!empty($this->image)) {
      $images[] = $this->image;
    }

    // Затем фото из галереи
    foreach ($this->getPhotos()->all() as $photo) {
      if (!empty($photo->image)) {
        $images[] = $photo->image;
      }
    }

    return $images;
  }

  public function seo($attribute, $default = '')
  {
    return !empty($this->seo->{$attribute}) ? $this->seo->{$attribute} : $default;
  }
}