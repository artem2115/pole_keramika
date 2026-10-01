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

    // Товары коллекции
    $items = Item::find()
      ->where(['collection_id' => $collection->collection_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC])
      ->all();

    // Один запрос — все нужные data для всех товаров
    $itemIds = array_map(function($item) { return $item->item_id; }, $items);
    $itemData = Item::getDataForItems($itemIds, ['size', 'unit']);

    // Похожие коллекции из той же категории (кроме текущей)
    $similarCollections = [];
    if ($collection->category_id) {
      $similarCollections = Collection::find()
        ->where(['category_id' => $collection->category_id])
        ->andWhere(['!=', 'collection_id', $collection->collection_id])
        ->andWhere(['status' => Collection::STATUS_ON])
        ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
        ->limit(8)
        ->all();
    }

    return $this->render('view', [
      'collection' => $collection,
      'items' => $items,
      'images' => $collection->getAllImages(),
      'itemData' => $itemData,
      'similarCollections' => $similarCollections,
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

    // Один запрос — data для всех товаров блока "другие товары"
    $itemIds = array_map(function($i) { return $i->item_id; }, $collectionItems);
    $itemData = Item::getDataForItems($itemIds, ['unit']);

    return $this->render('/catalog/item', [
      'category' => $collection->category,
      'collection' => $collection,
      'item' => $item,
      'images' => $item->getAllImages(),
      'characteristics' => $item->getCharacteristics(),
      'collectionItems' => $collectionItems,
      'itemData' => $itemData,
    ]);
  }
}