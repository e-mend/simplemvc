<?php 
namespace App\routes;

use Exception;
use App\Helpers\Request;
use App\Helpers\Uri;

final class Router
{
    private const CONTROLLER_NAMESPACE = 'App\\Controllers\\';

    public static function load(string $controller, string $method)
    {
        try {
            $controllerNamespace = self::CONTROLLER_NAMESPACE . $controller;

            if(!class_exists($controllerNamespace) || !method_exists($controllerNamespace, $method)) {   
                throw new Exception('Controller not found');
            }

            $controller = new $controllerNamespace();
            $controller->$method();
        } catch (\Throwable $th) {
            //echo $th->getMessage();

            $_SESSION['premature'] = true;
            header('Location: /');
        }
    }

    public static function routes(): array
    {
        return [
          'get' => [
                '/' => fn() => self::load('HomeController', 'indexAction'),
                '/bemvindo' => fn() => self::load('HomeController', 'welcomeAction'),
                '/inventario' => fn() => self::load('HomeController', 'dashboardAction'),
          ],
          'post' => [
                '/login' => fn() => self::load('HomeController', 'loginApi'),
                '/newAdmin' => fn() => self::load('HomeController', 'newAdminApi'),
                '/userExists' => fn() => self::load('HomeController', 'userExistsApi'),
                '/validateEmail' => fn() => self::load('HomeController', 'validateEmailApi'),
          ]
        ];
    }

    public static function execute()
    {
        try {
            $routes = self::routes();
            $request = Request::get();
            $uri = Uri::getUri('path');
            
            if(!isset($routes[$request]) || !array_key_exists($uri, $routes[$request])) {
                throw new Exception('Route not found');    
            }

            $router = $routes[$request][$uri];

            if(!is_callable($router)) {
               throw new Exception('Route not callable');
            }

            $router();

        } catch (\Throwable $th) {
            // $th->getMessage();

            $_SESSION['premature'] = true;
            header('Location: /');
        }
    }
    
}
