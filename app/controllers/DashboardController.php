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
            User::foresightDeath();

            if(!$this->secure->isLoggedIn()){
                throw new Exception("Não autorizado");
            }

            $params = Req::getParams();

            if(!in_array('admin', $_SESSION['user']['permission'])){
                throw new Exception("Não autorizadoo");
            }

            $query = [];

            if($params['new']){
                $query = array_merge($query, [
                    'days' => Carbon::now()->subDays(self::NEW_USER_DAYS)->format('Y-m-d H:i:s')
                ]);
            }

            if($params['deleted']){
                $query = array_merge($query, [
                    'is_deleted' => 1
                ]); 
            }else{
                $query = array_merge($query, [
                    'is_deleted' => 0
                ]); 
            }

            if($params['favorites']){
                $query = array_merge($query, [
                    'favorite' => 1
                ]);
            }

            $params['columns'] = [
                'id',
                'first_name',
                'last_name',
                'username',
                'email',
                'created_at',
                'is_deleted',
                'favorite'
            ];

            Json::send([
                'success' => true,
                'users' => $this->user->get($query),
                'message' => 'Pesquisa concluída'
            ]);
            
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
                'users' => $params
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
