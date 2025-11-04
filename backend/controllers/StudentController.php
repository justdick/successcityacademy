<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/TeacherAccessControl.php';

class StudentController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create a new student
     * 
     * @param array $data Student data (student_id, name, class_level_id)
     * @return void Sends JSON response
     */
    public function createStudent($data) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['student_id']) || !isset($data['name']) || !isset($data['class_level_id'])) {
                $this->sendErrorResponse("Student ID, name, and class level are required", 400);
                return;
            }

            $student_id = trim($data['student_id']);
            $name = trim($data['name']);
            $class_level_id = $data['class_level_id'];

            // Create student object for validation
            $student = new Student(null, $student_id, $name, $class_level_id);
            $validationErrors = $student->validate();

            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode(', ', $validationErrors), 400);
                return;
            }

            // Check if class level exists
            $classLevelQuery = "SELECT id FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classLevelStmt = $this->conn->prepare($classLevelQuery);
            $classLevelStmt->bindParam(':class_level_id', $class_level_id);
            $classLevelStmt->execute();

            if (!$classLevelStmt->fetch()) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Check for duplicate student ID
            $checkQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $student_id);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Student with ID " . $student_id . " already exists", 409);
                return;
            }

            // Insert new student
            $query = "INSERT INTO students (student_id, name, class_level_id) 
                     VALUES (:student_id, :name, :class_level_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':class_level_id', $class_level_id);

            if ($stmt->execute()) {
                $id = $this->conn->lastInsertId();

                // Fetch the created student with class level name
                $fetchQuery = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                                     cl.name as class_level_name, s.created_at, s.updated_at 
                              FROM students s
                              JOIN class_levels cl ON s.class_level_id = cl.id
                              WHERE s.id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $studentData = $fetchStmt->fetch();

                $this->sendSuccessResponse($studentData, 201);
            } else {
                $this->sendErrorResponse("Failed to create student", 500);
            }

        } catch (Exception $e) {
            error_log("Create Student Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating student", 500);
        }
    }

    /**
     * Get all students
     * 
     * @return void Sends JSON response
     */
    public function getAllStudents() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Get accessible classes for the current user
            $accessibleClasses = TeacherAccessControl::getAccessibleClasses($currentUser->id);

            // If no accessible classes, return empty array
            if (empty($accessibleClasses)) {
                $this->sendSuccessResponse([], 200);
                return;
            }

            // Build query with class filter
            $placeholders = implode(',', array_fill(0, count($accessibleClasses), '?'));
            $query = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                            cl.name as class_level_name, s.created_at, s.updated_at 
                     FROM students s
                     JOIN class_levels cl ON s.class_level_id = cl.id
                     WHERE s.class_level_id IN ($placeholders)
                     ORDER BY s.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($accessibleClasses);

            $students = $stmt->fetchAll();

            $this->sendSuccessResponse($students, 200);

        } catch (Exception $e) {
            error_log("Get All Students Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching students", 500);
        }
    }

    /**
     * Get a specific student by student_id
     * 
     * @param string $student_id Student ID
     * @return void Sends JSON response
     */
    public function getStudent($student_id) {
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

            // Fetch student with class level name
            $query = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                            cl.name as class_level_name, s.created_at, s.updated_at 
                     FROM students s
                     JOIN class_levels cl ON s.class_level_id = cl.id
                     WHERE s.student_id = :student_id
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();

            $student = $stmt->fetch();

            if (!$student) {
                $this->sendErrorResponse("Student with ID " . $student_id . " not found", 404);
                return;
            }

            // Check if user has access to this student
            if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $student['id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $student['id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            $this->sendSuccessResponse($student, 200);

        } catch (Exception $e) {
            error_log("Get Student Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching student", 500);
        }
    }

    /**
     * Update a student
     * 
     * @param string $student_id Student ID
     * @param array $data Updated student data (name, class_level_id)
     * @return void Sends JSON response
     */
    public function updateStudent($student_id, $data) {
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

            // Check if student exists
            $checkQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $student_id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Student with ID " . $student_id . " not found", 404);
                return;
            }

            // Validate input
            if (!isset($data['name']) || !isset($data['class_level_id'])) {
                $this->sendErrorResponse("Name and class level are required", 400);
                return;
            }

            $name = trim($data['name']);
            $class_level_id = $data['class_level_id'];

            // Validate data
            if (empty($name)) {
                $this->sendErrorResponse("Name cannot be empty", 400);
                return;
            }

            if (strlen($name) > 255) {
                $this->sendErrorResponse("Name must not exceed 255 characters", 400);
                return;
            }

            if (!is_numeric($class_level_id) || $class_level_id <= 0) {
                $this->sendErrorResponse("Class level ID must be a positive number", 400);
                return;
            }

            // Check if class level exists
            $classLevelQuery = "SELECT id FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classLevelStmt = $this->conn->prepare($classLevelQuery);
            $classLevelStmt->bindParam(':class_level_id', $class_level_id);
            $classLevelStmt->execute();

            if (!$classLevelStmt->fetch()) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Update student
            $query = "UPDATE students 
                     SET name = :name, class_level_id = :class_level_id 
                     WHERE student_id = :student_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':class_level_id', $class_level_id);
            $stmt->bindParam(':student_id', $student_id);

            if ($stmt->execute()) {
                // Fetch the updated student with class level name
                $fetchQuery = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                                     cl.name as class_level_name, s.created_at, s.updated_at 
                              FROM students s
                              JOIN class_levels cl ON s.class_level_id = cl.id
                              WHERE s.student_id = :student_id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':student_id', $student_id);
                $fetchStmt->execute();
                
                $studentData = $fetchStmt->fetch();

                $this->sendSuccessResponse($studentData, 200);
            } else {
                $this->sendErrorResponse("Failed to update student", 500);
            }

        } catch (Exception $e) {
            error_log("Update Student Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while updating student", 500);
        }
    }

    /**
     * Delete a student
     * 
     * @param string $student_id Student ID
     * @return void Sends JSON response
     */
    public function deleteStudent($student_id) {
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

            // Check if student exists
            $checkQuery = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $student_id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Student with ID " . $student_id . " not found", 404);
                return;
            }

            // Delete student (grades will be cascade deleted due to foreign key)
            $query = "DELETE FROM students WHERE student_id = :student_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id);

            if ($stmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Student deleted successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to delete student", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Student Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while deleting student", 500);
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
