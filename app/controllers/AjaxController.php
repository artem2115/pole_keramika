<?php
namespace app\controllers;

use app\models\AppForm;
use app\models\CallForm;
use app\models\ConsultForm;
use app\models\PhotoForm;
use Yii;
use yii\easyii\modules\catalogout\models\MaterialTable;
use yii\easyii\modules\feedback\models\Feedback;
use yii\easyii\modules\page\models\VideoInstruction;
use yii\easyii\modules\subscribe\models\Subscriber;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;


class AjaxController extends \yii\web\Controller
{
  public function beforeAction($action)
  {
    if(!parent::beforeAction($action)){
      return false;
    }

    // Для всех действий, кроме указанных, проверяем AJAX
    $excludedActions = ['resolveRoundByHash'];

    if(!in_array($action->id, $excludedActions) && !Yii::$app->request->isAjax){
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    return true;
  }


}