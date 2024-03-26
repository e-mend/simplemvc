<?php 

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();

require '../vendor/autoload.php';

use App\routes\Router;

Router::execute();