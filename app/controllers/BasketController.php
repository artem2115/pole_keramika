<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Basket;
use yii\easyii\modules\feedback\models\Feedback;
use yii\easyii\modules\addresses\models\Addresses;
use yii\widgets\MaskedInputAsset;

class BasketController extends Controller
{
  /**
   * Страница корзины
   */
  public function actionIndex()
  {
    MaskedInputAsset::register($this->view);
    return $this->render('index', [
      'addresses' => Addresses::find()
        ->where(['status' => Addresses::STATUS_ON])
        ->orderBy(['title' => SORT_ASC])
        ->all()
    ]);
  }

  /**
   * Синхронизация корзины с сервером.
   * Принимает JSON с составом, возвращает обогащённые данные.
   */
  public function actionSync()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $rawItems = Yii::$app->request->post('items', []);
    if (!is_array($rawItems)) {
      return ['success' => false, 'error' => 'Invalid items'];
    }

    $data = Basket::enrich($rawItems);

    return [
      'success' => true,
      'items' => $data['items'],
      'removed' => $data['removed'],
      'changed_prices' => $data['changed_prices'],
      'total' => $data['total'],
      'count' => $data['count'],
    ];
  }

  /**
   * Отправка заявки
   */
  public function actionSubmit()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!Yii::$app->request->isPost) {
      return ['success' => false, 'message' => 'Invalid method'];
    }

    // Состав корзины
    $rawItems = Yii::$app->request->post('items', []);
    if (!is_array($rawItems) || empty($rawItems)) {
      return ['success' => false, 'message' => 'Корзина пуста'];
    }

    // Обогащаем
    $enriched = Basket::enrich($rawItems);
    if (empty($enriched['items'])) {
      return ['success' => false, 'message' => 'Товары больше не доступны'];
    }

    // Контакты
    $name = trim((string)Yii::$app->request->post('name', ''));
    $phone = trim((string)Yii::$app->request->post('phone', ''));
    $phoneDigits = preg_replace('/\D/', '', $phone);

    if (empty($name) || empty($phone)) {
      return ['success' => false, 'message' => 'Укажите имя и телефон'];
    }

    if (strlen($phoneDigits) < 11) {
      return ['success' => false, 'message' => 'Введите корректный номер телефона'];
    }
    $email = trim((string)Yii::$app->request->post('email', ''));
    $comment = trim((string)Yii::$app->request->post('comment', ''));

    if (empty($name) || empty($phone)) {
      return ['success' => false, 'message' => 'Укажите имя и телефон'];
    }

    // Доставка
    $deliveryType = (string)Yii::$app->request->post('delivery_type', 'delivery');
    $address = trim((string)Yii::$app->request->post('address', ''));
    $pickup = trim((string)Yii::$app->request->post('pickup', ''));

    // Согласие
    $agree = (int)Yii::$app->request->post('agree', 0);
    if (!$agree) {
      return ['success' => false, 'message' => 'Подтвердите согласие на обработку данных'];
    }

    // Собираем текст
    $text = Basket::buildFeedbackText($enriched, [
      'name' => $name,
      'phone' => $phone,
      'email' => $email,
      'comment' => $comment,
    ], [
      'type' => $deliveryType,
      'address' => $address,
      'pickup' => $pickup,
    ]);

    // Сохраняем в Feedback
    $feedback = new Feedback();
    $feedback->name = $name;
    $feedback->phone = $phone;
    $feedback->email = $email;
    $feedback->title = 'Заявка из корзины';
    $feedback->text = $text;

    if (!$feedback->save(false)) {
      return ['success' => false, 'message' => 'Ошибка сохранения заявки'];
    }

    return [
      'success' => true,
      'message' => 'Заявка отправлена',
    ];
  }
}