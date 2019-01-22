<?php

namespace MetaRush\OtpAuth;

class Auth
{
    private $repo;

    public function __construct(Repo $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Check if user exist
     *
     * @param string $username
     * @return bool
     */
    public function userExist(string $username): bool
    {
        return $this->repo->userExist($username);
    }

    public function sendOtp(string $username)
    {

    }

    public function validOtp(string $otp, string $username): bool
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
