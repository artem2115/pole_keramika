<?php
namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\easyii\modules\feedback\models\Feedback;
use app\models\Feedback3dForm;

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

  /**
   * Модалка "Оставить заявку на 3D проект"
   */
  public function actionGet3dProjectForm()
  {
    $model = new Feedback3dForm();

    return $this->renderAjax('_modal_3d_project_form', [
      'modal_class' => 'm3',
      'model' => $model,
    ]);
  }

  /**
   * Отправка заявки на 3D проект
   * Доступно только через AJAX POST
   */
  public function actionSend3dProject()
  {
    if(!Yii::$app->request->isAjax || !Yii::$app->request->isPost){
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $model = new Feedback3dForm();

    if($model->load(Yii::$app->request->post()) && $model->validate()){
      $feedback = new Feedback();
      $feedback->name = $model->name;
      $feedback->phone = $model->phone;
      $feedback->email = $model->email;
      $feedback->title = 'Заявка на 3D проект';
      $feedback->text = 'Заявка на 3D проект от ' . $model->name . '. Телефон: ' . $model->phone . '. Email: ' . $model->email;

      if($feedback->save(false)){
        return [
          'success' => true,
          'message' => $this->renderPartial('_success_3d_project'),
        ];
      } else {
        return [
          'success' => false,
          'message' => 'Ошибка сохранения заявки. Попробуйте еще раз.'
        ];
      }
    }

    $errors = $model->getFirstErrors();
    return [
      'success' => false,
      'message' => !empty($errors) ? reset($errors) : 'Пожалуйста, заполните все поля правильно.'
    ];
  }}