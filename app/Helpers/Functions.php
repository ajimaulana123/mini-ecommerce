<?php

namespace App\Helpers;

class Functions
{
    public static function generateRandomId($length = 10)
    {
        return bin2hex(random_bytes((int)($length / 2)));
    }
}