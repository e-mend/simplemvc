<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use App\Helpers\Mailer;
use App\Exceptions\LoginException;
use App\Exceptions\PermissionException;
use App\Exceptions\RequestException;
use App\Helpers\Routines;
use Exception;
use Throwable;

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
            if ($this->secure->isLoggedIn()){
                throw new PermissionException("Não autorizado");
            }

            $json = Json::getJson();

            if(!$json){
                throw new RequestException();
            }

            if (
                !$json['username'] ||
                (!$this->secure->isValid('username', $json['username']) 
                && !$this->secure->isValid('email', $json['username']))
                || !$json['password']
            ){
                throw new LoginException("Usuário ou senha inválidos");
            }

            $user = $this->user->getUserForLogin([
                'username' => $json['username'],
                'email' => $json['email']
            ]);

            if (
                $user === false ||
                ((!$this->secure->isValid('password', $json['password']) 
                || !$this->secure->verify($json['password'], $user[0]['password']))
                && $user[0]['password'] !== Secure::DEFAULT_PASSWORD)
            ){
                throw new LoginException("Usuário ou senha inválidos");
            }

            $redirect = false;

            $_SESSION['user'] = $user[0];
            $_SESSION['user']['option'] = 
            json_decode($user[0]['option'], true);

            $_SESSION['token'] = true;

            if($user[0]['password'] === Secure::DEFAULT_PASSWORD){
                $redirect = '/bemvindo'; 
                $_SESSION['welcome'] = true;
            }

            Routines::isWaitingCoroutine(true);

            Json::send([
                'success' => true,
                'redirect' => $redirect,
                'message' => $redirect ? 'Redirecionando...' : 'Logado com sucesso',
            ]);
        } catch (LoginException $login) {
            Json::send([
                'success' => false,
                'redirect' => false,
                'message' => $login->getMessage()
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'redirect' => false,
                'message' => $th->getFile().' - '.$th->getLine().' - '.$th->getMessage()
            ]);
        }
    }

    public function sendCodeApi()
    {
        try {
            if(!$this->secure->hasEmailToken()){
                throw new PermissionException();
            }

            $isSent = Mailer::sendCode([
                'email' =>  $_SESSION['user']['email'],
                'name' => $_SESSION['user']['first_name'],
            ]);

            if(!$isSent){
                throw new Exception("Algo deu errado");
            }

            unset($_SESSION['token']);

            Json::send([
                'success' => true,
                'redirect' => false,
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição'
            ]);
        }
    }

    public function userExistsApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if(!$json){
                throw new RequestException();
            }

            if(
                !$json['username'] || 
                !$this->secure->isValid('username', $json['username'])
            ){
                throw new LoginException("Usuario invalido");
            }

            if(!$json['email'] ||
            !$this->secure->isValid('email', $json['email'])
            ){
                throw new LoginException("Email invalido");
            }

            $user = $this->user->userExists(
                $json['email'],
                $json['username']
            );

            if($user){
                throw new LoginException("Usuário ja existe");
            }

            Json::send([
                'success' => true,
                'exists' => false
            ]);
        } catch (LoginException $login) {
            Json::send([
                'success' => false,
                'exists' => true,
                'message' => $login->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição'
            ]);
        }
    }

    public function validateEmailApi()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if(!$json){
                throw new RequestException();
            }

            if(
                !$json['pin'] || 
                !$this->secure->verifyPin($json['pin'])
            ){
                throw new LoginException("Pin invalido");
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
    
                if(!$toUpdate){
                    throw new loginException("Erro ao criar o usuario");
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
        } catch (LoginException $login) {
            Json::send([
                'success' => false,
                'message' => $login->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        } finally {
            unset($_SESSION['toUpdate']);
        }
    }

    public function newAdminApi()
    {
        try {
            if($this->secure->isLoggedIn() || !$_SESSION['welcome']){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if(!$json){
                throw new RequestException();
            }

            if(
                !$json['username'] || 
                !$this->secure->isValid('username', $json['username'])
            ){
                throw new LoginException("Usuario invalido");
            }

            if(!$json['email'] ||
            !$this->secure->isValid('email', $json['email'])
            ){
                throw new LoginException("Email invalido");
            }

            if (!$json['password'] ||
                !$this->secure->isValid('password', $json['password'])
            ){
                throw new LoginException("Senha invalida");
            }

            $user = $this->user->userExists(
                $json['email'],
                $json['username']
            );

            if ($user[0]['username'] != $json['username']
            && $user[0]['email'] != $json['email']) {
                throw new LoginException("Usuario já existe");
            }

            $isSent = Mailer::sendCode([
                'email' => $json['email'],
                'name' => $_SESSION['user']['first_name'],
            ]);

            if(!$isSent){
                throw new LoginException("Erro ao enviar o email");  
            }

            $_SESSION['toUpdate'] = $json;

            Json::send([
                'success' => true,
                'message' => 'Valide o email para ativar a conta',
            ]);
        } catch (LoginException $login) {
            Json::send([
                'success' => false,
                'message' => $login->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }
}
