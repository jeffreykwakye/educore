<?php

namespace Jeffrey\Educore\Middleware\User\Admin;

use Jeffrey\Educore\Middleware\Middleware;

class MasterAdminMiddleware extends Middleware
{
    public function handle(): bool
    {
        
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            if (str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
                exit;
            }
            header("Location: /login");
            exit;
        }

        $userRoles = $_SESSION['user_roles'] ?? [];
        if (!in_array('master_admin', $userRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Forbidden. You do not have permission to perform this action.']);
            exit;
        }

        return true;
    }
}