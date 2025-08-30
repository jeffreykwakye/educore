<?php

namespace Jeffrey\Educore\Middleware\School;

use Jeffrey\Educore\Middleware\Middleware;
use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Utils\Utils;

class SchoolValidationMiddleware extends Middleware
{
    private $logger;

    public function __construct()
    {
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning("Invalid request method for registration form.");
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $schoolName = trim($_POST['name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');

        if (empty($schoolName) || empty($phoneNumber)) {
            $this->logger->error("Registration failed: Missing required fields.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        if (!Utils::isGhanaianPhoneNumberValid($phoneNumber)) {
            $this->logger->error("Registration failed: Invalid phone number format.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid phone number format.']);
            exit;
        }

        return true;
    }
}