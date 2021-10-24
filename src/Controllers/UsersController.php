<?php

namespace Src\Controllers;

use Src\Services\UsersService;
use Src\System\Utils;

class UsersController
{

  private $usersService = null;
  private $uri = null;

  public function __construct($db, $uri)
  {
    $this->usersService = new UsersService($db);
    $this->uri = $uri;

    $this->processRequest();
  }

  private function processRequest()
  {
    if (!isset($this->uri[2]) || $this->uri[2] == "") {
      $this->list();
      return;
    }

    $methodName = $this->uri[2];

    if (is_callable([$this, $methodName])) {
      $this->$methodName();
      return;
    }

    Utils::RespondWithNoRouteError();
  }

  public function list()
  {
    $allUsers = $this->usersService->findAll();

    echo $allUsers;
  }

  public function active()
  {
    echo "active users";
  }
}
