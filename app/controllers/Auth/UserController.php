<?php

namespace Jeffrey\Educore\Controllers\Auth;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Models\UserModel;
use Jeffrey\Educore\Models\RoleModel;

class UserController
{
    private $userModel;
    private $roleModel;
    private $logger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    public function processUserCreation()
    {
        // Simple input validation
        if (empty($_POST['phoneNumber']) || empty($_POST['password']) || empty($_POST['role'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phone number, password, and role are required.']);
            return;
        }

        $phoneNumber = trim($_POST['phoneNumber']);
        $password = $_POST['password'];
        $roleName = $_POST['role'];
        $schoolId = !empty($_POST['schoolId']) ? (int)$_POST['schoolId'] : null;

        // Check if the user already exists
        if ($this->userModel->findByPhoneNumber($phoneNumber)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User with this phone number already exists.']);
            return;
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Get role ID
        $role = $this->roleModel->findByName($roleName);
        if (!$role) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid role specified.']);
            return;
        }
        
        // Begin a transaction for atomic operation
        $this->userModel->beginTransaction();
        
        try {
            // Create the user
            $userData = ['phone_number' => $phoneNumber, 'password_hash' => $passwordHash];
            if ($schoolId) {
                $userData['school_id'] = $schoolId;
            }
            $userId = $this->userModel->create($userData);

            if ($userId) {
                // Link the user to the role
                $this->userModel->linkUserToRole($userId, $role['id']);
                
                $this->userModel->commit();
                $this->logger->info("New user created successfully. User ID: {$userId}, Role: {$roleName}");
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User created successfully!']);
            } else {
                $this->userModel->rollBack();
                $this->logger->error("Failed to create new user.");
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create user.']);
            }
        } catch (\PDOException $e) {
            $this->userModel->rollBack();
            $this->logger->error("Database error during user creation: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
        }
    }
}