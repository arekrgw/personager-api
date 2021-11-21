<?php

namespace Src\Controllers;

use Src\Services\EventsService;
use Src\System\DefaultResponses;
use Src\System\Guards;

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
    $allUsers = EventsService::findAll();

    echo $allUsers;
  }
}
