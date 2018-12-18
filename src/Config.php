<?php

namespace MetaRush\OtpAuth;

class Config
{
    private $otpLength;
    private $characterPool;

    /**
     * @return mixed
     */
    public function getOtpLength(): int
    {
        return $this->otpLength;
    }

    /**
     * @param mixed $otpLength
     *
     * @return self
     */
    public function setOtpLength(int $otpLength)
    {
        $this->otpLength = $otpLength;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCharacterPool():  ? string
    {
        return $this->characterPool;
    }

    /**
     * Set the pool of characters where the token will be derived from.
     * If null, the following pool will be used:
     *
     * 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
     *
     * @param mixed $characterPool
     *
     * @return self
     */
    public function setCharacterPool(string $characterPool)
    {
        $this->characterPool = $characterPool;

        return $this;
    }
}
