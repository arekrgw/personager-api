<?php

namespace Src\System;

use Src\Services\AuthService;

class Guards
{
  public static function LoggedInGuard()
  {
    if (AuthService::isAuthorized()) return true;

    Utils::RespondWithUnauthorizedError();
    return false;
  }
}
