<?php

/**
 * Sample child class if you want to extend Auth and add your own methods.
 * You then can pass your child class to the build() param as string.
 */
class ChildClassSample extends \MetaRush\OtpAuth\Auth
{

    public function sampleChildMethod(): string
    {
        return 'foo';
    }
}
