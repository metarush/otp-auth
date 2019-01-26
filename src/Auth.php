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

        // set otpHash and otpToken in DB
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $otpToken = base64_encode(random_bytes(9)); // outputs 12 chars
        $this->repo->setOtpHashAndToken($otpHash, $otpToken, $username);

        // set token in browser for later verification
        $expires = time() + (60 * $this->cfg->getOtpExpire());
        setcookie($this->cfg->getOtpCookieName(), $otpToken, $expires, '/');

        // send OTP to email
        $this->mailer($email, $otp);
    }

    /**
     * Send OTP to email using settings from Config class
     *
     * @param string $to
     * @param string $otp
     * @return void
     * @throws \Error
     */
    private function mailer(string $to, string $otp): void
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
        $mailer->Body = str_replace('{OTP}', $otp, $this->cfg->getBody());

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
