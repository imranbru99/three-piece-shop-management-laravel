<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class BdPhone
{
    public static function normalize(string $phone): string
    {
        $phone = strtr($phone, array_combine(preg_split('//u', '০১২৩৪৫৬৭৮৯', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)));
        $phone = preg_replace('/[\s()-]/', '', $phone) ?? '';
        $phone = preg_replace('/^(?:\+88|88)/', '', $phone) ?? '';
        if (! preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => Language::text('Enter a valid Bangladesh mobile number.', 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন।')]);
        }

        return $phone;
    }
}
