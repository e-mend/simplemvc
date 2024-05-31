<?php 

namespace App\Models;

use App\Models\Database;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Delete;
use Carbon\Carbon;
use Laminas\Db\Sql\Expression;
use App\Requests\Json;
use Throwable;

class User
{
    public const OFFSET = 30;
    public const NEW_USER_DAYS = 7;
    private const PASSWORD_LINK_EXPIRE_MINUTES = 60;
    private const NEW_USER_LINK_EXPIRE_MINUTES = 120;
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
        } catch (Throwable $th) {
            return false;
        }
    }

    public function userExists(string $email, string $username)
    {
        try {
            $select = $this->sql->select('user');
            $select->where([
                'email' => $email,
                'username' => $username
            ], 
            PredicateSet::OP_OR);
            $select = $this->sql->buildSqlString($select);    
            return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (Throwable $th) {
            return false;
        }
    }

    public function get(array $search = null, bool $isCount = false)
    {
        $select = $this->sql->select('user');
        $select->columns($search['columns'] ?? ['*']);

        if($search['search']){
            $searchTerm = '%' . $search['search'] . '%';
            $select->where(function ($where) use ($searchTerm) {
                $where->nest()
                    ->like('first_name', $searchTerm)
                    ->or
                    ->like('last_name', $searchTerm)
                    ->or
                    ->like('email', $searchTerm)
                    ->or
                    ->like('username', $searchTerm)
                    ->unnest();
                });
        }

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

        if($search['is_disabled']){
            $select->where(['is_disabled' => $search['is_disabled']]); 
        }

        $select->order($search['order'] ?? 'favorite DESC, id DESC, created_at DESC');

        if(!$isCount){
            $select->limit(self::OFFSET);
            $select->offset($search['offset'] ?? 0);
        }

        $select = $this->sql->buildSqlString($select);
        $result = $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE);
        
        return $isCount ? $result->count() : $result->toArray();
    }

    public function getUserForLogin(array $where = null)
    {
        try {
            $select = $this->sql->select('user');

            $select->where([
                'email' => $where['username'],
                'username' => $where['username']
            ], 
            PredicateSet::OP_OR);

            $select->where([
                'is_disasled' => false
            ]);

            $select = $this->sql->buildSqlString($select);
            $result = $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();

            return $result;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function getLinks(array $where)
    {
        $select = $this->sql->select('temp');
        $select->where($where['eq']);

        if($where['limit']){
            $select->limit($where['limit']);
        }

        if($where['order']){
            $select->order($where['order']);
        }

        $select->join(['u' => 'user'], 'u.id = temp.created_by', [
            'fullname' => new Expression('CONCAT(u.first_name, " ", u.last_name)')
        ]);

        $select = $this->sql->buildSqlString($select);    
        return $this->adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function insertLink(array $data)
    {
        $insert = new Insert();
        $insert->into('temp');
        $insert->values($data);
        
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $results = $statement->execute();
        return $results;
    }

    public static function generateLink(array $data)
    {
        $db = Database::getInstance();
        $adapter = $db->getConnection();
        $sql = new Sql($adapter);

        $insert = new Insert();
        $insert->into('temp');
        $insert->values($data);

        $statement = $sql->prepareStatementForSqlObject($insert);
        $results = $statement->execute();
        return $results;
    }

    public function getPasswordLink(string $link)
    {
        $update = $this->sql->update('temp');

        $update->set([
            'is_disabled' => true,
            'disabled_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        $update->where([
            'type' => 'reset',
            'link' => $link,
            'is_disabled' => false
        ]);

        $statement = $this->sql->prepareStatementForSqlObject($update);
        $results = $statement->execute();
        return $results->getAffectedRows() > 0;
    }

    public function getNewUserLink(string $link)
    {
        try {
            $update = $this->sql->update('temp');

            $update->set([
                'is_disabled' => true,
                'disabled_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $update->where([
                'type' => 'user',
                'link' => $link,
                'is_disabled' => false
            ]);

            $statement = $this->sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();
            return $results->getAffectedRows() > 0;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function deletePasswordLink(string $id)
    {
        try {
            $delete = $this->sql->delete('temp');
            $delete->where([
                'type' => 'reset',
                'id' => $id
            ]);
            $statement = $this->sql->prepareStatementForSqlObject($delete);
            $results = $statement->execute();
            return $results->getAffectedRows() > 0;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function createUser(array $data)
    {
        try {
            $insert = new Insert();
            $insert->into('user');
            $insert->values($data);
            
            $statement = $this->sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();

            if ($results->getAffectedRows() === 0) {
                return false;
            }

            return $results->getGeneratedValue();
        } catch (Throwable $th) {
            return false;
        }
    }
}
