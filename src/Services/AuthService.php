<?php

namespace Src\Services;

class AuthService
{
  public static $db = null;

  private static function extractToken()
  {
    return str_replace("Bearer ", "", $_SERVER['HTTP_AUTHORIZATION']);
  }

  private static function isAuthorizationHeaderSet()
  {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) return true;
    return false;
  }


  private static function setAuthHeaders($token)
  {
    header("Authorization: Bearer $token");
  }

  /**
   * TODO:
   * sanitize post vars
   */
  public static function loginUser()
  {
    $login = isset($_POST['login']) ? $_POST['login'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    self::setAuthHeaders("1eqwder123rdqerq1231wqe");
    return "$login and $password";
  }

  /**
   * TODO
   */
  public static function isAuthorized()
  {
    if (self::isAuthorizationHeaderSet()) {
      $token = self::extractToken();

      //check in database

      echo $token;

      return true;
    }

    return false;
  }
}
