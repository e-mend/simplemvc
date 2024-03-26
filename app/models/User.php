<?php 

namespace App\Models;

use App\Models\Database;

class User
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->db->getConnection();
    }

}
