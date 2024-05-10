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

class NewUserController extends Controller
{
    private User $user;
    private Secure $secure;
    private const NEW_USER_DAYS = 7;
    private const PASSWORD_LINK_EXPIRE_MINUTES = 60;

    public function __construct()
    {
        $this->user = new User();
        $this->secure = Secure::getInstance();
    }

    public function createLinkApi()
    {
        try {
            if($this->secure->isLoggedIn() || !in_array('admin', $_SESSION['user']['permission'])){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            if ($json['email'] === false){
                $this->user->createLink([
                    'type' => 'user',
                    
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

    public function newLinkApi()
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
