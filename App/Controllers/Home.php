<?php

namespace App\Controllers;

use \Core\View;

/**
 * Home controller
 */
class Home extends \Core\Controller {
    // Landing page
    protected function index() {
        View::render('Home/index.html');
    }
}