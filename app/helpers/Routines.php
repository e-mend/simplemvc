<?php

namespace App\Helpers;

use App\Models\Database;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use App\enum\AclRole;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Delete;
use App\Helpers\Secure;

class Routines
{
    public static function isWaitingCoroutine(bool $isDeath = false)
    {
        $secure = Secure::getInstance();

        if($secure->isLoggedIn()){
            $db = Database::getInstance();
            $adapter = $db->getConnection();
            $sql = new Sql($adapter);

            if($isDeath){
                $delete = new Delete();
                $delete->from('kill_switch')
                    ->where([
                            'user_id' => $_SESSION['user']['id'],
                        ]);

                $deleteStmt = $sql->buildSqlString($delete);
                $result = $adapter->query($deleteStmt, Adapter::QUERY_MODE_EXECUTE);

                return true;
            }

            $select = $sql->select('kill_switch');
            $select->where(['user_id' => $_SESSION['user']['id']]);

            $select = $sql->buildSqlString($select);
            $waitingUser = $adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray();

            foreach ($waitingUser as $routine) {
                if($routine['type'] === 'death'){
                    session_destroy();

                    $delete = new Delete();
                    $delete->from('kill_switch')
                        ->where([
                                'user_id' => $_SESSION['user']['id'],
                                'type' => 'death'
                            ]);
    
                    $deleteStmt = $sql->buildSqlString($delete);
                    $result = $adapter->query($deleteStmt, Adapter::QUERY_MODE_EXECUTE);
    
                    return 'death';
                }

                if($routine['type'] === 'reset'){
                    $select = $sql->select('user');
                    $select->where(['id' => $_SESSION['user']['id']]);

                    $select = $sql->buildSqlString($select);
                    $user = $adapter->query($select, Adapter::QUERY_MODE_EXECUTE)->toArray()[0];

                    $_SESSION['user']['option']['permission'] = 
                    json_decode($user['option'], true)['permission'];

                    $delete = new Delete();
                    $delete->from('kill_switch')
                        ->where([
                                'user_id' => $_SESSION['user']['id'],
                                'type' => 'reset',
                            ]);
    
                    $deleteStmt = $sql->buildSqlString($delete);
                    $result = $adapter->query($deleteStmt, Adapter::QUERY_MODE_EXECUTE);

                    return 'reset';
                }
            }
            return false;
        }
    }

    public static function foresightCoroutine(int $id, string $type)
    {
        $secure = Secure::getInstance();

        if($secure->isLoggedIn()){
            $db = Database::getInstance();
            $adapter = $db->getConnection();
            $sql = new Sql($adapter);

            $insert = new Insert();
            $insert->into('kill_switch');

            $insert->values([
                'user_id' => $id,
                'type' => $type,
            ]);

            $insert = $sql->buildSqlString($insert);
            $adapter->query($insert, Adapter::QUERY_MODE_EXECUTE);
        }
    }
}