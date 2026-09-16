<?php
namespace app\models;

use Yii;
use yii\base\Model;

class FeedbackCallForm extends Model
{
  public $name;
  public $phone;

  public function rules()
  {
    return [
      [['name', 'phone'], 'required'],
      [['name', 'phone'], 'trim'],
      ['name', 'string', 'max' => 255],
      ['phone', 'match', 'pattern' => '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'name' => 'Ваше имя',
      'phone' => 'Телефон',
    ];
  }
}