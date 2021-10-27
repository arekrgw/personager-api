<?php

namespace Src\System;

class Scope
{
  public static $db;
  public static $userId = null;

  public static function SetScope($userId)
  {
    self::$userId = $userId;
  }
}
