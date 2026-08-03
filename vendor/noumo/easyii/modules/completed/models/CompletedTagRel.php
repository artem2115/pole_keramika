<?php
// vendor/noumo/easyii/modules/completed/models/CompletedTagRel.php

namespace yii\easyii\modules\completed\models;

use Yii;

class CompletedTagRel extends \yii\db\ActiveRecord
{
  public static function tableName()
  {
    return '{{%completed_tag_rel}}';
  }

  public function rules()
  {
    return [
      [['completed_id', 'tag_id'], 'required'],
      [['completed_id', 'tag_id'], 'integer'],
      [['completed_id', 'tag_id'], 'unique', 'targetAttribute' => ['completed_id', 'tag_id']],
    ];
  }

  public function getCompleted()
  {
    return $this->hasOne(Completed::class, ['completed_id' => 'completed_id']);
  }

  public function getTag()
  {
    return $this->hasOne(CompletedTag::class, ['tag_id' => 'tag_id']);
  }
}