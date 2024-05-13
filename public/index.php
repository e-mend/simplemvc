<?php 

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();
date_default_timezone_set('America/Sao_Paulo');

require '../vendor/autoload.php';

use App\routes\Router;
use App\Helpers\Secure;

Secure::generateToken();
Router::execute();