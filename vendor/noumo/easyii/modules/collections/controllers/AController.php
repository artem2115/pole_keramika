<?php
// vendor/noumo/easyii/modules/collections/controllers/AController.php

namespace yii\easyii\modules\collections\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\behaviors\SortableController;
use yii\easyii\behaviors\StatusController;
use yii\easyii\components\Controller;
use yii\easyii\helpers\Image;
use yii\easyii\modules\collections\models\Collection;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class AController extends Controller
{
  public function behaviors()
  {
    return [
      [
        'class' => SortableController::class,
        'model' => Collection::class,
      ],
      [
        'class' => StatusController::class,
        'model' => Collection::class,
      ],
    ];
  }

  public function actionIndex()
  {
    $data = new ActiveDataProvider([
      'query' => Collection::find()->sort(),
    ]);

    return $this->render('index', [
      'data' => $data,
    ]);
  }

  public function actionCreate()
  {
    $model = new Collection;

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if (isset($_FILES) && $this->module->settings['enableThumb']) {
          $model->image = UploadedFile::getInstance($model, 'image');
          if ($model->image && $model->validate(['image'])) {
            $model->image = Image::upload($model->image, 'collections');
          } else {
            $model->image = '';
          }
        }
        if ($model->save()) {
          $this->flash('success', 'Коллекция создана');
          return $this->redirect(['/admin/' . $this->module->id]);
        } else {
          $this->flash('error', 'Ошибка создания: {0}', $model->formatErrors());
          return $this->refresh();
        }
      }
    } else {
      if (($categoryId = Yii::$app->request->get('category_id')) && !$model->category_id) {
        $model->category_id = (int)$categoryId;
      }
      return $this->render('create', [
        'model' => $model,
      ]);
    }
  }

  public function actionEdit($id)
  {
    $model = Collection::findOne($id);

    if ($model === null) {
      $this->flash('error', Yii::t('easyii', 'Not found'));
      return $this->redirect(['/admin/' . $this->module->id]);
    }

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        if (isset($_FILES) && $this->module->settings['enableThumb']) {
          $model->image = UploadedFile::getInstance($model, 'image');
          if ($model->image && $model->validate(['image'])) {
            $model->image = Image::upload($model->image, 'collections');
          } else {
            $model->image = $model->oldAttributes['image'];
          }
        }
        if ($model->save()) {
          $this->flash('success', 'Коллекция обновлена');
        } else {
          $this->flash('error', 'Ошибка обновления: {0}', $model->formatErrors());
        }
        return $this->refresh();
      }
    } else {
      return $this->render('edit', [
        'model' => $model,
      ]);
    }
  }

  public function actionPhotos($id)
  {
    if (!($model = Collection::findOne($id))) {
      return $this->redirect(['/admin/' . $this->module->id]);
    }

    return $this->render('photos', [
      'model' => $model,
    ]);
  }

  public function actionClearImage($id)
  {
    $model = Collection::findOne($id);

    if ($model === null) {
      $this->flash('error', Yii::t('easyii', 'Not found'));
    } else {
      $model->image = '';
      if ($model->update()) {
        @unlink(Yii::getAlias('@webroot') . $model->image);
        $this->flash('success', Yii::t('easyii', 'Image cleared'));
      } else {
        $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
      }
    }
    return $this->back();
  }

  public function actionDelete($id)
  {
    if (($model = Collection::findOne($id))) {
      $model->delete();
    } else {
      $this->error = Yii::t('easyii', 'Not found');
    }
    return $this->formatResponse('Коллекция удалена');
  }

  public function actionUp($id)
  {
    return $this->move($id, 'up');
  }

  public function actionDown($id)
  {
    return $this->move($id, 'down');
  }

  public function actionOn($id)
  {
    return $this->changeStatus($id, Collection::STATUS_ON);
  }

  public function actionOff($id)
  {
    return $this->changeStatus($id, Collection::STATUS_OFF);
  }
}