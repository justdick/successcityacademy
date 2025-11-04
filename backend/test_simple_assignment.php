<?php

/**
 * Simple test to check teacher assignment endpoint
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate API request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/backend/api/teacher-assignments/class';

// Mock input
$_POST = [];
file_put_contents('php://input', json_encode([
    'user_id' => 2,
    'class_level_id' => 1
]));

// Mock authentication (you'll need to set a valid token)
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'; // Replace with actual token

try {
    require_once __DIR__ . '/controllers/TeacherAssignmentController.php';
    
    $controller = new TeacherAssignmentController();
    
    $data = [
        'user_id' => 2,
        'class_level_id' => 1
    ];
    
    echo "Testing assignClass method...\n";
    $controller->assignClass($data);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
