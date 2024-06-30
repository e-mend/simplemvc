<?php 

namespace App\Models;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Sql;

class Database
{
    private static Database $connection;

    public Adapter $adapter;

    private function __construct()
    {
       // is not dead
    }

    public static function getInstance(): Database
    {
        if (!isset(self::$connection)) {
            self::$connection = new Database();
        }
        return self::$connection;
    }

    public function getConnection(): Adapter
    {
        $this->adapter = new Adapter([
            'driver'   => 'Pdo_Mysql',
            'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'hostname' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
        ]);

        return $this->adapter;
    }

    public function ping(): bool
    {
        try {
            $result = $this->adapter->query('SELECT 1', Adapter::QUERY_MODE_EXECUTE);
            return $result->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

}