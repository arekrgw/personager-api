<?php

namespace Src\Services;

use Exception;
use Src\System\Scope;

class EventsService
{
  public static $db = null;

  public static function validateBody($event)
  {

    if (!isset($event["name"]) || strlen($event["name"]) < 3) {
      return array("error" => "name is too short");
    }
    if (!isset($event["startDate"]) || !isset($event["endDate"]) || strtotime($event["startDate"]) - strtotime($event["endDate"]) > 0) {
      return array("error" => "invalid dates");
    }

    return true;
  }

  public static function findAll()
  {
    $stmt = "
      SELECT * FROM Events WHERE ownerId = :ownerId;
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId));

      $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function find($id)
  {
    $stmt = "
      SELECT * FROM Events WHERE ownerId=:ownerId AND id=:id;
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

  public static function updateEvent($eventId)
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        UPDATE Events SET name=:name, description=:description, startDate=:startDate, endDate=:endDate WHERE id=:id AND ownerId=:ownerId
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "name" => $_POST["name"],
        "description" => isset($_POST["description"]) ? $_POST["description"] : "",
        "startDate" => $_POST["startDate"],
        "endDate" => $_POST["endDate"],
        "id" => $eventId,
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $updatedEvent = self::find($eventId);

      if (isset($updatedEvent["error"])) {
        return array("error" => "something unexpected happened");;
      }

      if (!$updatedEvent) return false;

      return $updatedEvent;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function createEvent()
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        INSERT INTO Events VALUES (NULL, :startDate, :endDate, :name, :description, :ownerId);
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "name" => $_POST["name"],
        "description" => isset($_POST["description"]) ? $_POST["description"] : "",
        "startDate" => $_POST["startDate"],
        "endDate" => $_POST["endDate"],
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $updatedEvent = self::find(self::$db->lastInsertId());

      if (!$updatedEvent) return false;

      return $updatedEvent;
    } catch (Exception $e) {
      var_dump($e);
      return array("error" => "something unexpected happened");
    }
  }

  public static function deleteEvent($eventId)
  {
    try {
      $stmt = "
        DELETE FROM Events WHERE id = :id AND ownerId = :ownerId;
      ";

      $properties = array(
        "id" => $eventId,
        "ownerId" => Scope::$userId,
      );

      $stmt = self::$db->prepare($stmt);

      $stmt->execute($properties);

      return !!$stmt->rowCount();
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }


}
