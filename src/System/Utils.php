<?php

namespace Src\System;

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
}
