<?php

namespace Src\Services;

use Src\System\Scope;

class EventsService
{
  public static $db = null;

  public static function findAll()
  {
    $stmt = "
      SELECT * FROM Events WHERE ownerId = :ownerId;
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId));

      $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return json_encode($result);
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
