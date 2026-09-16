<?php
namespace yii\easyii\modules\feedback\models;

use Yii;
use yii\easyii\behaviors\CalculateNotice;
use yii\easyii\helpers\Mail;
use yii\easyii\models\Setting;
use yii\easyii\validators\EscapeValidator;
use yii\easyii\validators\ReCaptchaValidator;
use yii\helpers\Url;

class Feedback extends \yii\easyii\components\ActiveRecord
{
  const STATUS_NEW = 0;
  const STATUS_VIEW = 1;
  const STATUS_ANSWERED = 2;

  const FLASH_KEY = 'eaysiicms_feedback_send_result';

  //public $reCaptcha;

  public static function tableName()
  {
    return '{{%feedback}}';
  }

  public function rules()
  {
    return [
      //[['name', 'email', 'text'], 'required'],
      //[['name', 'email', 'phone', 'title', 'text'], 'trim'],
      //[['name', 'title', 'text'], EscapeValidator::class],
      //['title', 'string', 'max' => 128],
      //['email', 'email'],
      //['phone', 'match', 'pattern' => '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/'],
    ];
  }

  public function beforeSave($insert)
  {
    if(parent::beforeSave($insert)){
      if($insert){
        $this->ip = Yii::$app->request->userIP;
        $this->time = time();
        $this->status = self::STATUS_NEW;
      }
      return true;
    } else {
      return false;
    }
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    if($insert){
      //  $this->telegramSend();
      //$this->mailAdmin();
    }
  }

  public function behaviors()
  {
    return [
      'cn' => [
        'class' => CalculateNotice::class,
        'callback' => function (){
          return self::find()->status(self::STATUS_NEW)->count();
        }
      ]
    ];
  }

  public function getPhotosArray(): array
  {
    if(!empty($this->photos)){
      return explode(',', $this->photos);
    }
    return [];
  }

  public function getPhotosLinks(): string
  {
    $photos = $this->getPhotosArray();
    $links = [];

    foreach($photos as $photo){
      $photo = trim($photo);
      if(!empty($photo)){
        $links[] = '<a href="' . $photo . '" target="_blank">' . basename($photo) . '</a>';
      }
    }

    return implode('<br>', $links);
  }

  public function mailAdmin()
  {
    $settings = Yii::$app->getModule('admin')->activeModules['feedback']->settings;

    if(!$settings['mailAdminOnNewFeedback']){
      return false;
    }
    return Mail::send(
      Setting::get('admin_email'),
      $settings['subjectOnNewFeedback'] . ' ' . $this->title,
      $settings['templateOnNewFeedback'],
      ['feedback' => $this, 'link' => Url::to(['/admin/feedback/a/view', 'id' => $this->primaryKey], true)]
    );
  }

  public function sendAnswer()
  {
    $settings = Yii::$app->getModule('admin')->activeModules['feedback']->settings;

    return Mail::send(
      $this->email,
      $this->answer_subject,
      $settings['answerTemplate'],
      ['feedback' => $this],
      ['replyTo' => Setting::get('admin_email')]
    );
  }


  public function telegramSend()
  {
    $token = (string)Setting::get('tg_token');
    $chat_id = (string)Setting::get('tg_chat_id');
    if(empty($token) || empty($chat_id)) return null;
    $n = "%0A";

    $fields = [
      'Заголовок' => $this->title,
      'Имя' => $this->name,
      'Страница услуги' => $this->from_service,
      'Email' => $this->email,
      'Тел.' => $this->phone,
      'Вопрос' => strip_tags($this->question) . strip_tags($this->text),
      'Промокод' => strip_tags(urlencode($this->coupon_code)),
      'Сайт пользователя' => strip_tags($this->website),
      'Выбрана ниша' => strip_tags(urlencode($this->ch_item)),
      'Отправлено со страницы' => Yii::$app->request->hostInfo . $this->from_page_url,
    ];

    $txt = '';
    foreach($fields as $key => $value){
      if(!empty($value)) $txt .= "<b>" . $key . ":</b> $value" . $n;
    }
    $txt = str_replace(
      ['&laquo;', '&raquo;', '&quot;', '"', '(', ')', '+', '/', '_', '*', '[', ']', '~', '`', '>', '#', '-', '=', '|', '{', '}', '.', '!', '$', '&', '?'],
      ['%22', '%22', '%22', '%22', '%28', '%29', '%2B', '%2F', '%5F', '%2A', '%5B', '%5D', '%7E', '%60', '%3E', '%23', '%2D', '%3D', '%7C', '%7B', '%7D', '%2E', '%21', '%24', '%26', '%3F'], $txt);

    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&parse_mode=html&text=$txt";
    $response = @file_get_contents($url);

    if(!$response){
      for($i = 0; $i < 10; $i++){
        $response = @file_get_contents($url);
        if($response && json_decode($response)->{'ok'} == 1) break;
        sleep(1);
      }
    }
  }

}