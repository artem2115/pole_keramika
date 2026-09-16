<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\helpers\MetaWidget;
use yii\easyii\modules\completed\models\Completed;
use yii\easyii\modules\completed\models\CompletedTag;
use yii\easyii\modules\page\api\Page;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CompletedController extends Controller
{
  /**
   * Список реализованных проектов
   */
  public function actionIndex($tag = null)
  {
    $page = Page::get('page-completed');
    MetaWidget::widget(['model' => $page]);

    $query = Completed::find()->where(['status' => Completed::STATUS_ON])->orderBy(['time' => SORT_DESC]);

    if($tag){
      $tagModel = CompletedTag::findOne(['slug' => $tag]);
      if($tagModel){
        $query->innerJoin('{{%completed_tag_rel}}', '{{%completed}}.[[completed_id]] = {{%completed_tag_rel}}.[[completed_id]]')
          ->andWhere(['{{%completed_tag_rel}}.[[tag_id]]' => $tagModel->tag_id]);
      }
    }

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 12,
      ],
    ]);

    // Все теги для фильтра
    $tags = CompletedTag::find()
      ->innerJoin('{{%completed_tag_rel}}', '{{%completed_tags}}.[[tag_id]] = {{%completed_tag_rel}}.[[tag_id]]')
      ->groupBy('{{%completed_tags}}.[[tag_id]]')
      ->orderBy(['name' => SORT_ASC])
      ->all();

    return $this->render('index', [
      'page' => $page,
      'dataProvider' => $dataProvider,
      'tags' => $tags,
      'activeTag' => $tag,
    ]);
  }

  /**
   * Страница проекта
   */
  public function actionView($slug)
  {
    $completed = Completed::findOne(['slug' => $slug, 'status' => Completed::STATUS_ON]);

    if(!$completed){
      throw new NotFoundHttpException('Проект не найден.');
    }
    MetaWidget::widget(['model' => $completed]);
    $completed->updateCounters(['views' => 1]);

    return $this->render('view', [
      'completed' => $completed,
      'otherProjects' => Completed::find()
        ->where(['status' => Completed::STATUS_ON])
        ->andWhere(['!=', 'completed_id', $completed->completed_id])
        ->orderBy(['time' => SORT_DESC])
        ->limit(8)
        ->all()
    ]);
  }
}