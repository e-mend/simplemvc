<?php 

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();
date_default_timezone_set('America/Sao_Paulo');

require '../vendor/autoload.php';

use App\Routes\Router;
use App\Helpers\Secure;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__,1));
$dotenv->load();

Secure::generateToken();
Router::execute();