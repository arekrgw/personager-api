<?php

namespace Src\Services;

use Exception;
use Src\System\Scope;

class TodosService
{
  public static $db = null;

  public static function validateBody($todoList)
  {

    if (!isset($todoList["name"]) || strlen($todoList["name"]) < 3) {
      return array("error" => "name is too short");
    }

    if (!isset($todoList["completed"]) || !is_bool($todoList["completed"])) {
      return array("error" => "completed is not true nor false");
    }

    if (!isset($todoList["todos"]) || !is_array($todoList["todos"])) {
      return array("error" => "todo field is not an array");
    }

    foreach ($todoList["todos"] as $key => $value) {
      if (!isset($value["description"]) || strlen($value["description"]) < 1) {
        return array("error" => "todo[$key] description is too short");
      }

      if (!isset($value["completed"]) || !is_bool($value["completed"])) {
        return array("error" => "todo[$key] completed is not true nor false");
      }
    }

    return true;
  }

  private static function modifyTodoObject(&$todo)
  {
    $todo["completed"] = boolval($todo["completed"]);
    $todo["todos"] = json_decode($todo["todos"]);
  }

  public static function findAll()
  {
    $stmt = "
      SELECT * FROM Todos WHERE ownerId = :ownerId;
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId));

      $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      foreach ($results as &$result) {
        self::modifyTodoObject($result);
      }

      return $results;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function find($id)
  {
    $stmt = "
      SELECT * FROM Todos WHERE ownerId=:ownerId AND id=:id;
    ";

    try {
      $stmt = self::$db->prepare($stmt);

      $stmt->execute(array("ownerId" => Scope::$userId, "id" => $id));

      $result = $stmt->fetch(\PDO::FETCH_ASSOC);

      self::modifyTodoObject($result);

      return $result;
    } catch (\PDOException $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function updateTodo($todoId)
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        UPDATE Todos SET name=:name, completed=:completed, todos=:todos WHERE id=:id AND ownerId=:ownerId
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "name" => $_POST["name"],
        "completed" => (int)$_POST["completed"],
        "todos" => json_encode($_POST["todos"]),
        "id" => $todoId,
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $updatedTodo = self::find($todoId);

      if (isset($updatedTodo["error"])) {
        return array("error" => "something unexpected happened");;
      }

      if (!$updatedTodo) return false;

      return $updatedTodo;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function createTodo()
  {
    try {
      $validation = self::validateBody($_POST);

      if (isset($validation["error"])) {
        return $validation;
      }

      $stmt = "
        INSERT INTO Todos VALUES (NULL, :name, :completed, :ownerId, :todos);
      ";

      $stmt = self::$db->prepare($stmt);

      $properties = array(
        "name" => $_POST["name"],
        "completed" => (int)$_POST["completed"],
        "todos" => json_encode($_POST["todos"]),
        "ownerId" => Scope::$userId,
      );

      $stmt->execute($properties);

      $updatedTodo = self::find(self::$db->lastInsertId());

      if (!$updatedTodo) return false;

      return $updatedTodo;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function deleteTodo($todoId)
  {
    try {
      $stmt = "
        DELETE FROM Todos WHERE id = :id AND ownerId = :ownerId;
      ";

      $properties = array(
        "id" => $todoId,
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
