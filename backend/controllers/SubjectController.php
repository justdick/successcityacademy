<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SubjectController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create a new subject (admin only)
     * 
     * @param array $data Subject data (name)
     * @return void Sends JSON response
     */
    public function createSubject($data) {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['name'])) {
                $this->sendErrorResponse("Subject name is required", 400);
                return;
            }

            $name = trim($data['name']);

            // Validate name
            if (empty($name)) {
                $this->sendErrorResponse("Subject name cannot be empty", 400);
                return;
            }

            if (strlen($name) > 255) {
                $this->sendErrorResponse("Subject name must not exceed 255 characters", 400);
                return;
            }

            // Check for duplicate name
            $checkQuery = "SELECT id FROM subjects WHERE name = :name LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Subject with this name already exists", 409);
                return;
            }

            // Insert new subject
            $query = "INSERT INTO subjects (name) VALUES (:name)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);

            if ($stmt->execute()) {
                $subjectId = $this->conn->lastInsertId();

                // Fetch the created subject
                $fetchQuery = "SELECT id, name, created_at FROM subjects WHERE id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $subjectId);
                $fetchStmt->execute();
                
                $subjectData = $fetchStmt->fetch();

                $this->sendSuccessResponse($subjectData, 201);
            } else {
                $this->sendErrorResponse("Failed to create subject", 500);
            }

        } catch (Exception $e) {
            error_log("Create Subject Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating subject", 500);
        }
    }

    /**
     * Get all subjects (authenticated users)
     * 
     * @return void Sends JSON response
     */
    public function getAllSubjects() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Fetch all subjects
            $query = "SELECT id, name, created_at FROM subjects ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $subjects = $stmt->fetchAll();

            $this->sendSuccessResponse($subjects, 200);

        } catch (Exception $e) {
            error_log("Get All Subjects Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching subjects", 500);
        }
    }

    /**
     * Delete a subject (admin only)
     * 
     * @param int $id Subject ID
     * @return void Sends JSON response
     */
    public function deleteSubject($id) {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendErrorResponse("Invalid subject ID", 400);
                return;
            }

            // Check if subject exists
            $checkQuery = "SELECT id FROM subjects WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Check if subject is in use by any grades
            $usageQuery = "SELECT COUNT(*) as count FROM grades WHERE subject_id = :id";
            $usageStmt = $this->conn->prepare($usageQuery);
            $usageStmt->bindParam(':id', $id);
            $usageStmt->execute();
            
            $usageResult = $usageStmt->fetch();

            if ($usageResult['count'] > 0) {
                $this->sendErrorResponse("Cannot delete subject that is in use by existing grades", 409);
                return;
            }

            // Delete subject
            $query = "DELETE FROM subjects WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Subject deleted successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to delete subject", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Subject Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while deleting subject", 500);
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
