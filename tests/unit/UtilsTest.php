<?php

declare(strict_types=1);

use \PHPUnit\Framework\TestCase;
use \MetaRush\OtpAuth\Utils;

class UtilsTest extends TestCase
{
    public function testRandomTokenLength()
    {
        $length = 9;
        $test = Utils::randomToken($length);
        $test = mb_strlen($test);
        $this->assertEquals($length, $test);
    }

    public function testRandomTokenPool()
    {
        $length = 9;

        $pool = 'abc';
        $pattern = '~([a-z])+~';
        $test = Utils::randomToken($length, $pool);
        $this->assertRegExp($pattern, $test);

        $pool = 'ABC';
        $pattern = '~([A-Z])+~';
        $test = Utils::randomToken($length, $pool);
        $this->assertRegExp($pattern, $test);

        $pool = '123';
        $pattern = '~([0-9])+~';
        $test = Utils::randomToken($length, $pool);
        $this->assertRegExp($pattern, $test);

        $pool = 'xyzXYZ123';
        $pattern = '~([a-zA-Z0-9])+~';
        $test = Utils::randomToken($length, $pool);
        $this->assertRegExp($pattern, $test);
    }
}
