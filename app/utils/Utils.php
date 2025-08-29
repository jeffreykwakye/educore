<?php
declare(strict_types = 1);

namespace Jeffrey\Educore\Utils;

class Utils
{
    public static function generateSlug(string $string): string
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    
    public static function isGhanaianPhoneNumberValid(string $phoneNumber): bool
    {
        if (preg_match('/^0[0-9]{9}$/', $phoneNumber)) {
            return true;
        }
        if (preg_match('/^\+233[1-9][0-9]{8}$/', $phoneNumber)) {
            return true;
        }
        return false;
    }
}