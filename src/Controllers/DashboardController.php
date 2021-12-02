<?php

namespace Src\Controllers;

use Src\Services\DashboardService;
use Src\System\DefaultResponses;
use Src\System\Guards;

class DashboardController
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
    $response = DashboardService::getDashboard();

    if (DefaultResponses::RespondWithBadRequestError($response)) return;

    if (!$response) {
      http_response_code(400);
      return;
    }

    http_response_code(200);

    echo json_encode($response);
  }
}
