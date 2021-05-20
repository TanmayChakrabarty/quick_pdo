<?php


namespace tanmay\QuickPdoIntegrationTests;

use PDO;
use PHPUnit\Framework\TestCase;
use tanmay\QuickPdo\QuickPdo;

class QuickPdoIntegrationTest extends TestCase
{
    private static ?QuickPdo $connection = NULL;
    public static function setUpBeforeClass(): void
    {
        self::$connection = new QuickPdo(DB_USER, DB_PASSWORD, DB_DBNAME, DB_HOST, DB_PORT, DB_CHARACTER_ENCODING);
    }
    public function testConnect()
    {
        $ret = self::$connection->connect();

        self::assertEquals(true, $ret, 'Could not connect to database');
    }
    public function testInsert()
    {
        $ret = self::$connection->insert('users', ['user_name' => 'ABC', 'user_gender' => 'Male']);

        self::assertNotNull($ret);
    }
    public function testUpdate()
    {

    }
    public static function tearDownAfterClass(): void
    {
        self::$connection->closeConnection();
    }
}