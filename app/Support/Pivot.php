<?php

namespace App\Support;

class Pivot
{
    public static function schoolUserTable(): string
    {
        return config('v5.pivot.school_user_table', 'school_user');
    }
}

