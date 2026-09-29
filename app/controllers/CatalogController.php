<?php
namespace app\controllers;

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
   * Страница категории — список товаров и коллекций
   */
  public function actionCategory($category_slug)
  {
    $category = Category::findOne(['slug' => $category_slug, 'status' => Category::STATUS_ON]);

    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }

    $itemsQuery = Item::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Item::STATUS_ON])
      ->orderBy(['time' => SORT_DESC]);

    $collections = Collection::find()
      ->where(['category_id' => $category->category_id])
      ->andWhere(['status' => Collection::STATUS_ON])
      ->orderBy(['order_num' => SORT_ASC, 'title' => SORT_ASC])
      ->all();

    $dataProvider = new ActiveDataProvider([
      'query' => $itemsQuery,
      'pagination' => ['pageSize' => 12],
    ]);

    $filters = $this->getFilters($category);

    return $this->render('category', [
      'category' => $category,
      'dataProvider' => $dataProvider,
      'collections' => $collections,
      'filters' => $filters,
    ]);
  }

  /**
   * Товар без коллекции: /catalog/{category_slug}/{item_slug}/
   */
  public function actionItemNoCollection($category_slug, $item_slug)
  {
    $category = Category::findOne(['slug' => $category_slug, 'status' => Category::STATUS_ON]);
    if (!$category) {
      throw new NotFoundHttpException('Категория не найдена');
    }

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

    return $this->render('item', [
      'category' => $category,
      'item' => $item,
      'collectionItems' => [],
    ]);
  }

  /**
   * Фильтры (пока заготовка — наполним в шаге B)
   */
  private function getFilters($category)
  {
    $filters = [];
    if (empty($category->fields)) {
      return $filters;
    }

    foreach ($category->fields as $field) {
      if (in_array($field->type, ['checkbox', 'select'])) {
        $filters[] = $field;
      }
    }

    return $filters;
  }
}