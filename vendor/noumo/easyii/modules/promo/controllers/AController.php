<?php
namespace yii\easyii\modules\promo\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\behaviors\StatusController;
use yii\easyii\components\Controller;
use yii\easyii\modules\promo\models\Promo;
use yii\widgets\ActiveForm;

class AController extends Controller
{
  public function behaviors()
  {
    return [
      [
        'class' => StatusController::class,
        'model' => Promo::class,
      ],
    ];
  }

  public function actionIndex()
  {
    $data = new ActiveDataProvider([
      'query' => Promo::find()->orderBy(['created_at' => SORT_DESC]),
    ]);

    return $this->render('index', [
      'data' => $data,
    ]);
  }

  public function actionCreate()
  {
    $model = new Promo;

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if ($model->save()) {
          $this->flash('success', 'Промокод создан');
          return $this->redirect(['index']);
        } else {
          $this->flash('error', 'Ошибка создания: {0}', $model->formatErrors());
          return $this->refresh();
        }
      }
    }

    return $this->render('create', [
      'model' => $model,
    ]);
  }

  public function actionEdit($id)
  {
    $model = Promo::findOne($id);

    if ($model === null) {
      $this->flash('error', Yii::t('easyii', 'Not found'));
      return $this->redirect(['index']);
    }

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if ($model->save()) {
          $this->flash('success', 'Промокод обновлён');
        } else {
          $this->flash('error', 'Ошибка обновления: {0}', $model->formatErrors());
        }
        return $this->refresh();
      }
    }

    return $this->render('edit', [
      'model' => $model,
    ]);
  }

  public function actionDelete($id)
  {
    if (($model = Promo::findOne($id))) {
      $model->delete();
    } else {
      $this->error = Yii::t('easyii', 'Not found');
    }
    return $this->formatResponse('Промокод удалён');
  }

  public function actionOn($id)
  {
    return $this->changeStatus($id, Promo::STATUS_ON);
  }

  public function actionOff($id)
  {
    return $this->changeStatus($id, Promo::STATUS_OFF);
  }
}