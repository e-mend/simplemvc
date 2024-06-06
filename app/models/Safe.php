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

class Safe
{
    private $db;
    private $sql;
    private $adapter;

    public const OFFSET = 40;
    public const NEW_ITEM_DAYS = 7;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->adapter = $this->db->getConnection();
        $this->sql = new Sql($this->adapter);
    }

    public function update(array $data, string $id)
    {
        try {
            $update = $this->sql->update('inventory');
            $update->set(array_merge($data, [
                'updated_at' => Carbon::now(), 
                'updated_by' => $_SESSION['user']['id'],
            ]));
            $update->where(['id' => $id]);
            $update = $this->sql->buildSqlString($update);
            $result = $this->adapter->query($update, Adapter::QUERY_MODE_EXECUTE);
            return $result;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function delete(string $id)
    {
        try {
            $delete = $this->sql->delete('inventory');
            $delete->where(['id' => $id]);
            $delete = $this->sql->buildSqlString($delete);
            $result = $this->adapter->query($delete, Adapter::QUERY_MODE_EXECUTE);
            return $result->count() > 0;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function get(array $search = null, bool $isCount = false)
    {
        $select = $this->sql->select('inventory');
        $select->columns(['*']);

        $select->join(['uc' => 'user'], 
        'uc.id = inventory.created_by', 
        ['created_by_name' => new Expression('concat(uc.first_name, " ", uc.last_name)')], 
        Select::JOIN_LEFT);

        $select->join(['uu' => 'user'], 
        'uu.id = inventory.updated_by', 
        ['updated_by_name' => new Expression('concat(uu.first_name, " ", uu.last_name)')], 
        Select::JOIN_LEFT);

        if($search['search']){
            $searchTerm = '%' . $search['search'] . '%';
            $select->where(function ($where) use ($searchTerm) {
                $where->nest()
                    ->like('inventory.name', $searchTerm)
                    ->or
                    ->like('inventory.description', $searchTerm)
                    ->or
                    ->like('inventory.price', $searchTerm)
                    ->or
                    ->like('inventory.quantity', $searchTerm)
                    ->unnest();
                });
        }

        if(!empty($search['where'])){
            $select->where($search['where']);
        }

        if($search['days']){
            $select->where([
                'inventory.created_at >= ?' => $search['days']
            ]);
        }

        if($search['from']){
            $select->where([
                'inventory.created_at >= ?' => $search['from']
            ]);
        }

        if($search['to']){
            $select->where([
                'inventory.created_at <= ?' => $search['to']
            ]);
        }

        if($search['favorite']){
            $select->where(['inventory.favorite' => $search['favorite']]);
        }

        if($search['is_disabled']){
            $select->where(['inventory.is_disabled' => $search['is_disabled']]); 
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

    public function createItem(array $data)
    {
        $insert = new Insert();
        $insert->into('inventory');
        $insert->values(array_merge($data, [
            'created_at' => Carbon::now(),
            'created_by' => $_SESSION['user']['id']
        ]));
        
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $results = $statement->execute();
        return $results->getGeneratedValue();
    }
}