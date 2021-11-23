<?php

namespace Src\Controllers;

use Src\Services\EventsService;
use Src\System\DefaultResponses;
use Src\System\Guards;
use Src\System\Utils;

class EventsController
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
    $response = EventsService::findAll();

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }

    http_response_code(200);

    echo json_encode($response);
  }

  public function updateAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "PUT" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $_POST = Utils::EscapeWholeArray($_POST);

    $response = EventsService::updateEvent($this->uri[3]);

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }

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

    $response = EventsService::createEvent();

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }
    http_response_code(200);

    echo json_encode($response);
  }

  public function deleteAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "DELETE" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $response = EventsService::deleteEvent($this->uri[3]);

    if (isset($response["error"])) {
      http_response_code(400);
      echo json_encode(array("success" => false, "error" => $response["error"]));
      return;
    }

    if (!$response) {
      http_response_code(404);
      return;
    }

    http_response_code(204);
  }
}
