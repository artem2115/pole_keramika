<?php
// vendor/noumo/easyii/modules/completed/models/Completed.php

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

  public $selectedTags = [];

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
      ['selectedTags', 'safe'],
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
      'selectedTags' => 'Теги',
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

  /**
   * Связь с тегами через промежуточную таблицу
   */
  public function getTags()
  {
    return $this->hasMany(CompletedTag::class, ['tag_id' => 'tag_id'])
      ->viaTable('{{%completed_tag_rel}}', ['completed_id' => 'completed_id']);
  }

  /**
   * Связь с промежуточной таблицей
   */
  public function getCompletedTagRels()
  {
    return $this->hasMany(CompletedTagRel::class, ['completed_id' => 'completed_id']);
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      $settings = Yii::$app->getModule('admin')->activeModules['completed']->settings;
      $this->short = StringHelper::truncate($settings['enableShort'] ? ($this->short == '' ? strip_tags($this->text) : $this->short) : strip_tags($this->text), $settings['shortMaxLength']);

      if (!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']) {
        @unlink(Yii::getAlias('@webroot') . $this->oldAttributes['image']);
      }

      Slug::createSlug($this);

      return true;
    } else {
      return false;
    }
  }

  /**
   * После сохранения обновляем связи с тегами
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    // Удаляем старые связи
    CompletedTagRel::deleteAll(['completed_id' => $this->completed_id]);

    // Создаем новые связи
    if (!empty($this->selectedTags)) {
      foreach ($this->selectedTags as $tagId) {
        $rel = new CompletedTagRel();
        $rel->completed_id = $this->completed_id;
        $rel->tag_id = (int)$tagId;
        $rel->save();
      }
    }
  }

  /**
   * После загрузки из БД заполняем selectedTags
   */
  public function afterFind()
  {
    parent::afterFind();

    $this->selectedTags = [];
    foreach ($this->getTags()->all() as $tag) {
      $this->selectedTags[] = $tag->tag_id;
    }
  }

  /**
   * Перед удалением удаляем связи и фото
   */
  public function beforeDelete()
  {
    if (!parent::beforeDelete()) {
      return false;
    }

    // Удаляем связи с тегами через модель
    CompletedTagRel::deleteAll(['completed_id' => $this->completed_id]);

    return true;
  }

  /**
   * После удаления удаляем фото и изображение
   */
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
   * Получить проекты по тегу
   * @param int $tagId
   * @return \yii\db\ActiveQuery
   */
  public static function findByTag($tagId)
  {
    return self::find()
      ->innerJoin('{{%completed_tag_rel}}', '{{%completed}}.[[completed_id]] = {{%completed_tag_rel}}.[[completed_id]]')
      ->where(['{{%completed_tag_rel}}.[[tag_id]]' => $tagId])
      ->andWhere(['{{%completed}}.[[status]]' => self::STATUS_ON])
      ->groupBy('{{%completed}}.[[completed_id]]');
  }
}