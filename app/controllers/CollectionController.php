<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\easyii\modules\catalog\models\Category;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\collections\models\Collection;

class CollectionController extends Controller
{
  /**
   * Список всех коллекций: /collections/
   */
  public function actionIndex()
  {
    $collections = Collection::find()
      ->where(['status' => Collection::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    $collectionPrices = $this->getCollectionPrices($collections);

    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    return $this->render('index', [
      'category' => null,
      'collections' => $collections,
      'collectionPrices' => $collectionPrices,
      'allCategories' => $allCategories,
    ]);
  }

  /**
   * Коллекции категории: /collections/{category_slug}/
   */
  public function actionCategory($category_slug)
  {
    $category = Category::findOne(['slug' => $category_slug, 'status' => Category::STATUS_ON]);
    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }

    $collections = Collection::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Collection::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    $collectionPrices = $this->getCollectionPrices($collections);

    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    return $this->render('index', [
      'category' => $category,
      'collections' => $collections,
      'collectionPrices' => $collectionPrices,
      'allCategories' => $allCategories,
    ]);
  }

  /**
   * Одна коллекция: /collections/{category_slug}/{collection_slug}/
   */
  public function actionView($category_slug, $collection_slug)
  {
    $category = Category::findOne(['slug' => $category_slug, 'status' => Category::STATUS_ON]);
    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }

    $collection = Collection::findOne([
      'slug' => $collection_slug,
      'category_id' => $category->category_id,
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
    $similarCollections = Collection::find()
      ->where(['category_id' => $collection->category_id])
      ->andWhere(['!=', 'collection_id', $collection->collection_id])
      ->andWhere(['status' => Collection::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->limit(8)
      ->all();

    return $this->render('view', [
      'collection' => $collection,
      'items' => $items,
      'images' => $collection->getAllImages(),
      'itemData' => $itemData,
      'similarCollections' => $similarCollections,
    ]);
  }

  /**
   * Товар в коллекции: /collections/{category_slug}/{collection_slug}/{item_slug}/
   */
  public function actionItem($category_slug, $collection_slug, $item_slug)
  {
    $category = Category::findOne(['slug' => $category_slug, 'status' => Category::STATUS_ON]);
    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }

    $collection = Collection::findOne([
      'slug' => $collection_slug,
      'category_id' => $category->category_id,
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

  /**
   * Минимальные цены для списка коллекций
   */
  private function getCollectionPrices($collections)
  {
    $prices = [];
    $ids = array_map(function($c) { return $c->collection_id; }, $collections);
    if (empty($ids)) {
      return $prices;
    }

    $rows = Item::find()
      ->where(['collection_id' => $ids])
      ->andWhere(['status' => Item::STATUS_ON])
      ->andWhere(['>', 'price', 0])
      ->select(['collection_id', 'min_price' => 'MIN(price)'])
      ->groupBy('collection_id')
      ->asArray()
      ->all();

    foreach ($rows as $row) {
      $prices[$row['collection_id']] = (float)$row['min_price'];
    }

    return $prices;
  }
}