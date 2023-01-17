<?php

require './vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\Controllers\HomePageController;
use System\Router;

$router = new Router();

$router->get('/', HomePageController::class . '::index');

$router->run();
