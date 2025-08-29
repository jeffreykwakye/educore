<?php

namespace Jeffrey\Educore\Controllers;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Models\UserModel;

class UserController
{
    private $userModel;
    private $logger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function showLoginForm()
    {
        $viewPath = __DIR__ . '/../../resources/views/login.html';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(500);
            echo "Error: Login form not found.";
        }
    }

    public function processLogin()
    {
        $phoneNumber = trim($_POST['phone_number']);
        $password = $_POST['password'];

        // Find the user in the database
        $user = $this->userModel->findBy('phone_number', $phoneNumber);

        if (!$user) {
            $this->logger->warning("Failed login attempt for phone number: {$phoneNumber}. User not found.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid phone number or password.']);
            return;
        }

        // Verify the password
        if (!password_verify($password, $user['password_hash'])) {
            $this->logger->warning("Failed login attempt for user {$user['id']}. Incorrect password.");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid phone number or password.']);
            return;
        }

        // Start a session and store user data
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['logged_in'] = true;

        // Password is correct, user is authenticated
        $this->logger->info("User {$user['id']} logged in successfully.");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Login successful!']);
    }


    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        exit;
    }

}