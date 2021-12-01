<?php

namespace Src\Controllers;

use Src\Services\RemindersService;
use Src\System\DefaultResponses;
use Src\System\Guards;
use Src\System\Utils;

class RemindersController
{
  private $uri = null;

  public function __construct($uri)
  {
    $this->uri = $uri;

    $this->processRequest();
  }

  private function processRequest()
  {
    if (!Guards::LoggedInGuard()) return;

    if (!isset($this->uri[2]) || $this->uri[2] == "") {
      $this->list();
      return;
    }

    $methodName = (isset($this->uri[2]) ? $this->uri[2] : '') . 'Action';

    if (is_callable([$this, $methodName])) {
      $this->$methodName();
      return;
    }

    DefaultResponses::RespondWithNoRouteError();
  }

  public function list()
  {
    $response = RemindersService::findAll();

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    http_response_code(200);

    echo json_encode($response);
  }

  public function oneAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "GET" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $response = RemindersService::find($this->uri[3]);

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      http_response_code(404);
      return;
    }

    http_response_code(200);

    echo json_encode($response);
  }

  public function updateAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "PUT" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $_POST = Utils::EscapeWholeArray($_POST);

    $response = RemindersService::updateEvent($this->uri[3]);

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      http_response_code(404);
      return;
    }

    http_response_code(201);
    echo json_encode($response);
  }

  public function createAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "POST") return DefaultResponses::RespondWithNoRouteError();

    $_POST = Utils::EscapeWholeArray($_POST);

    $response = RemindersService::createEvent();

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      DefaultResponses::RespondWithBadRequestError(array("error" => "something unexpected happened"));
      return;
    }

    http_response_code(200);

    echo json_encode($response);
  }

  public function deleteAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "DELETE" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $response = RemindersService::deleteEvent($this->uri[3]);

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      http_response_code(404);
      return;
    }

    http_response_code(204);
  }
}
