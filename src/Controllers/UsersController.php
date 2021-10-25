<?php

namespace Src\Controllers;

use Src\Services\AuthService;
use Src\Services\UsersService;
use Src\System\Utils;

class UsersController
{
  private $uri = null;

  public function __construct($uri)
  {
    $this->uri = $uri;

    $this->processRequest();
  }

  private function processRequest()
  {
    // if (!isset($this->uri[2]) || $this->uri[2] == "") {
    //   $this->list();
    //   return;
    // }

    $methodName = (isset($this->uri[2]) ? $this->uri[2] : '') . 'Action';

    if (is_callable([$this, $methodName])) {
      $this->$methodName();
      return;
    }

    Utils::RespondWithNoRouteError();
  }

  // public function list()
  // {
  //   $allUsers = UsersService::findAll();

  //   echo $allUsers;
  // }

  public function activeAction()
  {
    if (AuthService::isAuthorized()) {
      echo "active users";
    } else {
      Utils::RespondWithUnauthorizedError();
    }
  }
}
