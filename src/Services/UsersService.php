<?php

namespace Src\Services;

class UsersService
{
  public static $db = null;

  public static function findAll()
  {
    $statement = "
      SELECT * FROM Users;
    ";

    try {
      $statement = self::$db->query($statement);

      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return json_encode($result);
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
