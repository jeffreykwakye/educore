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


    /**
     * Loads a view from the specified path and includes it.
     *
     * @param string $viewPath The absolute path to the view file.
     * @param array $data An associative array of data to be passed to the view.
     * @return void
     */
    public static function loadView(string $viewPath, array $data = []): void
    {
        if (file_exists($viewPath)) {
            // Extract the data array to make variables available in the view
            extract($data);
            require $viewPath;
        } else {
            http_response_code(404);
            echo "Error: View not found at {$viewPath}.";
        }
    }

}