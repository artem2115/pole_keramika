<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\data\Pagination;

/**
 * Виджет пагинации с поддержкой ЧПУ-формата страниц
 * и сохранением GET-параметров фильтров.
 *
 * Пример использования:
 *   <?= CategoryPager::widget([
 *     'pagination' => $dataProvider->pagination,
 *     'basePath' => '/catalog/' . $category->slug . '/tovary',
 *   ]) ?>
 *
 * Результат URL: /catalog/plitka/tovary/2/?size=120x120
 */
class CategoryPager extends Widget
{
  /** @var Pagination */
  public $pagination;

  /** @var string базовый путь без номера страницы (например /catalog/plitka/tovary) */
  public $basePath;

  /** @var array ключи GET, которые нужно исключить из ссылок */
  public $excludeKeys = ['page', 'ajax', 'category_slug', 'collection_slug', 'item_slug'];

  /** @var int максимальное количество кнопок с номерами страниц */
  public $maxButtonCount = 7;

  /** @var string CSS-класс для обёртки <ul> */
  public $options = ['class' => 'pgn'];

  public function run()
  {
    if(!$this->pagination){
      return '';
    }

    $pageCount = $this->pagination->getPageCount();
    if($pageCount <= 1){
      return '';
    }

    $currentPage = $this->pagination->getPage() + 1;

    // Текущие GET-параметры, кроме служебных
    $params = Yii::$app->request->get();
    foreach($this->excludeKeys as $key){
      unset($params[$key]);
    }

    $html = $this->renderButtons($currentPage, $pageCount, $params);

    $ulClass = $this->options['class'] ?? 'pgn';
    return '<ul class="' . $ulClass . '">' . $html . '</ul>';
  }

  /**
   * Рендер кнопок пагинации
   */
  protected function renderButtons($currentPage, $pageCount, $params)
  {
    $html = '';

    // Prev
    $html .= $this->renderPrev($currentPage, $params);

    // Диапазон страниц
    $range = $this->getPageRange($currentPage, $pageCount);

    // Первая страница + разделитель
    if(!in_array(1, $range)){
      $html .= $this->renderPage(1, $currentPage, $params);
      if($range[0] > 2){
        $html .= '<li class="disabled"><span>...</span></li>';
      }
    }

    // Страницы из диапазона
    foreach($range as $page){
      $html .= $this->renderPage($page, $currentPage, $params);
    }

    // Последняя страница + разделитель
    if(!in_array($pageCount, $range)){
      if(end($range) < $pageCount - 1){
        $html .= '<li class="disabled"><span>...</span></li>';
      }
      $html .= $this->renderPage($pageCount, $currentPage, $params);
    }

    // Next
    $html .= $this->renderNext($currentPage, $pageCount, $params);

    return $html;
  }

  /**
   * Кнопка "предыдущая страница"
   */
  protected function renderPrev($currentPage, $params)
  {
    if($currentPage > 1){
      return '<li class="prev"><a href="' . $this->buildUrl($currentPage - 1, $params) . '">«</a></li>';
    }
    return '<li class="prev disabled"><span>«</span></li>';
  }

  /**
   * Кнопка "следующая страница"
   */
  protected function renderNext($currentPage, $pageCount, $params)
  {
    if($currentPage < $pageCount){
      return '<li class="next"><a href="' . $this->buildUrl($currentPage + 1, $params) . '">»</a></li>';
    }
    return '<li class="next disabled"><span>»</span></li>';
  }

  /**
   * Одна кнопка со номером страницы
   */
  protected function renderPage($page, $currentPage, $params)
  {
    if($page === $currentPage){
      return '<li class="active"><a href="' . $this->buildUrl($page, $params) . '">' . $page . '</a></li>';
    }
    return '<li><a href="' . $this->buildUrl($page, $params) . '">' . $page . '</a></li>';
  }

  /**
   * Получить массив страниц для отображения (обрезаем длинные списки)
   */
  protected function getPageRange($currentPage, $pageCount)
  {
    $half = (int)floor($this->maxButtonCount / 2);
    $start = max(1, $currentPage - $half);
    $end = min($pageCount, $start + $this->maxButtonCount - 1);

    // Корректируем начало, если упёрлись в конец
    if($end - $start < $this->maxButtonCount - 1){
      $start = max(1, $end - $this->maxButtonCount + 1);
    }

    return range($start, $end);
  }

  /**
   * Построить URL для страницы с сохранением фильтров
   */
  protected function buildUrl($page, $params)
  {
    $url = rtrim($this->basePath, '/') . '/';
    if($page > 1){
      $url .= $page . '/';
    }
    if(!empty($params)){
      $query = http_build_query($params);
      $query = str_replace('%2C', ',', $query);
      $url .= '?' . $query;
    }
    return $url;
  }
}