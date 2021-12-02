<?php

namespace Src\Services;

use Exception;
use Src\System\Scope;

class RemindersService
{
  public static $db = null;

  public static function validateBody($event)
  {

    if (!isset($event["title"]) || strlen($event["title"]) < 3) {
      return array("error" => "name is too short");
    }
    if (!isset($event["targetDate"]) || strtotime($event["targetDate"]) - time() < 0) {
      return array("error" => "invalid date");
    }

    return true;
  }

  public static function findAll()
  {
    $stmt = "
      SELECT * FROM Reminders WHERE ownerId = :ownerId;
    ";

    $subStmt = "SELECT * FROM RemindersResolvers WHERE reminderId=:reminderId";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId));

      $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      foreach ($result as $key => &$val) {
        $prep = self::$db->prepare($subStmt);
        $prep->execute(array("reminderId" => $val["id"]));
        $val["resolvers"] = $prep->fetchAll(\PDO::FETCH_ASSOC);
      }

      return $result;
    } catch (\PDOException $e) {
      print_r($e);
      return array("error" => "something unexpected happened");
    }
  }

  public static function find($id, $resolvers = true)
  {
    $stmt = "
      SELECT * FROM Reminders WHERE ownerId=:ownerId AND id=:id;
    ";

    $subStmt = "SELECT * FROM RemindersResolvers WHERE reminderId=:reminderId";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId, "id" => $id));

      $result = $stmt->fetch(\PDO::FETCH_ASSOC);

      if ($resolvers) {
        $prep = self::$db->prepare($subStmt);
        $prep->execute(array("reminderId" => $result["id"]));
        $result["resolvers"] = $prep->fetchAll(\PDO::FETCH_ASSOC);
      }

      return $result;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function findByResolver($id)
  {
    $stmt = "
      SELECT * FROM Reminders WHERE ownerId=:ownerId AND id=(SELECT RemindersResolvers.reminderId FROM RemindersResolvers INNER JOIN Reminders ON Reminders.id = RemindersResolvers.reminderId WHERE RemindersResolvers.id = :id);
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId, "id" => $id));

      $result = $stmt->fetch(\PDO::FETCH_ASSOC);

      return $result;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function updateReminder($reminderId)
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        UPDATE Reminders SET title=:title, description=:description, targetDate=:targetDate WHERE id=:id AND ownerId=:ownerId
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "title" => $_POST["title"],
        "description" => isset($_POST["description"]) ? $_POST["description"] : "",
        "targetDate" => $_POST["targetDate"],
        "id" => $reminderId,
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $updatedReminder = self::find($reminderId);

      if (isset($updatedReminder["error"])) {
        return array("error" => "something unexpected happened");;
      }

      if (!$updatedReminder) return false;

      return $updatedReminder;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function createReminder()
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        INSERT INTO Reminders VALUES (NULL, :title, :description, :ownerId, :targetDate);
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "title" => $_POST["title"],
        "description" => isset($_POST["description"]) ? $_POST["description"] : "",
        "targetDate" => $_POST["targetDate"],
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $createdReminder = self::find(self::$db->lastInsertId());

      if (!$createdReminder) return false;

      return $createdReminder;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function deleteReminder($reminderId)
  {
    try {
      $stmt = "
        DELETE FROM Reminders WHERE id = :id AND ownerId = :ownerId;
      ";

      $stmtResolvers = "
        DELETE FROM RemindersResolvers WHERE reminderId=:reminderId;
      ";

      $properties = array(
        "id" => $reminderId,
        "ownerId" => Scope::$userId,
      );

      $stmt = self::$db->prepare($stmt);

      $stmt->execute($properties);

      $stmtResolvers = self::$db->prepare($stmtResolvers);

      $stmtResolvers->execute(array("reminderId" => $reminderId));

      return !!$stmt->rowCount();
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }
}
