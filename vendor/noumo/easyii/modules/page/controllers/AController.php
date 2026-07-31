<?php
namespace yii\easyii\modules\page\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\components\Controller;
use yii\easyii\modules\page\models\ContactForm;
use yii\easyii\modules\page\models\Page;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;

class AController extends Controller
{
  public $rootActions = ['create', 'delete'];

  public function actionIndex()
  {
    $data = new ActiveDataProvider([
      'query' => Page::find()->desc()
    ]);
    return $this->render('index', [
      'data' => $data
    ]);
  }

  public function actionCreate($slug = null)
  {
    $model = new Page;

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        // Загрузка изображения
        $imageFile = UploadedFile::getInstance($model, 'image');
        if ($imageFile) {
          $model->image = $this->uploadImage($imageFile);
        }

        if ($model->save()) {
          $this->flash('success', Yii::t('easyii/page', 'Page created'));
          return $this->redirect(['/admin/' . $this->module->id]);
        } else {
          $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
          return $this->refresh();
        }
      }
    } else {
      if ($slug) $model->slug = $slug;

      return $this->render('create', [
        'model' => $model
      ]);
    }
  }

  public function actionEdit($id)
  {
    $model = Page::findOne($id);

    if ($model === null) {
      $this->flash('error', Yii::t('easyii', 'Not found'));
      return $this->redirect(['/admin/' . $this->module->id]);
    }

    $oldImage = $model->image;

    if ($model->load(Yii::$app->request->post())) {
      if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      } else {
        // Загрузка изображения
        $imageFile = UploadedFile::getInstance($model, 'image');
        if ($imageFile) {
          // Удаляем старое изображение
          if ($oldImage) {
            $this->deleteImage($oldImage);
          }
          $model->image = $this->uploadImage($imageFile);
        } else {
          // Если изображение не загружено, сохраняем старое
          $model->image = $oldImage;
        }

        if ($model->save()) {
          $this->flash('success', Yii::t('easyii/page', 'Page updated'));
        } else {
          $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
        }
        return $this->refresh();
      }
    } else {
      return $this->render('edit', [
        'model' => $model
      ]);
    }
  }

  public function actionDelete($id)
  {
    $model = Page::findOne($id);
    if ($model) {
      // Удаляем изображение
      if ($model->image) {
        $this->deleteImage($model->image);
      }
      $model->delete();
    } else {
      $this->error = Yii::t('easyii', 'Not found');
    }
    return $this->formatResponse(Yii::t('easyii/page', 'Page deleted'));
  }

  public function actionContacts($id)
  {
    $page = Page::findOne($id);

    if ($page === null) {
      $this->flash('error', Yii::t('easyii', 'Not found'));
      return $this->redirect(['/admin/' . $this->module->id]);
    }

    $contactsData = $page->getContacts();

    $models = [];
    foreach ($contactsData as $contact) {
      $model = new ContactForm();
      $model->attributes = $contact;
      if (empty($model->width)) $model->width = 20;
      if (empty($model->height)) $model->height = 20;
      $models[] = $model;
    }

    if (empty($models)) {
      $models[] = new ContactForm();
    }

    if (Yii::$app->request->post()) {
      $postData = Yii::$app->request->post('ContactForm', []);

      $validContacts = [];
      $hasErrors = false;

      foreach ($postData as $contactData) {
        $contactModel = new ContactForm();
        $contactModel->load(['ContactForm' => $contactData], 'ContactForm');

        if ($contactModel->validate()) {
          $validContacts[] = [
            'icon' => $contactModel->icon,
            'width' => (int)$contactModel->width,
            'height' => (int)$contactModel->height,
            'link' => $contactModel->link,
            'title' => $contactModel->title,
            'subtitle' => $contactModel->subtitle,
            'description' => $contactModel->description,
          ];
        } else {
          $hasErrors = true;
          $this->flash('error', Yii::t('easyii', 'Validation error'));
          break;
        }
      }

      if (!$hasErrors) {
        $page->field1 = json_encode($validContacts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($page->save(false)) {
          $this->flash('success', Yii::t('easyii/page', 'Contacts saved'));
          return $this->refresh();
        }
      }
    }

    return $this->render('contacts', [
      'page' => $page,
      'models' => $models,
    ]);
  }

  /**
   * Загрузка изображения
   * @param UploadedFile $file
   * @return string имя файла
   */
  private function uploadImage($file)
  {
    $dir = Yii::getAlias('@webroot/uploads/pages/');
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }

    $filename = time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
    $file->saveAs($dir . $filename);

    return $filename;
  }

  /**
   * Удаление изображения
   * @param string $filename
   */
  private function deleteImage($filename)
  {
    $path = Yii::getAlias('@webroot/uploads/pages/') . $filename;
    if (file_exists($path)) {
      unlink($path);
    }
  }
}