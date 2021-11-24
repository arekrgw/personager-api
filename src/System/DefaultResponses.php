<?php

namespace Src\System;


class DefaultResponses
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
  public static function RespondWithBadRequestError($error)
  {
    if (isset($error["error"])) {
      http_response_code(401);
      echo json_encode(array(
        "status" => 401,
        "error" => $error["error"],
      ));
      return true;
    }

    return false;
  }
}
