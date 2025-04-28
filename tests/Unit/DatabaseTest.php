<?php

namespace Tests\Unit;

use Fixtures\DummyDatabase;
use PHPico\Core\Database;
use PHPico\Exceptions\DatabaseException;
use Tests\PHPicoTestCase;

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

    public function testBuildsSqliteDsn(): void
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => '/tmp/testdb.sqlite',
            'charset'  => 'utf8mb4',
        ];

        $db = new DummyDatabase($config);

        $this->assertSame('sqlite:/tmp/testdb.sqlite', $db->getLastDsn());
        $this->assertSame('sqlite', $db->usedDriver);
    }

    public function testBuildsSqliteWithMemoryDsn(): void
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'charset'  => 'utf8mb4',
        ];

        $db = new DummyDatabase($config);

        $this->assertSame('sqlite::memory:', $db->getLastDsn());
        $this->assertSame('sqlite', $db->usedDriver);
    }

    public function testBuildsSqliteWithEmptyDatabaseFallbackToMemory(): void
    {
        $config = [
            'driver'   => 'sqlite',
            'charset'  => 'utf8mb4',
        ];

        $db = new DummyDatabase($config);

        $this->assertSame('sqlite::memory:', $db->getLastDsn());
        $this->assertSame('sqlite', $db->usedDriver);
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

    public function testExceptionWhileConnecting()
    {
        $this->expectException(DatabaseException::class);

        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'testdb',
            'charset'  => 'utf8mb4',
            'username' => 'user',
            'password' => 'pass',
        ];

        new Database($config, function() { throw new \PDOException('test'); });
    }

    public function testHelperFunction()
    {
        $this->assertNull(\PHPico\database());
    }

}