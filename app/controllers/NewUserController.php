<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use App\Requests\Req;
use Exception;
use Carbon\Carbon;
use App\Helpers\Mailer;
use App\enum\AclRole;

class NewUserController extends Controller
{
    private User $user;
    private Secure $secure;
    private const NEW_USER_DAYS = 7;
    private const PASSWORD_LINK_EXPIRE_MINUTES = 60;
    private const NEW_USER_LINK_EXPIRE_MINUTES = 120;

    public function __construct()
    {
        $this->user = new User();
        $this->secure = Secure::getInstance();
    }

    public function newUserAction()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $request = Req::getParams();

            if(!$request['token'] || !$this->secure->isValidHex($request['token'])){
                throw new Exception("Token invalido");
            }

            $token = $this->user->getNewUserLink($request['token']);

            if(!$token){
                throw new Exception("Token expirado");
            }

            View::render('newUser');

        } catch (\Throwable $th) {
            header("Location: /");
        }
    }

    public function validateNewUserApi()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            if(!$json['token'] || !$this->secure->isValidHex($json['token'])){
                throw new Exception("Token invalido");
            }

            $token = $this->user->getLinks([
                'eq' => [
                    'link' => $json['token'],
                    'type' => 'user',
                ]
            ])[0];

            if(!$token ||
            Carbon::parse($token['deleted_at'])
            ->diffInHours(Carbon::now()) > self::NEW_USER_LINK_EXPIRE_MINUTES){  
                throw new Exception("Token expirado");
            }
            
            if(!$json['email'] || !$this->secure->isValid('email', $json['email'])){
                throw new Exception("Email invalido");
            }

            if(!$json['username'] || !$this->secure->isValid('username', $json['username'])){
                throw new Exception("Usuário invalido");
            }

            if(!$json['password'] || !$this->secure->isValid('password', $json['password'])){
                throw new Exception("Senha invalida");
            }

            if(!$json['firstName'] || !$this->secure->isValid('name', $json['firstName'])
            || !$json['lastName'] || !$this->secure->isValid('name', $json['lastName'])){
                throw new Exception("Nome invalido");
            }

            $user = $this->user->userExists($json['email'], $json['username'])[0];

            if($user){
                if($user['email'] === $json['email']){
                    throw new Exception("Email ja existe");
                }

                if($user['username'] === $json['username']){
                    throw new Exception("Usuário ja existe");
                }
            }

            $_SESSION['userToCreate'] = $json;
            $_SESSION['userToCreate']['permission'] = $token['permission'];

            Mailer::sendCode([
                'email' => $json['email'],
                'name' => $json['firstName'],
            ]);

            Json::send([
                'success' => true,
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function changePermissionApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission('admin')){
                throw new Exception("Not Authorized");
            }

            $json = Json::getJson();

            if(!$json['id'] || !$json['permission']){
                throw new Exception("Erro ao processar a requisição");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $json['id']
                ]
            ])[0];

            if(!$user){
                throw new Exception("Id invalido");
            }

            $permission = json_decode($user['permission'], true)['permission'];

            if($permission[AclRole::SUPER_ADMIN->value] === true){
                throw new Exception("Impossível alterar o super admin");
            }

            $json['permission'] = [
                'permission' => $json['permission'],
            ];

            $user = $this->user->update([
                'permission' => json_encode($json['permission']),
            ], $json['id']);

            User::foresightCoroutine($json['id'], 'reset');

            Json::send([
                'success' => $user,
                'message' => 'Alterado com sucesso',
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function validateNewUserEmailApi()
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

            $toCreate = [
                'username' => $_SESSION['userToCreate']['username'],
                'password' => $this->secure->hash($_SESSION['userToCreate']['password']),
                'first_name' => $_SESSION['userToCreate']['firstName'],
                'last_name' => $_SESSION['userToCreate']['lastName'],
                'email' => $_SESSION['userToCreate']['email'],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'permission' => $_SESSION['userToCreate']['permission'],
            ];

            $createUser = $this->user->createUser($toCreate);

            if(!$createUser){
                throw new Exception("Erro ao criar o usuário");
            }

            $toCreate['permission'] = json_decode($toCreate['permission'], true)['permission'];
            $_SESSION['user'] = $toCreate;

            unset($_SESSION['userToCreate']);

            $_SESSION['logged'] = true;

            Json::send([
                'success' => true,
                'message' => 'Validado com sucesso',
                'redirect' => '/inventario'
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function createNewUserLinkApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission('admin')){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            $isValidEmail = $this->secure->isValid('email', $json['email']);
            $isNullEmail = strlen($json['email']) === 0;

            if (!$isValidEmail && !$isNullEmail) {
                throw new Exception("Email invalido");
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

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function changePasswordAction()
    {
        try {
            if($this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $request = Req::getParams();

            if(!$request['token'] || !$this->secure->isValidHex($request['token'])){
                throw new Exception("Token invalido");
            }

            $token = $this->user->getPasswordLink($request['token']);

            if(!$token){
                throw new Exception("Token expirado");
            }

            View::render('resetPassword');

        } catch (\Throwable $th) {
            header("Location: /");
        }
    }

    public function changePasswordApi()
    {
        try {
            if($this->secure->isLoggedIn()){	
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            if (!$json['password'] || !$json['confirmPassword'] || 
            $json['password'] !== $json['confirmPassword'] ||
            !$this->secure->isValid('password', $json['password'])){
                throw new Exception("Revise os campos");
            }

            if(!$json['token'] || !$this->secure->isValidHex($json['token'])){
                throw new Exception("Token invalido");
            }

            $token = $this->user->getLinks([
                'link' => $json['token'],
                'type' => 'reset',
            ])[0];

            if(!$token ||
            Carbon::parse($token['deleted_at'])
            ->diffInHours(Carbon::now()) > self::PASSWORD_LINK_EXPIRE_MINUTES){  
                throw new Exception("Token expirado");
            }

            $userId = json_decode($token['permission'], true)['user'];

            $this->user->update([
                'password' => $this->secure->hash($json['password']),
            ], $userId);

            $this->user->deletePasswordLink($token['id']);

            Json::send([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
                'redirect' => '/'
            ]);
        }
    }

    public function resetPasswordLinkApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
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

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
            
    }
}

