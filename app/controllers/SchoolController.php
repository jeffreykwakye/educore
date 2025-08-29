<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers;

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Models\SchoolModel; 
use Jeffrey\Educore\Utils\Utils;

class SchoolController
{
    private $schoolModel;

    
    public function __construct()
    {
        $this->schoolModel = new SchoolModel();
    }


    public function showRegistrationForm()
    {
        // Use the reusable loadView method from the Utils class
        $viewPath = APP_ROOT . '/resources/views/register.html';
        Utils::loadView($viewPath);
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
            // Check for a duplicate phone number using the SchoolModel
            if ($this->schoolModel->findBy('phone_number', $phoneNumber)) {
                $logger->error("Registration failed: Phone number already exists.");
                
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Registration failed: Phone number already exists.']);
                $db->rollBack();
                return;
            }

            $slug = Utils::generateSlug($schoolName);
            
            // Prepare data for the insert operation
            $schoolData = [
                'name' => $schoolName,
                'slug' => $slug,
                'phone_number' => $phoneNumber,
                'address' => $address
            ];

            // Insert into the schools table using the SchoolModel
            if (!$this->schoolModel->create($schoolData)) {
                throw new \Exception("Failed to insert school data.");
            }

            $schoolId = $db->lastInsertId();

            // Insert into school_branding table
            $sqlBranding = "INSERT INTO school_branding (school_id) VALUES (?)";
            $stmtBranding = $db->prepare($sqlBranding);
            $stmtBranding->execute([$schoolId]);

            $db->commit();
            $logger->info("New school registered successfully: {$schoolName}");
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'School registered successfully!']);

        } catch (\Exception | \PDOException $e) {
            $db->rollBack();
            $logger->error("Database error during registration: " . $e->getMessage());

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration.']);
        }
    }
}