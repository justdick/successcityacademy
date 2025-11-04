<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TermAssessment.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/TeacherAccessControl.php';

class AssessmentController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create or update assessment
     * 
     * @param array $data Assessment data (student_id, subject_id, term_id, ca_mark, exam_mark)
     * @return void Sends JSON response
     */
    public function createOrUpdateAssessment($data) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate required fields
            if (!isset($data['student_id']) || !isset($data['subject_id']) || !isset($data['term_id'])) {
                $this->sendErrorResponse("Student ID, subject ID, and term ID are required", 400);
                return;
            }

            $student_id = trim($data['student_id']);
            $subject_id = intval($data['subject_id']);
            $term_id = intval($data['term_id']);

            // Check subject access
            if (!TeacherAccessControl::hasSubjectAccess($currentUser->id, $subject_id)) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'subject', $subject_id);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get student's internal ID for access check
            $studentQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();
            $studentData = $studentStmt->fetch();

            if ($studentData) {
                // Check student access
                if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $studentData['id'])) {
                    TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $studentData['id']);
                    TeacherAccessControl::sendForbiddenResponse();
                    return;
                }
            }
            $ca_mark = isset($data['ca_mark']) && $data['ca_mark'] !== '' ? floatval($data['ca_mark']) : null;
            $exam_mark = isset($data['exam_mark']) && $data['exam_mark'] !== '' ? floatval($data['exam_mark']) : null;

            // Get subject weighting
            $weighting = $this->getSubjectWeighting($subject_id);
            if (!$weighting) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Create assessment object for validation
            $assessment = new TermAssessment(
                null, 
                $student_id, 
                $subject_id, 
                $term_id, 
                $ca_mark, 
                $exam_mark
            );

            $validationErrors = $assessment->validate($weighting['ca_percentage'], $weighting['exam_percentage']);

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Verify student exists
            if (!$this->studentExists($student_id)) {
                $this->sendErrorResponse("Student not found", 404);
                return;
            }

            // Verify term exists
            if (!$this->termExists($term_id)) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Check if assessment already exists
            $existingId = $this->getAssessmentId($student_id, $subject_id, $term_id);

            if ($existingId) {
                // Update existing assessment
                $query = "UPDATE term_assessments 
                         SET ca_mark = :ca_mark, exam_mark = :exam_mark 
                         WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':ca_mark', $ca_mark);
                $stmt->bindParam(':exam_mark', $exam_mark);
                $stmt->bindParam(':id', $existingId);

                if ($stmt->execute()) {
                    $result = $this->getAssessmentById($existingId);
                    $this->sendSuccessResponse($result, 200);
                } else {
                    $this->sendErrorResponse("Failed to update assessment", 500);
                }
            } else {
                // Insert new assessment
                $query = "INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark) 
                         VALUES (:student_id, :subject_id, :term_id, :ca_mark, :exam_mark)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':subject_id', $subject_id);
                $stmt->bindParam(':term_id', $term_id);
                $stmt->bindParam(':ca_mark', $ca_mark);
                $stmt->bindParam(':exam_mark', $exam_mark);

                if ($stmt->execute()) {
                    $id = $this->conn->lastInsertId();
                    $result = $this->getAssessmentById($id);
                    $this->sendSuccessResponse($result, 201);
                } else {
                    $this->sendErrorResponse("Failed to create assessment", 500);
                }
            }

        } catch (Exception $e) {
            error_log("Create/Update Assessment Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while saving assessment", 500);
        }
    }

    /**
     * Get all assessments for a student in a term
     * 
     * @param string $student_id Student ID
     * @param int $term_id Term ID
     * @return void Sends JSON response
     */
    public function getStudentTermAssessments($student_id, $term_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($student_id)) {
                $this->sendErrorResponse("Student ID is required", 400);
                return;
            }

            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Get student's internal ID for access check
            $studentQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();
            $studentData = $studentStmt->fetch();

            if (!$studentData) {
                $this->sendErrorResponse("Student not found", 404);
                return;
            }

            // Check student access
            if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $studentData['id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $studentData['id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Fetch assessments with joined data
            $query = "SELECT 
                        ta.id,
                        ta.student_id,
                        s.name as student_name,
                        ta.subject_id,
                        sub.name as subject_name,
                        ta.term_id,
                        t.name as term_name,
                        ta.ca_mark,
                        ta.exam_mark,
                        ta.final_mark,
                        ta.created_at,
                        ta.updated_at
                     FROM term_assessments ta
                     INNER JOIN students s ON ta.student_id = s.student_id
                     INNER JOIN subjects sub ON ta.subject_id = sub.id
                     INNER JOIN terms t ON ta.term_id = t.id
                     WHERE ta.student_id = :student_id AND ta.term_id = :term_id
                     ORDER BY sub.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':term_id', $term_id);
            $stmt->execute();

            $assessments = $stmt->fetchAll();

            $this->sendSuccessResponse($assessments, 200);

        } catch (Exception $e) {
            error_log("Get Student Term Assessments Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching assessments", 500);
        }
    }

    /**
     * Get all assessments for a class in a term
     * 
     * @param int $term_id Term ID
     * @param int $class_level_id Class level ID
     * @return void Sends JSON response
     */
    public function getClassTermAssessments($term_id, $class_level_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            if (empty($class_level_id) || !is_numeric($class_level_id)) {
                $this->sendErrorResponse("Valid class level ID is required", 400);
                return;
            }

            // Check class access
            if (!TeacherAccessControl::hasClassAccess($currentUser->id, $class_level_id)) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'class', $class_level_id);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Fetch assessments for all students in the class
            $query = "SELECT 
                        ta.id,
                        ta.student_id,
                        s.name as student_name,
                        ta.subject_id,
                        sub.name as subject_name,
                        ta.term_id,
                        t.name as term_name,
                        ta.ca_mark,
                        ta.exam_mark,
                        ta.final_mark,
                        ta.created_at,
                        ta.updated_at
                     FROM term_assessments ta
                     INNER JOIN students s ON ta.student_id = s.student_id
                     INNER JOIN subjects sub ON ta.subject_id = sub.id
                     INNER JOIN terms t ON ta.term_id = t.id
                     WHERE ta.term_id = :term_id AND s.class_level_id = :class_level_id
                     ORDER BY s.name ASC, sub.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':term_id', $term_id);
            $stmt->bindParam(':class_level_id', $class_level_id);
            $stmt->execute();

            $assessments = $stmt->fetchAll();

            $this->sendSuccessResponse($assessments, 200);

        } catch (Exception $e) {
            error_log("Get Class Term Assessments Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching assessments", 500);
        }
    }

    /**
     * Update an assessment
     * 
     * @param int $id Assessment ID
     * @param array $data Updated assessment data
     * @return void Sends JSON response
     */
    public function updateAssessment($id, $data) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                $this->sendErrorResponse("Valid assessment ID is required", 400);
                return;
            }

            // Get existing assessment
            $existing = $this->getAssessmentById($id);
            if (!$existing) {
                $this->sendErrorResponse("Assessment not found", 404);
                return;
            }

            // Check subject access
            if (!TeacherAccessControl::hasSubjectAccess($currentUser->id, $existing['subject_id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'subject', $existing['subject_id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get student's internal ID for access check
            $studentQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $existing['student_id']);
            $studentStmt->execute();
            $studentData = $studentStmt->fetch();

            if ($studentData) {
                // Check student access
                if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $studentData['id'])) {
                    TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $studentData['id']);
                    TeacherAccessControl::sendForbiddenResponse();
                    return;
                }
            }

            // Get subject weighting
            $weighting = $this->getSubjectWeighting($existing['subject_id']);
            if (!$weighting) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Extract marks from data
            $ca_mark = isset($data['ca_mark']) && $data['ca_mark'] !== '' ? floatval($data['ca_mark']) : null;
            $exam_mark = isset($data['exam_mark']) && $data['exam_mark'] !== '' ? floatval($data['exam_mark']) : null;

            // Create assessment object for validation
            $assessment = new TermAssessment(
                $id,
                $existing['student_id'],
                $existing['subject_id'],
                $existing['term_id'],
                $ca_mark,
                $exam_mark
            );

            $validationErrors = $assessment->validate($weighting['ca_percentage'], $weighting['exam_percentage']);

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Update assessment
            $query = "UPDATE term_assessments 
                     SET ca_mark = :ca_mark, exam_mark = :exam_mark 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ca_mark', $ca_mark);
            $stmt->bindParam(':exam_mark', $exam_mark);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $result = $this->getAssessmentById($id);
                $this->sendSuccessResponse($result, 200);
            } else {
                $this->sendErrorResponse("Failed to update assessment", 500);
            }

        } catch (Exception $e) {
            error_log("Update Assessment Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while updating assessment", 500);
        }
    }

    /**
     * Delete an assessment
     * 
     * @param int $id Assessment ID
     * @return void Sends JSON response
     */
    public function deleteAssessment($id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                $this->sendErrorResponse("Valid assessment ID is required", 400);
                return;
            }

            // Check if assessment exists
            $existing = $this->getAssessmentById($id);
            if (!$existing) {
                $this->sendErrorResponse("Assessment not found", 404);
                return;
            }

            // Check subject access
            if (!TeacherAccessControl::hasSubjectAccess($currentUser->id, $existing['subject_id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'subject', $existing['subject_id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get student's internal ID for access check
            $studentQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $existing['student_id']);
            $studentStmt->execute();
            $studentData = $studentStmt->fetch();

            if ($studentData) {
                // Check student access
                if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $studentData['id'])) {
                    TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $studentData['id']);
                    TeacherAccessControl::sendForbiddenResponse();
                    return;
                }
            }

            // Delete assessment
            $query = "DELETE FROM term_assessments WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Assessment deleted successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to delete assessment", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Assessment Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while deleting assessment", 500);
        }
    }

    /**
     * Validate assessment mark against subject weighting
     * 
     * @param float $mark Mark value
     * @param float $max_mark Maximum allowed mark
     * @param string $type Mark type (CA or Exam)
     * @return bool True if valid, false otherwise
     */
    public function validateAssessmentMark($mark, $max_mark, $type) {
        if ($mark === null) {
            return true; // Null marks are allowed
        }

        if (!is_numeric($mark)) {
            return false;
        }

        if ($mark < 0) {
            return false;
        }

        if ($mark > $max_mark) {
            return false;
        }

        return true;
    }

    /**
     * Helper method to get subject weighting
     * Returns default weighting if not found
     * 
     * @param int $subject_id Subject ID
     * @return array|null Weighting data or null if subject doesn't exist
     */
    private function getSubjectWeighting($subject_id) {
        $query = "SELECT 
                    s.id as subject_id,
                    s.name as subject_name,
                    COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                    COALESCE(sw.exam_percentage, 60.00) as exam_percentage
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
     * Helper method to check if student exists
     * 
     * @param string $student_id Student ID
     * @return bool True if exists, false otherwise
     */
    private function studentExists($student_id) {
        $query = "SELECT student_id FROM students WHERE student_id = :student_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    /**
     * Helper method to check if term exists
     * 
     * @param int $term_id Term ID
     * @return bool True if exists, false otherwise
     */
    private function termExists($term_id) {
        $query = "SELECT id FROM terms WHERE id = :term_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':term_id', $term_id);
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    /**
     * Helper method to get assessment ID by student, subject, and term
     * 
     * @param string $student_id Student ID
     * @param int $subject_id Subject ID
     * @param int $term_id Term ID
     * @return int|null Assessment ID or null if not found
     */
    private function getAssessmentId($student_id, $subject_id, $term_id) {
        $query = "SELECT id FROM term_assessments 
                 WHERE student_id = :student_id AND subject_id = :subject_id AND term_id = :term_id 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->bindParam(':term_id', $term_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    /**
     * Helper method to get assessment by ID with joined data
     * 
     * @param int $id Assessment ID
     * @return array|null Assessment data or null if not found
     */
    private function getAssessmentById($id) {
        $query = "SELECT 
                    ta.id,
                    ta.student_id,
                    s.name as student_name,
                    ta.subject_id,
                    sub.name as subject_name,
                    ta.term_id,
                    t.name as term_name,
                    ta.ca_mark,
                    ta.exam_mark,
                    ta.final_mark,
                    ta.created_at,
                    ta.updated_at
                 FROM term_assessments ta
                 INNER JOIN students s ON ta.student_id = s.student_id
                 INNER JOIN subjects sub ON ta.subject_id = sub.id
                 INNER JOIN terms t ON ta.term_id = t.id
                 WHERE ta.id = :id
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
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
