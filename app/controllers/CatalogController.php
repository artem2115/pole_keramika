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
  public function actionCategory($category_slug, $page = null)
  {
    CatalogAsset::register($this->view);  // ← ВЕРНУТЬ

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

    $itemIds = array_map(function($i) { return $i->item_id; }, $dataProvider->models);
    $itemData = Item::getDataForItems($itemIds, ['unit', 'size']);

    $allCategories = Category::find()
      ->where(['status' => Category::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    // Минимум и максимум цены в категории
    $priceRange = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->andWhere(['>', 'price', 0])
      ->select(['min_price' => 'MIN(price)', 'max_price' => 'MAX(price)'])
      ->asArray()
      ->one();

    $priceMin = $priceRange ? (float)$priceRange['min_price'] : 0;
    $priceMax = $priceRange ? (float)$priceRange['max_price'] : 0;

    // AJAX-ветка
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
      'view' => 'tovary',
    ]);
  }  /**
   * Страница категории с коллекциями: /catalog/{category}/kollekcii/
   */
  public function actionCategoryCollections($category_slug)
  {
    $category = $this->findCategory($category_slug);

    // Получаем все коллекции категории
    $allCollections = Collection::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Collection::STATUS_ON])
      ->all();

    $collectionIds = array_map(function($c) { return $c->collection_id; }, $allCollections);

    // Собираем бренды из товаров коллекций: [slug => ['value' => оригинал, 'collection_ids' => [...]]]
    $brandsMap = [];
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
        $slug = Translit::slug($brand);

        if (!isset($brandsMap[$slug])) {
          $brandsMap[$slug] = [
            'value' => $brand,
            'collection_ids' => [],
          ];
        }
        $brandsMap[$slug]['collection_ids'][] = (int)$row['collection_id'];
      }

      // Сортируем по оригинальному значению (по алфавиту)
      uasort($brandsMap, function($a, $b) {
        return strcmp($a['value'], $b['value']);
      });
    }

    // Применяем фильтр по бренду из GET
    $activeBrand = Yii::$app->request->get('brand', '');
    if (is_array($activeBrand)) {
      $activeBrand = array_filter($activeBrand);
    } else {
      $activeBrand = array_filter(array_map('trim', explode(',', (string)$activeBrand)));
    }

    if (!empty($activeBrand)) {
      // Находим коллекции, в которых есть хотя бы один из выбранных брендов
      $matchedCollectionIds = [];
      foreach ($activeBrand as $slug) {
        if (!empty($brandsMap[$slug]['collection_ids'])) {
          $matchedCollectionIds = array_merge($matchedCollectionIds, $brandsMap[$slug]['collection_ids']);
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

    $valuesMap = [];
    foreach ($rows as $row) {
      $valuesMap[$row['name']][] = $row['value'];
    }

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

      // Готовим значения с slug
      $items_with_slug = [];
      foreach ($values as $value) {
        $label = $value;
        $hex = '';

        if ($field->name === 'color' && strpos($value, ':') !== false) {
          list($label, $hex) = explode(':', $value, 2);
        }

        $items_with_slug[] = [
          'value' => $value,      // оригинал (для данных)
          'label' => $label,      // отображаемое
          'slug' => Translit::slug($label), // для URL
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
   * @return array — активные фильтры
   */
  private function applyFilters(&$query, $category)
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

    // Фильтры по ItemData
    $filterNames = ['brand', 'color', 'purpose', 'size', 'surface'];
    foreach ($filterNames as $name) {
      $raw = Yii::$app->request->get($name, '');

      // Парсим: строка через запятую ИЛИ массив (для обратной совместимости)
      if (is_array($raw)) {
        $slugs = array_filter($raw);
      } else {
        $slugs = array_filter(array_map('trim', explode(',', (string)$raw)));
      }

      if (empty($slugs)) {
        continue;
      }

      // Преобразуем slug обратно в оригинальные значения
      $originalValues = [];
      foreach ($slugs as $slug) {
        if (isset($slugMap[$name][$slug])) {
          $originalValues[] = $slugMap[$name][$slug];
        } else {
          // Если в карте нет — используем как есть (обратная совместимость)
          $originalValues[] = $slug;
        }
      }

      if (empty($originalValues)) {
        continue;
      }

      $itemIds = ItemData::find()
        ->where(['name' => $name, 'value' => $originalValues])
        ->select(['item_id'])
        ->distinct()
        ->column();

      $query->andWhere(['item_id' => $itemIds]);

      // Возвращаем активные фильтры в виде slug'ов — для сравнения во вьюхе
      $active[$name] = $slugs;
    }

    return $active;
  }

  /**
   * Карта slug => value для всех полей категории
   * @return array ['brand' => ['cersanit' => 'Cersanit', ...], 'size' => [...], ...]
   */
  private function getSlugMap($category)
  {
    $map = [];

    if (empty($category->fields)) {
      return $map;
    }

    foreach ($category->fields as $field) {
      if (empty($field->options)) {
        continue;
      }

      $map[$field->name] = [];

      foreach ($field->options as $option) {
        // Для color — обрезаем HEX
        $label = $option;
        if ($field->name === 'color' && strpos($option, ':') !== false) {
          $label = explode(':', $option, 2)[0];
        }

        $slug = Translit::slug($label);
        $map[$field->name][$slug] = $option; // храним ОРИГИНАЛ для запроса
      }
    }

    return $map;
  }
}