<?php

namespace MetaRush\OtpAuth;

class Config
{
    private $smtpHost;
    private $smtpUser;
    private $smtpPass;
    private $smtpEncr;
    private $smtpPort;
    private $fromName;
    private $fromEmail;
    private $subject = "Here's your OTP";
    private $body = "{OTP}\r\n\r\nNote: This OTP is valid for 5 minutes";
    private $table;
    private $usernameColumn;
    private $emailColumn;
    private $otpColumn;
    private $otpLength = 8;
    private $characterPool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Get the name of table column where OTP is stored
     *
     * @return string
     */
    public function getOtpColumn(): string
    {
        return $this->otpColumn;
    }

    /**
     * Set the name of table column where OTP is stored
     *
     * @param string $otpColumn
     * @return $this
     */
    public function setOtpColumn(string $otpColumn)
    {
        $this->otpColumn = $otpColumn;

        return $this;
    }

    /**
     * Get SMTP host
     *
     * @return string
     */
    public function getSmtpHost(): string
    {
        return $this->smtpHost;
    }

    /**
     * Set SMTP host
     *
     * @param string $smtpHost
     * @return $this
     */
    public function setSmtpHost(string $smtpHost)
    {
        $this->smtpHost = $smtpHost;

        return $this;
    }

    /**
     * Get SMTP username
     *
     * @return string
     */
    public function getSmtpUser(): string
    {
        return $this->smtpUser;
    }

    /**
     * Set SMTP username
     *
     * @param string $smtpUser
     * @return $this
     */
    public function setSmtpUser(string $smtpUser)
    {
        $this->smtpUser = $smtpUser;

        return $this;
    }

    /**
     * Get SMTP password
     *
     * @return string
     */
    public function getSmtpPass(): string
    {
        return $this->smtpPass;
    }

    /**
     * Set SMTP password
     *
     * @param string $smtpPass
     * @return $this
     */
    public function setSmtpPass(string $smtpPass)
    {
        $this->smtpPass = $smtpPass;

        return $this;
    }

    /**
     * Get SMTP encryption protocol
     *
     * @return string|null
     */
    public function getSmtpEncr(): ?string
    {
        return $this->smtpEncr;
    }

    /**
     * Set SMTP encryption protocol e.g., TLS, SSL
     *
     * @param string|null $smtpEncr
     * @return $this
     */
    public function setSmtpEncr(?string $smtpEncr)
    {
        $this->smtpEncr = $smtpEncr;

        return $this;
    }

    /**
     * Get SMTP port
     *
     * @return int
     */
    public function getSmtpPort(): int
    {
        return $this->smtpPort;
    }

    /**
     * Set SMTP port
     *
     * @param int $smtpPort
     * @return $this
     */
    public function setSmtpPort(int $smtpPort)
    {
        $this->smtpPort = $smtpPort;

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
     * Get OTP length
     *
     * @return int
     */
    public function getOtpLength(): int
    {
        return $this->otpLength;
    }

    /**
     * Set OTP length
     *
     * Default: 8
     *
     * @param int $otpLength
     * @return $this
     */
    public function setOtpLength(int $otpLength)
    {
        $this->otpLength = $otpLength;

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
