<?php
namespace yii\easyii\modules\collections\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\models\Photo;

class Item extends \yii\easyii\components\ActiveRecord
{
  const STATUS_OFF = 0;
  const STATUS_ON = 1;

  public static function tableName()
  {
    return '{{%collections_items}}';
  }

  public function rules()
  {
    return [
      ['title', 'required'],
      ['title', 'trim'],
      ['title', 'string', 'max' => 256],
      ['image', 'image'],
      ['description', 'safe'],
      ['price', 'number'],
      ['discount', 'integer', 'max' => 99],
      [['status', 'category_id', 'available', 'time'], 'integer'],
      ['time', 'default', 'value' => time()],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
      ['status', 'default', 'value' => self::STATUS_ON],
      [['created_at', 'updated_at'], 'integer']
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'image' => Yii::t('easyii', 'Image'),
      'description' => Yii::t('easyii', 'Description'),
      'available' => Yii::t('easyii/collections', 'Available'),
      'price' => Yii::t('easyii/collections', 'Price'),
      'discount' => Yii::t('easyii/collections', 'Discount'),
      'time' => Yii::t('easyii', 'Date'),
      'slug' => Yii::t('easyii', 'Slug'),
    ];
  }

  public function behaviors()
  {
    return [
      'timestampBehavior' => TimestampBehavior::class,
      'seoBehavior' => SeoBehavior::class,
      'sluggable' => [
        'class' => SluggableBehavior::class,
        'attribute' => 'title',
        'ensureUnique' => true
      ]
    ];
  }

  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      if(!$this->data || (!is_object($this->data) && !is_array($this->data))){
        $this->data = new \stdClass();
      }

      $this->data = json_encode($this->data);

      if(!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']){
        @unlink(Yii::getAlias('@webroot') . $this->oldAttributes['image']);
      }

      return true;
    } else {
      return false;
    }
  }

  public function afterSave($insert, $attributes)
  {
    parent::afterSave($insert, $attributes);

    $this->parseData();

    ItemData::deleteAll(['item_id' => $this->primaryKey]);

    foreach($this->data as $name => $value){
      if(!is_array($value)){
        $this->insertDataValue($name, $value);
      } else {
        foreach($value as $arrayItem){
          $this->insertDataValue($name, $arrayItem);
        }
      }
    }
  }

  private function insertDataValue($name, $value)
  {
    Yii::$app->db->createCommand()->insert(ItemData::tableName(), [
      'item_id' => $this->primaryKey,
      'name' => $name,
      'value' => $value
    ])->execute();
  }

  public function afterFind()
  {
    parent::afterFind();
    $this->parseData();
  }

  public function getPhotos()
  {
    return $this->hasMany(Photo::class, ['item_id' => 'item_id'])->where(['class' => self::class])->sort();
  }

  public function getCategory()
  {
    return $this->hasOne(Category::class, ['category_id' => 'category_id']);
  }

  public function afterDelete()
  {
    parent::afterDelete();

    foreach($this->getPhotos()->all() as $photo){
      $photo->delete();
    }

    if($this->image){
      @unlink(Yii::getAlias('@webroot') . $this->image);
    }

    ItemData::deleteAll(['item_id' => $this->primaryKey]);
  }

  private function parseData()
  {
    $this->data = $this->data !== '' ? json_decode($this->data) : [];
  }
}