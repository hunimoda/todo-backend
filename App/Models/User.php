<?php

namespace App\Models;

use PDO;

class User extends \Core\Model
{
    /**
     * Get a user by email address
     * 
     * @param string    $email  Email address to search for
     * 
     * @return mixed    User object if found, false otherwise
     */
    public static function getUserByEmail($email) {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Authenticate a user by email and password.
     * 
     * @param string    $email      Email address
     * @param string    $password   Password
     * 
     * @return mixed    The user object on success, false otherwise
     */
    public static function authenticate($email, $password) {
        $user = static::getUserByEmail($email);

        if ($user) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }
        return false;
    }
}
