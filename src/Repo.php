<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

use MetaRush\DataMapper;

class Repo
{
    private $cfg;
    private $mapper;

    public function __construct(Config $cfg, DataMapper\DataMapper $mapper)
    {
        $this->cfg = $cfg;
        $this->mapper = $mapper;
    }

    /**
     * Check if user exist
     *
     * @param string $username
     * @return bool
     */
    public function userExist(string $username): bool
    {
        $where = [$this->cfg->getUsernameColumn() => $username];

        return $this->mapper->findOne($this->cfg->getTable(), $where) ? true : false;
    }

    /**
     * Get email of user
     *
     * @param string $username
     * @return string|null
     */
    public function getEmail(string $username): ?string
    {
        if (!$this->userExist($username))
            throw new \Error('User ' . $username . ' does not exist');

        $where = [$this->cfg->getUsernameColumn() => $username];

        if (!$user = $this->mapper->findOne($this->cfg->getTable(), $where))
            return null;

        return $user[$this->cfg->getEmailColumn()];
    }

    /**
     * Set OTP hash and OTP token in Db
     *
     * @param string $otpHash
     * @param string $otpToken
     * @param string $username
     * @return void
     */
    public function setOtpHashAndToken(string $otpHash, string $otpToken, string $username): void
    {
        $data = [
            $this->cfg->getOtpHashColumn()  => $otpHash,
            $this->cfg->getOtpTokenColumn() => $otpToken
        ];

        $where = [$this->cfg->getUsernameColumn() => $username];

        $this->mapper->update($this->cfg->getTable(), $data, $where);
    }

    /**
     * Get OTP hash and OTP token in Db
     *
     * Returns an array with keys:
     * - otpHash
     * - otpToken
     *
     * @param string $username
     * @return array
     */
    public function getOtpHashAndToken(string $username): array
    {
        $where = [$this->cfg->getUsernameColumn() => $username];

        $row = $this->mapper->findOne($this->cfg->getTable(), $where);

        return [
            'otpHash'  => $row[$this->cfg->getOtpHashColumn()],
            'otpToken' => $row[$this->cfg->getOtpTokenColumn()]
        ];
    }

    /**
     * Set "remember me" hash and OTP token in Db
     *
     * @param string $hash
     * @param string $token
     * @param string $username
     * @return void
     */
    public function setRememberMeHashAndToken(string $hash, string $token, string $username): void
    {
        $data = [
            $this->cfg->getRememberHashColumn()  => $hash,
            $this->cfg->getRememberTokenColumn() => $token
        ];

        $where = [$this->cfg->getUsernameColumn() => $username];

        $this->mapper->update($this->cfg->getTable(), $data, $where);
    }
}
