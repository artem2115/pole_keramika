<?php
namespace yii\easyii\modules\catalog\models;

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
    return '{{%catalog_items}}';
  }

  public function rules()
  {
    return [
      ['title', 'required'],
      ['title', 'trim'],
      ['title', 'string', 'max' => 256],
      ['image', 'image'],
      ['description', 'safe'],
      [['price', 'old_price'], 'number'],
      ['discount', 'integer', 'max' => 99],
      ['discount', 'default', 'value' => 0],
      [['status', 'category_id', 'available', 'time', 'collection_id'], 'integer'],
      [['data'], 'safe'],
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
      'available' => Yii::t('easyii/catalog', 'Available'),
      'price' => Yii::t('easyii/catalog', 'Price'),
      'discount' => Yii::t('easyii/catalog', 'Discount'),
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

  public function getCollection()
  {
    return $this->hasOne(\yii\easyii\modules\collections\models\Collection::class,
      ['collection_id' => 'collection_id']);
  }

  /**
   * Получить значение поля из data
   * @param string $name
   * @return string|null
   */
  public function getDataValue($name)
  {
    $value = ItemData::find()
      ->where(['item_id' => $this->item_id, 'name' => $name])
      ->select(['value'])
      ->scalar();

    return $value ?: null;
  }

  /**
   * Получить data для нескольких товаров одним запросом
   * @param array $itemIds
   * @param array $names — какие поля нужны (пусто = все)
   * @return array — [item_id => [name => [values]]]
   */
  public static function getDataForItems($itemIds, $names = [])
  {
    if (empty($itemIds)) {
      return [];
    }

    $query = ItemData::find()
      ->where(['item_id' => $itemIds])
      ->select(['item_id', 'name', 'value'])
      ->asArray();

    if (!empty($names)) {
      $query->andWhere(['name' => $names]);
    }

    $rows = $query->all();
    $result = [];

    foreach ($rows as $row) {
      $result[$row['item_id']][$row['name']][] = $row['value'];
    }

    return $result;
  }

  /**
   * Все изображения товара: главная image + photos
   * @return array
   */
  public function getAllImages()
  {
    $images = [];

    // Сначала главное изображение
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

  /**
   * Характеристики товара для таблицы (без boolean-флагов)
   * @return array — [title => value, ...]
   */
  public function getCharacteristics()
  {
    $result = [];

    if (empty($this->category->fields)) {
      return $result;
    }

    // Собираем data одним запросом
    $rows = ItemData::find()
      ->where(['item_id' => $this->item_id])
      ->select(['name', 'value'])
      ->asArray()
      ->all();

    // Группируем по name
    $dataMap = [];
    foreach ($rows as $row) {
      $dataMap[$row['name']][] = $row['value'];
    }

    // Проходим по полям категории в нужном порядке
    foreach ($this->category->fields as $field) {
      // Пропускаем boolean-флаги
      if ($field->type === 'boolean') {
        continue;
      }

      $values = $dataMap[$field->name] ?? [];
      if (empty($values)) {
        continue;
      }

      // Для color убираем HEX после двоеточия
      if ($field->name === 'color') {
        $values = array_map(function($v) {
          return explode(':', $v, 2)[0];
        }, $values);
      }

      // checkbox — объединяем через запятую
      if (is_array($values) && count($values) > 1) {
        $result[$field->title] = implode(', ', $values);
      } else {
        $result[$field->title] = reset($values);
      }
    }

    return $result;
  }

  public function seo($attribute, $default = '')
  {
    return !empty($this->seo->{$attribute}) ? $this->seo->{$attribute} : $default;
  }

}