<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use Exception;
use App\Helpers\Mailer;

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
        unset($_SESSION['premature']);
    }

    public function dashboardAction()
    {
        View::render('dashboard');
    }

    public function welcomeAction()
    {
        if(!$_SESSION['welcome']){
            header('Location: /');
        }

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
                $_SESSION['fname'] = $user[0]['first_name'];
                $_SESSION['lname'] = $user[0]['last_name'];

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

    public function userExistsApi()
    {
        try {
            $json = Json::getJson();

            if(!$json){
                throw new Exception("Erro ao processar a requisição");
            }

            if(
                !$json['username'] || 
                !$this->secure->isValid('username', $json['username'])
            ){
                throw new Exception("Usuario invalido");
            }

            if(!$json['email'] ||
            !$this->secure->isValid('email', $json['email'])
            ){
                throw new Exception("Email invalido");
            }

            $user = $this->user->userExists(
                $json['email'],
                $json['username']
            );

            if($user){
                throw new Exception("Usuário ja existe");
            }

            Json::send([
                'success' => true,
                'exists' => false
            ]);
        } catch (\Throwable $th) {
            Json::sendError('Something went wrong', 400);
        }
    }

    public function validateEmailApi()
    {
        try {
            $json = Json::getJson();

            if(!$json){
                throw new Exception("Erro ao processar a requisição");
            }

            if(
                !$json['pin'] || 
                !$this->secure->verifyPin($json['pin'])
            ){
                throw new Exception("Pin invalido");
            }

            $_SESSION['toUpdate'] = $json;

            $user = $this->user->get();

            Json::send([
                'success' => true,
                'message' => 'Validado com sucesso',
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function newAdminApi()
    {
        try {
            $json = Json::getJson();

            if(!$json){
                throw new Exception("Erro ao processar a requisição");
            }

            if(
                !$json['username'] || 
                !$this->secure->isValid('username', $json['username'])
            ){
                throw new Exception("Usuario invalido");
            }

            if(!$json['email'] ||
            !$this->secure->isValid('email', $json['email'])
            ){
                throw new Exception("Email invalido");
            }

            if( 
                !$json['password'] ||
                !$this->secure->isValid('password', $json['password'])
            ){
                throw new Exception("Senha invalida");
            }

            $user = $this->user->userExists(
                $json['email'],
                $json['username']
            );

            if($user && !$_SESSION['welcome']){
                throw new Exception("Usuario já existe");
            }

            $_SESSION['toUpdate'] = $json;

            $isSent = Mailer::sendCode([
                'email' => $json['email'],
                'name' => $_SESSION['fname'],
            ]);

            if(!$isSent){
                throw new Exception("Erro ao enviar o email");  
            }

            Json::send([
                'success' => true,
                'message' => 'Valide o email para ativar a conta',
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
