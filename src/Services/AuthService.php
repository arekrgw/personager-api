<?php

namespace Src\Services;

use Exception;
use Src\System\Utils;
use Firebase\JWT\JWT;
use Src\System\Scope;

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

  public static function loginUser()
  {
    try {
      $login = Utils::EscapeString(isset($_POST['login']) ? $_POST['login'] : '');
      $password = Utils::EscapeString(isset($_POST['password']) ? $_POST['password'] : '');

      $user = self::checkCredentials($login, $password);
      if (!$user) {
        return array("error" => "login or password is incorrect");
      }

      $token = self::createTokenInDatabase($user);

      self::setAuthHeaders($token);

      return true;
    } catch (Exception $e) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function createTokenInDatabase($user)
  {
    $expirationTime = time() + 60 * 60 * 24 * 30;
    $tokenData = array("email" => $user["email"], "expire" => $expirationTime);
    $salt = $_ENV['TOKEN_SALT'];

    $jwt = JWT::encode($tokenData, $salt);

    $stmt = "
      INSERT INTO Tokens VALUES (NULL, :token, :expire, :userId);
    ";

    $stmt = self::$db->prepare($stmt);

    $stmt->execute(array("token" => $jwt, "expire" => $expirationTime, "userId" => $user["id"]));

    return $jwt;
  }

  public static function checkCredentials($login, $password)
  {
    $stmt = "
      SELECT * FROM Users WHERE login=:login LIMIT 1;
    ";

    $stmt = self::$db->prepare($stmt);

    $stmt->execute(array("login" => $login));

    $results = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($results && password_verify($password, $results["password"])) {
      return $results;
    }

    return false;
  }

  public static function isAuthorized()
  {
    try {
      if (self::isAuthorizationHeaderSet()) {
        $token = self::extractToken();

        $stmt = "
          SELECT * FROM Tokens WHERE token=:token AND UNIX_TIMESTAMP() < expire LIMIT 1;
        ";

        $stmt = self::$db->prepare($stmt);

        $stmt->execute(array("token" => $token));

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
          Scope::SetScope($result["userId"]);

          return true;
        }
      }

      return false;
    } catch (Exception $e) {
      return false;
    }
  }

  public static function registerUser()
  {
    try {
      $login = Utils::EscapeString(isset($_POST['login']) ? $_POST['login'] : '');
      $email = Utils::EscapeString(isset($_POST['email']) ? $_POST['email'] : '');
      $password = Utils::EscapeString(isset($_POST['password']) ? $_POST['password'] : '');
      $firstName = Utils::EscapeString(isset($_POST['firstName']) ? $_POST['firstName'] : '');
      $lastName = Utils::EscapeString(isset($_POST['lastName']) ? $_POST['lastName'] : '');

      if (strlen($login) < 4) {
        return array("error" => "login has to be at least 4 characters long");
      }

      if (strlen($firstName) < 2) {
        return array("error" => "first name has to be at least 2 characters long");
      }

      if (strlen($lastName) < 2) {
        return array("error" => "last name has to be at least 2 characters long");
      }

      if (strlen($password) < 6) {
        return array("error" => "password has to be at least 6 characters long");
      }

      if (!Utils::IsEmailValid($email)) {
        return array("error" => "email is invalid");
      }

      if (self::userExists($email, $login)) {
        return array("error" => "user with this login or email exists");
      }

      $user = self::insertNewUser($email, $login, $password, $firstName, $lastName);

      return $user;
    } catch (\Exception $err) {
      return array("error" => "something unexpected happened");
    }
  }

  public static function userExists($email, $login)
  {
    $stmt = "
      SELECT COUNT(id) FROM Users WHERE email=:email OR login=:login;
    ";

    $stmt = self::$db->prepare($stmt);
    $stmt->execute(array("email" => $email, "login" => $login));

    $rowCount = $stmt->fetch(\PDO::FETCH_NUM);

    return !!$rowCount[0];
  }

  public static function insertNewUser($email, $login, $password, $firstName, $lastName)
  {
    $stmt = "
      INSERT INTO Users VALUES (NULL, :firstName, :lastName, :email, :password, :login);
    ";

    $stmt = self::$db->prepare($stmt);

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $properties = array(
      "firstName" => $firstName,
      "lastName" => $lastName,
      "login" => $login,
      "email" => $email,
      "password" => $hashedPassword,
    );

    $stmt->execute($properties);

    unset($properties["password"]);
    return $properties;
  }
}
