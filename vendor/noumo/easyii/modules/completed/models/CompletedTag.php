<?php
// vendor/noumo/easyii/modules/completed/models/CompletedTag.php

namespace yii\easyii\modules\completed\models;

use Yii;

class CompletedTag extends \yii\easyii\components\ActiveRecord
{
  public static function tableName()
  {
    return '{{%completed_tags}}';
  }

  public function rules()
  {
    return [
      ['name', 'required'],
      ['name', 'string', 'max' => 255],
      ['name', 'trim'],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'name' => 'Название тега',
      'slug' => 'Slug',
    ];
  }

  public function behaviors()
  {
    return [
      'timestampBehavior' => \yii\behaviors\TimestampBehavior::class,
    ];
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      if (empty($this->slug)) {
        // Генерируем slug из name
        $slug = mb_strtolower(trim($this->name));
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/u', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $this->slug = $slug;
      }
      return true;
    }
    return false;
  }

  /**
   * Связь с проектами через промежуточную таблицу
   */
  public function getCompleted()
  {
    return $this->hasMany(Completed::class, ['completed_id' => 'completed_id'])
      ->viaTable('{{%completed_tag_rel}}', ['tag_id' => 'tag_id']);
  }

  /**
   * Связь с промежуточной таблицей
   */
  public function getCompletedTagRels()
  {
    return $this->hasMany(CompletedTagRel::class, ['tag_id' => 'tag_id']);
  }

  /**
   * Перед удалением тега удаляем все связи
   */
  public function beforeDelete()
  {
    if (!parent::beforeDelete()) {
      return false;
    }

    CompletedTagRel::deleteAll(['tag_id' => $this->tag_id]);

    return true;
  }
}