<?php


namespace tanmay\UnitTests;

use PHPUnit\Framework\TestCase;
use tanmay\CallReturn\CallReturn;

class UnitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
 
    }

    public function testErrorReturn()
    {
        $ret = new CallReturn();

        $ret->add_error('Err');

        self::assertTrue($ret->is_error());
    }

    public function testErrorSuccess()
    {
        $ret = new CallReturn();

        $ret->add_success(1, 'done');

        self::assertTrue($ret->is_success());
    }

    public static function tearDownAfterClass(): void
    {

    }
}