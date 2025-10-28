<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

use MetaRush\DataAccess;

class Builder extends Config
{
    /**
     * Return an instance of the OtpAuth class
     *
     * @return \MetaRush\OtpAuth\Auth
     */
    public function build(?string $childClass = null): Auth
    {
        $dal = (new DataAccess\Builder)
            ->setDsn($this->getDsn())
            ->setDbUser($this->getDbUser())
            ->setDbPass($this->getDbPass())
            ->build();

        $repo = new Repo($this, $dal);

        if ($childClass)
            return new $childClass($this, $repo);

        return new Auth($this, $repo);
    }

    /**
     * Return an instance of EmailFallback\Server class
     *
     * @return \MetaRush\EmailFallback\Server
     */
    public function SmtpServer(): SmtpServer
    {
        return new SmtpServer;
    }
}