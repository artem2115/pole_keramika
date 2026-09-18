<?php
// app/models/FeedbackConditionsForm.php

namespace app\models;

use yii\base\Model;

class FeedbackConditionsForm extends Model
{
  public $name;
  public $phone;
  public $email;

  public function rules()
  {
    return [
      [['name', 'phone'], 'required'],
      ['name', 'string', 'max' => 255],
      ['phone', 'string', 'max' => 20],
      ['phone', 'match', 'pattern' => '/^\+7 \(\d{3}\) \d{3} \d{2} \d{2}$/', 'message' => 'Введите телефон в формате +7 (___) ___ __ __'],
      ['email', 'email'],
      ['email', 'string', 'max' => 255],
    ];
  }

  public function attributeLabels()
  {
    return [
      'name'  => 'Ваше имя',
      'phone' => 'Телефон',
      'email' => 'Email',
    ];
  }
}