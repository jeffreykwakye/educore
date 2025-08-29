<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Core\AppLogger;

class AuthMiddleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle($next)
    {
        session_start();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->logger->warning("Unauthorized access attempt. User not logged in.");
            
            // Redirect to login page
            header("Location: /login");
            exit();
        }

        return $next();
    }
}