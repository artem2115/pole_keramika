<?php
namespace yii\easyii\helpers;

class Translit
{
  private static $map = [
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
    'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
    'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
    'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
    'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
    'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd',
    'Е' => 'e', 'Ё' => 'yo', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i',
    'Й' => 'y', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
    'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't',
    'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch',
    'Ш' => 'sh', 'Щ' => 'sch', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '',
    'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya',
    ' ' => '-', '_' => '-', '.' => '-', ',' => '-', '(' => '', ')' => '',
    '№' => '', '#' => '', ':' => '-', ';' => '-', 'x' => 'x', 'х' => 'x',
  ];

  /**
   * Транслитерировать строку в slug
   */
  public static function slug($string)
  {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');

    $result = '';
    $length = mb_strlen($string, 'UTF-8');

    for ($i = 0; $i < $length; $i++) {
      $char = mb_substr($string, $i, 1, 'UTF-8');
      $result .= self::$map[$char] ?? $char;
    }

    $result = preg_replace('/[^a-z0-9\-]/', '', $result);
    $result = preg_replace('/-+/', '-', $result);
    $result = trim($result, '-');

    return $result;
  }
}