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

        $user = $this->mapper->findOne($this->cfg->getTable(), $where);

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
    public function setOtpData(string $otpHash, string $otpToken, string $username): void
    {
        $data = [
            $this->cfg->getOtpHashColumn()   => $otpHash,
            $this->cfg->getOtpTokenColumn()  => $otpToken,
            $this->cfg->getOtpExpireColumn() => time() + ($this->cfg->getOtpExpire() * 60)
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
    public function getOtpData(string $username): array
    {
        $where = [$this->cfg->getUsernameColumn() => $username];

        $row = $this->mapper->findOne($this->cfg->getTable(), $where);

        return [
            $this->cfg->getOtpHashColumn()   => $row[$this->cfg->getOtpHashColumn()],
            $this->cfg->getOtpTokenColumn()  => $row[$this->cfg->getOtpTokenColumn()],
            $this->cfg->getOtpExpireColumn() => $row[$this->cfg->getOtpExpireColumn()]
        ];
    }

    /**
     * Set "remember me" hash and token in Db
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

    /**
     * Get "remember me" hash and token in Db
     *
     * @param string $token
     * @return ?array
     */
    public function getRememberMeHashAndToken(string $token): ?array
    {
        $where = [$this->cfg->getRememberTokenColumn() => $token];

        $row = $this->mapper->findOne($this->cfg->getTable(), $where);

        if (!$row)
            return null;

        return [
            $this->cfg->getRememberHashColumn()  => $row[$this->cfg->getRememberHashColumn()],
            $this->cfg->getRememberTokenColumn() => $row[$this->cfg->getRememberTokenColumn()]
        ];
    }

    /**
     * Get username from remember me cookie token
     *
     * @param string $token
     * @return string
     */
    public function getUsernameFromRememberToken(string $token): string
    {
        $where = [$this->cfg->getRememberTokenColumn() => $token];

        $row = $this->mapper->findOne($this->cfg->getTable(), $where);

        return $row[$this->cfg->getUsernameColumn()];
    }

    /**
     * Get userId of $username
     *
     * @param string $username
     * @return int
     */
    public function userId(string $username): int
    {
        $where = [$this->cfg->getUsernameColumn() => $username];

        $row = $this->mapper->findOne($this->cfg->getTable(), $where);

        return (int) $row[$this->cfg->getUserIdColumn()];
    }

}