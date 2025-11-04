<?php

require_once __DIR__ . '/../utils/JWT.php';

class AuthMiddleware {
    /**
     * Authenticate the request by verifying JWT token
     * 
     * @return object|null The decoded user data or null on failure
     */
    public static function authenticate() {
        // Get authorization header
        // Support CLI testing mode
        if (php_sapi_name() === 'cli') {
            if (isset($GLOBALS['TEST_AUTH_TOKEN'])) {
                $authHeader = 'Bearer ' . $GLOBALS['TEST_AUTH_TOKEN'];
            } else {
                $authHeader = null;
            }
        } else {
            $headers = getallheaders();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 
                         (isset($headers['authorization']) ? $headers['authorization'] : null);
        }

        if (!$authHeader) {
            self::sendUnauthorizedResponse("Missing authorization token");
            return null;
        }

        // Extract token from "Bearer <token>" format
        $parts = explode(' ', $authHeader);
        
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            self::sendUnauthorizedResponse("Invalid authorization header format");
            return null;
        }

        $token = $parts[1];

        // Decode and verify token
        $decoded = JWT::decode($token);

        if (!$decoded) {
            self::sendUnauthorizedResponse("Invalid or expired token");
            return null;
        }

        // Return user data from token
        return $decoded;
    }

    /**
     * Require admin role for the request
     * 
     * @return object|null The decoded user data or null on failure
     */
    public static function requireAdmin() {
        // First authenticate the user
        $user = self::authenticate();

        if (!$user) {
            return null;
        }

        // Check if user has admin role
        if (!isset($user->role) || $user->role !== 'admin') {
            self::sendForbiddenResponse("Access denied - admin privileges required");
            return null;
        }

        return $user;
    }

    /**
     * Send 401 Unauthorized response
     * 
     * @param string $message The error message
     */
    private static function sendUnauthorizedResponse($message) {
        http_response_code(401);
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

    /**
     * Send 403 Forbidden response
     * 
     * @param string $message The error message
     */
    private static function sendForbiddenResponse($message) {
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

