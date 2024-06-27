<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use App\Requests\Req;
use Carbon\Carbon;
use App\Helpers\Routines;
use App\Helpers\Mailer;
use App\Enum\AclRole;
use App\Exceptions\ReachableException;
use App\Exceptions\PermissionException;
use App\Exceptions\RequestException;
use Throwable;
use Exception;

class DashboardController extends Controller
{
    private User $user;
    private Secure $secure;

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
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if (!$json['id']){
                throw new ReachableException("Id invalido");
            }

            $toUpdate = $this->user->update([
                'favorite' => $json['favorite'],
            ], $json['id']);

            if (!$toUpdate){
                throw new ReachableException("Erro ao atualizar");
            }

            Json::send([
                'success' => true,
                'message' => $json['favorite'] ? 'Favorito adicionado com sucesso' : 'Favorito removido com sucesso',
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição'
            ]);
        }
    }

    public function disableUserApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException("Não autorizado");
            }

            $params = Req::getParams();

            if (!$params){
                throw new RequestException();
            }

            if (!$params['id']){
                throw new ReachableException("Id invalido");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $params['id']
                ]
            ])[0];

            if(!$user){
                throw new ReachableException("Id invalido");
            }

            $permission = json_decode($user['option'], true)['permission'];

            if($permission[AclRole::SUPER_ADMIN->value] === true){
                throw new ReachableException("Impossível desabilitar o super admin");
            }

            $update = $this->user->update([
                'is_disabled' => $user['is_disabled'] === 1 ? false : true
            ], $params['id']);

            if(!$update){
                throw new ReachableException("Erro ao processar a requisição");
            }

            Routines::foresightCoroutine($params['id'], 'death');

            Json::send([
                'success' => true,
                'message' => $user['is_disabled'] === 0 ? 'Conta desabilitada com sucesso' : 'Conta habilitada com sucesso',
                'is_disabled' => $user['is_disabled'] === 1
            ]);

        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
    
    public function routinesApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new Exception("Sessão expirada");
            }

            $isWaiting = Routines::isWaitingCoroutine();

            if($isWaiting === 'death'){
                Json::send([
                    'success' => true,
                    'redirect' => '/',
                    'message' => 'Sua conta foi morreu',
                ]);
            }

            if($isWaiting === 'reset'){
                Json::send([
                    'success' => true,
                    'redirect' => false,
                    'type' => 'reset',
                    'permission' => $_SESSION['user']['option']['permission'],
                    'message' => 'Permissões alteradas com sucesso',
                ]);
            }

            Json::send([
                'success' => true,
                'redirect' => false,
                'message' => 'Nada aconteceu',
            ]);

        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'redirect' => '/',
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function logoutApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new PermissionException("Não autorizado");
            }

            $this->secure->logout();

            Json::send([
                'success' => true,
                'message' => 'Logout efetuado com sucesso'
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição'
            ]);
        }
    }

    public function getUsersApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException("Não autorizado");
            }

            $params = Req::getParams();
            $query = [];

            if(!$params['id']){
                if($params['search'] && $this->secure->isValid('search', $params['search'])){
                    $query['search'] = $params['search'];
                }

                if($params['new']){
                    $query['days'] = Carbon::now()->subDays(User::NEW_USER_DAYS)->format('Y-m-d H:i:s');
                }
    
                if($params['deleted']){
                    $query['is_disabled'] = 1; 
                }else{
                    $query['is_disabled'] = 0;
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
                'updated_at',
                'disabled_at',
                'is_disabled',
                'favorite',
                'option',
            ];

            $users = $this->user->get($query);
            $count = $this->user->get($query, true);

            foreach ($users as &$user) {
                $user['created_at_formatted'] = Carbon::createFromFormat('Y-m-d H:i:s', $user['created_at'])
                                                ->format('d/m/Y H:i:s');
                $user['isNew'] = Carbon::createFromFormat('Y-m-d H:i:s', $user['created_at'])
                                ->diffInDays(Carbon::now()) <= User::NEW_USER_DAYS;
            }

            if($params['id'] && $users[0]) {
                $users[0]['option'] = json_decode($users[0]['option'], true);
                $users[0]['permission'] = $users[0]['option']['permission'];
            }

            Json::send([
                'success' => true,
                'users' => $users,
                'message' => 'Pesquisa concluída',
                'count' => ceil($count / User::OFFSET)
            ]);
            
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao fazer busca',
            ]);
        }
    }

    public function sendPasswordApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException("Erro ao processar a requisição");
            }

            if (
                !$json['id']
            ){
                throw new ReachableException("Id invalido");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $json['id']
                ]
            ])[0];

            if (!$user){
                throw new ReachableException("Usuario inexistente");
            }

            $item = Mailer::sendPassword([
                'email' => $user['email'],
                'name' => $user['first_name'],
                'for' => [
                    'user' => $user['id']
                ]
            ]);

            if (!$item){
                throw new ReachableException("Erro ao enviar email");
            }

            Json::send([
                'success' => true,
                'message' => 'Email enviado com sucesso',
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

    public function getLinksApi()
    {
        try {
            if(!$this->secure->isLoggedIn() 
            || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException();
            }

            $links = $this->user->getLinks([
                'eq' => [
                    'temp.created_by' => $_SESSION['user']['id'],
                    'type' => 'user',
                ],
                'limit' => 5,
                'order' => 'temp.id DESC'
            ]);

            if ($links){
                foreach ($links as &$link) {
                    $link['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $link['created_at'])
                                                    ->format('d/m/Y H:i:s');
                    if ($link['disabled_at']){
                        $link['disabled_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $link['disabled_at'])
                        ->format('d/m/Y H:i:s');
                    }
                }
            }

            Json::send([
                'success' => true,
                'links' => $links ? $this->secure->getFullLink($links) : [],
            ]);
            
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function updatePasswordApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN)){
                throw new PermissionException("Não autorizado");
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException("Erro ao processar a requisição");
            }

            if (!$json['password'] 
                || !$this->secure->isValid('password', $json['password'])
            ){
                throw new ReachableException("Utilize todos os caracteres!");
            }

            $user = $this->user->get([
                'where' => [
                    'id' => $json['id']
                ]
            ])[0];

            if (!$user){
                throw new ReachableException("Usuario inexistente");
            }

            $permission = json_decode($user['option'], true)['permission'];

            if($permission[AclRole::SUPER_ADMIN->value] === true 
            && $_SESSION['user']['id'] !== $json['id']){
                throw new ReachableException("Não é permitido alterar a senha de outros super-admininistradores");
            }
            
            $update = $this->user->update([
                'password' => $this->secure->hash($json['password']),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['user']['id']
            ], $json['id']);

            if (!$update){
                throw new ReachableException("Erro ao atualizar");
            }
            
            Json::send([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
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

    public function updateUserApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new PermissionException("Não autorizado");
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException("Erro ao processar a requisição");
            }

            if (
            !$json['username'] || !$this->secure->isValid('username', $json['username'])
            || !$json['email'] || !$this->secure->isValid('email', $json['email'])
            || !$json['first_name'] || !$this->secure->isValid('name', $json['first_name'])
            || !$json['last_name'] || !$this->secure->isValid('name', $json['last_name'])
            ){
                throw new ReachableException("Preencha todos os campos corretamente!");
            }

            $update = $this->user->update([
                'username' => $json['username'],
                'email' => $json['email'],
                'first_name' => $json['first_name'],
                'last_name' => $json['last_name'],
            ], $json['id']);

            if (!$update){
                throw new Exception("Erro ao atualizar os dados");
            }
                
            Json::send([
                'success' => true,
                'message' => 'Dados atualizados com sucesso'
            ]);
            
        } catch (ReachableException $le) {
            Json::send([
                'success' => false,
                'message' => $le->getMessage()
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Falha ao atualizar os dados',
            ]);
        }
    }

    public function userDataApi()
    {
        try {
            if(!$this->secure->isLoggedIn()){
                throw new PermissionException("Não autorizado");
            }

            Json::send([
                'success' => true,
                'user' => [
                    'email' => $_SESSION['user']['email'],
                    'first_name' => $_SESSION['user']['first_name'],
                    'last_name' => $_SESSION['user']['last_name'],
                    'username' => $_SESSION['user']['username'],
                    'permission' => $_SESSION['user']['option']['permission'],
                    'days' => Carbon::createFromFormat('Y-m-d H:i:s', $_SESSION['user']['created_at'])
                            ->diffInDays(Carbon::now()),
                    'image' => false
                ],
                'message' => 'Dados sincronizados com sucesso'
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Falha ao sincronizar os dados',
            ]);
        }
    }
}
