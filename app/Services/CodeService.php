<?php

namespace App\Services;

use Illuminate\Support\Str;

class CodeService
{
    public static function generateCode($prefix, $id)
    {
        return $prefix.'-'.$id.'-'.strtoupper(Str::random(5));
    }
}
