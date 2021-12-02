<?php

namespace Src\Services;

use Exception;
use Src\System\Scope;

class RemindersResolversService
{
  public static $db = null;

  public static function validateBody($resolver, $reminderTargetDate)
  {
    if (!isset($resolver["whence"]) || strtotime($resolver["whence"]) - strtotime($reminderTargetDate) >= 0) {
      return array("error" => "invalid date");
    }

    return true;
  }

  public static function find($id)
  {
    $stmt = "
      SELECT * FROM RemindersResolvers WHERE id=:id;
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("id" => $id));

      $result = $stmt->fetch(\PDO::FETCH_ASSOC);

      return $result;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function updateResolver($reminderResolverId)
  {
    try {
      $reminder = RemindersService::findByResolver($reminderResolverId);
      if (!$reminder) return false;

      $validation = self::validateBody($_POST, $reminder["targetDate"]);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        UPDATE RemindersResolvers SET whence=:whence WHERE id=:id
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "whence" => $_POST["whence"],
        "id" => $reminderResolverId,
      );

      $stmt->execute($properties);

      $updatedReminderResolver = self::find($reminderResolverId);

      if (isset($updatedReminderResolver["error"])) {
        return array("error" => "something unexpected happened");;
      }

      if (!$updatedReminderResolver) return false;

      return $updatedReminderResolver;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function createResolver($reminderId)
  {
    try {
      $reminder = RemindersService::find($reminderId, false);
      if (!$reminder) return false;

      $validation = self::validateBody($_POST, $reminder["targetDate"]);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        INSERT INTO RemindersResolvers VALUES (NULL, :reminderId, :whence);
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "reminderId" => $reminderId,
        "whence" => $_POST["whence"],
      );

      $stmt->execute($properties);

      $createdReminder = self::find(self::$db->lastInsertId());

      if (!$createdReminder) return false;

      return $createdReminder;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function deleteResolver($resolverId)
  {
    try {
      $stmt = "
        DELETE RemindersResolvers FROM RemindersResolvers INNER JOIN Reminders ON Reminders.id = RemindersResolvers.reminderId WHERE RemindersResolvers.id = :id AND Reminders.ownerId = :ownerId;
      ";

      $properties = array(
        "id" => $resolverId,
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
