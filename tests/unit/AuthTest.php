<?php

error_reporting(E_ALL);

use \PHPUnit\Framework\TestCase;
use \MetaRush\OtpAuth;
use \MetaRush\DataMapper;

class AuthTest extends TestCase
{
    private $mapper;
    private $table;
    private $pdo;
    private $dbFile;
    private $otpAuth;

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
                `username`	TEXT
            )');
        }

        // ----------------------------------------------
        // init OtpAuth
        // ----------------------------------------------

        $this->mapper = new DataMapper\DataMapper(
            new DataMapper\Adapters\AtlasQuery($dsn, null, null)
        );

        $this->seedTestData();

        $cfg = (new OtpAuth\Config())
            ->setTable($this->table)
            ->setUsernameColumn('username');

        $this->otpAuth = new OtpAuth\Auth(
            new OtpAuth\Repo($cfg, $this->mapper)
        );
    }

    public function tearDown()
    {
        // close the DB connections so unlink will work
        unset($this->otpAuth);
        unset($this->mapper);
        unset($this->pdo);

        if (file_exists($this->dbFile))
            unlink($this->dbFile);
    }

    public function seedTestData()
    {
        $data = [
            ['username' => 'foo']
        ];

        foreach ($data as $v)
            $this->mapper->create($this->table, $v);
    }

    public function testUserExist()
    {
        $r = $this->otpAuth->userExist('foo');

        $this->assertTrue($r);
    }
}
