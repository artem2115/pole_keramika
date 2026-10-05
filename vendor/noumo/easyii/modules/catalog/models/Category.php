<?php
namespace yii\easyii\modules\catalog\models;

class Category extends \yii\easyii\components\CategoryModel
{
  static $fieldTypes = [
    'string' => 'String',
    'text' => 'Text',
    'boolean' => 'Boolean',
    'select' => 'Select',
    'checkbox' => 'Checkbox'
  ];

  public static function tableName()
  {
    return '{{%catalog_categories}}';
  }

  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      if($insert && ($parent = $this->parents(1)->one())){
        $this->fields = $parent->fields;
      }

      if(!$this->fields || !is_array($this->fields)){
        $this->fields = [];
      }
      $this->fields = json_encode($this->fields, JSON_UNESCAPED_UNICODE);

      return true;
    } else {
      return false;
    }
  }

  /**
   * Коллекции этой категории
   * @return \yii\db\ActiveQuery
   */
  public function getCollections()
  {
    return $this->hasMany(\yii\easyii\modules\collections\models\Collection::class,
      ['category_id' => 'category_id']);
  }

  public function afterSave($insert, $attributes)
  {
    parent::afterSave($insert, $attributes);
    $this->parseFields();
  }

  public function afterFind()
  {
    parent::afterFind();
    $this->parseFields();
  }

  public function getItems()
  {
    return $this->hasMany(Item::class, ['category_id' => 'category_id'])->sortDate();
  }

  public function afterDelete()
  {
    parent::afterDelete();

    foreach($this->getItems()->all() as $item){
      $item->delete();
    }
  }

  private function parseFields()
  {
    $this->fields = $this->fields !== '' ? json_decode($this->fields) : [];
  }

  public function seo($attribute, $default = '')
  {
    return !empty($this->seo->{$attribute}) ? $this->seo->{$attribute} : $default;
  }
}