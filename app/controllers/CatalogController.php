<?php
namespace app\controllers;

use app\assets\CatalogAsset;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\easyii\modules\catalog\models\Category;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\catalog\models\ItemData;
use yii\easyii\modules\collections\models\Collection;

class CatalogController extends Controller
{
  /**
   * Главная каталога (список всех категорий)
   */
  public function actionIndex()
  {
    $categories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    return $this->render('index', [
      'categories' => $categories,
    ]);
  }

  /**
   * Редирект: /catalog/{category}/ → /catalog/{category}/tovary/
   */
  public function actionCategoryRedirect($category_slug)
  {
    return $this->redirect(['/catalog/' . $category_slug . '/tovary'], 301);
  }

  /**
   * Страница категории с товарами: /catalog/{category}/tovary/
   */
  public function actionCategory($category_slug)
  {
    CatalogAsset::register($this->view);
    $category = $this->findCategory($category_slug);

    // Запрос товаров
    $query = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC]);

    // Применяем фильтры из GET
    $activeFilters = $this->applyFilters($query, $category);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => ['pageSize' => 12],
    ]);

    // Значения для сайдбара
    $filterValues = $this->getFilterValues($category);

    // Единицы измерения для товаров — одним запросом
    $itemIds = array_map(function($i) { return $i->item_id; }, $dataProvider->models);
    $itemData = Item::getDataForItems($itemIds, ['unit', 'size']);

    // Все категории для сайдбара
    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    // Если AJAX — возвращаем только плитку
    if (Yii::$app->request->isAjax && Yii::$app->request->get('ajax') === '1') {
      return $this->renderPartial('_products', [
        'category' => $category,
        'dataProvider' => $dataProvider,
        'itemData' => $itemData,
        'view' => 'tovary',
      ]);
    }

    return $this->render('category', [
      'category' => $category,
      'dataProvider' => $dataProvider,
      'filterValues' => $filterValues,
      'activeFilters' => $activeFilters,
      'itemData' => $itemData,
      'allCategories' => $allCategories,
      'view' => 'tovary',
    ]);
  }
  /**
   * Страница категории с коллекциями: /catalog/{category}/kollekcii/
   */
  public function actionCategoryCollections($category_slug)
  {
    $category = $this->findCategory($category_slug);

    // Получаем все коллекции категории
    $collectionsQuery = Collection::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Collection::STATUS_ON]);

    // Собираем список коллекций для дальнейших манипуляций
    $allCollections = $collectionsQuery->all();
    $collectionIds = array_map(function($c) { return $c->collection_id; }, $allCollections);

    // Собираем все бренды, которые встречаются в товарах этих коллекций
    $brandsMap = []; // [brand => [collection_ids]]
    if (!empty($collectionIds)) {
      $rows = ItemData::find()
        ->alias('d')
        ->innerJoin(['i' => Item::tableName()], 'i.item_id = d.item_id')
        ->where(['i.collection_id' => $collectionIds])
        ->andWhere(['i.status' => Item::STATUS_ON])
        ->andWhere(['d.name' => 'brand'])
        ->select(['d.value', 'i.collection_id'])
        ->asArray()
        ->all();

      foreach ($rows as $row) {
        $brand = $row['value'];
        if (!isset($brandsMap[$brand])) {
          $brandsMap[$brand] = [];
        }
        $brandsMap[$brand][] = (int)$row['collection_id'];
      }
    }

    // Сортируем бренды по алфавиту
    ksort($brandsMap);

    // Применяем фильтр по бренду из GET
    $activeBrand = Yii::$app->request->get('brand', []);
    if (!is_array($activeBrand)) {
      $activeBrand = [$activeBrand];
    }
    $activeBrand = array_filter($activeBrand);

    if (!empty($activeBrand)) {
      // Находим коллекции, в которых есть хотя бы один из выбранных брендов
      $matchedCollectionIds = [];
      foreach ($activeBrand as $brand) {
        if (!empty($brandsMap[$brand])) {
          $matchedCollectionIds = array_merge($matchedCollectionIds, $brandsMap[$brand]);
        }
      }
      $matchedCollectionIds = array_unique($matchedCollectionIds);

      $collections = Collection::find()
        ->where(['collection_id' => $matchedCollectionIds])
        ->andWhere(['status' => Collection::STATUS_ON])
        ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
        ->all();
    } else {
      $collections = Collection::find()
        ->where(['category_id' => $category->category_id])
        ->andWhere(['status' => Collection::STATUS_ON])
        ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
        ->all();
    }

    // Собираем минимальные цены для найденных коллекций
    $currentCollectionIds = array_map(function($c) { return $c->collection_id; }, $collections);
    $collectionPrices = [];
    if (!empty($currentCollectionIds)) {
      $rows = Item::find()
        ->where(['collection_id' => $currentCollectionIds])
        ->andWhere(['status' => Item::STATUS_ON])
        ->andWhere(['>', 'price', 0])
        ->select(['collection_id', 'min_price' => 'MIN(price)'])
        ->groupBy('collection_id')
        ->asArray()
        ->all();

      foreach ($rows as $row) {
        $collectionPrices[$row['collection_id']] = (float)$row['min_price'];
      }
    }

    // Все категории для сайдбара
    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    return $this->render('category', [
      'category' => $category,
      'collections' => $collections,
      'collectionPrices' => $collectionPrices,
      'allCategories' => $allCategories,
      'brandsMap' => $brandsMap,
      'activeBrand' => $activeBrand,
      'filterValues' => [],
      'activeFilters' => [],
      'view' => 'kollekcii',
    ]);
  }  /**
   * Товар без коллекции: /catalog/{category_slug}/{item_slug}/
   */
  public function actionItemNoCollection($category_slug, $item_slug)
  {
    $category = $this->findCategory($category_slug);

    $item = Item::findOne([
      'slug' => $item_slug,
      'category_id' => $category->category_id,
      'status' => Item::STATUS_ON,
    ]);

    if (!$item) {
      throw new NotFoundHttpException('Товар не найден');
    }

    // Если у товара есть коллекция — редиректим на канонический URL
    if ($item->collection_id && $item->collection) {
      return $this->redirect(
        ['/collection/' . $item->collection->slug . '/' . $item->slug],
        301
      );
    }

    return $this->render('/catalog/item', [
      'category' => $category,
      'collection' => null,
      'item' => $item,
      'images' => $item->getAllImages(),
      'characteristics' => $item->getCharacteristics(),
      'collectionItems' => [],
      'itemData' => [],
    ]);
  }

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
   * Собрать значения для фильтров из ItemData
   * @return array — [['field' => fieldObject, 'values' => [...]], ...]
   */
  private function getFilterValues($category)
  {
    $filters = [];

    if (empty($category->fields)) {
      return $filters;
    }

    // Собираем данные по всем товарам категории одним запросом
    $items = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->select(['item_id'])
      ->column();

    if (empty($items)) {
      return $filters;
    }

    $rows = ItemData::find()
      ->where(['item_id' => $items])
      ->select(['name', 'value'])
      ->asArray()
      ->all();

    // Группируем значения по имени поля
    $valuesMap = [];
    foreach ($rows as $row) {
      $valuesMap[$row['name']][] = $row['value'];
    }

    // Проходим по полям категории, оставляем только нужные для фильтра
    $filterFields = ['brand', 'color', 'purpose', 'size', 'surface'];

    foreach ($category->fields as $field) {
      if (!in_array($field->name, $filterFields)) {
        continue;
      }

      $values = $valuesMap[$field->name] ?? [];
      if (empty($values)) {
        continue;
      }

      $values = array_unique($values);
      sort($values);

      $filters[] = [
        'field' => $field,
        'values' => $values,
      ];
    }

    return $filters;
  }

  /**
   * Применить фильтры из GET к запросу
   * @return array — активные фильтры
   */
  private function applyFilters(&$query, $category)
  {
    $active = [];

    // Цена от/до
    $priceFrom = Yii::$app->request->get('price_from');
    $priceTo = Yii::$app->request->get('price_to');
    if ($priceFrom !== null && $priceFrom !== '') {
      $query->andWhere(['>=', 'price', (float)$priceFrom]);
      $active['price_from'] = $priceFrom;
    }
    if ($priceTo !== null && $priceTo !== '') {
      $query->andWhere(['<=', 'price', (float)$priceTo]);
      $active['price_to'] = $priceTo;
    }

    // Хит
    $isHit = Yii::$app->request->get('is_hit');
    if ($isHit) {
      $itemIds = ItemData::find()
        ->where(['name' => 'ishit', 'value' => '1'])
        ->select(['item_id'])
        ->column();
      $query->andWhere(['item_id' => $itemIds]);
      $active['is_hit'] = 1;
    }

    // Фильтры по ItemData (brand, color, purpose, size, surface)
    $filterNames = ['brand', 'color', 'purpose', 'size', 'surface'];
    foreach ($filterNames as $name) {
      $values = Yii::$app->request->get($name, []);
      if (!is_array($values) || empty($values)) {
        continue;
      }

      $itemIds = ItemData::find()
        ->where(['name' => $name, 'value' => $values])
        ->select(['item_id'])
        ->distinct()
        ->column();

      $query->andWhere(['item_id' => $itemIds]);
      $active[$name] = $values;
    }

    return $active;
  }
}