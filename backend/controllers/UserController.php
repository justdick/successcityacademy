<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UserController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Create a new user (admin only)
     * 
     * @param array $data User data (username, password, role)
     * @return void Sends JSON response
     */
    public function createUser($data) {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Validate input
            if (!isset($data['username']) || !isset($data['password'])) {
                $this->sendErrorResponse("Username and password are required", 400);
                return;
            }

            $username = trim($data['username']);
            $password = $data['password'];
            $role = isset($data['role']) ? $data['role'] : 'user';

            // Validate username
            if (empty($username)) {
                $this->sendErrorResponse("Username cannot be empty", 400);
                return;
            }

            if (strlen($username) < 3 || strlen($username) > 50) {
                $this->sendErrorResponse("Username must be between 3 and 50 characters", 400);
                return;
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $this->sendErrorResponse("Username must be alphanumeric", 400);
                return;
            }

            // Validate password
            if (strlen($password) < 6) {
                $this->sendErrorResponse("Password must be at least 6 characters", 400);
                return;
            }

            // Validate role
            if (!in_array($role, ['admin', 'user'])) {
                $this->sendErrorResponse("Role must be 'admin' or 'user'", 400);
                return;
            }

            // Check for duplicate username
            $checkQuery = "SELECT id FROM users WHERE username = :username LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $this->sendErrorResponse("Username already exists", 409);
                return;
            }

            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $query = "INSERT INTO users (username, password_hash, role) 
                     VALUES (:username, :password_hash, :role)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();

                // Fetch the created user
                $fetchQuery = "SELECT id, username, role, created_at, updated_at 
                              FROM users 
                              WHERE id = :id";
                $fetchStmt = $this->conn->prepare($fetchQuery);
                $fetchStmt->bindParam(':id', $userId);
                $fetchStmt->execute();
                
                $userData = $fetchStmt->fetch();

                $this->sendSuccessResponse($userData, 201);
            } else {
                $this->sendErrorResponse("Failed to create user", 500);
            }

        } catch (Exception $e) {
            error_log("Create User Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while creating user", 500);
        }
    }

    /**
     * Get all users (admin only)
     * 
     * @return void Sends JSON response
     */
    public function getAllUsers() {
        try {
            // Verify admin access
            $currentUser = AuthMiddleware::requireAdmin();
            if (!$currentUser) {
                return;
            }

            // Fetch all users
            $query = "SELECT id, username, role, created_at, updated_at 
                     FROM users 
                     ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $users = $stmt->fetchAll();

            $this->sendSuccessResponse($users, 200);

        } catch (Exception $e) {
            error_log("Get All Users Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while fetching users", 500);
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
