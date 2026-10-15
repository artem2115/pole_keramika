<?php
namespace app\models;

use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\catalog\models\ItemData;
use yii\easyii\modules\collections\models\Collection;
use yii\helpers\Url;

/**
 * Сервис для работы с корзиной.
 * Не хранит данные — только обогащает массив {item_id, qty} из localStorage.
 */
class Basket
{
  /**
   * Обогатить состав корзины данными из БД
   * @param array $rawItems [{item_id, qty}, ...]
   * @return array
   */
  public static function enrich($rawItems)
  {
    $items = [];
    $removed = [];
    $changedPrices = [];

    // Собираем ID
    $itemIds = [];
    foreach ($rawItems as $raw) {
      if (!empty($raw['item_id'])) {
        $itemIds[] = (int)$raw['item_id'];
      }
    }

    if (empty($itemIds)) {
      return [
        'items' => [],
        'removed' => [],
        'changed_prices' => [],
        'total' => 0,
        'count' => 0,
      ];
    }

    // Загружаем товары одним запросом
    $dbItems = Item::find()
      ->where(['item_id' => $itemIds])
      ->andWhere(['status' => Item::STATUS_ON])
      ->all();

    $dbItemsMap = [];
    foreach ($dbItems as $dbItem) {
      $dbItemsMap[$dbItem->item_id] = $dbItem;
    }

    // Загружаем характеристики (unit, size) одним запросом
    $itemData = Item::getDataForItems($itemIds, ['unit', 'size']);

    // Проходим по исходному составу
    foreach ($rawItems as $raw) {
      $itemId = (int)($raw['item_id'] ?? 0);
      $qty = (float)($raw['qty'] ?? 0);

      if ($itemId <= 0 || $qty <= 0) {
        continue;
      }

      // Товара нет в БД или он выключен
      if (!isset($dbItemsMap[$itemId])) {
        $removed[] = $itemId;
        continue;
      }

      $dbItem = $dbItemsMap[$itemId];

      // Формируем URL
      $url = $dbItem->collection
        ? Url::to([
          '/collections/'
          . $dbItem->collection->category->slug . '/'
          . $dbItem->collection->slug . '/'
          . $dbItem->slug
        ])
        : Url::to(['/catalog/' . $dbItem->category->slug . '/' . $dbItem->slug]);

      // Данные для рендера
      $unit = $itemData[$itemId]['unit'][0] ?? $dbItem->getDataValue('unit') ?? '';
      $sizes = $itemData[$itemId]['size'] ?? [];

      $items[] = [
        'item_id' => $itemId,
        'title' => $dbItem->title,
        'url' => $url,
        'image' => $dbItem->image ?: '',
        'price' => (float)$dbItem->price,
        'old_price' => (float)$dbItem->old_price,
        'qty' => $qty,
        'unit' => $unit,
        'sizes' => $sizes,
        'sum' => round($dbItem->price * $qty, 2),
        'available' => (int)$dbItem->available > 0,
      ];
    }

    // Итог
    $total = 0;
    foreach ($items as $item) {
      $total += $item['sum'];
    }

    return [
      'items' => $items,
      'removed' => $removed,
      'changed_prices' => $changedPrices,
      'total' => round($total, 2),
      'count' => count($items),
    ];
  }

  /**
   * Собрать текст заявки для Feedback
   * @param array $enrichedItems — результат enrich()
   * @param array $contacts — ['name', 'phone', 'email', 'comment']
   * @param array $delivery — ['type' => 'delivery'|'pickup', 'address', 'pickup']
   * @return string
   */
  public static function buildFeedbackText($enrichedItems, $contacts, $delivery)
  {
    $lines = [];
    $lines[] = 'Заявка из корзины';
    $lines[] = '';
    $lines[] = 'Товары:';

    foreach ($enrichedItems['items'] as $item) {
      $lines[] = sprintf(
        '• %s — %s %s × %s = %s ₽',
        $item['title'],
        number_format($item['price'], 0, '.', ' '),
        $item['unit'],
        $item['qty'],
        number_format($item['sum'], 0, '.', ' ')
      );
    }

    $lines[] = '';
    $lines[] = 'Итого: ' . number_format($enrichedItems['total'], 0, '.', ' ') . ' ₽';
    $lines[] = '';
    $lines[] = 'Контакты:';
    $lines[] = 'Имя: ' . ($contacts['name'] ?? '');
    $lines[] = 'Телефон: ' . ($contacts['phone'] ?? '');
    if (!empty($contacts['email'])) {
      $lines[] = 'Email: ' . $contacts['email'];
    }
    if (!empty($contacts['comment'])) {
      $lines[] = 'Комментарий: ' . $contacts['comment'];
    }

    $lines[] = '';
    if (($delivery['type'] ?? 'delivery') === 'pickup') {
      $lines[] = 'Способ получения: самовывоз';
      if (!empty($delivery['pickup'])) {
        $lines[] = 'Магазин: ' . $delivery['pickup'];
      }
    } else {
      $lines[] = 'Способ получения: доставка';
      if (!empty($delivery['address'])) {
        $lines[] = 'Адрес: ' . $delivery['address'];
      }
    }

    return implode("\n", $lines);
  }
}