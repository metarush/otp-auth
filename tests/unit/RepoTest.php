<?php

declare(strict_types=1);

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
    private $cfg;

    public function setUp(): void
    {
        // ----------------------------------------------
        // setup test db
        // ----------------------------------------------

        $this->dbFile = __DIR__ . '/test-' . uniqid() . '.db';
        $this->table = 'Users';

        $dsn = 'sqlite:' . $this->dbFile;

        // create test db if doesn't exist yet
        if (!file_exists($this->dbFile)) {

            $this->pdo = new \PDO($dsn);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->pdo->query('
                CREATE TABLE `' . $this->table . '` (
                `id`            INTEGER PRIMARY KEY AUTOINCREMENT,
                `username`      TEXT,
                `email`         TEXT,
                `otpHash`       TEXT,
                `otpToken`      TEXT,
                `otpExpire`     TEXT,
                `rememberHash`  TEXT,
                `rememberToken` TEXT
            )');
        }

        // ----------------------------------------------
        // init Repo
        // ----------------------------------------------

        $this->mapper = (new DataMapper\Builder)
            ->setDsn($dsn)
            ->build();

        $this->seedTestData();

        $this->cfg = (new OtpAuth\Config())
            ->setTable($this->table)
            ->setUsernameColumn('username')
            ->setEmailColumn('email')
            ->setOtpHashColumn('otpHash')
            ->setOtpTokenColumn('otpToken');

        $this->repo = new OtpAuth\Repo($this->cfg, $this->mapper);
    }

    public function tearDown(): void
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

    /**
     * @runInSeparateProcess
     */
    public function testUserExist()
    {
        $exist = $this->repo->userExist('foo');

        $this->assertTrue($exist);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetEmail()
    {
        $expected = 'foo@example.com';

        $actual = $this->repo->getEmail('foo');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetOtpData()
    {
        $otpHash = '123';
        $otpToken = 'abc';
        $username = 'foo';

        $this->repo->setOtpData($otpHash, $otpToken, $username);

        $row = $this->mapper->findOne($this->table, [$this->cfg->getUsernameColumn() => $username]);

        $this->assertEquals($otpHash, $row[$this->cfg->getOtpHashColumn()]);
        $this->assertEquals($otpToken, $row[$this->cfg->getOtpTokenColumn()]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetOtpData()
    {
        $otpHash = '123';
        $otpToken = 'abc';
        $username = 'foo';

        // seed data first
        $this->repo->setOtpData($otpHash, $otpToken, $username);

        // then check
        $arr = $this->repo->getOtpData($username);

        $this->assertEquals($otpHash, $arr[$this->cfg->getOtpHashColumn()]);
        $this->assertEquals($otpToken, $arr[$this->cfg->getOtpTokenColumn()]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetRememberMeHashAndToken()
    {
        $hash = '123';
        $token = 'abc';
        $username = 'foo';

        $this->repo->setRememberMeHashAndToken($hash, $token, $username);

        $row = $this->mapper->findOne($this->table, [$this->cfg->getUsernameColumn() => $username]);

        $this->assertEquals($hash, $row[$this->cfg->getRememberHashColumn()]);
        $this->assertEquals($token, $row[$this->cfg->getRememberTokenColumn()]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetRememberMeHashAndToken()
    {
        $hash = '123';
        $token = 'abc';
        $username = 'foo';

        // seed data
        $this->repo->setRememberMeHashAndToken($hash, $token, $username);

        // then check
        $arr = $this->repo->getRememberMeHashAndToken($token);

        $this->assertEquals($hash, $arr[$this->cfg->getRememberHashColumn()]);
        $this->assertEquals($token, $arr[$this->cfg->getRememberTokenColumn()]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetRememberMeHashAndTokenTokenNotExist()
    {
        $hash = '123';
        $token = 'abc';
        $username = 'foo';

        // seed data
        $this->repo->setRememberMeHashAndToken($hash, $token, $username);

        // then check
        $res = $this->repo->getRememberMeHashAndToken('non-existent-token');

        $this->assertNull($res);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetUsernameFromRememberToken()
    {
        $token = 'abc';
        $username = 'foo';

        $data = [
            $this->cfg->getUsernameColumn()      => $username,
            $this->cfg->getRememberTokenColumn() => $token
        ];

        // seed data
        $this->mapper->create($this->table, $data);

        $dbUsername = $this->repo->getUsernameFromRememberToken($token);

        $this->assertEquals($username, $dbUsername);
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserId()
    {
        $username = 'bar';
        $data = [$this->cfg->getUsernameColumn() => $username];
        // seed data
        $this->mapper->create($this->table, $data);
        $actual = $this->repo->userId($username);
        $expected = 2;
        $this->assertEquals($expected, $actual);

        $username = 'qux';
        $data = [$this->cfg->getUsernameColumn() => $username];
        // seed data
        $this->mapper->create($this->table, $data);
        $actual = $this->repo->userId($username);
        $expected = 3;
        $this->assertEquals($expected, $actual);
    }
}
