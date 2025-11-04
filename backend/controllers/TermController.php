<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Term.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class TermController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create a new term (admin only)
     * 
     * @param array $data Term data (name, academic_year, start_date, end_date, is_active)
     * @return void Sends JSON response
     */
    public function createTerm($data) {
        try {
            // Verify admin authentication
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['name']) || !isset($data['academic_year'])) {
                $this->sendErrorResponse("Term name and academic year are required", 400);
                return;
            }

            $name = trim($data['name']);
            $academic_year = trim($data['academic_year']);
            $start_date = isset($data['start_date']) ? $data['start_date'] : null;
            $end_date = isset($data['end_date']) ? $data['end_date'] : null;
            $is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;

            // Create term object for validation
            $term = new Term(null, $name, $academic_year, $start_date, $end_date, $is_active);
            $validationErrors = $term->validate();

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Check for duplicate term (same name and academic year)
            $checkQuery = "SELECT id FROM terms WHERE name = :name AND academic_year = :academic_year LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->bindParam(':academic_year', $academic_year);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Term with this name and academic year already exists", 409);
                return;
            }

            // Insert new term
            $query = "INSERT INTO terms (name, academic_year, start_date, end_date, is_active) 
                     VALUES (:name, :academic_year, :start_date, :end_date, :is_active)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':academic_year', $academic_year);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $id = $this->conn->lastInsertId();

                // Fetch the created term
                $fetchQuery = "SELECT id, name, academic_year, start_date, end_date, is_active, created_at 
                              FROM terms WHERE id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $termData = $fetchStmt->fetch();

                $this->sendSuccessResponse($termData, 201);
            } else {
                $this->sendErrorResponse("Failed to create term", 500);
            }

        } catch (Exception $e) {
            error_log("Create Term Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating term", 500);
        }
    }

    /**
     * Get all terms
     * 
     * @return void Sends JSON response
     */
    public function getAllTerms() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Fetch all terms ordered by academic year and name
            $query = "SELECT id, name, academic_year, start_date, end_date, is_active, created_at 
                     FROM terms
                     ORDER BY academic_year DESC, name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $terms = $stmt->fetchAll();

            $this->sendSuccessResponse($terms, 200);

        } catch (Exception $e) {
            error_log("Get All Terms Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching terms", 500);
        }
    }

    /**
     * Get a specific term by ID
     * 
     * @param int $id Term ID
     * @return void Sends JSON response
     */
    public function getTerm($id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Fetch term
            $query = "SELECT id, name, academic_year, start_date, end_date, is_active, created_at 
                     FROM terms WHERE id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $term = $stmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            $this->sendSuccessResponse($term, 200);

        } catch (Exception $e) {
            error_log("Get Term Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching term", 500);
        }
    }

    /**
     * Update a term (admin only)
     * 
     * @param int $id Term ID
     * @param array $data Updated term data
     * @return void Sends JSON response
     */
    public function updateTerm($id, $data) {
        try {
            // Verify admin authentication
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Check if term exists
            $checkQuery = "SELECT id FROM terms WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Validate input
            if (!isset($data['name']) || !isset($data['academic_year'])) {
                $this->sendErrorResponse("Term name and academic year are required", 400);
                return;
            }

            $name = trim($data['name']);
            $academic_year = trim($data['academic_year']);
            $start_date = isset($data['start_date']) ? $data['start_date'] : null;
            $end_date = isset($data['end_date']) ? $data['end_date'] : null;
            $is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;

            // Create term object for validation
            $term = new Term(null, $name, $academic_year, $start_date, $end_date, $is_active);
            $validationErrors = $term->validate();

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Check for duplicate term (same name and academic year, but different ID)
            $duplicateQuery = "SELECT id FROM terms WHERE name = :name AND academic_year = :academic_year AND id != :id LIMIT 1";
            $duplicateStmt = $this->conn->prepare($duplicateQuery);
            $duplicateStmt->bindParam(':name', $name);
            $duplicateStmt->bindParam(':academic_year', $academic_year);
            $duplicateStmt->bindParam(':id', $id);
            $duplicateStmt->execute();

            if ($duplicateStmt->fetch()) {
                $this->sendErrorResponse("Term with this name and academic year already exists", 409);
                return;
            }

            // Update term
            $query = "UPDATE terms 
                     SET name = :name, academic_year = :academic_year, start_date = :start_date, 
                         end_date = :end_date, is_active = :is_active 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':academic_year', $academic_year);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                // Fetch the updated term
                $fetchQuery = "SELECT id, name, academic_year, start_date, end_date, is_active, created_at 
                              FROM terms WHERE id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $termData = $fetchStmt->fetch();

                $this->sendSuccessResponse($termData, 200);
            } else {
                $this->sendErrorResponse("Failed to update term", 500);
            }

        } catch (Exception $e) {
            error_log("Update Term Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while updating term", 500);
        }
    }

    /**
     * Delete a term (admin only)
     * 
     * @param int $id Term ID
     * @return void Sends JSON response
     */
    public function deleteTerm($id) {
        try {
            // Verify admin authentication
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Check if term exists
            $checkQuery = "SELECT id FROM terms WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Delete term (assessments will be cascade deleted due to foreign key)
            $query = "DELETE FROM terms WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Term deleted successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to delete term", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Term Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while deleting term", 500);
        }
    }

    /**
     * Get active terms
     * 
     * @return void Sends JSON response
     */
    public function getActiveTerms() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Fetch active terms ordered by academic year and name
            $query = "SELECT id, name, academic_year, start_date, end_date, is_active, created_at 
                     FROM terms
                     WHERE is_active = 1
                     ORDER BY academic_year DESC, name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $terms = $stmt->fetchAll();

            $this->sendSuccessResponse($terms, 200);

        } catch (Exception $e) {
            error_log("Get Active Terms Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching active terms", 500);
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
