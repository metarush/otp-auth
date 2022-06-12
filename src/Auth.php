<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

class Auth
{
    const OTP_TOKEN_COOKIE_NAME = 'otpTkn';
    const LAST_SMTP_SERVER_COOKIE_NAME = 'lstSmtpSrvr';
    const AUTHENTICATED_VAR = 'athntctd';
    const USER_DATA_VAR = 'usrDta';
    const USERNAME_VAR = 'usrnme';
    const REMEMBER_COOKIE_NAME = 'rmmbr';
    const TOKEN_LENGTH = 12;
    const HASH_LENGTH = 24;

    private $cfg;
    private $repo;
    private $serverRequest;
    private $session;
    private $sesAuth;

    public function __construct(Config $cfg, Repo $repo)
    {
        $this->cfg = $cfg;
        $this->repo = $repo;

        $this->serverRequest = \Laminas\Diactoros\ServerRequestFactory::fromGlobals(
                $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );

        $this->session = (new \Aura\Session\SessionFactory)->newInstance($_COOKIE);
        $this->sesAuth = $this->session->getSegment('MROA');
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
     * @param int $testLastServerKey Optional param used for testing. Emulates last SMTP server used.
     * @return void
     */
    public function sendOtp(string $otp, string $username, bool $useNextSmtpHost = false, int $testLastServerKey = null): void
    {
        // generate otpToken
        $otpToken = $this->generateToken(self::TOKEN_LENGTH);

        // set otpHash and otpToken in DB
        $this->setOtpDataInDb($otp, $otpToken, $username);

        // set token in browser for later verification
        $this->setOtpTokenCookie($otpToken);

        $email = $this->repo->getEmail($username);

        // send OTP to email
        $this->otpMailer($otp, $email, $useNextSmtpHost, $testLastServerKey);
    }

    /**
     * Set token in browser for later verification
     *
     * @param string $otpToken
     * @return void
     */
    private function setOtpTokenCookie(string $otpToken): void
    {
        $expire = ((60 * $this->cfg->getOtpExpire()) + time());
        setcookie($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME,
            $otpToken,
            $expire,
            $this->cfg->getCookiePath());
    }

    /**
     * Set otpHash and otpToken in DB
     *
     * @param string $otp
     * @param string $otpToken
     * @param string $username
     * @return void
     */
    private function setOtpDataInDb(string $otp, string $otpToken, string $username): void
    {
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $this->repo->setOtpData($otpHash, $otpToken, $username);
    }

    /**
     * Send OTP to email (private method)
     *
     * @param string $otp
     * @param string $email
     * @param bool $useNextSmtpHost
     * @param int $testLastServerKey Optional param used for testing. Emulates last SMTP server used.
     * @return void
     * @throws Error
     * @throws Exception
     */
    private function otpMailer(string $otp, string $email, bool $useNextSmtpHost = false, int $testLastServerKey = null): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new Error('Email appears to be invalid: ' . $email);

        $mailer = (new \MetaRush\EmailFallback\Builder)
            ->setServers($this->cfg->getServers())
            ->setFromEmail($this->cfg->getFromEmail())
            ->setFromName($this->cfg->getFromName())
            ->setAdminEmails($this->cfg->getAdminEmails())
            ->setAppName($this->cfg->getAppName())
            ->setNotificationFromEmail($this->cfg->getNotificationFromEmail())
            ->setRoundRobinMode($this->cfg->getRoundRobinMode())
            ->setRoundRobinDriver($this->cfg->getRoundRobinDriver())
            ->setRoundRobinDriverConfig($this->cfg->getRoundRobinDriverConfig())
            ->setTos([$email])
            ->setSubject($this->cfg->getSubject())
            ->setBody(str_replace('{OTP}', $otp, $this->cfg->getBody()))
            ->build();

        $cookieName = $this->cfg->getCookiePrefix() . self::LAST_SMTP_SERVER_COOKIE_NAME;

        // use next smtp host if flag is set
        if ($useNextSmtpHost) {
            $lastServerKey = $testLastServerKey ?? $this->serverRequest->getCookieParams()[$cookieName];
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
        if (count($this->cfg->getServers()) > 1)
            setcookie($cookieName,
                (string) $serverKey,
                0,
                $this->cfg->getCookiePath());
    }

    /**
     * Validate OTP
     *
     * @param string $otp
     * @param string $username
     * @param string|null $testOtpToken Optional, for testing. Emulates cookie token.
     * @return bool
     */
    public function validOtp(string $otp, string $username, ?string $testOtpToken = null): bool
    {
        // get OTP data from DB
        $dbData = $this->repo->getOtpData($username);

        // check if OTP is expired
        if ($this->timestampPassed((int) $dbData[$this->cfg->getOtpExpireColumn()]))
            return false;

        // check if OTP is correct
        $otpVerified = password_verify($otp, $dbData['otpHash']);

        // check if OTP cookie token is valid
        $newOtpToken = $testOtpToken ?? $this->serverRequest->getCookieParams()[$this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME];

        return ($otpVerified && $newOtpToken === $dbData['otpToken']);
    }

    /**
     * Login user as authenticated
     *
     * @param string $username
     * @param array $userData Optional arbitrary user data, e.g., userId, email
     * @return void
     */
    public function login(string $username, ?array $userData = []): void
    {
        $this->sesAuth->set(self::AUTHENTICATED_VAR, true);
        $this->sesAuth->set(self::USERNAME_VAR, $username);
        $this->sesAuth->set(self::USER_DATA_VAR, $userData);

        $this->session->regenerateId();
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
     * Returns arbitrary user data, if set via login() param
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
            ($howLong + time()),
            $this->cfg->getCookiePath());
    }

    /**
     * Get "remember me" cookie
     *
     * @return string|null
     */
    private function getRememberMeCookie(): ?string
    {
        return $this->serverRequest->getCookieParams()[$this->cfg->getCookiePrefix() . self::REMEMBER_COOKIE_NAME] ?? null;
    }

    /**
     * Get remembered username (via cookie) if any
     *
     * @param string|null $cookie
     * @return string|null
     */
    public function rememberedUsername(?string $cookie = null): ?string
    {
        $cookie = $cookie ?? $this->getRememberMeCookie();

        if (!$cookie)
            return null;

        // parse cookie data
        $token = substr($cookie, 0, self::TOKEN_LENGTH);
        $validator = substr($cookie, self::TOKEN_LENGTH);

        // get db data
        $dbData = $this->repo->getRememberMeHashAndToken($token);
        if (!$dbData)
            return null;

        $dbHash = $dbData[$this->cfg->getRememberHashColumn()];

        if (!password_verify($validator, $dbHash))
            return null;

        return $this->repo->getUsernameFromRememberToken($token);
    }

    /**
     * Logout user and remove "remember me" cookie
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();

        setcookie($this->cfg->getCookiePrefix() . self::REMEMBER_COOKIE_NAME,
            '',
            -1,
            $this->cfg->getCookiePath());

        setcookie($this->cfg->getCookiePrefix() . self::OTP_TOKEN_COOKIE_NAME,
            '',
            -1,
            $this->cfg->getCookiePath());
    }

    /**
     * Check if OTP is expired
     *
     * @param string $username
     * @return bool
     */
    public function otpExpired(string $username): bool
    {
        $otpExpire = (int) $this->repo->getOtpData($username)[$this->cfg->getOtpExpireColumn()];

        return $this->timestampPassed($otpExpire);
    }

    /**
     * Check if unix $timestamp has already passed in the real world
     *
     * @param int $timestamp
     * @return bool
     */
    private function timestampPassed(int $timestamp): bool
    {
        $diff = time() - $timestamp;

        return ($diff >= 0);
    }

    /**
     * Get userId of currently logged user or of $username if set
     *
     * @param string|null $username
     * @return int
     */
    public function userId(?string $username = null): int
    {
        $username = $username ?? $this->sesAuth->get(self::USERNAME_VAR);

        return $this->repo->userId($username);
    }

}