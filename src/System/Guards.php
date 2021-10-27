<?php

namespace Src\System;

use Src\Services\AuthService;

class Guards
{
  public static function LoggedInGuard()
  {
    if (AuthService::isAuthorized()) return true;

    DefaultResponses::RespondWithUnauthorizedError();
    return false;
  }
}
