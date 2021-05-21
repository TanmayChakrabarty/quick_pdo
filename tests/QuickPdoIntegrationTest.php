<?php


namespace tanmay\QuickPdoIntegrationTests;

use PDO;
use PHPUnit\Framework\TestCase;
use tanmay\QuickPdo\QuickPdo;
use tanmay\QuickPdoIntegrationTests\TemplateClass\User;

class QuickPdoIntegrationTest extends TestCase
{
    private static ?QuickPdo $connection = NULL;
    private static array $dataSet = [
        [
            'pk_user_id' => 1,
            'user_name' => 'Tanmay',
            'user_gender' => 'Male'
        ],
        [
            'pk_user_id' => 2,
            'user_name' => 'Arif',
            'user_gender' => 'Male'
        ],
        [
            'pk_user_id' => 3,
            'user_name' => 'Aliza',
            'user_gender' => 'Female'
        ],
    ];
    public static function setUpBeforeClass(): void
    {
        self::$connection = new QuickPdo(DB_USER, DB_PASSWORD, DB_DBNAME, DB_HOST, DB_PORT, DB_CHARACTER_ENCODING);
        self::$connection->connect();
        self::$connection->getConnection()->prepare('TRUNCATE TABLE users')->execute();
        self::$connection->closeConnection();

        self::$connection = new QuickPdo(DB_USER, DB_PASSWORD, DB_DBNAME, DB_HOST, DB_PORT, DB_CHARACTER_ENCODING);

        require_once dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'templateClasses'.DIRECTORY_SEPARATOR.'User.php';
    }
    public function testConnect()
    {
        $ret = self::$connection->connect();

        self::assertEquals(true, $ret, 'Could not connect to database');
    }
    public function testInsert()
    {
        foreach (self::$dataSet as $items){
            $ret = self::$connection->insert('users', ['pk_user_id' => $items['pk_user_id'], 'user_name' => $items['user_name'], 'user_gender' => $items['user_gender']]);
            self::assertTrue($ret->is_success());
            if($ret->is_success()){
                self::assertNotNull($ret->get_data());
            }
        }
    }

    public function testSelectAll()
    {
         $ret = self::$connection->query('SELECT * FROM users');
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $result = [];
            while ($row = $data->fetch()){
                $result[] = $row;
            }

            self::assertEqualsCanonicalizing(self::$dataSet, $result);
        }
    }

    public function testSelectSpecific()
    {
        $ret = self::$connection->query('SELECT * FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data()->fetch();
            $original_data = self::$dataSet[1];

            self::assertEqualsCanonicalizing($original_data, $data);
        }
    }

    public function testSelectAssoc()
    {
        $ret = self::$connection->selectAssoc('SELECT * FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $data = $data ? $data[0] : [];
            $original_data = self::$dataSet[1];

            self::assertEqualsCanonicalizing($original_data, $data);
        }
    }

    public function testSelectNum()
    {
        $ret = self::$connection->selectNum('SELECT * FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $data = $data ? $data[0] : [];
            $original_data = array_values(self::$dataSet[1]);

            self::assertEqualsCanonicalizing($original_data, $data);
        }
    }

    public function testSelectObject()
    {
        $ret = self::$connection->selectObject('SELECT * FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $data = $data ? $data[0] : [];
            $original_data = (object)(self::$dataSet[1]);

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectColumn()
    {
        $ret = self::$connection->selectColumn('SELECT user_name, user_gender FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $data = $data ? $data[0] : [];
            $original_data = self::$dataSet[1]['user_name'];

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectKeyPair()
    {
        $ret = self::$connection->selectKeyPair('SELECT user_name, user_gender FROM users WHERE pk_user_id = :user_id', ['user_id' => 2]);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $original_data = [self::$dataSet[1]['user_name'] => self::$dataSet[1]['user_gender']];

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectUniqueIndexed()
    {
        $ret = self::$connection->selectUniqueIndexed('SELECT users.pk_user_id, users.* FROM users');
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $original_data = [
                self::$dataSet[0]['pk_user_id'] => self::$dataSet[0],
                self::$dataSet[1]['pk_user_id'] => self::$dataSet[1],
                self::$dataSet[2]['pk_user_id'] => self::$dataSet[2],
            ];

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectGroupIndexed()
    {
        $ret = self::$connection->selectGroupIndexed('SELECT users.user_gender, users.* FROM users');
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $original_data = [
                'Male' => [
                    self::$dataSet[0],self::$dataSet[1]
                ],
                'Female' => [
                    self::$dataSet[2]
                ],
            ];

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectGroupIndexedColumn()
    {
        $ret = self::$connection->selectGroupIndexedColumn('SELECT users.user_gender, users.user_name FROM users');
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $original_data = [
                'Male' => [
                    self::$dataSet[0]['user_name'],self::$dataSet[1]['user_name']
                ],
                'Female' => [
                    self::$dataSet[2]['user_name']
                ],
            ];

            self::assertEquals($original_data, $data);
        }
    }

    public function testSelectClass()
    {
        $ret = self::$connection->selectClass('SELECT * FROM users', 'templateClasses\User');
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            $data = $ret->get_data();
            $original_data = [
                (new \templateClasses\User())->setPkUserId(self::$dataSet[0]['pk_user_id'])->setUserName(self::$dataSet[0]['user_name'])->setUserGender(self::$dataSet[0]['user_gender']),
                (new \templateClasses\User())->setPkUserId(self::$dataSet[1]['pk_user_id'])->setUserName(self::$dataSet[1]['user_name'])->setUserGender(self::$dataSet[1]['user_gender']),
                (new \templateClasses\User())->setPkUserId(self::$dataSet[2]['pk_user_id'])->setUserName(self::$dataSet[2]['user_name'])->setUserGender(self::$dataSet[2]['user_gender']),
            ];

            self::assertEquals($original_data, $data);
        }
    }

    public function testUpdate()
    {
        $ret = self::$connection->update('users', ['user_name' => 'Tanmay Chakrabarty'], " pk_user_id = :user_id", ['user_id' => 1]);
        self::assertTrue($ret->is_success());

        if($ret->is_success()){
            self::assertEquals(1, $ret->get_data());

            $ret = self::$connection->selectColumn("SELECT user_name FROM users WHERE pk_user_id = :user_id", ['user_id' => 1]);
            self::assertTrue($ret->is_success());
            if($ret->is_success()){
                $data = $ret->get_data();
                self::assertEquals('Tanmay Chakrabarty', $data[0]);
            }
        }

        $ret = self::$connection->update('users', ['user_gender' => 'M'], " user_gender = 'Male'");
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            self::assertEquals(2, $ret->get_data());
        }

        $ret = self::$connection->update('users', ['user_gender' => 'F'], " user_gender = 'Female'");
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            self::assertEquals(1, $ret->get_data());
        }

        $ret = self::$connection->selectKeyPair("SELECT user_gender, COUNT(pk_user_id) FROM users GROUP BY user_gender");
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            self::assertEquals([
                'M' => 2,
                'F' => 1
            ], $ret->get_data());
        }
    }

    public function testDelete()
    {
        $ret = self::$connection->delete('users', " user_gender = :gender", ['gender' => 'F']);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            self::assertEquals(1, $ret->get_data());
        }
        $ret = self::$connection->delete('users', " user_gender = :gender", ['gender' => 'M']);
        self::assertTrue($ret->is_success());
        if($ret->is_success()){
            self::assertEquals(2, $ret->get_data());
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$connection->closeConnection();
    }
}