<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Utils\Utils;

class SchoolController
{
    public function showRegistrationForm()
    {
        $viewPath = __DIR__ . '/../../resources/views/register.html';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(500);
            echo "Error: Registration form not found.";
        }
    }

    public function processRegistration()
    {
        $logger = AppLogger::getInstance()->getLogger();

        $schoolName = trim($_POST['name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM schools WHERE phone_number = ?");
            $stmt->execute([$phoneNumber]);
            if ($stmt->fetchColumn() > 0) {
                $logger->error("Registration failed: Phone number already exists.");
                
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Registration failed: Phone number already exists.']);
                $db->rollBack();
                return;
            }

            $slug = Utils::generateSlug($schoolName);
            
            $sql = "INSERT INTO schools (name, slug, phone_number, address) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$schoolName, $slug, $phoneNumber, $address]);

            $schoolId = $db->lastInsertId();

            $sqlBranding = "INSERT INTO school_branding (school_id) VALUES (?)";
            $stmtBranding = $db->prepare($sqlBranding);
            $stmtBranding->execute([$schoolId]);

            $db->commit();
            $logger->info("New school registered successfully: {$schoolName}");
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'School registered successfully!']);

        } catch (\PDOException $e) {
            $db->rollBack();
            $logger->error("Database error during registration: " . $e->getMessage());

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration.']);
        }
    }

}