<?php

error_reporting(E_ALL);

use \PHPUnit\Framework\TestCase;
use \MetaRush\OtpAuth;
use \MetaRush\DataMapper;

class RepoTest extends TestCase
{
    private $mapper;
    private $table;
    private $pdo;
    private $dbFile;
    private $repo;

    public function setUp()
    {
        // ----------------------------------------------
        // setup test db
        // ----------------------------------------------

        $this->dbFile = __DIR__ . '/test.db';
        $this->table = 'Users';

        $dsn = 'sqlite:' . $this->dbFile;

        // create test db if doesn't exist yet
        if (!file_exists($this->dbFile)) {

            $this->pdo = new \PDO($dsn);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->pdo->query('
                CREATE TABLE `' . $this->table . '` (
                `id`        INTEGER PRIMARY KEY AUTOINCREMENT,
                `username`  TEXT,
                `email`     TEXT
            )');
        }

        // ----------------------------------------------
        // init Repo
        // ----------------------------------------------

        $this->mapper = new DataMapper\DataMapper(
            new DataMapper\Adapters\AtlasQuery($dsn, null, null)
        );

        $this->seedTestData();

        $cfg = (new OtpAuth\Config())
            ->setTable($this->table)
            ->setUsernameColumn('username')
            ->setEmailColumn('email');

        $this->repo = new OtpAuth\Repo($cfg, $this->mapper);
    }

    public function tearDown()
    {
        // close the DB connections so unlink will work
        unset($this->repo);
        unset($this->mapper);
        unset($this->pdo);

        if (file_exists($this->dbFile))
            unlink($this->dbFile);
    }

    public function seedTestData()
    {
        $data = [
            [
                'username' => 'foo',
                'email'    => 'foo@example.com',
            ]
        ];

        foreach ($data as $v)
            $this->mapper->create($this->table, $v);
    }

    public function testUserExist()
    {
        $r = $this->repo->userExist('foo');

        $this->assertTrue($r);
    }

    public function testGetEmail()
    {
        $expected = 'foo@example.com';

        $actual = $this->repo->getEmail('foo');

        $this->assertEquals($expected, $actual);
    }
}
