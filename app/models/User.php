<?php 

namespace App\Models;

use App\Models\Database;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Insert;

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

    public static function isWaitingDeath()
    {
        if($_SESSION['user']){
            $db = Database::getInstance();
            $adapter = $db->getConnection();
            $sql = new Sql($adapter);

            $select = $sql->select('kill_switch');
            $select->where(['user_id' => $_SESSION['user']['id']]);

            $select = $sql->buildSqlString($select);
            $count = $adapter->query($select, Adapter::QUERY_MODE_EXECUTE);

            if(count($count) > 0){
                session_destroy();
                header('Location: /');
            }
        }
    }

    public static function foresightDeath()
    {
        if($_SESSION['user']){
            $db = Database::getInstance();
            $adapter = $db->getConnection();
            $sql = new Sql($adapter);

            $insert = new Insert();
            $insert->into('kill_switch');

            $insert->values([
                'user_id' => $_SESSION['user']['id']
            ]);

            $insert = $sql->buildSqlString($insert);
            $adapter->query($insert, Adapter::QUERY_MODE_EXECUTE);
        }
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
        $select->columns($search['columns'] ?? ['*']);

        if(!empty($search['where'])){
            $select->where($search['where']);
        }

        if($search['days']){
            $select->where([
                'created_at >= ?' => $search['days']
            ]);
        }

        if($search['favorite']){
            $select->where(['favorite' => $search['favorite']]);
        }

        $select->where(['is_deleted' => $search['is_deleted']]);
        $select->order($search['order'] ?? 'id DESC');

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
        return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }

}
