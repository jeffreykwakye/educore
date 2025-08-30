<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Middleware\Middleware;
use Jeffrey\Educore\Core\AppLogger;

class AutoLogoutMiddleware extends Middleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle(): bool
    {
        session_start();
        
        $exemptRoles = ['parent_user', 'student_user']; 

        $userRole = $_SESSION['role_name'] ?? null;
        if (in_array($userRole, $exemptRoles)) {
            return true;
        }

        $lastActivity = $_SESSION['last_activity'] ?? null;
        $inactivityLimit = 30 * 60; // 30 minutes in seconds

        if ($lastActivity && (time() - $lastActivity > $inactivityLimit)) {
            session_unset();
            session_destroy();
            $this->logger->info("User {$userRole} was logged out due to inactivity.");
            
            header("Location: /login");
            exit;
            return false;
        }

        $_SESSION['last_activity'] = time();

        return true;
    }
}