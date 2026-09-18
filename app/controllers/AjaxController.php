<?php
namespace app\controllers;

use app\models\FeedbackCallForm;
use app\models\FeedbackConditionsForm;
use app\models\FeedbackKpForm;
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
      'modal_class' => 'm_bnr'
    ]);
  }

  /**
   * Модалка "Оставить заявку на 3D проект"
   */
  public function actionGet3dProjectForm()
  {
    $model = new Feedback3dForm();

    return $this->renderAjax('_modal_3d_project_form', [
      'modal_class' => '',
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
  }

  /**
   * Модалка "Обратная связь" (Заказать звонок)
   */
  public function actionGetFeedbackForm()
  {
    $model = new FeedbackCallForm();

    return $this->renderAjax('_modal_feedback_call', [
      'modal_class' => '',
      'model' => $model,
    ]);
  }

  /**
   * Отправка заявки "Заказать звонок"
   */
  public function actionSendFeedbackCall()
  {
    if(!Yii::$app->request->isAjax || !Yii::$app->request->isPost){
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $model = new FeedbackCallForm();

    if($model->load(Yii::$app->request->post()) && $model->validate()){
      $feedback = new Feedback();
      $feedback->name = $model->name;
      $feedback->phone = $model->phone;
      $feedback->title = 'Заказать звонок';
      $feedback->text = 'Заказать звонок от ' . $model->name . '. Телефон: ' . $model->phone;

      if($feedback->save(false)){
        return [
          'success' => true,
          'message' => $this->renderPartial('_success_feedback_call'),
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
  }

  /**
   * Модалка «Получить КП»
   */
  public function actionGetKpForm()
  {
    $model = new FeedbackKpForm();

    return $this->renderAjax('_modal_kp_form', [
      'modal_class' => '',
      'model' => $model,
    ]);
  }

  /**
   * Отправка заявки на КП
   */
  public function actionSendKp()
  {
    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $model = new FeedbackKpForm();

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
      $feedback = new Feedback();
      $feedback->name = $model->name;
      $feedback->phone = $model->phone;
      $feedback->email = $model->email;
      $feedback->title = 'Заявка на КП';
      $feedback->text = 'Заявка на КП от ' . $model->company . '. Контакт: ' . $model->name
        . '. Телефон: ' . $model->phone
        . ($model->email ? '. Email: ' . $model->email : '');

      if ($feedback->save(false)) {
        return [
          'success' => true,
          'message' => $this->renderPartial('_success_kp'),
        ];
      }

      return [
        'success' => false,
        'message' => 'Ошибка сохранения заявки. Попробуйте еще раз.',
      ];
    }

    $errors = $model->getFirstErrors();
    return [
      'success' => false,
      'message' => !empty($errors) ? reset($errors) : 'Пожалуйста, заполните все поля правильно.',
    ];
  }

  /**
   * Модалка «Получить условия»
   */
  public function actionGetConditionsForm()
  {
    $model = new FeedbackConditionsForm();

    return $this->renderAjax('_modal_conditions_form', [
      'modal_class' => '',
      'model' => $model,
    ]);
  }

  /**
   * Отправка заявки на партнёрские условия
   */
  public function actionSendConditions()
  {
    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $model = new FeedbackConditionsForm();

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
      $feedback = new Feedback();
      $feedback->name = $model->name;
      $feedback->phone = $model->phone;
      $feedback->email = $model->email;
      $feedback->title = 'Заявка на партнёрские условия';
      $feedback->text = 'Заявка на партнёрские условия от ' . $model->name
        . '. Телефон: ' . $model->phone
        . ($model->email ? '. Email: ' . $model->email : '');

      if ($feedback->save(false)) {
        return [
          'success' => true,
          'message' => $this->renderPartial('_success_conditions'),
        ];
      }

      return [
        'success' => false,
        'message' => 'Ошибка сохранения заявки. Попробуйте еще раз.',
      ];
    }

    $errors = $model->getFirstErrors();
    return [
      'success' => false,
      'message' => !empty($errors) ? reset($errors) : 'Пожалуйста, заполните все поля правильно.',
    ];
  }

}