<?php

namespace MetaRush\OtpAuth;

use MetaRush\OtpAuth;

class Mailer
{
    private $cfg;
    private $to;
    private $body;

    public function __construct(OtpAuth\Config $cfg, string $to, string $body)
    {
        $this->cfg = $cfg;
        $this->to = $to;
        $this->body = $body;
    }

    public function send(): void
    {
        $mailer = new \Swift_Mailer($this->cfg->getTransport());

        $fromName = $this->cfg->getFromName() ?? $this->cfg->getFromEmail();
        $from = [$this->cfg->getFromEmail() => $fromName];

        $message = (new \Swift_Message($this->cfg->getSubject()))
            ->setFrom($from)
            ->setTo([$this->to])
            ->setBody($this->body);

        if ($mailer->send($message) <= 0)
            throw new \Error('Unable to send email');
    }
}
