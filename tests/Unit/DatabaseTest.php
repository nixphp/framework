<?php

namespace Unit;

use Fixtures\DummyDatabase;
use PHPico\Core\Database;
use Tests\PHPicoTestCase;
use function PHPico\app;

class DatabaseTest extends PHPIcoTestCase
{

    public function testBuildsMysqlDsn(): void
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'testdb',
            'charset'  => 'utf8mb4',
        ];

        $db = new DummyDatabase($config);

        $this->assertSame('mysql:host=localhost;dbname=testdb;charset=utf8mb4', $db->getLastDsn());
        $this->assertSame('mysql', $db->usedDriver);
    }

    public function testConstructCreatesPdoInstance(): void
    {
        $pdoMock = $this->createMock(\PDO::class);

        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'testdb',
            'charset'  => 'utf8mb4',
            'username' => 'user',
            'password' => 'pass',
        ];

        $db = new Database($config, fn() => $pdoMock);

        $this->assertSame($pdoMock, $db->getConnection());
    }

    public function testHelperFunction()
    {
        $this->assertNull(\PHPico\database());
    }

}