<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Core\AppLogger;

class AutoLogoutMiddleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle($next)
    {
        session_start();
        
        // Define the roles that are exempt from auto-logout
        $exemptRoles = ['parent_user', 'student_user']; 

        // Check if the user's role is in the exempt list
        $userRole = $_SESSION['role_name'] ?? null;
        if (in_array($userRole, $exemptRoles)) {
            // User is exempt, so just continue
            return $next();
        }

        // Check for inactivity
        $lastActivity = $_SESSION['last_activity'] ?? null;
        $inactivityLimit = 30 * 60; // 30 minutes in seconds

        if ($lastActivity && (time() - $lastActivity > $inactivityLimit)) {
            // Inactivity limit exceeded, log the user out
            session_unset();
            session_destroy();
            $this->logger->info("User {$userRole} was logged out due to inactivity.");
            
            // Redirect to login page
            header("Location: /login");
            exit;
        }

        // Update the last activity timestamp on every request
        $_SESSION['last_activity'] = time();

        return $next();
    }
}