<?php 

namespace App\Models;

use App\Models\Database;
use Laminas\Db\Sql\Select;
use Laminas\Db\Adapter\Adapter;

class User
{
    private $db;
    private $sql;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->sql = $this->db->getConnection();
    }

    public function getUsers(array $where = null)
    {
        $select = new Select();
        $select->from('users');

        if ($where) {
            $select->where($where);
        }

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

}
