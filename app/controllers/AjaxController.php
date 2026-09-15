<?php
namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;

class AjaxController extends \yii\web\Controller
{
  public $enableCsrfValidation = true;

  public function beforeAction($action)
  {
    if(!parent::beforeAction($action)){
      return false;
    }

    $excludedActions = ['resolveRoundByHash'];

    if(!in_array($action->id, $excludedActions) && !Yii::$app->request->isAjax){
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    return true;
  }

  /**
   * Модалка "Заказать дизайн-проект"
   */
  public function actionGetModal3dProject()
  {
    return $this->renderAjax('_modal_3d_project', [
      'modal_class' => 'm3'
    ]);
  }
}