<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Models\UserModel;

class AuthController
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
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        $user = $this->userModel->findByPhoneNumberWithRole($phoneNumber);

        if ($user) {
            $lockedUntil = $user['locked_until'] ? strtotime($user['locked_until']) : 0;
            if ($lockedUntil > time()) {
                $this->logger->warning("Login attempt for locked account: {$phoneNumber}.");
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Your account is temporarily locked. Please try again later.']);
                return;
            }
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            // Success: log attempt and clear previous failures
            $this->logLoginAttempt($user['id'], $ipAddress, true);

            // Start a session and store user data
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['logged_in'] = true;

            $this->logger->info("User {$user['id']} logged in successfully with role: {$user['role_name']}.");
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Login successful!']);
            return;
        }

        // Failure: user not found or incorrect password
        if ($user) {
            $this->logLoginAttempt($user['id'], $ipAddress, false);
            $failedAttempts = $this->countRecentFailedAttempts($user['id']);

            $maxAttempts = 3;
            $lockoutTime = 15 * 60; // 15 minutes

            if ($failedAttempts >= $maxAttempts) {
                $lockUntil = date('Y-m-d H:i:s', time() + $lockoutTime);
                $this->userModel->updateById($user['id'], ['locked_until' => $lockUntil]);
                $this->logger->alert("Account locked for user {$user['id']} due to too many failed login attempts.");
            }
        }

        $this->logger->warning("Failed login attempt for phone number: {$phoneNumber}.");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid phone number or password.']);
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

    private function logLoginAttempt(int $userId, string $ipAddress, bool $wasSuccessful)
    {
        $attemptModel = new \Jeffrey\Educore\Models\LoginAttemptModel(); // We need to create this
        $attemptModel->create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'was_successful' => $wasSuccessful
        ]);
    }

    private function countRecentFailedAttempts(int $userId): int
    {
        $attemptModel = new \Jeffrey\Educore\Models\LoginAttemptModel(); // We need to create this
        $timeframe = 15 * 60; // 15 minutes
        return $attemptModel->countFailedAttempts($userId, $timeframe);
    }
}