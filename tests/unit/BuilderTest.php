<?php

require 'ChildClassSample.php';

use MetaRush\OtpAuth;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{

    public function setUp()
    {
        $this->dbFile = __DIR__ . '/test.db';
    }

    public function tearDown()
    {
        if (file_exists($this->dbFile))
            unlink($this->dbFile);
    }

    public function testBuilder()
    {
        $otpAuth = (new OtpAuth\Builder)
            ->setDsn('sqlite:' . $this->dbFile)
            ->build();

        $this->assertInstanceOf(OtpAuth\Auth::class, $otpAuth);
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
