<?php

namespace MetaRush\OtpAuth;

class Auth
{
    private $cfg;
    private $repo;

    public function __construct(Config $cfg, Repo $repo)
    {
        $this->cfg = $cfg;
        $this->repo = $repo;
    }

    /**
     * Check if user exist
     *
     * @param string $username
     * @return bool
     */
    public function userExist(string $username): bool
    {
        return $this->repo->userExist($username);
    }

    /**
     * Send OTP to user via email
     *
     * @param string $username
     * @return void
     */
    public function sendOtp(string $username): void
    {
        $email = $this->repo->getEmail($username);

        // generate OTP
        $otp = Utils::randomToken(
                $this->cfg->getOtpLength(),
                $this->cfg->getCharacterPool()
        );

        // set OTP in DB
        $this->repo->setOtp($otp, $username);

        // insert OTP to email body
        $body = str_replace('{OTP}', $otp, $this->cfg->getBody());

        // send OTP to email
        $this->mailer($email, $body);
    }

    /**
     * Send email using settings fron Config class
     *
     * @param string $to
     * @param string $body
     * @return void
     * @throws \Error
     */
    private function mailer(string $to, string $body): void
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL))
            throw new \Error('Email appears to be invalid');

        // set PHPMailer construct to true to enable exception
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        $mailer->isSMTP();
        $mailer->SMTPAuth = true;

        $mailer->Host = $this->cfg->getSmtpHost();
        $mailer->Username = $this->cfg->getSmtpUser();
        $mailer->Password = $this->cfg->getSmtpPass();
        $mailer->SMTPSecure = $this->cfg->getSmtpEncr();
        $mailer->Port = $this->cfg->getSmtpPort();

        $fromName = $this->cfg->getFromName() ?? $this->cfg->getFromEmail();
        $mailer->setFrom($this->cfg->getFromEmail(), $fromName);
        $mailer->addAddress($to);

        $mailer->Subject = $this->cfg->getSubject();
        $mailer->Body = $body;

        $mailer->send();
    }

    public function validOtp(string $otp, string $username): bool
    {

    }

    public function login()
    {

    }

    public function remember()
    {

    }

    public function logout()
    {

    }
}
