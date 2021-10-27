<?php

namespace Src\Controllers;

use Src\Services\UsersService;
use Src\System\DefaultResponses;
use Src\System\Guards;
use Src\System\Scope;

class UsersController
{
  private $uri = null;

  public function __construct($uri)
  {
    $this->uri = $uri;

    $this->processRequest();

    $this->activeAction = function () {
      echo 'hello';
    };
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

    DefaultResponses::RespondWithNoRouteError();
  }

  // public function list()
  // {
  //   $allUsers = UsersService::findAll();

  //   echo $allUsers;
  // }

  public function activeAction()
  {
    if (!Guards::LoggedInGuard()) return;
    echo "active users" . Scope::$userId;
  }
}
