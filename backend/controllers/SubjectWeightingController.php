<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SubjectWeighting.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SubjectWeightingController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create or update subject weighting (admin only)
     * 
     * @param array $data Subject weighting data (subject_id, ca_percentage, exam_percentage)
     * @return void Sends JSON response
     */
    public function createOrUpdateWeighting($data) {
        try {
            // Verify admin authentication
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['subject_id'])) {
                $this->sendErrorResponse("Subject ID is required", 400);
                return;
            }

            $subject_id = $data['subject_id'];
            $ca_percentage = isset($data['ca_percentage']) ? floatval($data['ca_percentage']) : 40.00;
            $exam_percentage = isset($data['exam_percentage']) ? floatval($data['exam_percentage']) : 60.00;

            // Create weighting object for validation
            $weighting = new SubjectWeighting(null, $subject_id, $ca_percentage, $exam_percentage);
            $validationErrors = $weighting->validate();

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Check if subject exists
            $subjectQuery = "SELECT id, name FROM subjects WHERE id = :subject_id LIMIT 1";
            $subjectStmt = $this->conn->prepare($subjectQuery);
            $subjectStmt->bindParam(':subject_id', $subject_id);
            $subjectStmt->execute();
            $subject = $subjectStmt->fetch();

            if (!$subject) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Check if weighting already exists
            $checkQuery = "SELECT id FROM subject_weightings WHERE subject_id = :subject_id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':subject_id', $subject_id);
            $checkStmt->execute();
            $existing = $checkStmt->fetch();

            if ($existing) {
                // Update existing weighting
                $query = "UPDATE subject_weightings 
                         SET ca_percentage = :ca_percentage, exam_percentage = :exam_percentage 
                         WHERE subject_id = :subject_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':ca_percentage', $ca_percentage);
                $stmt->bindParam(':exam_percentage', $exam_percentage);
                $stmt->bindParam(':subject_id', $subject_id);

                if ($stmt->execute()) {
                    // Fetch the updated weighting
                    $result = $this->getWeightingBySubjectId($subject_id);
                    $this->sendSuccessResponse($result, 200);
                } else {
                    $this->sendErrorResponse("Failed to update subject weighting", 500);
                }
            } else {
                // Insert new weighting
                $query = "INSERT INTO subject_weightings (subject_id, ca_percentage, exam_percentage) 
                         VALUES (:subject_id, :ca_percentage, :exam_percentage)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':subject_id', $subject_id);
                $stmt->bindParam(':ca_percentage', $ca_percentage);
                $stmt->bindParam(':exam_percentage', $exam_percentage);

                if ($stmt->execute()) {
                    // Fetch the created weighting
                    $result = $this->getWeightingBySubjectId($subject_id);
                    $this->sendSuccessResponse($result, 201);
                } else {
                    $this->sendErrorResponse("Failed to create subject weighting", 500);
                }
            }

        } catch (Exception $e) {
            error_log("Create/Update Subject Weighting Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while saving subject weighting", 500);
        }
    }

    /**
     * Get all subject weightings
     * 
     * @return void Sends JSON response
     */
    public function getAllWeightings() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Fetch all weightings with subject names
            // Include subjects without custom weightings (default 40/60)
            $query = "SELECT 
                        s.id as subject_id,
                        s.name as subject_name,
                        COALESCE(sw.id, NULL) as id,
                        COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                        COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                        sw.created_at,
                        sw.updated_at
                     FROM subjects s
                     LEFT JOIN subject_weightings sw ON s.id = sw.subject_id
                     ORDER BY s.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $weightings = $stmt->fetchAll();

            $this->sendSuccessResponse($weightings, 200);

        } catch (Exception $e) {
            error_log("Get All Subject Weightings Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching subject weightings", 500);
        }
    }

    /**
     * Get weighting for a specific subject
     * 
     * @param int $subject_id Subject ID
     * @return void Sends JSON response
     */
    public function getWeighting($subject_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($subject_id) || !is_numeric($subject_id)) {
                $this->sendErrorResponse("Valid subject ID is required", 400);
                return;
            }

            $result = $this->getWeightingBySubjectId($subject_id);

            if (!$result) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            $this->sendSuccessResponse($result, 200);

        } catch (Exception $e) {
            error_log("Get Subject Weighting Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching subject weighting", 500);
        }
    }

    /**
     * Update subject weighting (admin only)
     * 
     * @param int $subject_id Subject ID
     * @param array $data Updated weighting data
     * @return void Sends JSON response
     */
    public function updateWeighting($subject_id, $data) {
        try {
            // Verify admin authentication
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($subject_id) || !is_numeric($subject_id)) {
                $this->sendErrorResponse("Valid subject ID is required", 400);
                return;
            }

            // Add subject_id to data and call createOrUpdateWeighting
            $data['subject_id'] = $subject_id;
            $this->createOrUpdateWeighting($data);

        } catch (Exception $e) {
            error_log("Update Subject Weighting Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while updating subject weighting", 500);
        }
    }

    /**
     * Helper method to get weighting by subject ID
     * Returns default weighting if not found
     * 
     * @param int $subject_id Subject ID
     * @return array|null Weighting data or null if subject doesn't exist
     */
    private function getWeightingBySubjectId($subject_id) {
        // Fetch weighting with subject name, or default if not exists
        $query = "SELECT 
                    s.id as subject_id,
                    s.name as subject_name,
                    COALESCE(sw.id, NULL) as id,
                    COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                    COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                    sw.created_at,
                    sw.updated_at
                 FROM subjects s
                 LEFT JOIN subject_weightings sw ON s.id = sw.subject_id
                 WHERE s.id = :subject_id
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();

        return $stmt->fetch();
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
