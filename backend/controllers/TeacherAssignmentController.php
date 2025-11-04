<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class TeacherAssignmentController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Assign a teacher to a class
     * 
     * @param array $data Assignment data (user_id, class_level_id)
     * @return void Sends JSON response
     */
    public function assignClass($data) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Validate input
            if (!isset($data['user_id']) || !isset($data['class_level_id'])) {
                $this->sendErrorResponse("User ID and class level ID are required", 400);
                return;
            }

            $user_id = $data['user_id'];
            $class_level_id = $data['class_level_id'];

            // Validate IDs are numeric
            if (!is_numeric($user_id) || $user_id <= 0) {
                $this->sendErrorResponse("User ID must be a positive number", 400);
                return;
            }

            if (!is_numeric($class_level_id) || $class_level_id <= 0) {
                $this->sendErrorResponse("Class level ID must be a positive number", 400);
                return;
            }

            // Verify user exists and has role 'user' (teacher)
            $userQuery = "SELECT id, role FROM users WHERE id = :user_id LIMIT 1";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':user_id', $user_id);
            $userStmt->execute();
            $user = $userStmt->fetch();

            if (!$user) {
                $this->sendErrorResponse("User not found", 404);
                return;
            }

            if ($user['role'] !== 'user') {
                $this->sendErrorResponse("User must have role 'user' (teacher) to be assigned to classes", 400);
                return;
            }

            // Verify class level exists
            $classQuery = "SELECT id FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classStmt = $this->conn->prepare($classQuery);
            $classStmt->bindParam(':class_level_id', $class_level_id);
            $classStmt->execute();

            if (!$classStmt->fetch()) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Check for duplicate assignment
            $checkQuery = "SELECT id FROM teacher_class_assignments 
                          WHERE user_id = :user_id AND class_level_id = :class_level_id 
                          LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $user_id);
            $checkStmt->bindParam(':class_level_id', $class_level_id);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Teacher is already assigned to this class", 409);
                return;
            }

            // Create assignment
            $insertQuery = "INSERT INTO teacher_class_assignments (user_id, class_level_id) 
                           VALUES (:user_id, :class_level_id)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(':user_id', $user_id);
            $insertStmt->bindParam(':class_level_id', $class_level_id);

            if ($insertStmt->execute()) {
                $id = $this->conn->lastInsertId();

                // Fetch the created assignment with details
                $fetchQuery = "SELECT tca.id, tca.user_id, tca.class_level_id, tca.created_at,
                                     u.username, cl.name as class_level_name
                              FROM teacher_class_assignments tca
                              JOIN users u ON tca.user_id = u.id
                              JOIN class_levels cl ON tca.class_level_id = cl.id
                              WHERE tca.id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $assignment = $fetchStmt->fetch();

                $this->sendSuccessResponse($assignment, 201, "Teacher assigned to class successfully");
            } else {
                $this->sendErrorResponse("Failed to create class assignment", 500);
            }

        } catch (Exception $e) {
            error_log("Assign Class Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while assigning teacher to class", 500);
        }
    }

    /**
     * Assign a teacher to a subject
     * 
     * @param array $data Assignment data (user_id, subject_id)
     * @return void Sends JSON response
     */
    public function assignSubject($data) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Validate input
            if (!isset($data['user_id']) || !isset($data['subject_id'])) {
                $this->sendErrorResponse("User ID and subject ID are required", 400);
                return;
            }

            $user_id = $data['user_id'];
            $subject_id = $data['subject_id'];

            // Validate IDs are numeric
            if (!is_numeric($user_id) || $user_id <= 0) {
                $this->sendErrorResponse("User ID must be a positive number", 400);
                return;
            }

            if (!is_numeric($subject_id) || $subject_id <= 0) {
                $this->sendErrorResponse("Subject ID must be a positive number", 400);
                return;
            }

            // Verify user exists and has role 'user' (teacher)
            $userQuery = "SELECT id, role FROM users WHERE id = :user_id LIMIT 1";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':user_id', $user_id);
            $userStmt->execute();
            $user = $userStmt->fetch();

            if (!$user) {
                $this->sendErrorResponse("User not found", 404);
                return;
            }

            if ($user['role'] !== 'user') {
                $this->sendErrorResponse("User must have role 'user' (teacher) to be assigned to subjects", 400);
                return;
            }

            // Verify subject exists
            $subjectQuery = "SELECT id FROM subjects WHERE id = :subject_id LIMIT 1";
            $subjectStmt = $this->conn->prepare($subjectQuery);
            $subjectStmt->bindParam(':subject_id', $subject_id);
            $subjectStmt->execute();

            if (!$subjectStmt->fetch()) {
                $this->sendErrorResponse("Subject not found", 404);
                return;
            }

            // Check for duplicate assignment
            $checkQuery = "SELECT id FROM teacher_subject_assignments 
                          WHERE user_id = :user_id AND subject_id = :subject_id 
                          LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $user_id);
            $checkStmt->bindParam(':subject_id', $subject_id);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Teacher is already assigned to this subject", 409);
                return;
            }

            // Create assignment
            $insertQuery = "INSERT INTO teacher_subject_assignments (user_id, subject_id) 
                           VALUES (:user_id, :subject_id)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(':user_id', $user_id);
            $insertStmt->bindParam(':subject_id', $subject_id);

            if ($insertStmt->execute()) {
                $id = $this->conn->lastInsertId();

                // Fetch the created assignment with details
                $fetchQuery = "SELECT tsa.id, tsa.user_id, tsa.subject_id, tsa.created_at,
                                     u.username, s.name as subject_name
                              FROM teacher_subject_assignments tsa
                              JOIN users u ON tsa.user_id = u.id
                              JOIN subjects s ON tsa.subject_id = s.id
                              WHERE tsa.id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $id);
                $fetchStmt->execute();
                
                $assignment = $fetchStmt->fetch();

                $this->sendSuccessResponse($assignment, 201, "Teacher assigned to subject successfully");
            } else {
                $this->sendErrorResponse("Failed to create subject assignment", 500);
            }

        } catch (Exception $e) {
            error_log("Assign Subject Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while assigning teacher to subject", 500);
        }
    }

    /**
     * Bulk assign classes and subjects to a teacher
     * 
     * @param array $data Assignment data (user_id, class_level_ids[], subject_ids[])
     * @return void Sends JSON response
     */
    public function bulkAssign($data) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Validate input
            if (!isset($data['user_id'])) {
                $this->sendErrorResponse("User ID is required", 400);
                return;
            }

            $user_id = $data['user_id'];
            $class_level_ids = isset($data['class_level_ids']) ? $data['class_level_ids'] : [];
            $subject_ids = isset($data['subject_ids']) ? $data['subject_ids'] : [];

            // Validate user_id
            if (!is_numeric($user_id) || $user_id <= 0) {
                $this->sendErrorResponse("User ID must be a positive number", 400);
                return;
            }

            // Validate arrays
            if (!is_array($class_level_ids) && !is_array($subject_ids)) {
                $this->sendErrorResponse("At least one of class_level_ids or subject_ids must be provided as an array", 400);
                return;
            }

            // Verify user exists and has role 'user' (teacher)
            $userQuery = "SELECT id, role FROM users WHERE id = :user_id LIMIT 1";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':user_id', $user_id);
            $userStmt->execute();
            $user = $userStmt->fetch();

            if (!$user) {
                $this->sendErrorResponse("User not found", 404);
                return;
            }

            if ($user['role'] !== 'user') {
                $this->sendErrorResponse("User must have role 'user' (teacher) to receive assignments", 400);
                return;
            }

            // Validate all class level IDs exist
            $validationErrors = [];
            foreach ($class_level_ids as $class_level_id) {
                if (!is_numeric($class_level_id) || $class_level_id <= 0) {
                    $validationErrors[] = "Invalid class level ID: $class_level_id";
                    continue;
                }

                $classQuery = "SELECT id FROM class_levels WHERE id = :class_level_id LIMIT 1";
                $classStmt = $this->conn->prepare($classQuery);
                $classStmt->bindParam(':class_level_id', $class_level_id);
                $classStmt->execute();

                if (!$classStmt->fetch()) {
                    $validationErrors[] = "Class level with ID $class_level_id not found";
                }
            }

            // Validate all subject IDs exist
            foreach ($subject_ids as $subject_id) {
                if (!is_numeric($subject_id) || $subject_id <= 0) {
                    $validationErrors[] = "Invalid subject ID: $subject_id";
                    continue;
                }

                $subjectQuery = "SELECT id FROM subjects WHERE id = :subject_id LIMIT 1";
                $subjectStmt = $this->conn->prepare($subjectQuery);
                $subjectStmt->bindParam(':subject_id', $subject_id);
                $subjectStmt->execute();

                if (!$subjectStmt->fetch()) {
                    $validationErrors[] = "Subject with ID $subject_id not found";
                }
            }

            // If any validation errors, reject the entire operation
            if (!empty($validationErrors)) {
                $this->sendErrorResponse(implode('; ', $validationErrors), 400);
                return;
            }

            // Begin transaction
            $this->conn->beginTransaction();

            $classes_assigned = 0;
            $subjects_assigned = 0;

            try {
                // Insert class assignments (skip duplicates)
                foreach ($class_level_ids as $class_level_id) {
                    // Check if assignment already exists
                    $checkQuery = "SELECT id FROM teacher_class_assignments 
                                  WHERE user_id = :user_id AND class_level_id = :class_level_id 
                                  LIMIT 1";
                    $checkStmt = $this->conn->prepare($checkQuery);
                    $checkStmt->bindParam(':user_id', $user_id);
                    $checkStmt->bindParam(':class_level_id', $class_level_id);
                    $checkStmt->execute();

                    if (!$checkStmt->fetch()) {
                        // Create assignment
                        $insertQuery = "INSERT INTO teacher_class_assignments (user_id, class_level_id) 
                                       VALUES (:user_id, :class_level_id)";
                        $insertStmt = $this->conn->prepare($insertQuery);
                        $insertStmt->bindParam(':user_id', $user_id);
                        $insertStmt->bindParam(':class_level_id', $class_level_id);
                        $insertStmt->execute();
                        $classes_assigned++;
                    }
                }

                // Insert subject assignments (skip duplicates)
                foreach ($subject_ids as $subject_id) {
                    // Check if assignment already exists
                    $checkQuery = "SELECT id FROM teacher_subject_assignments 
                                  WHERE user_id = :user_id AND subject_id = :subject_id 
                                  LIMIT 1";
                    $checkStmt = $this->conn->prepare($checkQuery);
                    $checkStmt->bindParam(':user_id', $user_id);
                    $checkStmt->bindParam(':subject_id', $subject_id);
                    $checkStmt->execute();

                    if (!$checkStmt->fetch()) {
                        // Create assignment
                        $insertQuery = "INSERT INTO teacher_subject_assignments (user_id, subject_id) 
                                       VALUES (:user_id, :subject_id)";
                        $insertStmt = $this->conn->prepare($insertQuery);
                        $insertStmt->bindParam(':user_id', $user_id);
                        $insertStmt->bindParam(':subject_id', $subject_id);
                        $insertStmt->execute();
                        $subjects_assigned++;
                    }
                }

                // Commit transaction
                $this->conn->commit();

                $this->sendSuccessResponse([
                    'classes_assigned' => $classes_assigned,
                    'subjects_assigned' => $subjects_assigned
                ], 201, "Bulk assignments created successfully");

            } catch (Exception $e) {
                // Rollback on error
                $this->conn->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Bulk Assign Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating bulk assignments", 500);
        }
    }

    /**
     * Get all teacher assignments with optional filtering
     * 
     * @param array $filters Optional filters (user_id, class_level_id, subject_id)
     * @return void Sends JSON response
     */
    public function getAllAssignments($filters = []) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Build query to get all teachers with role 'user'
            $whereConditions = ["u.role = 'user'"];
            $params = [];

            // Apply filters if provided
            if (isset($filters['user_id']) && !empty($filters['user_id'])) {
                $whereConditions[] = "u.id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Get all teachers
            $teacherQuery = "SELECT u.id, u.username 
                            FROM users u 
                            WHERE $whereClause 
                            ORDER BY u.username";
            $teacherStmt = $this->conn->prepare($teacherQuery);
            foreach ($params as $key => $value) {
                $teacherStmt->bindValue($key, $value);
            }
            $teacherStmt->execute();
            $teachers = $teacherStmt->fetchAll();

            $result = [];

            foreach ($teachers as $teacher) {
                $teacherId = $teacher['id'];

                // Get class assignments for this teacher
                $classQuery = "SELECT cl.id, cl.name 
                              FROM teacher_class_assignments tca
                              JOIN class_levels cl ON tca.class_level_id = cl.id
                              WHERE tca.user_id = :user_id";
                
                // Apply class filter if provided
                if (isset($filters['class_level_id']) && !empty($filters['class_level_id'])) {
                    $classQuery .= " AND cl.id = :class_level_id";
                }
                
                $classQuery .= " ORDER BY cl.name";
                
                $classStmt = $this->conn->prepare($classQuery);
                $classStmt->bindParam(':user_id', $teacherId);
                if (isset($filters['class_level_id']) && !empty($filters['class_level_id'])) {
                    $classStmt->bindValue(':class_level_id', $filters['class_level_id']);
                }
                $classStmt->execute();
                $classes = $classStmt->fetchAll();

                // Get subject assignments for this teacher
                $subjectQuery = "SELECT s.id, s.name 
                                FROM teacher_subject_assignments tsa
                                JOIN subjects s ON tsa.subject_id = s.id
                                WHERE tsa.user_id = :user_id";
                
                // Apply subject filter if provided
                if (isset($filters['subject_id']) && !empty($filters['subject_id'])) {
                    $subjectQuery .= " AND s.id = :subject_id";
                }
                
                $subjectQuery .= " ORDER BY s.name";
                
                $subjectStmt = $this->conn->prepare($subjectQuery);
                $subjectStmt->bindParam(':user_id', $teacherId);
                if (isset($filters['subject_id']) && !empty($filters['subject_id'])) {
                    $subjectStmt->bindValue(':subject_id', $filters['subject_id']);
                }
                $subjectStmt->execute();
                $subjects = $subjectStmt->fetchAll();

                // Only include teacher if they have assignments (when filters are applied)
                if (!empty($filters) && (isset($filters['class_level_id']) || isset($filters['subject_id']))) {
                    if (empty($classes) && empty($subjects)) {
                        continue;
                    }
                }

                $result[] = [
                    'user_id' => $teacher['id'],
                    'username' => $teacher['username'],
                    'classes' => $classes,
                    'subjects' => $subjects
                ];
            }

            $this->sendSuccessResponse($result, 200);

        } catch (Exception $e) {
            error_log("Get All Assignments Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching assignments", 500);
        }
    }

    /**
     * Get current teacher's assignments
     * 
     * @return void Sends JSON response
     */
    public function getMyAssignments() {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            $userId = $currentUser->id;

            // Get class assignments with student counts
            $classQuery = "SELECT cl.id, cl.name, 
                                 COUNT(s.id) as student_count
                          FROM teacher_class_assignments tca
                          JOIN class_levels cl ON tca.class_level_id = cl.id
                          LEFT JOIN students s ON s.class_level_id = cl.id
                          WHERE tca.user_id = :user_id
                          GROUP BY cl.id, cl.name
                          ORDER BY cl.name";
            
            $classStmt = $this->conn->prepare($classQuery);
            $classStmt->bindParam(':user_id', $userId);
            $classStmt->execute();
            $classes = $classStmt->fetchAll();

            // Get subject assignments
            $subjectQuery = "SELECT s.id, s.name 
                            FROM teacher_subject_assignments tsa
                            JOIN subjects s ON tsa.subject_id = s.id
                            WHERE tsa.user_id = :user_id
                            ORDER BY s.name";
            
            $subjectStmt = $this->conn->prepare($subjectQuery);
            $subjectStmt->bindParam(':user_id', $userId);
            $subjectStmt->execute();
            $subjects = $subjectStmt->fetchAll();

            $this->sendSuccessResponse([
                'classes' => $classes,
                'subjects' => $subjects
            ], 200);

        } catch (Exception $e) {
            error_log("Get My Assignments Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching your assignments", 500);
        }
    }

    /**
     * Delete a class assignment
     * 
     * @param int $id Assignment ID
     * @return void Sends JSON response
     */
    public function deleteClassAssignment($id) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendErrorResponse("Invalid assignment ID", 400);
                return;
            }

            // Check if assignment exists
            $checkQuery = "SELECT id FROM teacher_class_assignments WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Class assignment not found", 404);
                return;
            }

            // Delete assignment
            $deleteQuery = "DELETE FROM teacher_class_assignments WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id);

            if ($deleteStmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Class assignment removed successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to remove class assignment", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Class Assignment Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while removing class assignment", 500);
        }
    }

    /**
     * Delete a subject assignment
     * 
     * @param int $id Assignment ID
     * @return void Sends JSON response
     */
    public function deleteSubjectAssignment($id) {
        try {
            // Verify authentication and admin role
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            if ($currentUser->role !== 'admin') {
                $this->sendErrorResponse("Access denied. Admin privileges required.", 403);
                return;
            }

            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendErrorResponse("Invalid assignment ID", 400);
                return;
            }

            // Check if assignment exists
            $checkQuery = "SELECT id FROM teacher_subject_assignments WHERE id = :id LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $this->sendErrorResponse("Subject assignment not found", 404);
                return;
            }

            // Delete assignment
            $deleteQuery = "DELETE FROM teacher_subject_assignments WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id);

            if ($deleteStmt->execute()) {
                $this->sendSuccessResponse(['message' => 'Subject assignment removed successfully'], 200);
            } else {
                $this->sendErrorResponse("Failed to remove subject assignment", 500);
            }

        } catch (Exception $e) {
            error_log("Delete Subject Assignment Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while removing subject assignment", 500);
        }
    }

    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @param string $message Optional success message
     */
    private function sendSuccessResponse($data, $statusCode = 200, $message = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        $response = [
            'success' => true,
            'data' => $data
        ];
        if ($message) {
            $response['message'] = $message;
        }
        echo json_encode($response);
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
