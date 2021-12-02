<?php

namespace Src\Controllers;

use Src\Services\RemindersResolversService;
use Src\System\DefaultResponses;
use Src\System\Guards;
use Src\System\Utils;

class RemindersResolversController
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

    $methodName = (isset($this->uri[2]) ? $this->uri[2] : '') . 'Action';

    if (is_callable([$this, $methodName])) {
      $this->$methodName();
      return;
    }

    DefaultResponses::RespondWithNoRouteError();
  }

  public function updateAction()
  {
    if ($_SERVER['REQUEST_METHOD'] != "PUT" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $_POST = Utils::EscapeWholeArray($_POST);

    $response = RemindersResolversService::updateResolver($this->uri[3]);

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
    if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($this->uri[3])) return DefaultResponses::RespondWithNoRouteError();

    $_POST = Utils::EscapeWholeArray($_POST);

    $response = RemindersResolversService::createResolver($this->uri[3]);

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

    $response = RemindersResolversService::deleteResolver($this->uri[3]);

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      http_response_code(404);
      return;
    }

    http_response_code(204);
  }
}
