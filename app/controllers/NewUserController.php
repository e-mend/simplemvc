<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use App\Requests\Req;
use Carbon\Carbon;
use App\Helpers\Mailer;
use App\enum\AclRole;
use App\Exceptions\ReachableException;
use App\Exceptions\PermissionException;
use App\Exceptions\RequestException;
use App\Helpers\Routines;
use Throwable;
use Exception;

class NewUserController extends Controller
{
    private User $user;
    private Secure $secure;

    public function __construct()
    {
        $this->user = new User();
        $this->secure = Secure::getInstance();
    }

    public function newUserAction()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new Exception();
            }

            $request = Req::getParams();

            if (!$request){
                throw new Exception();
            }

            if (!$request['token'] || !$this->secure->isValidHex($request['token'])){
                throw new Exception("Token invalido");
            }

            $token = $this->user->getNewUserLink($request['token']);

            if(!$token){
                throw new Exception("Token expirado");
            }

            View::render('newUser');
        } catch (Throwable $th) {
            $_SESSION['premature'] = true;
            header("Location: /");
        }
    }

    public function newUserApi()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if(!$json['token'] || !$this->secure->isValidHex($json['token'])){
                throw new ReachableException("Token invalido");
            }

            $token = $this->user->getLinks([
                'eq' => [
                    'link' => $json['token'],
                    'type' => 'user',
                ]
            ])[0];

            if(!$token ||
            Carbon::parse($token['disabled_at'])
            ->diffInHours(Carbon::now()) > User::NEW_USER_LINK_EXPIRE_MINUTES){  
                throw new ReachableException("Token expirado");
            }
            
            if(!$json['email'] || !$this->secure->isValid('email', $json['email'])){
                throw new ReachableException("Email já existente");
            }

            if(!$json['username'] || !$this->secure->isValid('username', $json['username'])){
                throw new ReachableException("Usuário já existente");
            }

            if(!$json['password'] || !$this->secure->isValid('password', $json['password'])){
                throw new ReachableException("Senha invalida");
            }

            if(!$json['firstName'] || !$this->secure->isValid('name', $json['firstName'])
            || !$json['lastName'] || !$this->secure->isValid('name', $json['lastName'])){
                throw new ReachableException("Nome invalido");
            }

            $user = $this->user->userExists($json['email'], $json['username'])[0];

            if($user){
                if($user['email'] === $json['email']){
                    throw new ReachableException("Email ja existe");
                }

                if($user['username'] === $json['username']){
                    throw new ReachableException("Usuário ja existe");
                }
            }

            $isSent = Mailer::sendCode([
                'email' => $json['email'],
                'name' => $json['firstName'],
            ]);

            if (!$isSent){
                throw new ReachableException("Erro ao enviar o email");
            }

            $_SESSION['user_to_create'] = $json;
            $_SESSION['user_to_create']['created_by'] = $token['created_by'];
            $_SESSION['user_to_create']['option'] = [
                'permission' => json_decode($token['option'], true)['permission']
            ];

            Json::send([
                'success' => true,
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function validateNewEmailApi()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if (!$json['pin'] || 
                !$this->secure->verifyPin($json['pin'])
            ){
                throw new ReachableException("Pin invalido");
            }

            $toCreate = [
                'username' => $_SESSION['user_to_create']['username'],
                'password' => $this->secure->hash($_SESSION['user_to_create']['password']),
                'first_name' => $_SESSION['user_to_create']['firstName'],
                'last_name' => $_SESSION['user_to_create']['lastName'],
                'email' => $_SESSION['user_to_create']['email'],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_by' => $_SESSION['user_to_create']['created_by'],
                'option' => json_encode($_SESSION['user_to_create']['option']),
            ];

            $createUser = $this->user->createUser($toCreate);

            if(!$createUser){
                throw new ReachableException("Erro ao criar o usuário");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $createUser
                ]
            ])[0];

            $user['option'] = json_decode($user['option'], true);
            $_SESSION['user'] = $user;

            unset($_SESSION['user_to_create']);

            $_SESSION['logged'] = true;

            Json::send([
                'success' => true,
                'message' => 'Redirecionando para o inventário',
                'redirect' => '/inventario'
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function changePermissionApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || 
            !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if(!$json['id'] || !$json['permission']){
                throw new ReachableException("Dados invalidos");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $json['id']
                ]
            ])[0];

            if(!$user){
                throw new ReachableException("Id invalido");
            }

            $option = json_decode($user['option'], true);

            if($option['permission'][AclRole::SUPER_ADMIN->value] === true){
                throw new ReachableException("Impossível alterar permissoes de super admin");
            }

            $option['permission'] = $json['permission'];

            $user = $this->user->update([
                'option' => json_encode($option),
            ], $json['id']);

            Routines::foresightCoroutine($json['id'], 'reset');

            Json::send([
                'success' => $user,
                'message' => 'Alterado com sucesso',
            ]);

        }  catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function createNewLinkApi()
    {
        try {
            if(!$this->secure->isLoggedIn() 
            || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            $isValidEmail = $this->secure->isValid('email', $json['email']);
            $isNullEmail = strlen($json['email']) === 0;

            if (!$isValidEmail && !$isNullEmail) {
                throw new ReachableException("Email invalido");
            }

            $this->secure->generateNewUserLink($json);

            if($isValidEmail){
                Mailer::sendLink([
                    'email' => $json['email'],
                    'link' => $this->secure->getFullLink(),
                ]);
            }

            Json::send([
                'success' => true,
                'message' => $isValidEmail ? 'Email enviado' : 'Link copiado para a área de transferência',
                'link' => $this->secure->getFullLink(),
                'linkType' => $isValidEmail ? 'email' : 'copy',
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function changePasswordAction()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $request = Req::getParams();

            if (!$request){
                throw new RequestException();
            }

            if(!$request['token'] || !$this->secure->isValidHex($request['token'])){
                throw new ReachableException("Token invalido");
            }

            $token = $this->user->getPasswordLink($request['token']);

            if(!$token){
                throw new ReachableException("Token expirado");
            }

            View::render('resetPassword');
        } catch (Throwable $th) {
            header("Location: /");
        }
    }

    public function changePasswordApi()
    {
        try {
            if($this->secure->isLoggedIn()){	
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json['password'] || !$json['confirmPassword'] || 
            $json['password'] !== $json['confirmPassword'] ||
            !$this->secure->isValid('password', $json['password'])){
                throw new ReachableException("Revise os campos");
            }

            if(!$json['token'] || !$this->secure->isValidHex($json['token'])){
                throw new ReachableException("Token invalido");
            }

            $token = $this->user->getLinks([
                'eq' => [
                    'link' => $json['token'],
                    'type' => 'reset',
                ]
            ])[0];

            if(!$token ||
            Carbon::parse($token['disabled_at'])
            ->diffInHours(Carbon::now()) > User::PASSWORD_LINK_EXPIRE_MINUTES){  
                throw new ReachableException("Token expirado");
            }

            $userId = json_decode($token['option'], true)['user'];

            $this->user->update([
                'password' => $this->secure->hash($json['password']),
            ], $userId);

            $this->user->deletePasswordLink($token['id']);
            $this->user->forceLogin($userId);

            Json::send([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
                'redirect' => false
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
                'redirect' => '/'
            ]);
        }
    }

    public function resetPasswordLinkApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new Exception("Erro ao processar a requisição");
            }

            if (
                !$json['id']
            ){
                throw new Exception("Id invalido");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $json['id']
                ]
            ]);

            if (!$user){
                throw new Exception("Id invalido");
            }

            Mailer::sendPassword([
                'email' => $user['email'],
                'name' => $user['first_name'],
            ]);

            Json::send([
                'success' => true,
                'message' => 'Email enviado com sucesso'
            ]);

        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
            
    }
}

