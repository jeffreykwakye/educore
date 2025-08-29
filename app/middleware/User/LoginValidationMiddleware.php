<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Utils\Utils;

class LoginValidationMiddleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle($next)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning("Invalid request method for login form.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
            return;
        }

        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($phoneNumber) || empty($password)) {
            $this->logger->error("Login failed: Missing phone number or password.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phone number and password are required.']);
            return;
        }
        
        if (!Utils::isGhanaianPhoneNumberValid($phoneNumber)) {
            $this->logger->error("Login failed: Invalid phone number format.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid phone number format.']);
            return;
        }

        return $next();
    }
}