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

class DashboardController extends Controller
{
    private User $user;
    private Secure $secure;
    private const NEW_USER_DAYS = 7;

    public function __construct()
    {
        $this->user = new User();
        $this->secure = Secure::getInstance();
    }

    public function dashboardAction()
    {
        if(!$this->secure->isLoggedIn()){
            header('Location: /');
        }

        View::render('dashboard');
    }

    public function toggleFavoriteApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            if (!$json){
                throw new Exception("Erro ao processar a requisição");
            }

            if (!$json['id']){
                throw new Exception("Id invalido");
            }

            $toUpdate = $this->user->update([
                'favorite' => $json['favorite'],
            ], $json['id']);

            Json::send([
                'success' => true,
                'message' => $json['favorite'] ? 'Favorito adicionado com sucesso' : 'Favorito removido com sucesso',
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function logoutApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $this->secure->logout();

            Json::send([
                'success' => true,
                'message' => 'Logout efetuado com sucesso'
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function getUsersApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission('admin')){
                throw new Exception("Não autorizado");
            }

            $params = Req::getParams();
            $query = [];

            if(!$params['id']){
                if($params['new']){
                    $query['days'] = Carbon::now()->subDays(self::NEW_USER_DAYS)->format('Y-m-d H:i:s');
                }
    
                if($params['deleted']){
                    $query['is_deleted'] = 1; 
                }else{
                    $query['is_deleted'] = 0;
                }
    
                if($params['favorites']){
                    $query['favorite'] = 1;
                    $query['order'] = 'favorite DESC, created_at DESC';
                }

                if($params['pagination']){
                    $query['offset'] = ($params['pagination'] - 1) * User::OFFSET;
                }
            }else{
                $query['where'] = [
                    'id' => $params['id']
                ];
            }

            $query['columns'] = [
                'id',
                'first_name',
                'last_name',
                'username',
                'email',
                'created_at',
                'is_deleted',
                'favorite',
                'permission'
            ];

            $users = $this->user->get($query);
            $count = $this->user->get($query, true);

            foreach ($users as &$user) {
                $user['created_at_formatted'] = Carbon::createFromFormat('Y-m-d H:i:s', $user['created_at'])
                                                ->format('d/m/Y H:i:s');
                $user['isNew'] = Carbon::createFromFormat('Y-m-d H:i:s', $user['created_at'])
                                ->diffInDays(Carbon::now()) <= User::NEW_USER_DAYS;
            }

            if($params['id']){
                $users[0]['permission'] = json_decode($users[0]['permission'], true)['permission'];
            }

            Json::send([
                'success' => true,
                'users' => $users,
                'message' => 'Pesquisa concluída',
                'count' => ceil($count / User::OFFSET)
            ]);
            
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
                'users' => $params
            ]);
        }
    }

    public function sendPasswordApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission('admin')){
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
            ])[0];

            if (!$user){
                throw new Exception("Id invalido");
            }

            $item = Mailer::sendPassword([
                'email' => $user['email'],
                'name' => $user['first_name'],
                'for' => [
                    'user' => $user['id']
                ]
            ]);

            Json::send([
                'success' => true,
                'message' => 'Email enviado com sucesso',
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
            
    }

    public function getLinksApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission('admin')){
                throw new Exception("Não autorizado");
            }

            $links = $this->user->getLinks([
                'eq' => [
                    'created_by' => $_SESSION['user']['id'],
                    'type' => 'user',
                ],
                'limit' => 5,
                'order' => 'id DESC'
            ]);

            if ($links){
                foreach ($links as &$link) {
                    $link['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $link['created_at'])
                                                    ->format('d/m/Y H:i:s');
                }
            }

            Json::send([
                'success' => true,
                'links' => $links ? $this->secure->getFullLink($links) : [],
            ]);
            
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function updatePasswordApi()
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
                !$json['password'] || !$this->secure->isValid('password', $json['password'])
            ){
                throw new Exception("Utilize todos os caracteres!");
            }
            
            $this->user->update([
                'password' => $this->secure->hash($json['new_password'])
            ], $json['id']);
            
            Json::send([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function updateUserApi()
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
            !$json['username'] || !$this->secure->isValid('username', $json['username'])
            || !$json['email'] || !$this->secure->isValid('email', $json['email'])
            || !$json['first_name'] || !$this->secure->isValid('text', $json['first_name'])
            || !$json['last_name'] || !$this->secure->isValid('text', $json['last_name'])
            ){
                throw new Exception("Preencha todos os campos corretamente!");
            }

            $this->user->update([
                'username' => $json['username'],
                'email' => $json['email'],
                'first_name' => $json['first_name'],
                'last_name' => $json['last_name']
            ], $json['id']);
                
            Json::send([
                'success' => true,
                'message' => 'Dados atualizados com sucesso'
            ]);
            
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function userDataApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            Json::send([
                'success' => true,
                'user' => [
                    'email' => $_SESSION['user']['email'],
                    'first_name' => $_SESSION['user']['first_name'],
                    'last_name' => $_SESSION['user']['last_name'],
                    'username' => $_SESSION['user']['username'],
                    'permission' => $_SESSION['user']['permission'],
                    'days' => Carbon::createFromFormat('Y-m-d H:i:s', $_SESSION['user']['created_at'])
                            ->diffInDays(Carbon::now()),
                    'image' => false
                ],
                'message' => 'Dados sincronizados com sucesso'
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
