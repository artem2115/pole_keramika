<?php
namespace yii\easyii\modules\completed\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\helpers\Slug;
use yii\easyii\models\Photo;
use yii\helpers\StringHelper;

class Completed extends \yii\easyii\components\ActiveRecord
{
  const STATUS_OFF = 0;
  const STATUS_ON = 1;

  public static function tableName()
  {
    return '{{%completed}}';
  }

  public function rules()
  {
    return [
      [['text', 'title'], 'required'],
      [['title', 'short', 'text'], 'trim'],
      ['title', 'string', 'max' => 255],
      ['image', 'image'],
      [['views', 'time', 'status'], 'integer'],
      ['short', 'filter', 'filter' => 'strip_tags'],
      ['time', 'default', 'value' => time()],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
      ['status', 'default', 'value' => self::STATUS_ON],
      ['tagNames', 'safe']
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'text' => Yii::t('easyii', 'Text'),
      'short' => Yii::t('easyii/completed', 'Short'),
      'image' => Yii::t('easyii', 'Image'),
      'time' => Yii::t('easyii', 'Date'),
      'slug' => Yii::t('easyii', 'Slug'),
      'tagNames' => Yii::t('easyii', 'Tags'),
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
    return $this->hasMany(Photo::class, ['item_id' => 'completed_id'])->where(['class' => self::class])->sort();
  }

  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      $settings = Yii::$app->getModule('admin')->activeModules['completed']->settings;
      $this->short = StringHelper::truncate($settings['enableShort'] ? ($this->short == '' ? strip_tags($this->text) : $this->short) : strip_tags($this->text), $settings['shortMaxLength']);

      if(!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']){
        @unlink(Yii::getAlias('@webroot') . $this->oldAttributes['image']);
      }

      Slug::createSlug($this);

      return true;
    } else {
      return false;
    }
  }

  public function afterDelete()
  {
    parent::afterDelete();

    if($this->image){
      @unlink(Yii::getAlias('@webroot') . $this->image);
    }

    foreach($this->getPhotos()->all() as $photo){
      $photo->delete();
    }
  }
}