<?php

namespace Src\Controllers;

use Src\System\Utils;
use Src\Services\AuthService;


class AuthController
{
  private $uri = null;

  public function __construct($uri)
  {
    $this->uri = $uri;

    $this->processRequest();
  }

  private function processRequest()
  {
    $methodName = (isset($this->uri[2]) ? $this->uri[2] : '') . 'Action';

    if (is_callable([$this, $methodName])) {
      $this->$methodName();
      return;
    }

    Utils::RespondWithNoRouteError();
  }

  private function loginAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "POST") return Utils::RespondWithNoRouteError();

    echo AuthService::loginUser();
  }
}
