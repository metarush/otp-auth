<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

class Auth
{
    const OTP_TOKEN_COOKIE_NAME = 'otpToken';
    const LAST_SMTP_SERVER_COOKIE_NAME = 'lastSmtpServer';
    const AUTHENTICATED_VAR = 'authenticated';
    const USER_DATA_VAR = 'userData';
    const REMEMBER_COOKIE_NAME = 'remember';
    const TOKEN_LENGTH = 12;
    const HASH_LENGTH = 24;
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
     * @param int $length
     * @return string
     */
    public function generateToken(int $length): string
    {
        return Utils::randomToken(
                $length,
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
        $otpToken = $this->generateToken(self::TOKEN_LENGTH);
        $this->repo->setOtpHashAndToken($otpHash, $otpToken, $username);

        // set token in browser for later verification
        $expire = '+' . (60 * $this->cfg->getOtpExpire()) + time();
        setcookie($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME, $otpToken, $expire);

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
            setcookie($cookieName, (string) $serverKey);
    }

    /**
     * Validate OTP
     *
     * @param string $otp
     * @param string $username
     * @param string|null $testOtpToken Optional, for testing
     * @return bool
     */
    public function validOtp(string $otp, string $username, ?string $testOtpToken = null): bool
    {
        $dbData = $this->repo->getOtpHashAndToken($username);

        $otpVerified = password_verify($otp, $dbData['otpHash']);

        $newOtpToken = $testOtpToken ?? $this->request->cookies->get($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME);

        return ($otpVerified && $newOtpToken === $dbData['otpToken']);
    }

    /**
     * Login user as authenticated
     *
     * @param array $userData Optional arbitrary user data, e.g., userId, email
     * @return void
     */
    public function login(?array $userData = []): void
    {
        $this->sesAuth->set(self::AUTHENTICATED_VAR, true);
        $this->sesAuth->set(self::USER_DATA_VAR, $userData);
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function authenticated(): bool
    {
        return $this->sesAuth->get(self::AUTHENTICATED_VAR, false);
    }

    /**
     * Returns arbitrary user data, if set
     *
     * @return array
     */
    public function userData(): array
    {
        return $this->sesAuth->get(self::USER_DATA_VAR, []);
    }

    /**
     * Remember username's login in browser
     *
     * @param type $username
     * @param int $howLong
     * @return void
     */
    public function remember(string $username, int $howLong = null): void
    {
        $token = $this->generateToken(self::TOKEN_LENGTH);
        $validator = $this->generateToken(self::HASH_LENGTH);
        $hash = password_hash($validator, PASSWORD_DEFAULT);

        $this->repo->setRememberMeHashAndToken($hash, $token, $username);

        $howLong = $howLong ?? $this->cfg->getRememberCookieExpire();

        setcookie($this->cfg->getCookiePrefix() . self::REMEMBER_COOKIE_NAME,
                  $token . $validator,
                  $howLong);
    }

    /**
     * Get "remember me" cookie
     *
     * @return string|null
     */
    private function getRememberMeCookie(): ?string
    {
        return $this->request->cookies->get($this->cfg->getCookiePrefix() . self::REMEMBER_COOKIE_NAME, null);
    }

    /**
     * Check if user is remembered in the browser
     *
     * @param string|null $testCookie
     * @return bool
     */
    public function remembered(?string $testCookie = null): bool
    {
        $cookie = $testCookie ?? $this->getRememberMeCookie();

        if (!$cookie)
            return false;

        // get cookie data
        $token = substr($cookie, 0, self::TOKEN_LENGTH);
        $validator = substr($cookie, self::TOKEN_LENGTH);

        // get db data
        $dbData = $this->repo->getRememberMeHashAndToken($token);
        $dbHash = $dbData[$this->cfg->getRememberHashColumn()];

        return password_verify($validator, $dbHash);
    }

    /**
     * Logout user and remove "remember me" cookie
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();

        setcookie($this->cfg->getCookiePrefix() . self::REMEMBER_COOKIE_NAME, '', -1);
    }
}
