<?php

namespace Src\Services;

class UsersService
{
  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function findAll()
  {
    $statement = "
      SELECT * FROM Users;
    ";

    try {
      $statement = $this->db->query($statement);

      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return json_encode($result);
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
