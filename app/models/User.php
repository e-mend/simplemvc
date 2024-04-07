<?php 

namespace App\Models;

use App\Models\Database;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class User
{
    private $db;
    private $sql;
    private $adapter;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->adapter = $this->db->getConnection();
        $this->sql = new Sql($this->adapter);
    }

    public function getUsers(array $where = null)
    {
        $select = $this->sql->select('user');

        $select->where([
            'email' => $where['username'],
            'username' => $where['username']
        ], 
        PredicateSet::OP_OR);

        $select = $this->sql->buildSqlString($select);        
        return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }

}
