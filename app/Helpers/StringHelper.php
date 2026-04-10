<?php

namespace App\Helpers;

use Str;

class StringHelper
{
    public static function normalizeString($str)
    {
        if (empty($str))
            return $str;

        $str = Str::lower(Str::ascii($str));

        $str = trim($str);

        return $str;
    }

    public static function uppercaseFirst($str)
    {
        if (empty($str))
            return $str;

        $str = Str::ucfirst(Str::lower($str));
        return $str;
    }
}