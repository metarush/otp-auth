<?php

error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;
use MetaRush\OtpAuth;

class MailerTest extends TestCase
{
    private $cfg;

    public function setUp()
    {
        // load smtp details from .env to $_ENV
        $dotenv = \Dotenv\Dotenv::create(__DIR__);
        $dotenv->load();

        // setup SwitfMailer SMTP transport
        $transport = (new Swift_SmtpTransport($_ENV['SMTP_HOST'], $_ENV['SMTP_PORT']))
            ->setUsername($_ENV['SMTP_USER'])
            ->setPassword($_ENV['SMTP_PASS']);

        $this->cfg = (new OtpAuth\Config())
            ->setTransport($transport)
            ->setFromEmail('sender@example.com');
    }

    public function testSend()
    {
        $email = 'foo@example.com';
        $body = 'hello world';

        (new OtpAuth\Mailer($this->cfg, $email, $body))->send();

        $this->assertTrue(true);
    }
}
