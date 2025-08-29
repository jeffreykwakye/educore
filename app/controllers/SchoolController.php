<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers;

class SchoolController
{
    public function showRegistrationForm()
    {
        // Path to the registration form HTML in the new location
        $viewPath = __DIR__ . '/../../resources/views/register.html';
        
        // Check if the file exists and include it
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Handle the case where the view file is missing
            http_response_code(500);
            echo "Error: Registration form not found.";
        }
    }

    public function processRegistration()
    {
        echo "Processing the school registration form...";
    }
}