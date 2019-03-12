<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

class Config extends \MetaRush\EmailFallback\Config
{
    private $rememberHashColumn = 'rememberHash';
    private $rememberTokenColumn = 'rememberToken';
    private $rememberCookieExpire = 2592000; // 30 days
    private $smtpServers;
    private $fromName;
    private $fromEmail;
    private $subject = "Here's your OTP";
    private $body = "{OTP}\r\n\r\nNote: This OTP is valid for 5 minutes";
    private $dsn;
    private $dbUser = null;
    private $dbPass = null;
    private $table;
    private $usernameColumn = 'username';
    private $emailColumn = 'email';
    private $otpHashColumn = 'otpHash';
    private $otpTokenColumn = 'otpToken';
    private $otpExpireColumn = 'otpExpire';
    private $otpExpire = 5;
    private $cookiePrefix = 'MROA_';
    private $characterPool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Get PDO DSN
     *
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * Set PDO DSN
     *
     * @param string $dsn
     * @return $this
     */
    public function setDsn(string $dsn)
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * Get DB username
     *
     * @return string|null
     */
    public function getDbUser(): ?string
    {
        return $this->dbUser;
    }

    /**
     * Set DB username
     *
     * @param string|null $dbUser
     * @return $this
     */
    public function setDbUser(?string $dbUser)
    {
        $this->dbUser = $dbUser;

        return $this;
    }

    /**
     * Get DB password
     *
     * @return string|null
     */
    public function getDbPass(): ?string
    {
        return $this->dbPass;
    }

    /**
     * Set DB password
     *
     * @param string|null $dbPass
     * @return $this
     */
    public function setDbPass(?string $dbPass)
    {
        $this->dbPass = $dbPass;

        return $this;
    }

    /**
     * Get column name for "remember me" hash
     *
     * @return string
     */
    public function getRememberHashColumn(): string
    {
        return $this->rememberHashColumn;
    }

    /**
     * Set column name for "remember me" hash
     *
     * @param string $rememberHashColumn
     * @return $this
     */
    public function setRememberHashColumn(string $rememberHashColumn)
    {
        $this->rememberHashColumn = $rememberHashColumn;

        return $this;
    }

    /**
     * Get column name for lookup token for "remember me"
     *
     * @return string
     */
    public function getRememberTokenColumn(): string
    {
        return $this->rememberTokenColumn;
    }

    /**
     *  Set column name for lookup token for "remember me"
     *
     * @param string $rememberLookupTokenColumn
     * @return $this
     */
    public function setRememberTokenColumn(string $rememberTokenColumn)
    {
        $this->rememberTokenColumn = $rememberTokenColumn;

        return $this;
    }

    /**
     * Get how long "remember me" cookie in seconds
     *
     * @return int
     */
    public function getRememberCookieExpire(): int
    {
        return $this->rememberCookieExpire;
    }

    /**
     * Set how long "remember me" cookie in seconds
     *
     * @param int $rememberCookieExpire
     * @return $this
     */
    public function setRememberCookieExpire(int $rememberCookieExpire)
    {
        $this->rememberCookieExpire = $rememberCookieExpire;

        return $this;
    }

    /**
     * Return an array of SmtpServer objects
     *
     * @return array
     */
    public function getSmtpServers(): array
    {
        return $this->smtpServers;
    }

    /**
     * Set an array of SmtpServer objects
     *
     * @param array $smtpServers
     * @return $this
     */
    public function setSmtpServers(array $smtpServers)
    {
        foreach ($smtpServers as $server)
            if (!$server instanceof SmtpServer)
                throw new Error('Parameter of ' . __METHOD__ . ' must be an array with elements consisting of SmtpServer objects');

        $this->smtpServers = $smtpServers;

        return $this;
    }

    /**
     * Get OTP token column
     *
     * @return string
     */
    public function getOtpTokenColumn(): string
    {
        return $this->otpTokenColumn;
    }

    /**
     * Set OTP token column
     *
     * @param string $otpTokenColumn
     * @return $this
     */
    public function setOtpTokenColumn(string $otpTokenColumn)
    {
        $this->otpTokenColumn = $otpTokenColumn;

        return $this;
    }

    /**
     * Get OTP expiration in minutes
     *
     * @return int
     */
    public function getOtpExpire(): int
    {
        return $this->otpExpire;
    }

    /**
     * Set OTP expiration in minutes
     *
     * @param int $otpExpire
     * @return $this
     */
    public function setOtpExpire(int $otpExpire)
    {
        $this->otpExpire = $otpExpire;

        return $this;
    }

    /**
     * Get the name of table column where OTP is stored
     *
     * @return string
     */
    public function getOtpHashColumn(): string
    {
        return $this->otpHashColumn;
    }

    /**
     * Set the name of table column where OTP hash is stored
     *
     * Note: Must be at least 255 characters in length
     *
     * @param string $otpHashColumn
     * @return $this
     */
    public function setOtpHashColumn(string $otpHashColumn)
    {
        $this->otpHashColumn = $otpHashColumn;

        return $this;
    }

    /**
     * Get the name of table column where OTP expire is stored
     *
     * @return string
     */
    public function getOtpExpireColumn(): string
    {
        return $this->otpExpireColumn;
    }

    /**
     * Set the name of table column where OTP expire is stored
     *
     * @param string $otpExpireColumn
     * @return $this
     */
    public function setOtpExpireColumn(string $otpExpireColumn)
    {
        $this->otpExpireColumn = $otpExpireColumn;

        return $this;
    }

    /**
     * Get the From Name of the OTP message
     *
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Set the From Name of the OTP message
     *
     * @param string $fromName
     * @return $this
     */
    public function setFromName(string $fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * Get the From Email of the OTP message
     *
     * @return string
     */
    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    /**
     * Set the From Email of the OTP message
     *
     * @param string $fromEmail
     * @return $this
     */
    public function setFromEmail(string $fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Get subject of the OTP email
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Set subject of the OTP email
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get body of the OTP email
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Set body of the OTP email
     *
     * @param string $body
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the name of table column where email is stored
     *
     * @return string
     */
    public function getEmailColumn(): string
    {
        return $this->emailColumn;
    }

    /**
     * Set the name of table column where email is stored
     *
     * @param string $emailColumn
     * @return $this
     */
    public function setEmailColumn(string $emailColumn)
    {
        $this->emailColumn = $emailColumn;

        return $this;
    }

    /**
     * Get the name of table column where username is stored
     *
     * @return string
     */
    public function getUsernameColumn(): string
    {
        return $this->usernameColumn;
    }

    /**
     * Set the name of table column where username is stored
     *
     * @param string $usernameColumn
     * @return $this
     */
    public function setUsernameColumn(string $usernameColumn)
    {
        $this->usernameColumn = $usernameColumn;

        return $this;
    }

    /**
     * Get name of table where usernames will be authenticated
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Name of table where usernames will be authenticated
     *
     * @param string $table
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get cookie name prefix used by metarush/otp-auth
     *
     * @return string
     */
    public function getCookiePrefix(): string
    {
        return $this->cookiePrefix;
    }

    /**
     * Set cookie name prefix used by metarush/otp-auth
     *
     * @param string $cookiePrefix
     * @return $this
     */
    public function setCookiePrefix(string $cookiePrefix)
    {
        $this->cookiePrefix = $cookiePrefix;

        return $this;
    }

    /**
     * Get character pool where the token will be derived from
     *
     * @return string
     */
    public function getCharacterPool(): string
    {
        return $this->characterPool;
    }

    /**
     * Set the pool of characters where the token will be derived from
     *
     * Default: 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
     *
     * @param string $characterPool
     * @return $this
     */
    public function setCharacterPool(string $characterPool)
    {
        $this->characterPool = $characterPool;

        return $this;
    }
}
