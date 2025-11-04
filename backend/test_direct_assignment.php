<?php

/**
 * Direct test of TeacherAssignmentController
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Direct Controller Test ===\n\n";

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/utils/JWT.php';
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    require_once __DIR__ . '/controllers/TeacherAssignmentController.php';
    
    // Create admin token for testing
    $adminPayload = [
        'id' => 1,
        'username' => 'admin',
        'role' => 'admin'
    ];
    $adminToken = JWT::encode($adminPayload);
    $GLOBALS['TEST_AUTH_TOKEN'] = $adminToken;
    
    $controller = new TeacherAssignmentController();
    
    echo "Test 1: Assign teacher to class\n";
    ob_start();
    $controller->assignClass([
        'user_id' => 2,
        'class_level_id' => 1
    ]);
    $output = ob_get_clean();
    echo "Response: " . $output . "\n\n";
    
    echo "Test 2: Assign teacher to subject\n";
    ob_start();
    $controller->assignSubject([
        'user_id' => 2,
        'subject_id' => 1
    ]);
    $output = ob_get_clean();
    echo "Response: " . $output . "\n\n";
    
    echo "Test 3: Get my assignments\n";
    // Switch to teacher token
    $teacherPayload = [
        'id' => 2,
        'username' => 'teacher1',
        'role' => 'user'
    ];
    $teacherToken = JWT::encode($teacherPayload);
    $GLOBALS['TEST_AUTH_TOKEN'] = $teacherToken;
    ob_start();
    $controller->getMyAssignments();
    $output = ob_get_clean();
    echo "Response: " . $output . "\n\n";
    
    echo "✓ All tests completed\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
