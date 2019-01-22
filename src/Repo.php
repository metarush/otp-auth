<?php

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
        $where = [$this->cfg->getUsernameColumn() => $username];

        if (!$user = $this->mapper->findOne($this->cfg->getTable(), $where))
            return null;

        return $user[$this->cfg->getEmailColumn()];
    }
}
