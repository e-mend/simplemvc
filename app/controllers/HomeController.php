<?php 

namespace App\Controllers;

use App\Helpers\View;
use App\Models\User;
use App\Requests\JsonResponse;

class HomeController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index()
    {
        View::render('login');
    }

    public function login()
    {
        JsonResponse::send([
            'success' => true
        ]);
    }
}
