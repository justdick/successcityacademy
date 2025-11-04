<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Grade.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class GradeController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Add a grade for a student
     * 
     * @param array $data Grade data (student_id, subject_id, mark)
     * @return void Sends JSON response
     */
    public function addGrade($data) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['student_id']) || !isset($data['subject_id']) || !isset($data['mark'])) {
                $this->sendErrorResponse("Student ID, subject ID, and mark are required", 400);
                return;
            }

            $student_id = trim($data['student_id']);
            $subject_id = $data['subject_id'];
            $mark = $data['mark'];

            // Create grade object for validation
            $grade = new Grade(null, $student_id, $subject_id, null, $mark);
            $validationErrors = $grade->validate();

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Check if student exists
            $studentQuery = "SELECT student_id FROM students WHERE student_id = :student_id LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();

            if (!$studentStmt->fetch()) {
                $this->sendErrorResponse("Student with ID " . $student_id . " not found", 404);
                return;
            }

            // Check if subject exists
            $subjectQuery = "SELECT id FROM subjects WHERE id = :subject_id LIMIT 1";
            $subjectStmt = $this->conn->prepare($subjectQuery);
            $subjectStmt->bindParam(':subject_id', $subject_id);
            $subjectStmt->execute();

            if (!$subjectStmt->fetch()) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Insert new grade
            $query = "INSERT INTO grades (student_id, subject_id, mark) 
                     VALUES (:student_id, :subject_id, :mark)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':subject_id', $subject_id);
            $stmt->bindParam(':mark', $mark);

            if ($stmt->execute()) {
                $id = $this->conn->lastInsertId();

                // Fetch the created grade with subject name
                $fetchQuery = "SELECT g.id, g.student_id, g.subject_id, 
                                     s.name as subject_name, g.mark, g.created_at 
                              FROM grades g
                              JOIN subjects s ON g.subject_id = s.id
                              WHERE g.id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $gradeData = $fetchStmt->fetch();

                $this->sendSuccessResponse($gradeData, 201);
            } else {
                $this->sendErrorResponse("Failed to add grade", 500);
            }

        } catch (Exception $e) {
            error_log("Add Grade Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while adding grade", 500);
        }
    }

    /**
     * Get all grades for a specific student
     * 
     * @param string $student_id Student ID
     * @return void Sends JSON response
     */
    public function getStudentGrades($student_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate student_id
            if (empty($student_id)) {
                $this->sendErrorResponse("Student ID is required", 400);
                return;
            }

            // Check if student exists and get student info
            $studentQuery = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                                   cl.name as class_level_name 
                            FROM students s
                            JOIN class_levels cl ON s.class_level_id = cl.id
                            WHERE s.student_id = :student_id
                            LIMIT 1";
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();

            $student = $studentStmt->fetch();

            if (!$student) {
                $this->sendErrorResponse("Student with ID " . $student_id . " not found", 404);
                return;
            }

            // Fetch all grades for the student with subject names
            $gradesQuery = "SELECT g.id, g.student_id, g.subject_id, 
                                  s.name as subject_name, g.mark, g.created_at 
                           FROM grades g
                           JOIN subjects s ON g.subject_id = s.id
                           WHERE g.student_id = :student_id
                           ORDER BY g.created_at DESC";
            
            $gradesStmt = $this->conn->prepare($gradesQuery);
            $gradesStmt->bindParam(':student_id', $student_id);
            $gradesStmt->execute();

            $grades = $gradesStmt->fetchAll();

            // Return student info with grades
            $response = [
                'student' => $student,
                'grades' => $grades
            ];

            $this->sendSuccessResponse($response, 200);

        } catch (Exception $e) {
            error_log("Get Student Grades Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching grades", 500);
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
