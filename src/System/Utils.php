<?php

namespace Src\System;

use Src\Services\UsersService;
use Src\Services\AuthService;
use Src\Services\DashboardService;
use Src\Services\EventsService;
use Src\Services\RemindersResolversService;
use Src\Services\RemindersService;
use Src\Services\TodosService;

class Utils
{
  public static function InjectDbIntoServices($db)
  {
    UsersService::$db = $db;
    AuthService::$db = $db;
    EventsService::$db = $db;
    TodosService::$db = $db;
    RemindersService::$db = $db;
    RemindersResolversService::$db = $db;
    DashboardService::$db = $db;
    Scope::$db = $db;
  }

  private static function isAssoc(array $arr)
  {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  public static function EscapeWholeArray($arr)
  {
    $newArr = array();

    if (is_array($arr) && !self::isAssoc($arr)) {
      foreach ($arr as $element) {
        array_push($newArr, self::EscapeWholeArray($element));
      }

      return $newArr;
    }

    foreach ($arr as $key => $value) {
      if (is_array($value)) {
        $newArr[$key] = self::EscapeWholeArray($value);
      } else if (is_bool($value) || is_numeric($value)) {
        $newArr[$key] = $value;
      } else {
        $newArr[$key] = self::EscapeString($value);
      }
    }

    return $newArr;
  }

  public static function EscapeString($string)
  {
    $str = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');

    return $str;
  }

  public static function IsEmailValid($string)
  {
    return !!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string);
  }
}
