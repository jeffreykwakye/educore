<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Middleware\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle(): bool
    {
        session_start();

        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            if (str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
            } else {
                header("Location: /login");
            }
            return false;
        }
        return true;
    }
}