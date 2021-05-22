<?php

namespace tanmay\QuickPdo;

use PDO;
use PDOException;
use tanmay\CallReturn\CallReturn;

class QuickPdo
{
    private $dbUser = false;
    private $dbPassword = false;
    private $dbName = false;
    private $dbHost = false;
    private $dbPort = '3306';
    private $characterEncoding = 'utf8mb4';

    private ?PDO $connection = null;

    const QUERY_RETURN_INSERT_ID = 1;
    const QUERY_RETURN_ROW_COUNT = 2;
    const QUERY_RETURN_DATA = 3;
    const QUERY_RETURN_STATEMENT = 4;

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

    public function getPDO()
    {
        return $this->connection;
    }

    public function query(string $sql, array $replacers = [], int $query_return = self::QUERY_RETURN_STATEMENT) :CallReturn
    {
        $ret = new CallReturn();

        try {
            $statement = $this->connection->prepare($sql);

            foreach($replacers as $col => $val){
                $statement->bindValue(':'.$col, $val);
            }

            $statement->execute();

            $return_value = null;

            switch ($query_return){
                case self::QUERY_RETURN_INSERT_ID:
                    $return_value = $this->connection->lastInsertId();
                    break;
                case self::QUERY_RETURN_ROW_COUNT:
                    $return_value = $statement->rowCount();
                    break;
                case self::QUERY_RETURN_DATA:
                    $return_value = $statement->fetchAll();
                    break;
                case self::QUERY_RETURN_STATEMENT:
                    $return_value = $statement;
                    break;
            }

            $ret->add_success($return_value);
        }
        catch (PDOException $e){
            $ret->add_error($e->getMessage());
        }

        return $ret;
    }

    private function select(string $fetch_mode, string $sql, array $replacers = [], string $class = '')
    {
        $ret = $this->query($sql, $replacers);

        if($ret->is_success()){
            switch ($fetch_mode){
                case "assoc":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "num":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_NUM);
                    break;
                case "object":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_OBJ);
                    break;
                case "column":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_COLUMN);
                    break;
                case "key_pair":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_KEY_PAIR);
                    break;
                case "unique_indexed":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_UNIQUE);
                    break;
                case "group_indexed":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_GROUP);
                    break;
                case "group_indexed_column":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
                    break;
                case "class":
                    $data = $ret->get_data()->fetchAll(PDO::FETCH_CLASS, $class);
                    break;
                default:
                    $data = [];
                    break;
            }

            $ret->clear_data();
            $ret->add_data($data);
        }

        return $ret;
    }

    public function selectAssoc(string $sql, array $replacers = [])
    {
        return $this->select('assoc', $sql, $replacers);
    }

    public function selectNum(string $sql, array $replacers = [])
    {
        return $this->select('num', $sql, $replacers);
    }

    public function selectObject(string $sql, array $replacers = [])
    {
        return $this->select('object', $sql, $replacers);
    }

    public function selectColumn(string $sql, array $replacers = [])
    {
        return $this->select('column', $sql, $replacers);
    }

    public function selectKeyPair(string $sql, array $replacers = [])
    {
        return $this->select('key_pair', $sql, $replacers);
    }

    public function selectUniqueIndexed(string $sql, array $replacers = [])
    {
        return $this->select('unique_indexed', $sql, $replacers);
    }

    public function selectGroupIndexed(string $sql, array $replacers = [])
    {
        return $this->select('group_indexed', $sql, $replacers);
    }

    public function selectGroupIndexedColumn(string $sql, array $replacers = [])
    {
        return $this->select('group_indexed_column', $sql, $replacers);
    }

    public function selectClass(string $sql, string $class, array $replacers = [])
    {
        return $this->select('class', $sql, $replacers, $class);
    }

    public function insert(string $table, array $data)
    {
        $ret = new CallReturn();

        $cols = array_keys($data);

        try {
            $statement = $this->connection->prepare('INSERT INTO '.$table.' (`'.implode('`,`', $cols).'`) VALUES (:'.implode(',:', $cols).')');

            foreach($data as $col => $val){
                $statement->bindValue(':'.$col, $val);
            }

            $statement->execute();

            $ret->add_success($this->connection->lastInsertId());

        } catch (PDOException $e){
            $ret->add_error($e->getMessage());
        }

        return $ret;
    }

    public function update(string $table, array $data, string $condition_part = '', array $condition_values = [])
    {
        $ret = new CallReturn();

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

        try {
            $statement = $this->connection->prepare($sql);

            foreach($replacers as $col => $val){
                $statement->bindValue(':'.$col, $val);
            }

            $statement->execute();

            $ret->add_success($statement->rowCount());

        } catch (PDOException $e){
            $ret->add_error($e->getMessage());
        }

        return $ret;
    }

    public function delete(string $table, string $condition_part = '', array $condition_values = [])
    {
        $ret = new CallReturn();

        $sql = 'DELETE FROM '.$table;

        if(strlen($condition_part)) $sql .= " WHERE ".$condition_part;

        try {
            $statement = $this->connection->prepare($sql);

            foreach($condition_values as $col => $val){
                $statement->bindValue(':'.$col, $val);
            }

            $statement->execute();

            $ret->add_success($statement->rowCount());

        } catch (PDOException $e){
            $ret->add_error($e->getMessage());
        }

        return $ret;
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