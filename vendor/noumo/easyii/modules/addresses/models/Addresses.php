<?php
// vendor/noumo/easyii/modules/addresses/models/Addresses.php

namespace yii\easyii\modules\addresses\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\helpers\Slug;
use yii\easyii\models\Photo;

class Addresses extends \yii\easyii\components\ActiveRecord
{
  const STATUS_OFF = 0;
  const STATUS_ON = 1;

  public $phoneNumbers = [];
  public $emailAddresses = [];

  public static function tableName()
  {
    return '{{%addresses}}';
  }

  public function rules()
  {
    return [
      [['title'], 'required'],
      [['title', 'text'], 'trim'],
      ['title', 'string', 'max' => 255],
      [['address', 'phone', 'email', 'mode', 'coordinates'], 'string', 'max' => 1024],
      [['views', 'time', 'status'], 'integer'],
      ['time', 'default', 'value' => time()],
      ['slug', 'string', 'max' => 128],
      ['slug', 'unique'],
      ['status', 'default', 'value' => self::STATUS_ON],
      ['tagNames', 'safe'],
      [['phoneNumbers', 'emailAddresses'], 'safe'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'title' => Yii::t('easyii', 'Title'),
      'text' => Yii::t('easyii', 'Text'),
      'address' => 'Адрес',
      'phone' => 'Телефоны',
      'email' => 'Email адреса',
      'mode' => 'Режим работы',
      'coordinates' => 'Координаты для карты',
      'time' => Yii::t('easyii', 'Date'),
      'slug' => Yii::t('easyii', 'Slug'),
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
    return $this->hasMany(Photo::class, ['item_id' => 'addresses_id'])->where(['class' => self::class])->sort();
  }

  /**
   * Получить данные для карты
   * @return array|null
   */
  public function getMapData()
  {
    if(empty($this->coordinates)){
      return null;
    }

    $coords = explode(',', $this->coordinates);
    if(count($coords) !== 2){
      return null;
    }

    $lat = trim($coords[0]);
    $lon = trim($coords[1]);

    if(!$lat || !$lon){
      return null;
    }

    $phones = json_decode($this->phone, true);
    $firstPhone = is_array($phones) && !empty($phones) ? strip_tags($phones[0]) : '';

    $cleanAddress = strip_tags($this->address);
    $cleanAddress = preg_replace('/\s+/', ' ', $cleanAddress);
    $cleanAddress = trim($cleanAddress);

    return [
      'lat' => (float)$lat,
      'lon' => (float)$lon,
      'title' => $this->title,
      'address' => $cleanAddress,
      'phone' => $firstPhone
    ];
  }

  /**
   * Получить координаты в виде строки "lat,lng"
   * @return string|null
   */
  public function getCoordsString()
  {
    if(empty($this->coordinates)){
      return null;
    }

    $coords = explode(',', $this->coordinates);
    if(count($coords) !== 2){
      return null;
    }

    $lat = trim($coords[0]);
    $lon = trim($coords[1]);

    if(!$lat || !$lon){
      return null;
    }

    return $lat . ',' . $lon;
  }

  /**
   * Получить первый телефон в виде строки
   * @return string
   */
  public function getFirstPhone()
  {
    $phones = json_decode($this->phone, true);
    if(is_array($phones) && !empty($phones)){
      return strip_tags($phones[0]);
    }
    return '';
  }

  /**
   * Получить чистый адрес без HTML-тегов
   * @return string
   */
  public function getCleanAddress()
  {
    $clean = strip_tags($this->address);
    $clean = preg_replace('/\s+/', ' ', $clean);
    return trim($clean);
  }

  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      if(!empty($this->phoneNumbers)){
        $phones = [];
        foreach($this->phoneNumbers as $phone){
          $phone = trim($phone);
          if(!empty($phone)){
            $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
            $phones[] = '<a href="tel:' . $cleanPhone . '">' . $phone . '</a>';
          }
        }
        $this->phone = json_encode($phones, JSON_UNESCAPED_UNICODE);
      } else {
        $this->phone = '[]';
      }

      if(!empty($this->emailAddresses)){
        $emails = [];
        foreach($this->emailAddresses as $email){
          $email = trim($email);
          if(!empty($email)){
            $emails[] = '<a href="mailto:' . $email . '">' . $email . '</a>';
          }
        }
        $this->email = json_encode($emails, JSON_UNESCAPED_UNICODE);
      } else {
        $this->email = '[]';
      }

      Slug::createSlug($this);

      return true;
    } else {
      return false;
    }
  }

  public function afterFind()
  {
    parent::afterFind();

    if(!empty($this->phone)){
      $phones = json_decode($this->phone, true);
      if(is_array($phones)){
        foreach($phones as $phoneHtml){
          preg_match('/>([^<]+)</', $phoneHtml, $matches);
          $this->phoneNumbers[] = $matches[1] ?? $phoneHtml;
        }
      }
    }
    if(!empty($this->email)){
      $emails = json_decode($this->email, true);
      if(is_array($emails)){
        foreach($emails as $emailHtml){
          preg_match('/>([^<]+)</', $emailHtml, $matches);
          $this->emailAddresses[] = $matches[1] ?? $emailHtml;
        }
      }
    }
  }

  public function afterDelete()
  {
    parent::afterDelete();

    foreach($this->getPhotos()->all() as $photo){
      $photo->delete();
    }
  }

  /**
   * Склонение слова "магазин" по числу
   * @param int $count
   * @return string
   */
  public static function pluralizeShops($count)
  {
    $count = (int)$count;
    $mod10 = $count % 10;
    $mod100 = $count % 100;

    if ($mod10 === 1 && $mod100 !== 11) {
      return $count . ' магазин';
    }

    if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
      return $count . ' магазина';
    }

    return $count . ' магазинов';
  }
}