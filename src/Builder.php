<?php

declare(strict_types=1);

namespace MetaRush\OtpAuth;

use MetaRush\DataMapper;

class Builder extends Config
{

    /**
     * Return an instance of the OtpAuth class
     *
     * @return \MetaRush\OtpAuth\Auth
     */
    public function build(): Auth
    {
        $mapper = (new DataMapper\Builder)
            ->setDsn($this->getDsn())
            ->setDbUser($this->getDbUser())
            ->setDbPass($this->getDbPass())
            ->build();

        $repo = new Repo($this, $mapper);

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
