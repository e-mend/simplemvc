<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;
use Exception;
use Carbon\Carbon;
use App\Helpers\Mailer;

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
