<?php

namespace App;

/**
 * Authentication
 */
class Auth {
  /**
   * Login the user
   * 
   * @param User  $user The user model
   * 
   * @return void
   */
  public static function login($user) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user->id;
  }

  /**
   * Check if user is logged in
   * 
   * @return boolean  True if logged in, false if not.
   */
  public static function isLogin() {
    return isset($_SESSION['user_id']);
  }
}