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
        if($this->secure->isLoggedIn()){
            header('Location: /inventario');
        }

        View::render('login');
        unset($_SESSION['premature']);
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
            if($this->secure->isLoggedIn()){
                throw new Exception("Not Authorized");
            }

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

            $user = $this->user->getUsers([
                'username' => $json['username'],
                'email' => $json['email']
            ]);

            if( 
                !$json['password']
                || ((!$this->secure->isValid('password', $json['password']) 
                || !$this->secure->verify($json['password'], $user[0]['password']))
                && $user[0]['password'] !== Secure::DEFAULT_PASSWORD)
            ){
                throw new Exception("Invalid Password");
            }

            if(!$user){
                throw new Exception("There is no user with this username and password");
            }

            $redirect = false;

            $_SESSION['user'] = $user[0];
            $_SESSION['user']['permission'] = 
            json_decode($_SESSION['user']['permission'], true)['permission'];
            $_SESSION['token'] = true;

            if(count($user) === 1 && $user[0]['password'] === Secure::DEFAULT_PASSWORD){
                $redirect = '/bemvindo'; 
                $_SESSION['welcome'] = true;
            }

            User::isWaitingCoroutine(true);

            Json::send([
                'success' => true,
                'redirect' => $redirect
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'redirect' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function sendCodeApi()
    {
        try {
            if(!$this->secure->hasEmailToken()){
                throw new Exception("Not Authorized");
            }

            $isSent = Mailer::sendCode([
                'email' =>  $_SESSION['user']['email'],
                'name' => $_SESSION['user']['first_name'],
            ]);

            if(!$isSent){
                unset($_SESSION['token']);
                throw new Exception("Algo deu errado");
            }

            Json::send([
                'success' => true,
                'redirect' => false,
                'session' => $_SESSION
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $this->secure->isLoggedIn()
            ]);
        }
    }

    public function userExistsApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Not Authorized");
            }

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
            if($this->secure->isLoggedIn()){
                throw new Exception("Not Authorized");
            }

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

            $redirect = false;

            if($_SESSION['welcome']){
                $toUpdate = $this->user->update([
                    'email' => $_SESSION['toUpdate']['email'],
                    'username' => $_SESSION['toUpdate']['username'],
                    'password' => $this->secure->hash($_SESSION['toUpdate']['password'])
                ], $_SESSION['user']['id']);
    
                $user = $this->user->get()[0];
                $_SESSION['user'] = $user;
                $_SESSION['user']['permission'] = json_decode($_SESSION['user']['permission'], true)['permission'];
    
                if(!$toUpdate){
                    throw new Exception($toUpdate);
                }

                $redirect = '/inventario';
                unset($_SESSION['welcome']);
            }

            $_SESSION['logged'] = true;

            Json::send([
                'success' => true,
                'message' => 'Validado com sucesso',
                'redirect' => $redirect
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
            if($this->secure->isLoggedIn()){
                throw new Exception("Not Authorized");
            }

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

            if(!$_SESSION['welcome'] 
            || $user[0]['username'] != Secure::ADMIN
            || count($user) > 1
           ){
                throw new Exception("Usuario já existe");
            }

            $_SESSION['toUpdate'] = $json;

            $isSent = Mailer::sendCode([
                'email' => $json['email'],
                'name' => $_SESSION['user']['first_name'],
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
