<?php
require "../bootstrap.php";

use Src\System\DefaultResponses;
use Src\System\Utils;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
header('Access-Control-Expose-Headers: Authorization');


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  exit(0);
}

$_POST = json_decode(file_get_contents('php://input'), true);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

Utils::InjectDbIntoServices($dbConnection);

$controllers = array(
  "users" => 'Src\Controllers\UsersController',
  "auth" => 'Src\Controllers\AuthController',
);

if (isset($controllers[$uri[1]])) {
  new $controllers[$uri[1]]($uri);
  return;
}

DefaultResponses::RespondWithNoRouteError();
