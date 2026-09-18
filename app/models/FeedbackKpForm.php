<?php
// app/models/FeedbackKpForm.php

namespace app\models;

use Yii;
use yii\base\Model;

class FeedbackKpForm extends Model
{
  public $company;
  public $name;
  public $phone;
  public $email;

  public function rules()
  {
    return [
      [['company', 'name', 'phone'], 'required'],
      [['company', 'name'], 'string', 'max' => 255],
      ['phone', 'string', 'max' => 20],
      ['phone', 'match', 'pattern' => '/^\+7 \(\d{3}\) \d{3} \d{2} \d{2}$/', 'message' => 'Введите телефон в формате +7 (___) ___ __ __'],
      ['email', 'email'],
      ['email', 'string', 'max' => 255],
    ];
  }

  public function attributeLabels()
  {
    return [
      'company' => 'Название компании',
      'name'    => 'Ваше имя',
      'phone'   => 'Телефон',
      'email'   => 'Email',
    ];
  }
}