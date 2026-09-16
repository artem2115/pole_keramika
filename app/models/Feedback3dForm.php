<?php
// app/models/Feedback3dForm.php

namespace app\models;

use yii\base\Model;

class Feedback3dForm extends Model
{
  public $name;
  public $phone;
  public $email;

  public function rules()
  {
    return [
      [['name', 'phone', 'email'], 'required'],
      [['name', 'phone', 'email'], 'trim'],
      ['name', 'string', 'max' => 255],
      ['phone', 'match', 'pattern' => '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/'],
      ['email', 'email'],
      ['email', 'string', 'max' => 255],
    ];
  }

  public function attributeLabels()
  {
    return [
      'name' => 'Ваше имя',
      'phone' => 'Телефон',
      'email' => 'Email',
    ];
  }
}