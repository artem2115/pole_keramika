<?php
namespace app\controllers;

use app\assets\CatalogAsset;
use Yii;
use yii\easyii\helpers\Translit;
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
   * Типы полей, которые могут участвовать в фильтрах
   */
  private const FILTER_FIELD_TYPES = ['checkbox', 'select'];

  /**
   * Поля-исключения — не участвуют в фильтрах,
   * даже если у них тип из FILTER_FIELD_TYPES
   */
  private const NON_FILTER_FIELDS = [
    'article',
    'unit',
    'thickness',
    'pcsinpack',
  ];

  /**
   * Главная каталога — все товары всех категорий
   */
  public function actionIndex($page = null)
  {
    CatalogAsset::register($this->view);

    $query = Item::find()
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC]);

    $activeFilters = $this->applyFilters($query, null);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 12,
        'page' => $page ? ((int)$page - 1) : 0,
        'route' => '/catalog',
        'params' => [],
      ],
    ]);

    $filterValues = $this->getFilterValues(null);
    $filterCounts = $this->getFilterCounts(null, $activeFilters);

    $itemIds = array_map(function($i) { return $i->item_id; }, $dataProvider->models);
    $itemData = Item::getDataForItems($itemIds, ['unit', 'size']);

    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    $totalCount = Item::find()->where(['status' => Item::STATUS_ON])->count();

    $priceRange = Item::find()
      ->where(['status' => Item::STATUS_ON])
      ->andWhere(['>', 'price', 0])
      ->select(['min_price' => 'MIN(price)', 'max_price' => 'MAX(price)'])
      ->asArray()
      ->one();

    $priceMin = $priceRange ? (float)$priceRange['min_price'] : 0;
    $priceMax = $priceRange ? (float)$priceRange['max_price'] : 0;

    if (Yii::$app->request->isAjax && Yii::$app->request->get('ajax') === '1') {
      return $this->renderPartial('_products', [
        'category' => null,
        'dataProvider' => $dataProvider,
        'itemData' => $itemData,
        'filterValues' => $filterValues,
        'activeFilters' => $activeFilters,
      ]);
    }

    return $this->render('category', [
      'category' => null,
      'dataProvider' => $dataProvider,
      'filterValues' => $filterValues,
      'activeFilters' => $activeFilters,
      'itemData' => $itemData,
      'allCategories' => $allCategories,
      'priceMin' => $priceMin,
      'priceMax' => $priceMax,
      'visibleLimit' => (int) \yii\easyii\models\Setting::get('visible_limit_filters') ?: 7,
      'view' => 'tovary',
      'filterCounts' => $filterCounts,
      'totalCount' => $totalCount,
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
  public function actionCategory($category_slug, $page = null)
  {
    CatalogAsset::register($this->view);

    $category = $this->findCategory($category_slug);

    $query = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC]);

    $activeFilters = $this->applyFilters($query, $category);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 12,
        'page' => $page ? ((int)$page - 1) : 0,
        'route' => '/catalog/' . $category_slug . '/tovary',
        'params' => [],
      ],
    ]);

    $filterValues = $this->getFilterValues($category);
    $filterCounts = $this->getFilterCounts($category, $activeFilters);

    $itemIds = array_map(function($i) { return $i->item_id; }, $dataProvider->models);
    $itemData = Item::getDataForItems($itemIds, ['unit', 'size']);

    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    $priceRange = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->andWhere(['>', 'price', 0])
      ->select(['min_price' => 'MIN(price)', 'max_price' => 'MAX(price)'])
      ->asArray()
      ->one();

    $priceMin = $priceRange ? (float)$priceRange['min_price'] : 0;
    $priceMax = $priceRange ? (float)$priceRange['max_price'] : 0;

    if (Yii::$app->request->isAjax && Yii::$app->request->get('ajax') === '1') {
      return $this->renderPartial('_products', [
        'category' => $category,
        'dataProvider' => $dataProvider,
        'itemData' => $itemData,
        'filterValues' => $filterValues,
        'activeFilters' => $activeFilters,
      ]);
    }

    return $this->render('category', [
      'category' => $category,
      'dataProvider' => $dataProvider,
      'filterValues' => $filterValues,
      'activeFilters' => $activeFilters,
      'itemData' => $itemData,
      'allCategories' => $allCategories,
      'priceMin' => $priceMin,
      'priceMax' => $priceMax,
      'visibleLimit' => (int) \yii\easyii\models\Setting::get('visible_limit_filters') ?: 7,
      'view' => 'tovary',
      'filterCounts' => $filterCounts,
      'totalCount' => $dataProvider->totalCount,
    ]);
  }

  /**
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

    if ($item->collection_id && $item->collection) {
      return $this->redirect([
        '/collections/'
        . $item->collection->category->slug . '/'
        . $item->collection->slug . '/'
        . $item->slug
      ], 301);
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
   * Получить поля, которые участвуют в фильтрах.
   * @param Category|null $category
   * @return array — массив объектов field
   */
  private function getFilterFields($category = null)
  {
    $fields = $this->getAllFields($category);
    $result = [];

    foreach ($fields as $field) {
      // Только checkbox и select
      if (!in_array($field->type, self::FILTER_FIELD_TYPES, true)) {
        continue;
      }
      // Пропускаем исключения
      if (in_array($field->name, self::NON_FILTER_FIELDS, true)) {
        continue;
      }
      $result[] = $field;
    }

    return $result;
  }

  /**
   * Получить все поля категории (или всех категорий)
   * @param Category|null $category
   * @return array
   */
  private function getAllFields($category = null)
  {
    if ($category) {
      return $category->fields ?: [];
    }

    // Все поля всех категорий — уникальные по имени
    $uniqueFields = [];
    $allCategories = Category::find()->where(['status' => Category::STATUS_ON])->all();
    foreach ($allCategories as $cat) {
      foreach ($cat->fields ?: [] as $field) {
        if (!isset($uniqueFields[$field->name])) {
          $uniqueFields[$field->name] = $field;
        }
      }
    }
    return array_values($uniqueFields);
  }

  /**
   * Собрать значения для фильтров из ItemData
   */
  private function getFilterValues($category = null)
  {
    $filters = [];

    $fields = $this->getFilterFields($category);
    if (empty($fields)) {
      return $filters;
    }

    // Собираем item_id
    if ($category) {
      $items = Item::find()
        ->where(['category_id' => $category->category_id])
        ->andWhere(['status' => Item::STATUS_ON])
        ->select(['item_id'])
        ->column();
    } else {
      $items = Item::find()
        ->where(['status' => Item::STATUS_ON])
        ->select(['item_id'])
        ->column();
    }

    if (empty($items)) {
      return $filters;
    }

    $rows = ItemData::find()
      ->where(['item_id' => $items])
      ->select(['name', 'value'])
      ->asArray()
      ->all();

    $valuesMap = [];
    foreach ($rows as $row) {
      $valuesMap[$row['name']][] = $row['value'];
    }

    foreach ($fields as $field) {
      $values = $valuesMap[$field->name] ?? [];
      if (empty($values)) {
        continue;
      }

      $values = array_unique($values);
      sort($values);

      $items_with_slug = [];
      foreach ($values as $value) {
        $label = $value;
        $hex = '';

        if ($field->name === 'color' && strpos($value, ':') !== false) {
          list($label, $hex) = explode(':', $value, 2);
        }

        $items_with_slug[] = [
          'value' => $value,
          'label' => $label,
          'slug' => Translit::slug($label),
          'hex' => $hex,
        ];
      }

      $filters[] = [
        'field' => $field,
        'values' => $items_with_slug,
      ];
    }

    return $filters;
  }

  /**
   * Применить фильтры из GET к запросу
   */
  private function applyFilters(&$query, $category = null)
  {
    $active = [];
    $slugMap = $this->getSlugMap($category);

    // Цена
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

    // Фильтры по полям (динамически)
    foreach ($this->getFilterFields($category) as $field) {
      $name = $field->name;
      $raw = Yii::$app->request->get($name, '');

      if (is_array($raw)) {
        $slugs = array_filter($raw);
      } else {
        $slugs = array_filter(array_map('trim', explode(',', (string)$raw)));
      }

      if (empty($slugs)) continue;

      $originalValues = [];
      foreach ($slugs as $slug) {
        if (isset($slugMap[$name][$slug])) {
          $originalValues[] = $slugMap[$name][$slug];
        } else {
          $originalValues[] = $slug;
        }
      }

      if (empty($originalValues)) continue;

      $itemIds = ItemData::find()
        ->where(['name' => $name, 'value' => $originalValues])
        ->select(['item_id'])
        ->distinct()
        ->column();

      $query->andWhere(['item_id' => $itemIds]);
      $active[$name] = $slugs;
    }

    return $active;
  }

  /**
   * Карта slug => value для всех фильтруемых полей категории.
   */
  private function getSlugMap($category = null)
  {
    $map = [];

    foreach ($this->getFilterFields($category) as $field) {
      if (empty($field->options)) continue;

      $map[$field->name] = [];

      foreach ($field->options as $option) {
        $label = $option;
        if ($field->name === 'color' && strpos($option, ':') !== false) {
          $label = explode(':', $option, 2)[0];
        }

        $slug = Translit::slug($label);
        $map[$field->name][$slug] = $option;
      }
    }

    return $map;
  }

  /**
   * Считает количество товаров для каждого значения фильтра.
   */
  private function getFilterCounts($category, $activeFilters)
  {
    $counts = [];
    $slugMap = $this->getSlugMap($category);
    $filterFields = $this->getFilterFields($category);

    foreach ($filterFields as $field) {
      $fieldName = $field->name;

      $otherFilters = $activeFilters;
      unset($otherFilters[$fieldName]);

      $itemQuery = Item::find()
        ->where(['status' => Item::STATUS_ON])
        ->select(['item_id']);

      if ($category) {
        $itemQuery->andWhere(['category_id' => $category->category_id]);
      }

      foreach ($otherFilters as $name => $values) {
        if (empty($values)) continue;

        if ($name === 'price_from') {
          $itemQuery->andWhere(['>=', 'price', (float)$values]);
          continue;
        }
        if ($name === 'price_to') {
          $itemQuery->andWhere(['<=', 'price', (float)$values]);
          continue;
        }
        if ($name === 'is_hit') {
          $itemIds = ItemData::find()
            ->where(['name' => 'ishit', 'value' => '1'])
            ->select(['item_id'])
            ->column();
          $itemQuery->andWhere(['item_id' => $itemIds]);
          continue;
        }

        $originalValues = [];
        foreach ((array)$values as $slug) {
          $originalValues[] = $slugMap[$name][$slug] ?? $slug;
        }
        if (!empty($originalValues)) {
          $itemIds = ItemData::find()
            ->where(['name' => $name, 'value' => $originalValues])
            ->select(['item_id'])
            ->column();
          $itemQuery->andWhere(['item_id' => $itemIds]);
        }
      }

      $matchingItemIds = $itemQuery->column();

      if (empty($matchingItemIds)) {
        $counts[$fieldName] = [];
        continue;
      }

      $rows = ItemData::find()
        ->where(['item_id' => $matchingItemIds, 'name' => $fieldName])
        ->select(['value', 'cnt' => 'COUNT(DISTINCT item_id)'])
        ->groupBy('value')
        ->asArray()
        ->all();

      $fieldCounts = [];
      foreach ($rows as $row) {
        $fieldCounts[$row['value']] = (int)$row['cnt'];
      }
      $counts[$fieldName] = $fieldCounts;
    }

    return $counts;
  }
}