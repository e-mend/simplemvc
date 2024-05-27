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
            // echo $th->getMessage();

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
                '/inventario' => fn() => self::load('DashboardController', 'dashboardAction'),
                '/sendcode' => fn() => self::load('HomeController', 'sendCodeApi'),
                '/userdata' => fn() => self::load('DashboardController', 'userDataApi'),
                '/logout' => fn() => self::load('DashboardController', 'logoutApi'),
                '/getusers' => fn() => self::load('DashboardController', 'getUsersApi'),
                '/password' => fn() => self::load('NewUserController', 'changePasswordAction'),
                '/getlinks' => fn() => self::load('DashboardController', 'getLinksApi'),
                '/newuser' => fn() => self::load('NewUserController', 'newUserAction'),
                '/disableuser' => fn() => self::load('DashboardController', 'disableUserApi'),
                '/foresight' => fn() => self::load('DashboardController', 'routinesApi'),
                '/getitems' => fn() => self::load('InventoryController', 'getItemsApi'),
                '/toggleitemfavorite' => fn() => self::load('InventoryController', 'toggleFavoriteApi'),
          ],
          'post' => [
                '/login' => fn() => self::load('HomeController', 'loginApi'),
                '/newadmin' => fn() => self::load('HomeController', 'newAdminApi'),
                '/userexists' => fn() => self::load('HomeController', 'userExistsApi'),
                '/validateemail' => fn() => self::load('HomeController', 'validateEmailApi'),
                '/togglefavorite' => fn() => self::load('DashboardController', 'toggleFavoriteApi'),
                '/updatepassword' => fn() => self::load('DashboardController', 'updatePasswordApi'),
                '/updateuser' => fn() => self::load('DashboardController', 'updateUserApi'),
                '/disableuser' => fn() => self::load('DashboardController', 'disableUserApi'),
                '/sendpasswordemail' => fn() => self::load('DashboardController', 'sendPasswordApi'),
                '/changepassword' => fn() => self::load('NewUserController', 'changePasswordApi'),
                '/createlink' => fn() => self::load('NewUserController', 'createNewUserLinkApi'),
                '/newuser' => fn() => self::load('NewUserController', 'validateNewUserApi'),
                '/validatenewemail' => fn() => self::load('NewUserController', 'validateNewUserEmailApi'),
                '/changepermissions' => fn() => self::load('NewUserController', 'changePermissionApi'),
                '/additem' => fn() => self::load('InventoryController', 'addItemApi'),
                '/uploadimage' => fn() => self::load('InventoryController', 'uploadImageApi'),
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
            //echo $th->getMessage();
            
            $_SESSION['premature'] = true;
            header('Location: /');
        }
    }
    
}
