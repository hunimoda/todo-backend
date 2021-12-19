<?php

/**
 * Front controller
 * 
 * Created by: Hunimoda
 * Date: 2021-12-19
 * 
 * PHP version 8.0.12
 */

// Define custom constants
define('ROOT', dirname(__DIR__));
define('WEB_ROOT', ROOT . '/public');

// Composer autoload
require ROOT . '/vendor/autoload.php';

/********** Error/exception handling **************************/
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

// Start sessions
session_start();

/********** Routing *******************************************/
$router = new Core\Router();
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'login']);
$router->add('<controller>/<action>');

$router->dispatch($_SERVER['QUERY_STRING']);