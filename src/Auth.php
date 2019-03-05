<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

class Auth
{
    const OTP_TOKEN_COOKIE_NAME = 'otpToken';
    const LAST_SMTP_SERVER_COOKIE_NAME = 'lastSmtpServer';
    private $cfg;
    private $repo;
    private $request;
    private $response;
    private $session;
    private $sesAuth;

    public function __construct(Config $cfg, Repo $repo)
    {
        $this->cfg = $cfg;
        $this->repo = $repo;

        $webFactory = new \Aura\Web\WebFactory($GLOBALS);
        $this->request = $webFactory->newRequest();
        $this->response = $webFactory->newResponse();

        $this->session = (new \Aura\Session\SessionFactory)->newInstance($_COOKIE);
        $this->sesAuth = $this->session->getSegment(__NAMESPACE__ . 'AUTH');
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
     * Generate random token
     *
     * @return string
     */
    public function generateToken(): string
    {
        return Utils::randomToken(
                $this->cfg->getOtpLength(),
                $this->cfg->getCharacterPool()
        );
    }

    /**
     * Send OTP to user via email
     *
     * @param string $otp
     * @param string $username
     * @param bool $useNextSmtpHost
     * @param int $testLastServerKey
     * @return void
     */
    public function sendOtp(string $otp, string $username, bool $useNextSmtpHost = false, int $testLastServerKey = null): void
    {
        $email = $this->repo->getEmail($username);

        // set otpHash and otpToken in DB
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $otpToken = Utils::randomToken(12);
        $this->repo->setOtpHashAndToken($otpHash, $otpToken, $username);

        // set token in browser for later verification
        $this->response->cookies->setPath('/');
        $this->response->cookies->setExpire('+' . (60 * $this->cfg->getOtpExpire()));
        $this->response->cookies->set($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME, $otpToken);

        // send OTP to email
        $this->otpMailer($otp, $email, $useNextSmtpHost, $testLastServerKey);
    }

    /**
     * Send OTP to email (private method)
     *
     * @param string $otp
     * @param string $email
     * @param bool $useNextSmtpHost
     * @param int $testLastServerKey Optional param used for testing
     * @return void
     * @throws Error
     * @throws Exception
     */
    private function otpMailer(string $otp, string $email, bool $useNextSmtpHost = false, int $testLastServerKey = null): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new Error('Email appears to be invalid: ' . $email);

        $mailer = (new \MetaRush\EmailFallback\Builder($this->cfg->getSmtpServers()))
            ->setAdminEmail($this->cfg->getAdminEmail())
            ->setAppName($this->cfg->getAppName())
            ->setNotificationFromEmail($this->cfg->getNotificationFromEmail())
            ->setRoundRobinMode($this->cfg->getRoundRobinMode())
            ->setRoundRobinDriver($this->cfg->getRoundRobinDriver())
            ->setRoundRobinDriverConfig($this->cfg->getRoundRobinDriverConfig())
            ->build();

        $fromName = $this->cfg->getFromName() ?? $this->cfg->getFromEmail();
        $mailer->setFrom($this->cfg->getFromEmail(), $fromName);
        $mailer->addAddress($email);

        $mailer->Subject = $this->cfg->getSubject();
        $mailer->Body = str_replace('{OTP}', $otp, $this->cfg->getBody());

        $cookieName = $this->cfg->getCookiePrefix() . self::LAST_SMTP_SERVER_COOKIE_NAME;

        // use next smtp host if flag is set
        if ($useNextSmtpHost) {
            $lastServerKey = $testLastServerKey ?? $this->request->cookies->get($cookieName);
            $nextServerKey = $lastServerKey + 1;
            $serverKey = $mailer->sendEmailFallback($nextServerKey);
            return;
        }

        try {
            $serverKey = $mailer->sendEmailFallback();
        } catch (\MetaRush\EmailFallback\Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        // if multiple smtp hosts are set, track last smtp host used
        if (count($this->cfg->getSmtpServers()) > 1)
            $this->response->cookies->set($cookieName, $serverKey);
    }

    /**
     * Validate OTP
     *
     * @param string $otp
     * @param string $username
     * @param string $testOtpToken Optional param used for testing
     * @return bool
     */
    public function validOtp(string $otp, string $username, string $testOtpToken = null): bool
    {
        $dbData = $this->repo->getOtpHashAndToken($username);

        $otpVerified = password_verify($otp, $dbData['otpHash']);

        $newOtpToken = $testOtpToken ?? $this->request->cookies->get($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME);

        return ($otpVerified && $newOtpToken === $dbData['otpToken']);
    }

    public function login(): void
    {
        $this->sesAuth->set('username', $username);
    }

    public function remember()
    {

    }

    public function logout()
    {

    }
}
