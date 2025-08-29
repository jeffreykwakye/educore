<?php

namespace Jeffrey\Educore\Controllers;

class DashboardController
{
    public function showDashboard()
    {
        $viewPath = __DIR__ . '/../../resources/views/dashboard.html';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Log this error
            http_response_code(500);
            echo "Error: Dashboard view not found.";
        }
    }
}