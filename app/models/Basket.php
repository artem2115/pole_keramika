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
  public static function buildFeedbackText($enrichedItems, $contacts, $delivery, $promoResult = [])
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

    // Промокод
    if (!empty($promoResult['promo_code'])) {
      $lines[] = '';
      $lines[] = 'Промокод: ' . $promoResult['promo_code'] . ' (−' . number_format($promoResult['discount'], 0, '.', ' ') . ' ₽)';
      $lines[] = 'Итого со скидкой: ' . number_format($promoResult['total_with_discount'], 0, '.', ' ') . ' ₽';
    }

    return implode("\n", $lines);
  }

  /**
   * Применить промокод к обогащённой корзине.
   * Возвращает: [discount, total_with_discount, promo_applied, promo_error, promo_code]
   *
   * @param array $enriched — результат enrich()
   * @param string|null $promoCode
   * @return array
   */
  public static function applyPromo(array $enriched, ?string $promoCode): array
  {
    $total = (float)$enriched['total'];

    if (empty($promoCode)) {
      return [
        'discount' => 0,
        'total_with_discount' => $total,
        'promo_applied' => null,
        'promo_error' => null,
        'promo_code' => null,
      ];
    }

    $promo = \yii\easyii\modules\promo\models\Promo::findByCode($promoCode);

    if (!$promo) {
      return [
        'discount' => 0,
        'total_with_discount' => $total,
        'promo_applied' => null,
        'promo_error' => 'Промокод не найден',
        'promo_code' => null,
      ];
    }

    $result = $promo->apply($total);

    if (!$result['success']) {
      return [
        'discount' => 0,
        'total_with_discount' => $total,
        'promo_applied' => null,
        'promo_error' => $result['message'],
        'promo_code' => null,
      ];
    }

    return [
      'discount' => $result['discount'],
      'total_with_discount' => round($total - $result['discount'], 2),
      'promo_applied' => $result['message'],
      'promo_error' => null,
      'promo_code' => $promo->code,
    ];
  }
}