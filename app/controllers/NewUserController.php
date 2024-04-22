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
