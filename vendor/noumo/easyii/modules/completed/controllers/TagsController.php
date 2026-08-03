<?php
// vendor/noumo/easyii/modules/completed/controllers/TagsController.php

namespace yii\easyii\modules\completed\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\components\Controller;
use yii\easyii\modules\completed\models\CompletedTag;
use yii\widgets\ActiveForm;

class TagsController extends Controller
{
  public function actionIndex()
  {
    $data = new ActiveDataProvider([
      'query' => CompletedTag::find()->orderBy(['name' => SORT_ASC]),
    ]);

    return $this->render('index', [
      'data' => $data
    ]);
  }

  public function actionCreate()
  {
    $model = new CompletedTag;

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if ($model->save()) {
          $this->flash('success', 'Тег создан');
          return $this->redirect(['index']);
        } else {
          $this->flash('error', 'Ошибка создания тега');
        }
      }
    }

    return $this->render('create', [
      'model' => $model
    ]);
  }

  public function actionEdit($id)
  {
    $model = CompletedTag::findOne($id);

    if ($model === null) {
      $this->flash('error', 'Тег не найден');
      return $this->redirect(['index']);
    }

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if ($model->save()) {
          $this->flash('success', 'Тег обновлен');
          return $this->redirect(['index']);
        } else {
          $this->flash('error', 'Ошибка обновления тега');
        }
      }
    }

    return $this->render('edit', [
      'model' => $model
    ]);
  }

  public function actionDelete($id)
  {
    if (($model = CompletedTag::findOne($id))) {
      $model->delete();
    } else {
      $this->error = 'Тег не найден';
    }
    return $this->formatResponse('Тег удален');
  }
}