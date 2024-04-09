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

    public function update(array $data, string $id)
    {
        try {
            $update = $this->sql->update('user');
            $update->set($data);
            $update->where(['id' => $id]);
            $update = $this->sql->buildSqlString($update);
            $this->adapter->query($update, Adapter::QUERY_MODE_EXECUTE);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function get(array $search = null)
    {
        $select = $this->sql->select('user');

        if($search['where']){
            $select->where($search['where']);
        }

        $select = $this->sql->buildSqlString($select);        
        return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function getUsers(array $where = null)
    {
        $select = $this->sql->select('user');

        $select->where([
            'email' => $where['username'],
            'username' => $where['username']
        ], 
        PredicateSet::OP_OR);

        $select->where([
            'is_deleted' => false
        ]);

        $select = $this->sql->buildSqlString($select);        
        return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function userExists(string $email, string $username)
    {
        $select = $this->sql->select('user');

        $select->where([
            'email' => $email,
            'username' => $username
        ], 
        PredicateSet::OP_OR);

        $select = $this->sql->buildSqlString($select);    
        $query = $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();

        return count($query) > 0;
    }

}
