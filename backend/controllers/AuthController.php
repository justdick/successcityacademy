<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';

class AuthController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Handle user login
     * 
     * @param array $data Login credentials (username, password)
     * @return void Sends JSON response
     */
    public function login($data) {
        try {
            // Validate input
            if (!isset($data['username']) || !isset($data['password'])) {
                $this->sendErrorResponse("Username and password are required", 400);
                return;
            }

            $username = trim($data['username']);
            $password = $data['password'];

            if (empty($username) || empty($password)) {
                $this->sendErrorResponse("Username and password cannot be empty", 400);
                return;
            }

            // Find user by username
            $query = "SELECT id, username, password_hash, role, created_at, updated_at 
                     FROM users 
                     WHERE username = :username 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $row = $stmt->fetch();

            if (!$row) {
                $this->sendErrorResponse("Invalid username or password", 401);
                return;
            }

            // Create User object
            $user = new User(
                $row['id'],
                $row['username'],
                $row['password_hash'],
                $row['role'],
                $row['created_at'],
                $row['updated_at']
            );

            // Verify password
            if (!$user->verifyPassword($password)) {
                $this->sendErrorResponse("Invalid username or password", 401);
                return;
            }

            // Generate JWT token
            $payload = [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role
            ];

            $token = JWT::encode($payload);

            // Send success response
            $this->sendSuccessResponse([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role
                ]
            ], 200);

        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred during login", 500);
        }
    }

    /**
     * Handle user logout
     * 
     * @return void Sends JSON response
     */
    public function logout() {
        try {
            // For JWT-based authentication, logout is handled client-side
            // by removing the token. Server-side, we just confirm the action.
            $this->sendSuccessResponse([
                'message' => 'Logged out successfully'
            ], 200);

        } catch (Exception $e) {
            error_log("Logout Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred during logout", 500);
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
