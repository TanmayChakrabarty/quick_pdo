<?php

namespace tanmay\QuickPdo;

use PDO;
use PDOException;

class QuickPdo
{
    private $dbUser = false;
    private $dbPassword = false;
    private $dbName = false;
    private $dbHost = false;
    private $dbPort = '3306';
    private $characterEncoding = 'utf8mb4';

    private ?PDO $connection = null;

    /**
     * QuickPdo constructor.
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbPort
     * @param string $characterEncoding
     */
    public function __construct($dbUser, $dbPassword, $dbName, $dbHost = 'localhost', $dbPort = '3306', $characterEncoding = 'utf8mb4')
    {
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->characterEncoding = $characterEncoding;
    }

    public function connect()
    {
        $dsn = "mysql:host=$this->dbHost;dbname=$this->dbName;charset=$this->characterEncoding;port=$this->dbPort";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->connection = new PDO($dsn, $this->dbUser, $this->dbPassword, $options);
        } catch (PDOException $e) {
            //throw new PDOException($e->getMessage(), (int)$e->getCode());
            return false;
        }

        return true;
    }

    public function insert(string $table, array $data)
    {
        $cols = array_keys($data);

        $statement = $this->connection->prepare('INSERT INTO '.$table.' (`'.implode('`,`', $cols).'`) VALUES (:'.implode(',:', $cols).')');

        foreach($data as $col => $val){
            $statement->bindValue(':'.$col, $val);
        }

        $statement->execute();

        return $this->connection->lastInsertId();

    }

    public function update(string $table, array $data, string $condition_part = '', array $condition_values = [])
    {
        $replacers = self::array_combine_duplicate_keys($data, $condition_values);

        $sql = 'UPDATE '.$table.' SET';

        $updates = [];

        foreach($data as $col => $val){
            $updates[] = " $col = :$col";
        }

        $sql .= implode(', ', $updates);

        if(strlen($condition_part)){
            $sql .= " WHERE ".$condition_part;
        }

        $statement = $this->connection->prepare($sql);

        foreach($replacers as $col => $val){
            $statement->bindValue(':'.$col, $val);
        }

        return $statement->execute();
    }

    public static function array_combine_duplicate_keys(array $a, array $b)
    {
        $output = $a;
        foreach ($b as $i=>$v){
            if(isset($output[$i])){
                $limit = 0;
                $new_key = $i;
                while (++$limit <= 1000){
                    $new_key = $i.$limit;
                    if(!isset($output[$new_key])){
                        $limit = 0;
                        break;
                    }
                }
                if($limit == 0) $output[$new_key] = $v;
                else{
                    trigger_error('array_combine_duplicate_keys can not create an unique key, sorry, limit reached', E_USER_ERROR);
                }
            }
            else $output[$i] = $v;
        }

        return $output;
    }

    public function closeConnection()
    {
        $this->connection = null;
    }
}