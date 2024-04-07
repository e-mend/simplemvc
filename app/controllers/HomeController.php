<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use Exception;

class HomeController extends Controller
{
    private User $user;
    private Secure $secure;

    public function __construct()
    {
        $this->user = new User();
        $this->secure = Secure::getInstance();
    }

    public function indexAction()
    {
        View::render('login');
    }

    public function dashboardAction()
    {
        View::render('dashboard');
    }

    public function welcomeAction()
    {
        View::render('welcome');
    }

    public function loginApi()
    {
        try {
            $json = Json::getJson();

            if(!$json){
                throw new Exception("Error Processing Request");
            }

            if(
                !$json['username'] ||
                (!$this->secure->isValid('username', $json['username']) &&
                !$this->secure->isValid('email', $json['username']))
            ){
                throw new Exception("Invalid Username");
            }

            if( 
                !$json['password'] ||
                !$this->secure->isValid('password', $json['password'])
            ){
                throw new Exception("Invalid Password");
            }

            $user = $this->user->getUsers([
                'username' => $json['username'],
                'password' => $json['password']
            ]);

            if(!$user){
                throw new Exception("There is no user with this username and password");
            }

            if(count($user) === 1){
                $redirect = '/welcome';

                $_SESSION['welcome'] = true;
                setcookie('welcome', true, time() + 3600, '/');
            }

            Json::send([
                'success' => true,
                'redirect' => $redirect
            ]);
        } catch (\Throwable $th) {
            Json::sendError('Something went wrong', 400);
        }
    }
}
