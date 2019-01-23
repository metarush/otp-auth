<?php

namespace MetaRush\OtpAuth;

class Config
{
    private $fromName;
    private $fromEmail;
    private $subject = "Here's your OTP";
    private $body = "{OTP}\r\n\r\nNote: This OTP is valid for 5 minutes";
    private $transport;
    private $table;
    private $usernameColumn;
    private $emailColumn;
    private $otpLength = 8;
    private $characterPool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
     * Get the SwiftMailer transport object that will be used to send email
     *
     * @return \Swift_Transport
     */
    public function getTransport(): \Swift_Transport
    {
        return $this->transport;
    }

    /**
     * Set the SwiftMailer transport object that will be used to send email
     *
     * @link https://swiftmailer.symfony.com/docs/sending.html#transport-types
     * @param \Swift_Transport $transport
     * @return $this
     */
    public function setTransport(\Swift_Transport $transport)
    {
        $this->transport = $transport;

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
        return $this->message;
    }

    /**
     * Set body of the OTP email
     *
     * @param string $body
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->message = $body;
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
