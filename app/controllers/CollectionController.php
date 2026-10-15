<?php
namespace app\controllers;

use app\assets\CatalogAsset;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\easyii\models\Setting;
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
    CatalogAsset::register($this->view);
    $activeStyle = $this->parseActiveStyle();
    $activeBrand = $this->parseActiveBrand();
    $brandsMap = $this->getBrandsMap(null);
    $collections = $this->findCollections(null, $activeStyle, $activeBrand, $brandsMap);

    return $this->render('index', [
      'category' => null,
      'collections' => $collections,
      'collectionPrices' => $this->getCollectionPrices($collections),
      'allCategories' => $this->getAllCategories(),
      'stylesMap' => $this->getStylesMap(null),
      'activeStyle' => $activeStyle,
      'brandsMap' => $brandsMap,
      'activeBrand' => $activeBrand,
      'visibleLimit' => $this->getVisibleLimit(),
    ]);
  }

  /**
   * Коллекции категории: /collections/{category_slug}/
   */
  public function actionCategory($category_slug)
  {
    $category = $this->findCategory($category_slug);
    $activeStyle = $this->parseActiveStyle();
    $activeBrand = $this->parseActiveBrand();

    // Собираем бренды из товаров коллекций категории
    $brandsMap = $this->getBrandsMap($category);

    // Находим коллекции по фильтрам
    $collections = $this->findCollections($category, $activeStyle, $activeBrand, $brandsMap);

    return $this->render('index', [
      'category' => $category,
      'collections' => $collections,
      'collectionPrices' => $this->getCollectionPrices($collections),
      'allCategories' => $this->getAllCategories(),
      'stylesMap' => $this->getStylesMap($category),
      'activeStyle' => $activeStyle,
      'brandsMap' => $brandsMap,
      'activeBrand' => $activeBrand,
      'visibleLimit' => $this->getVisibleLimit(),
    ]);
  }  /**
   * Одна коллекция: /collections/{category_slug}/{collection_slug}/
   */
  public function actionView($category_slug, $collection_slug)
  {
    $category = $this->findCategory($category_slug);
    $collection = $this->findCollection($category, $collection_slug);

    // Товары коллекции
    $items = Item::find()
      ->where(['collection_id' => $collection->collection_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC])
      ->all();

    $itemIds = array_map(function($i) { return $i->item_id; }, $items);

    // Похожие коллекции
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
      'itemData' => Item::getDataForItems($itemIds, ['size', 'unit']),
      'similarCollections' => $similarCollections,
    ]);
  }

  /**
   * Товар в коллекции: /collections/{category_slug}/{collection_slug}/{item_slug}/
   */
  public function actionItem($category_slug, $collection_slug, $item_slug)
  {
    $category = $this->findCategory($category_slug);
    $collection = $this->findCollection($category, $collection_slug);

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

    $itemIds = array_map(function($i) { return $i->item_id; }, $collectionItems);

    return $this->render('/catalog/item', [
      'category' => $collection->category,
      'collection' => $collection,
      'item' => $item,
      'images' => $item->getAllImages(),
      'characteristics' => $item->getCharacteristics(),
      'collectionItems' => $collectionItems,
      'itemData' => Item::getDataForItems($itemIds, ['unit']),
    ]);
  }

  // ============================================================
  // Приватные хелперы
  // ============================================================

  /**
   * Найти категорию по slug или 404
   */
  private function findCategory($slug)
  {
    $category = Category::findOne(['slug' => $slug, 'status' => Category::STATUS_ON]);
    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }
    return $category;
  }

  /**
   * Найти коллекцию по slug в категории или 404
   */
  private function findCollection($category, $slug)
  {
    $collection = Collection::findOne([
      'slug' => $slug,
      'category_id' => $category->category_id,
      'status' => Collection::STATUS_ON,
    ]);
    if (!$collection) {
      throw new NotFoundHttpException('Коллекция не найдена');
    }
    return $collection;
  }

  /**
   * Список коллекций с фильтром по стилю
   * @param Category|null $category
   * @param array $activeStyle — массив slug'ов стилей
   * @return Collection[]
   */
  private function findCollections($category, $activeStyle = [], $activeBrand = [], $brandsMap = [])
  {
    $query = Collection::find()
      ->where(['status' => Collection::STATUS_ON]);

    if ($category) {
      $query->andWhere(['category_id' => $category->category_id]);
    }

    if (!empty($activeStyle)) {
      $query->andWhere(['style' => $activeStyle]);
    }

    // Фильтр по бренду — только коллекции, у которых есть товары с этими брендами
    if (!empty($activeBrand)) {
      $collectionIds = [];
      foreach ($activeBrand as $slug) {
        if (!empty($brandsMap[$slug]['collection_ids'])) {
          $collectionIds = array_merge($collectionIds, $brandsMap[$slug]['collection_ids']);
        }
      }
      $collectionIds = array_unique($collectionIds);

      if (empty($collectionIds)) {
        // Ни одна коллекция не подходит — возвращаем пусто
        return [];
      }

      $query->andWhere(['collection_id' => $collectionIds]);
    }

    return $query
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();
  }

  /**
   * Все категории для сайдбара
   */
  private function getAllCategories()
  {
    return Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();
  }

  /**
   * Минимальные цены для списка коллекций
   * @param Collection[] $collections
   * @return array [collection_id => min_price]
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

  /**
   * Карта стилей: [slug => ['label' => 'Название', 'count' => N]]
   * @param Category|null $category — если null, по всем коллекциям
   * @return array
   */
  private function getStylesMap($category = null)
  {
    $query = Collection::find()
      ->where(['status' => Collection::STATUS_ON])
      ->andWhere(['not', ['style' => null]])
      ->andWhere(['!=', 'style', '']);

    if ($category) {
      $query->andWhere(['category_id' => $category->category_id]);
    }

    $rows = $query
      ->select(['style', 'cnt' => 'COUNT(*)'])
      ->groupBy('style')
      ->asArray()
      ->all();

    $map = [];
    foreach ($rows as $row) {
      $slug = $row['style'];
      if (isset(Collection::$styles[$slug])) {
        $map[$slug] = [
          'label' => Collection::$styles[$slug],
          'count' => (int)$row['cnt'],
        ];
      }
    }

    // Сортируем по порядку в массиве $styles
    $ordered = [];
    foreach (Collection::$styles as $slug => $label) {
      if (isset($map[$slug])) {
        $ordered[$slug] = $map[$slug];
      }
    }

    return $ordered;
  }

  /**
   * Парсинг активных стилей из GET.
   * Всегда возвращает массив.
   */
  private function parseActiveStyle()
  {
    $raw = Yii::$app->request->get('style', '');

    if (is_array($raw)) {
      $slugs = array_filter($raw);
    } else {
      $slugs = array_filter(array_map('trim', explode(',', (string)$raw)));
    }

    // Оставляем только известные стили
    return array_values(array_intersect(array_keys(Collection::$styles), $slugs));
  }

  /**
   * Лимит видимых значений в фильтрах
   */
  private function getVisibleLimit()
  {
    return (int) Setting::get('visible_limit_filters') ?: 7;
  }


  /**
   * Карта брендов из товаров коллекций категории
   * @param Category|null $category
   * @return array [slug => ['value' => 'Cersanit', 'collection_ids' => [...], 'count' => N]]
   */
  private function getBrandsMap($category = null)
  {
    // Собираем ID коллекций
    $collectionsQuery = Collection::find()
      ->where(['status' => Collection::STATUS_ON]);

    if ($category) {
      $collectionsQuery->andWhere(['category_id' => $category->category_id]);
    }

    $collectionIds = $collectionsQuery->select(['collection_id'])->column();

    if (empty($collectionIds)) {
      return [];
    }

    // Собираем бренды из товаров этих коллекций
    $rows = \yii\easyii\modules\catalog\models\ItemData::find()
      ->alias('d')
      ->innerJoin(['i' => Item::tableName()], 'i.item_id = d.item_id')
      ->where(['i.collection_id' => $collectionIds])
      ->andWhere(['i.status' => Item::STATUS_ON])
      ->andWhere(['d.name' => 'brand'])
      ->select(['d.value', 'i.collection_id'])
      ->asArray()
      ->all();

    $map = [];
    foreach ($rows as $row) {
      $brand = $row['value'];
      $slug = \yii\easyii\helpers\Translit::slug($brand);

      if (!isset($map[$slug])) {
        $map[$slug] = [
          'value' => $brand,
          'collection_ids' => [],
        ];
      }
      $map[$slug]['collection_ids'][] = (int)$row['collection_id'];
    }

    // Уникализируем и считаем количество
    foreach ($map as $slug => &$data) {
      $data['collection_ids'] = array_values(array_unique($data['collection_ids']));
      $data['count'] = count($data['collection_ids']);
    }
    unset($data);

    // Сортируем по алфавиту
    uasort($map, function($a, $b) {
      return strcmp($a['value'], $b['value']);
    });

    return $map;
  }

  /**
   * Парсинг активных брендов из GET.
   * Всегда массив.
   */
  private function parseActiveBrand()
  {
    $raw = Yii::$app->request->get('brand', '');

    if (is_array($raw)) {
      return array_values(array_filter($raw));
    }

    return array_values(array_filter(array_map('trim', explode(',', (string)$raw))));
  }
}