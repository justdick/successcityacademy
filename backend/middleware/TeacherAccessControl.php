<?php

require_once __DIR__ . '/../config/database.php';

class TeacherAccessControl {
    private static $db = null;
    private static $conn = null;

    /**
     * Initialize database connection
     */
    private static function initDb() {
        if (self::$conn === null) {
            self::$db = new Database();
            self::$conn = self::$db->connect();
        }
        return self::$conn;
    }

    /**
     * Check if user is an admin
     * 
     * @param int $userId User ID
     * @return bool True if user is admin
     */
    private static function isAdmin($userId) {
        $conn = self::initDb();
        
        $query = "SELECT role FROM users WHERE id = :user_id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $user = $stmt->fetch();
        return $user && $user['role'] === 'admin';
    }

    /**
     * Get all class level IDs (for admin users)
     * 
     * @return array Array of class level IDs
     */
    private static function getAllClassIds() {
        $conn = self::initDb();
        
        $query = "SELECT id FROM class_levels";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $classes;
    }

    /**
     * Get all subject IDs (for admin users)
     * 
     * @return array Array of subject IDs
     */
    private static function getAllSubjectIds() {
        $conn = self::initDb();
        
        $query = "SELECT id FROM subjects";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $subjects;
    }

    /**
     * Get student's class level ID
     * 
     * @param int $studentId Student ID
     * @return int|null Class level ID or null if not found
     */
    private static function getStudentClassLevel($studentId) {
        $conn = self::initDb();
        
        $query = "SELECT class_level_id FROM students WHERE id = :student_id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        $student = $stmt->fetch();
        return $student ? $student['class_level_id'] : null;
    }

    /**
     * Check if user has access to a specific class
     * 
     * @param int $userId User ID
     * @param int $classLevelId Class level ID
     * @return bool True if user has access
     */
    public static function hasClassAccess($userId, $classLevelId) {
        // Admins have access to all classes
        if (self::isAdmin($userId)) {
            return true;
        }
        
        $conn = self::initDb();
        
        // Check if teacher has class assignment
        $query = "SELECT COUNT(*) as count 
                  FROM teacher_class_assignments 
                  WHERE user_id = :user_id AND class_level_id = :class_level_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':class_level_id', $classLevelId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    }

    /**
     * Check if user has access to a specific subject
     * 
     * @param int $userId User ID
     * @param int $subjectId Subject ID
     * @return bool True if user has access
     */
    public static function hasSubjectAccess($userId, $subjectId) {
        // Admins have access to all subjects
        if (self::isAdmin($userId)) {
            return true;
        }
        
        $conn = self::initDb();
        
        // Check if teacher has subject assignment
        $query = "SELECT COUNT(*) as count 
                  FROM teacher_subject_assignments 
                  WHERE user_id = :user_id AND subject_id = :subject_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':subject_id', $subjectId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    }

    /**
     * Get all class IDs accessible by user
     * 
     * @param int $userId User ID
     * @return array Array of class level IDs
     */
    public static function getAccessibleClasses($userId) {
        // Admins get all classes
        if (self::isAdmin($userId)) {
            return self::getAllClassIds();
        }
        
        $conn = self::initDb();
        
        // Teachers get assigned classes
        $query = "SELECT class_level_id 
                  FROM teacher_class_assignments 
                  WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $classes;
    }

    /**
     * Get all subject IDs accessible by user
     * 
     * @param int $userId User ID
     * @return array Array of subject IDs
     */
    public static function getAccessibleSubjects($userId) {
        // Admins get all subjects
        if (self::isAdmin($userId)) {
            return self::getAllSubjectIds();
        }
        
        $conn = self::initDb();
        
        // Teachers get assigned subjects
        $query = "SELECT subject_id 
                  FROM teacher_subject_assignments 
                  WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $subjects;
    }

    /**
     * Check if user can access a specific student
     * 
     * @param int $userId User ID
     * @param int $studentId Student ID
     * @return bool True if user has access
     */
    public static function hasStudentAccess($userId, $studentId) {
        // Admins have access to all students
        if (self::isAdmin($userId)) {
            return true;
        }
        
        // Get student's class level
        $studentClass = self::getStudentClassLevel($studentId);
        
        if ($studentClass === null) {
            return false;
        }
        
        // Check if teacher has access to that class
        return self::hasClassAccess($userId, $studentClass);
    }

    /**
     * Log unauthorized access attempt
     * 
     * @param int $userId User ID attempting access
     * @param string $resource Resource being accessed
     * @param string $resourceId ID of the resource
     */
    public static function logUnauthorizedAccess($userId, $resource, $resourceId) {
        $conn = self::initDb();
        
        try {
            $query = "INSERT INTO access_logs (user_id, resource_type, resource_id, access_denied, created_at) 
                     VALUES (:user_id, :resource_type, :resource_id, 1, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':resource_type', $resource);
            $stmt->bindParam(':resource_id', $resourceId);
            $stmt->execute();
        } catch (Exception $e) {
            // If access_logs table doesn't exist, log to error log
            error_log("Unauthorized access attempt - User: $userId, Resource: $resource, ID: $resourceId");
        }
    }

    /**
     * Send 403 Forbidden response
     * 
     * @param string $message Error message
     */
    public static function sendForbiddenResponse($message = "Access denied. You do not have permission to access this resource.") {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        // Don't exit in CLI test mode
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }
}
