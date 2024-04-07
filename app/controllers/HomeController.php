<?php 

namespace App\Controllers;

use App\Helpers\View;
use App\Models\User;
use App\Requests\Json;

class HomeController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function indexAction()
    {
        View::render('login');
    }

    public function dashboardAction()
    {
        View::render('dashboard');
    }

    public function loginApi()
    {
        $json = Json::getJson();

        if(!$json){
            Json::sendError('Invalid json', 400);
        }

        $this->user->getUsers();

        dd($json);

        Json::send([
            'success' => true
        ]);
    }
}
