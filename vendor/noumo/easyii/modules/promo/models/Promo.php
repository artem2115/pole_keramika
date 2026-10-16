<?php
namespace yii\easyii\modules\promo\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * Модель промокода.
 *
 * @property int $promo_id
 * @property string $code Код промокода (уникальный)
 * @property string $type Тип скидки: percent | fixed
 * @property float $value Значение скидки (процент или сумма)
 * @property float|null $min_sum Минимальная сумма корзины
 * @property int|null $date_from Timestamp начала действия
 * @property int|null $date_to Timestamp окончания действия
 * @property int|null $usage_limit Лимит использований
 * @property int $usage_count Сколько раз использован
 * @property int $status 1 = активен, 0 = выключен
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property bool $date_from_enabled Виртуальное поле: включена ли дата начала
 * @property bool $date_to_enabled Виртуальное поле: включена ли дата окончания
 */
class Promo extends \yii\easyii\components\ActiveRecord
{
  /** @var int Промокод выключен */
  public const STATUS_OFF = 0;

  /** @var int Промокод включён */
  public const STATUS_ON = 1;

  /** @var string Скидка в процентах */
  public const TYPE_PERCENT = 'percent';

  /** @var string Скидка фиксированной суммой */
  public const TYPE_FIXED = 'fixed';

  /**
   * Виртуальное поле: "действует с" включено
   * @var bool
   */
  public bool $date_from_enabled = false;

  /**
   * Виртуальное поле: "действует до" включено
   * @var bool
   */
  public bool $date_to_enabled = false;

  public static function tableName(): string
  {
    return '{{%promo}}';
  }

  /**
   * Список доступных типов скидки
   * @return array<string, string>
   */
  public static function getTypes(): array
  {
    return [
      self::TYPE_PERCENT => 'Процент (%)',
      self::TYPE_FIXED => 'Фиксированная сумма (₽)',
    ];
  }

  public function rules(): array
  {
    return [
      [['code', 'type', 'value'], 'required'],
      ['code', 'trim'],
      ['code', 'string', 'max' => 64],
      ['code', 'unique'],
      ['code', 'match', 'pattern' => '/^[A-Z0-9_-]+$/i', 'message' => 'Только латиница, цифры, дефис и подчёркивание'],
      ['type', 'in', 'range' => array_keys(self::getTypes())],
      ['value', 'number', 'min' => 0],
      ['value', 'validateValue'],
      [['min_sum'], 'number', 'min' => 0],
      [['date_from', 'date_to', 'usage_limit', 'usage_count', 'status'], 'integer'],
      [['date_from_enabled', 'date_to_enabled'], 'boolean'],
      ['usage_limit', 'default', 'value' => null],
      ['usage_count', 'default', 'value' => 0],
      ['status', 'default', 'value' => self::STATUS_ON],
      ['date_to', 'validateDates'],
      ['date_from', 'required', 'when' => fn(self $model): bool => $model->date_from_enabled,
        'whenClient' => 'function() { return $("#promo-date_from_enabled").is(":checked"); }'],
      ['date_to', 'required', 'when' => fn(self $model): bool => $model->date_to_enabled,
        'whenClient' => 'function() { return $("#promo-date_to_enabled").is(":checked"); }'],
    ];
  }

  /**
   * Проверка: процент не может быть больше 100
   */
  public function validateValue(string $attribute): void
  {
    if ($this->type === self::TYPE_PERCENT && (float)$this->value > 100) {
      $this->addError($attribute, 'Процент не может быть больше 100');
    }
  }

  /**
   * Проверка: дата окончания должна быть позже даты начала
   */
  public function validateDates(string $attribute): void
  {
    if ($this->date_from && $this->date_to && $this->date_to < $this->date_from) {
      $this->addError($attribute, 'Дата окончания должна быть позже даты начала');
    }
  }

  public function attributeLabels(): array
  {
    return [
      'code' => 'Код промокода',
      'type' => 'Тип скидки',
      'value' => 'Значение',
      'min_sum' => 'Мин. сумма корзины, ₽',
      'date_from' => 'Действует с',
      'date_to' => 'Действует до',
      'date_from_enabled' => 'Действует с',
      'date_to_enabled' => 'Действует до',
      'usage_limit' => 'Лимит использований',
      'usage_count' => 'Использовано',
      'status' => 'Активен',
    ];
  }

  public function behaviors(): array
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function beforeSave($insert): bool
  {
    if (!parent::beforeSave($insert)) {
      return false;
    }

    $this->code = mb_strtoupper(trim((string)$this->code));

    // Если чекбокс "с" выключен — обнуляем дату
    if (!$this->date_from_enabled) {
      $this->date_from = null;
    }
    // Если чекбокс "до" выключен — обнуляем дату
    if (!$this->date_to_enabled) {
      $this->date_to = null;
    }

    return true;
  }

  public function afterFind(): void
  {
    parent::afterFind();
    $this->date_from_enabled = !empty($this->date_from);
    $this->date_to_enabled = !empty($this->date_to);
  }

  // ============================================================
  // Бизнес-логика
  // ============================================================

  /**
   * Найти активный промокод по коду
   *
   * @param string|null $code
   * @return self|null
   */
  public static function findByCode(?string $code): ?self
  {
    if (empty($code)) {
      return null;
    }

    return self::findOne([
      'code' => mb_strtoupper(trim($code)),
      'status' => self::STATUS_ON,
    ]);
  }

  /**
   * Проверить, применим ли промокод к корзине на сумму $cartTotal.
   *
   * @param float $cartTotal
   * @return array{success: bool, discount: float, message: string}
   */
  public function apply(float $cartTotal): array
  {
    $now = time();

    // Активность
    if ((int)$this->status !== self::STATUS_ON) {
      return $this->error('Промокод неактивен');
    }

    // Срок действия
    if ($this->date_from && $now < $this->date_from) {
      return $this->error('Промокод ещё не начал действовать');
    }
    if ($this->date_to && $now > $this->date_to) {
      return $this->error('Срок действия промокода истёк');
    }

    // Лимит использований
    if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
      return $this->error('Лимит использований промокода исчерпан');
    }

    // Минимальная сумма
    if ($this->min_sum !== null && $this->min_sum > 0 && $cartTotal < $this->min_sum) {
      return $this->error('Промокод действует при сумме заказа от ' . number_format((float)$this->min_sum, 0, '.', ' ') . ' ₽');
    }

    // Считаем скидку
    $discount = $this->type === self::TYPE_PERCENT
      ? round($cartTotal * (float)$this->value / 100, 2)
      : min((float)$this->value, $cartTotal);

    return [
      'success' => true,
      'discount' => $discount,
      'message' => 'Промокод ' . $this->code . ' применён. ' . $this->getDiscountLabel(),
    ];
  }

  /**
   * Текстовое описание скидки: "Скидка 10%" или "Скидка 500 ₽"
   */
  public function getDiscountLabel(): string
  {
    if ($this->type === self::TYPE_PERCENT) {
      return 'Скидка ' . (float)$this->value . '%';
    }
    return 'Скидка ' . number_format((float)$this->value, 0, '.', ' ') . ' ₽';
  }

  /**
   * Отметить использование промокода
   */
  public function incrementUsage(): void
  {
    $this->updateCounters(['usage_count' => 1]);
  }

  /**
   * Хелпер: вернуть неуспешный результат apply()
   *
   * @return array{success: bool, discount: float, message: string}
   */
  private function error(string $message): array
  {
    return ['success' => false, 'discount' => 0, 'message' => $message];
  }
}