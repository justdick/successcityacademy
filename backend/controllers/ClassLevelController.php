<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ClassLevel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ClassLevelController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create a new class level (admin only)
     * 
     * @param array $data Class level data (name)
     * @return void Sends JSON response
     */
    public function createClassLevel($data) {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['name'])) {
                $this->sendErrorResponse("Class level name is required", 400);
                return;
            }

            $name = trim($data['name']);

            // Validate name
            if (empty($name)) {
                $this->sendErrorResponse("Class level name cannot be empty", 400);
                return;
            }

            if (strlen($name) > 50) {
                $this->sendErrorResponse("Class level name must not exceed 50 characters", 400);
                return;
            }

            // Check for duplicate name
            $checkQuery = "SELECT id FROM class_levels WHERE name = :name LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Class level with this name already exists", 409);
                return;
            }

            // Insert new class level
            $query = "INSERT INTO class_levels (name) VALUES (:name)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);

            if ($stmt->execute()) {
                $classLevelId = $this->conn->lastInsertId();

                // Fetch the created class level
                $fetchQuery = "SELECT id, name, created_at FROM class_levels WHERE id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $classLevelId);
                $fetchStmt->execute();
                
                $classLevelData = $fetchStmt->fetch();

                $this->sendSuccessResponse($classLevelData, 201);
            } else {
                $this->sendErrorResponse("Failed to create class level", 500);
            }

        } catch (Exception $e) {
            error_log("Create Class Level Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating class level", 500);
        }
    }

    /**
     * Get all class levels (authenticated users)
     * 
     * @return void Sends JSON response
     */
    public function getAllClassLevels() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Fetch all class levels
            $query = "SELECT id, name, created_at FROM class_levels ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $classLevels = $stmt->fetchAll();

            $this->sendSuccessResponse($classLevels, 200);

        } catch (Exception $e) {
            error_log("Get All Class Levels Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching class levels", 500);
        }
    }

    /**
     * Delete a class level (admin only)
     * 
     * @param int $id Class level ID
     * @return void Sends JSON response
     */
    public function deleteClassLevel($id) {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendErrorResponse("Invalid class level ID", 400);
                return;
            }

            // Check if class level exists
            $checkQuery = "SELECT id FROM class_levels WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Check if class level is in use by any students
            $usageQuery = "SELECT COUNT(*) as count FROM students WHERE class_level_id = :id";
            $usageStmt = $this->conn->prepare($usageQuery);
            $usageStmt->bindParam(':id', $id);
            $usageStmt->execute();
            
            $usageResult = $usageStmt->fetch();

            if ($usageResult['count'] > 0) {
                $this->sendErrorResponse("Cannot delete class level that is in use by existing students", 409);
                return;
            }

            // Delete class level
            $query = "DELETE FROM class_levels WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Class level deleted successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to delete class level", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Class Level Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while deleting class level", 500);
        }
    }

    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    private function sendSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    private function sendErrorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
