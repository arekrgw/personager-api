<?php

namespace Src\System;

use Src\Services\UsersService;
use Src\Services\AuthService;
use Src\Services\EventsService;

class Utils
{
  public static function InjectDbIntoServices($db)
  {
    UsersService::$db = $db;
    AuthService::$db = $db;
    EventsService::$db = $db;
    Scope::$db = $db;
  }

  public static function EscapeString($string)
  {
    $str = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    $str = htmlentities($string, ENT_QUOTES, 'UTF-8');

    return $str;
  }

  public static function IsEmailValid($string)
  {
    return !!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string);
  }
}
