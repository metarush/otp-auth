<?php

declare(strict_types=1);

use \PHPUnit\Framework\TestCase;
use \MetaRush\OtpAuth;
use \MetaRush\DataMapper;

/**
 * Note: Don't remove the @runInSeparateProcess annottation in methods or else
 * you must run the tests one by one because PhpFastCache doesn't work on
 * "too fast" tests. E.g.,
 * vendor/bin/phpunit tests/unit/AuthTest.php --filter testName --stderr
 * Also, include the --stderr flag so tests with cookies will not error
 */
class AuthTest extends TestCase
{
    private $mapper;
    private $table;
    private $pdo;
    private $dbFile;
    private $otpAuth;
    private $cfg;
    private $testUserEmail;
    private $otp;

    public function setUp(): void
    {
        // ----------------------------------------------
        // load test smtp details from .env to $_ENV
        // ----------------------------------------------

        $dotenv = \Dotenv\Dotenv::create(__DIR__);
        $dotenv->load();

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
                `otpExpire`     INTEGER,
                `rememberHash`  TEXT,
                `rememberToken` TEXT
            )');
        }

        // ----------------------------------------------
        // init OtpAuth
        // ----------------------------------------------

        $this->mapper = new DataMapper\DataMapper(
            new DataMapper\Adapters\AtlasQuery(
                (new DataMapper\Config)
                    ->setAdapter('AtlasQuery')
                    ->setDsn($dsn)
            )
        );

        $smtpServers = [
            0 => (new OtpAuth\SmtpServer)
                ->setHost($_ENV['MROA_SMTP_HOST_0'])
                ->setUser($_ENV['MROA_SMTP_USER_0'])
                ->setPass($_ENV['MROA_SMTP_PASS_0'])
                ->setPort((int) $_ENV['MROA_SMTP_PORT_0'])
                ->setEncr($_ENV['MROA_SMTP_ENCR_0']),
            1 => (new OtpAuth\SmtpServer)
                ->setHost($_ENV['MROA_SMTP_HOST_1'])
                ->setUser($_ENV['MROA_SMTP_USER_1'])
                ->setPass($_ENV['MROA_SMTP_PASS_1'])
                ->setPort((int) $_ENV['MROA_SMTP_PORT_1'])
                ->setEncr($_ENV['MROA_SMTP_ENCR_1'])
        ];

        $driverConfig = [
            'path' => $_ENV['MROA_EF_DRIVER_PATH']
        ];

        $this->cfg = (new OtpAuth\Config())
            ->setAppName('MROATester')
            ->setAdminEmails([$_ENV['MROA_ADMIN_EMAIL']])
            ->setFromEmail('sender@example.com')
            ->setTable($this->table)
            ->setUsernameColumn('email')
            ->setEmailColumn('email')
            ->setOtpHashColumn('otpHash')
            ->setOtpTokenColumn('otpToken')
            ->setNotificationFromEmail('noreply@example.com')
            ->setServers($smtpServers)
            ->setRoundRobinMode(true)
            ->setRoundRobinDriver('files')
            ->setRoundRobinDriverConfig($driverConfig);

        $this->otpAuth = new OtpAuth\Auth(
            $this->cfg,
            new OtpAuth\Repo($this->cfg, $this->mapper)
        );

        // ----------------------------------------------
        // common vars
        // ----------------------------------------------
        $this->testUserEmail = $_ENV['MROA_TEST_USER_EMAIL'];
        $this->otp = $this->otpAuth->generateToken(8);

        // ----------------------------------------------
        // seed test data
        // ----------------------------------------------
        $this->seedTestData();
    }

    public function tearDown(): void
    {
        // close the DB connections so unlink will work
        unset($this->otpAuth);
        unset($this->mapper);
        unset($this->pdo);

        if (file_exists($this->dbFile))
            unlink($this->dbFile);

        sleep(3);
    }

    public function seedTestData()
    {
        $data = [
            ['email' => $this->testUserEmail],
            ['email' => 'invalid-email-user']
        ];

        foreach ($data as $v)
            $this->mapper->create($this->table, $v);
    }

    public function testUserExist()
    {
        $userExist = $this->otpAuth->userExist($this->testUserEmail);

        $this->assertTrue($userExist);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendOtpRegular()
    {
        $this->otpAuth->sendOtp($this->otp, $this->testUserEmail);

        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendOtpNonExistentUser()
    {
        $this->expectExceptionMessageMatches('/does not exist/');

        $this->otpAuth->sendOtp($this->otp, 'non-existent-user');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendOtpInvalidEmail()
    {
        $this->expectExceptionMessageMatches('/Email appears to be invalid/');

        $this->otpAuth->sendOtp($this->otp, 'invalid-email-user');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendOtpUsingOneFailedSmtpHost()
    {
        $smtpServers = [
            0 => (new OtpAuth\SmtpServer)
                ->setHost('deliberateInvalidHost')
                ->setUser('deliberateInvalidHost')
                ->setPass('deliberateInvalidHost')
                ->setPort(123)
                ->setEncr('deliberateInvalidHost'),
            1 => (new OtpAuth\SmtpServer)
                ->setHost($_ENV['MROA_SMTP_HOST_0'])
                ->setUser($_ENV['MROA_SMTP_USER_0'])
                ->setPass($_ENV['MROA_SMTP_PASS_0'])
                ->setPort((int) $_ENV['MROA_SMTP_PORT_0'])
                ->setEncr($_ENV['MROA_SMTP_ENCR_0'])
        ];

        $this->cfg->setServers($smtpServers);

        $this->otpAuth->sendOtp($this->otp, $this->testUserEmail);

        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendOtpUsingAllFailedSmtpHost()
    {
        $smtpServers = [
            0 => (new OtpAuth\SmtpServer)
                ->setHost('deliberateInvalidHost')
                ->setUser('deliberateInvalidHost')
                ->setPass('deliberateInvalidHost')
                ->setPort(123)
                ->setEncr('deliberateInvalidHost'),
            1 => (new OtpAuth\SmtpServer)
                ->setHost('AnotherdeliberateInvalidHost')
                ->setUser('AnotherdeliberateInvalidHost')
                ->setPass('AnotherdeliberateInvalidHost')
                ->setPort(123)
                ->setEncr('AnotherdeliberateInvalidHost'),
        ];

        $this->cfg->setServers($smtpServers);

        $this->expectException(OtpAuth\Exception::class);

        $this->otpAuth->sendOtp($this->otp, $this->testUserEmail);
    }

    /**
     * This should send emails using 2 different SMTP hosts
     *
     * @runInSeparateProcess
     */
    public function testSendOtpUsingNextHostFlag()
    {
        // turn off round-robin mode to ensure the useNextHost flag is working
        $this->cfg->setRoundRobinMode(false);

        // mock the last server key used
        $lastServerKey = 0;
        $this->otpAuth->sendOtp($this->otp, $this->testUserEmail, true, $lastServerKey);
        // mock the last server key used
        $lastServerKey = 1;
        $this->otpAuth->sendOtp($this->otp, $this->testUserEmail, true, $lastServerKey);

        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     */
    public function testValidOtp()
    {
        $username = $this->testUserEmail;

        // seed data
        $this->otpAuth->sendOtp($this->otp, $username);

        $where = [$this->cfg->getUsernameColumn() => $username];
        $row = $this->mapper->findOne($this->table, $where);

        // test normal valid otp
        $valid = $this->otpAuth->validOtp($this->otp, $username, $row[$this->cfg->getOtpTokenColumn()]);
        $this->assertTrue($valid);

        // deliberately expire otp
        $data = [$this->cfg->getOtpExpireColumn() => time() - (10 * 60)];
        $this->mapper->update($this->table, $data, $where);
        $row = $this->mapper->findOne($this->table, $where);
        $valid = $this->otpAuth->validOtp($this->otp, $username, $row[$this->cfg->getOtpTokenColumn()]);
        $this->assertFalse($valid);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemember()
    {
        $this->otpAuth->remember($this->testUserEmail);

        $row = $this->mapper->findOne($this->table, [$this->cfg->getUsernameColumn() => $this->testUserEmail]);

        $this->assertNotNull($row[$this->cfg->getRememberHashColumn()]);
        $this->assertNotNull($row[$this->cfg->getRememberTokenColumn()]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRememberedUsername()
    {
        // seed data
        $username = 'foo';
        $token = OtpAuth\Utils::randomToken(OtpAuth\Auth::TOKEN_LENGTH);
        $validator = OtpAuth\Utils::randomToken(OtpAuth\Auth::HASH_LENGTH);
        $hash = password_hash($validator, PASSWORD_DEFAULT);

        $data = [
            $this->cfg->getUsernameColumn()      => $username,
            $this->cfg->getRememberTokenColumn() => $token,
            $this->cfg->getRememberHashColumn()  => $hash
        ];

        $this->mapper->create($this->table, $data);

        // test
        $dbUsername = $this->otpAuth->rememberedUsername($token . $validator);

        $this->assertEquals($username, $dbUsername);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOtpExpired()
    {
        $this->cfg->setOtpExpire(1);
        $this->otpAuth->sendOtp('123', $this->testUserEmail);

        $expired = $this->otpAuth->otpExpired($this->testUserEmail);
        $this->assertFalse($expired);

        // modify otp expiration to just 5 seconds so the test will be fast
        $data = [
            $this->cfg->getOtpExpireColumn() => time() + 5
        ];

        $where = [
            $this->cfg->getUsernameColumn() => $this->testUserEmail
        ];

        $this->mapper->update($this->table, $data, $where);

        // simulate elapsed time
        sleep(4);

        $expired = $this->otpAuth->otpExpired($this->testUserEmail);
        $this->assertFalse($expired);

        // simulate elapsed time
        sleep(5);

        $expired = $this->otpAuth->otpExpired($this->testUserEmail);
        $this->assertTrue($expired);
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserId()
    {
        $username = 'bar@example.com';
        $data = [$this->cfg->getUsernameColumn() => $username];
        // seed data
        $this->mapper->create($this->table, $data);
        $actual = $this->otpAuth->userId($username);
        $expected = 3;
        $this->assertEquals($expected, $actual);
    }

}