<?php

namespace App\Support;

class Language
{
    public static function text(string $english, string $bangla): string
    {
        return app()->getLocale() === 'bn' ? $bangla : $english;
    }
}
