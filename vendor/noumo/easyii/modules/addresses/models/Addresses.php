<?php
namespace yii\easyii\modules\addresses\models;

use app\models\Comments;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\behaviors\Taggable;
//use yii\easyii\helpers\AddNofollow;
use yii\easyii\helpers\Slug;
use yii\easyii\models\Photo;
use yii\helpers\StringHelper;

class Addresses extends \yii\easyii\components\ActiveRecord
{
  const STATUS_OFF = 0;
  const STATUS_ON = 1;

  public static function tableName()
  {
    return '{{%addresses}}';
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
      /*['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
      ['slug', 'default', 'value' => null],*/
      ['status', 'default', 'value' => self::STATUS_ON],
      ['tagNames', 'safe']
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'text' => Yii::t('easyii', 'Text'),
      'short' => Yii::t('easyii/addresses', 'Short'),
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
      'taggabble' => Taggable::class,
      'sluggable' => [
        'class' => SluggableBehavior::class,
        'attribute' => 'title',
        'ensureUnique' => true
      ],
    ];
  }

  public function getPhotos()
  {
    return $this->hasMany(Photo::class, ['item_id' => 'addresses_id'])->where(['class' => self::class])->sort();
  }


  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      $settings = Yii::$app->getModule('admin')->activeModules['addresses']->settings;
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