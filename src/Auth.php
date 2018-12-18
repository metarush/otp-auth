<?php

namespace MetaRush\OtpAuth;

class Auth
{
    private $cfg;
    private $repo;

    public function __construct(Config $cfg, Repo $repo)
    {
        $this->cfg = $cfg;
        $this->repo = $repo;
    }

    public function isUserExist(string $username)
    {

    }

    public function sendOtp(string $username)
    {


    }

    public function isValidOtp(string $otp, string $username): bool
    {

    }

    public function login()
    {

    }

    public function remember()
    {

    }

    public function logout()
    {

    }

}
