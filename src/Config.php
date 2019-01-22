<?php

namespace MetaRush\OtpAuth;

class Config
{
    private $table;
    private $usernameColumn;
    private $emailColumn;
    private $otpLength = 8;
    private $characterPool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
        if (!$this->usernameColumn)
            throw new Errors\EmptyError('You must call setUsernameColumn() with a non-empty argument');

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
