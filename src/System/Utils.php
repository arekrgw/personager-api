<?php

namespace Src\System;

use Src\Services\UsersService;
use Src\Services\AuthService;

class Utils
{
  public static function RespondWithNoRouteError()
  {
    http_response_code(404);
    echo json_encode(array(
      "status" => 404,
      "error" => "this route does not exist"
    ));
  }

  public static function RespondWithUnauthorizedError()
  {
    http_response_code(401);
    echo json_encode(array(
      "status" => 401,
      "error" => "unauthorized"
    ));
  }

  public static function InjectDbIntoServices($db)
  {
    UsersService::$db = $db;
    AuthService::$db = $db;
  }
}
