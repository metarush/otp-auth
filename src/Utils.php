<?php

namespace MetaRush\Auth;

class Utils
{
    /**
     * Generates random token
     *
     * @param int $length Length of token to be generated
     * @param int $pool Pool of characters where token will be generated from. Default: a-z A-Z 0-9
     * @return string
     */
    public static function randomToken(int $length, string $pool = null): string
    {
        $pool = $pool ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $chars = str_split($pool);

        $c = count($chars) - 1;

        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[random_int(0, $c)];
        }

        return $token;
    }
}
