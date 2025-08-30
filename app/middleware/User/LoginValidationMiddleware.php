<?php

namespace Jeffrey\Educore\Middleware\User;

use Jeffrey\Educore\Middleware\Middleware;
use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Utils\Utils;

class LoginValidationMiddleware extends Middleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning("Invalid request method for login form.");
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
            exit;
        }

        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($phoneNumber) || empty($password)) {
            $this->logger->error("Login failed: Missing phone number or password.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phone number and password are required.']);
            exit;
        }
        
        if (!Utils::isGhanaianPhoneNumberValid($phoneNumber)) {
            $this->logger->error("Login failed: Invalid phone number format.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid phone number format.']);
            exit;
        }

        return true;
    }
}