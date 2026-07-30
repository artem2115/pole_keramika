<?php

namespace app\controllers;

use Yii;
use yii\easyii\helpers\MetaWidget;
use yii\easyii\modules\page\api\Page;
use yii\easyii\modules\page\models\Page as PageModel;
use yii\web\Controller;
use yii\easyii\modules\addresses\models\Addresses;

class SiteController extends Controller
{
  public $bodyClass;

  public function actions()
  {
    return [
      'error' => [
        'class' => 'yii\web\ErrorAction',
      ],
    ];
  }

  public function actionIndex()
  {
    $this->bodyClass = 'main';
    $page = Page::get('page-index');
    MetaWidget::widget(['model' => $page]);

    return $this->render('index', [
      'page' => $page
    ]);
  }

  public function actionContacts()
  {
    $page = Page::get('page-contacts');
    MetaWidget::widget(['model' => $page]);
    $page_c = PageModel::findOne(['slug' => 'page-contacts']);

    return $this->render('contacts', [
      'page' => $page,
      'contacts' => $page_c ? $page_c->getContacts() : [],
      'addresses' => Addresses::find()->where(['status' => Addresses::STATUS_ON])->all()
    ]);
  }
}