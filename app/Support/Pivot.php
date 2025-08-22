<?php

namespace App\Support;

class Pivot
{
    public const USER_SCHOOLS = 'school_user';

    public static function schoolUserTable(): string
    {
        return self::USER_SCHOOLS;
    }
}

