<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;

/**
 * Login controller
 */
class Login extends \Core\Controller
{
    protected function login()
    {
        $loginJson = file_get_contents('php://input');
        $auth = json_decode($loginJson);

        $user = User::authenticate($auth->username, $auth->password);
        if ($user) {
            $response['authenticated'] = true;
            Auth::login($user);
        } else {
            $response['authenticated'] = false;
        }
        $response['temp'] = password_hash("password", PASSWORD_DEFAULT);
        echo json_encode($response);
    }

    protected function check() {
        echo json_encode(['isLogin' => Auth::isLogin()]);
    }
}
