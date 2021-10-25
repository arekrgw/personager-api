<?php
require "../bootstrap.php";

use Src\System\Utils;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

Utils::RespondWithNoRouteError();
