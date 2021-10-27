<?php

namespace Src\Controllers;

use Src\System\DefaultResponses;
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

    DefaultResponses::RespondWithNoRouteError();
  }

  private function loginAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "POST") return DefaultResponses::RespondWithNoRouteError();

    $response = AuthService::loginUser();

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }
    http_response_code(200);

    echo json_encode(array("success" => true));
  }

  private function registerAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "POST") return DefaultResponses::RespondWithNoRouteError();

    $response = AuthService::registerUser();

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }
    http_response_code(201);

    echo json_encode($response);
  }
}
