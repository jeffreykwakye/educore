<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers;

class SchoolController
{
    public function showRegistrationForm()
    {
        echo "This is the school registration form.";
    }

    public function processRegistration()
    {
        echo "Processing the school registration form...";
    }
}