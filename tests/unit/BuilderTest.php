<?php

require 'ChildClassSample.php';

use MetaRush\OtpAuth;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function setUp(): void
    {
        $this->dbFile = __DIR__ . '/test.db';
    }

    public function tearDown(): void
    {
        if (file_exists($this->dbFile))
            unlink($this->dbFile);
    }

    public function testBuilder()
    {
        $otpAuth = (new OtpAuth\Builder)
            ->setDsn('sqlite:' . $this->dbFile)
            ->setDbUser('none') // all stuff below are for test/code coverage only
            ->setDbPass('none')
            ->setRememberHashColumn('none')
            ->setRememberTokenColumn('none')
            ->setOtpExpireColumn('none')
            ->setSubject('none')
            ->setBody('none')
            ->setCookiePrefix('none')
            ->setCookiePath('/')
            ->setRememberCookieExpire(0)
            ->setUserIdColumn('none')
            ->setCharacterPool('none')
            ->build();

        $this->assertInstanceOf(OtpAuth\Auth::class, $otpAuth);
    }

    public function testBuilderGetSmtpServer()
    {
        $builder = new OtpAuth\Builder;
        $smtpServer = $builder->SmtpServer();

        $this->assertInstanceOf(\MetaRush\EmailFallback\Server::class, $smtpServer);
    }

    public function testChildClass()
    {
        $otpAuth = (new OtpAuth\Builder)
            ->setDsn('sqlite:' . $this->dbFile)
            ->build('ChildClassSample');

        $this->assertInstanceOf(OtpAuth\Auth::class, $otpAuth);

        $foo = $otpAuth->sampleChildMethod();

        $this->assertEquals('foo', $foo);
    }

}