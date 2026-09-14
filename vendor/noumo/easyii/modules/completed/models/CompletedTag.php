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
        $this->slug = $this->transliterate($this->name);
      }
      return true;
    }
    return false;
  }

  private function transliterate($string)
  {
    $string = mb_strtolower(trim($string));

    $replace = [
      'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
      'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
      'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
      'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
      'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
      'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
      'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
      ' ' => '-', '_' => '-', ',' => '', '.' => '',
    ];

    $string = strtr($string, $replace);
    $string = preg_replace('/[^a-z0-9\-]/', '', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');

    return $string;
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