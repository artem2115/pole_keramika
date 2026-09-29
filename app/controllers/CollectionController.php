<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\collections\models\Collection;

class CollectionController extends Controller
{
  /**
   * Страница коллекции: /collection/{slug}/
   */
  public function actionView($collection_slug)
  {
    $collection = Collection::findOne([
      'slug' => $collection_slug,
      'status' => Collection::STATUS_ON,
    ]);

    if (!$collection) {
      throw new NotFoundHttpException('Коллекция не найдена');
    }

    $items = Item::find()
      ->where(['collection_id' => $collection->collection_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC])
      ->all();

    return $this->render('view', [
      'collection' => $collection,
      'items' => $items,
    ]);
  }

  /**
   * Страница товара в коллекции: /collection/{collection_slug}/{item_slug}/
   */
  public function actionItem($collection_slug, $item_slug)
  {
    $collection = Collection::findOne([
      'slug' => $collection_slug,
      'status' => Collection::STATUS_ON,
    ]);

    if (!$collection) {
      throw new NotFoundHttpException('Коллекция не найдена');
    }

    $item = Item::findOne([
      'slug' => $item_slug,
      'collection_id' => $collection->collection_id,
      'status' => Item::STATUS_ON,
    ]);

    if (!$item) {
      throw new NotFoundHttpException('Товар не найден');
    }

    // Другие товары этой коллекции
    $collectionItems = Item::find()
      ->where(['collection_id' => $collection->collection_id])
      ->andWhere(['!=', 'item_id', $item->item_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC])
      ->limit(8)
      ->all();

    return $this->render('/catalog/item', [
      'category' => $collection->category,
      'item' => $item,
      'collection' => $collection,
      'collectionItems' => $collectionItems,
    ]);
  }
}