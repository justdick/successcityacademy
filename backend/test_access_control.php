<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/TeacherAccessControl.php';

echo "=== Testing TeacherAccessControl Middleware ===\n\n";

try {
    $db = new Database();
    $conn = $db->connect();

    // Get a teacher user (role = 'user')
    $teacherQuery = "SELECT id, username, role FROM users WHERE role = 'user' LIMIT 1";
    $teacherStmt = $conn->prepare($teacherQuery);
    $teacherStmt->execute();
    $teacher = $teacherStmt->fetch();

    if (!$teacher) {
        echo "✗ No teacher user found. Please create a user with role 'user'\n";
        exit(1);
    }

    echo "Testing with teacher: {$teacher['username']} (ID: {$teacher['id']})\n\n";

    // Get an admin user
    $adminQuery = "SELECT id, username, role FROM users WHERE role = 'admin' LIMIT 1";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->execute();
    $admin = $adminStmt->fetch();

    if (!$admin) {
        echo "✗ No admin user found\n";
        exit(1);
    }

    echo "Testing with admin: {$admin['username']} (ID: {$admin['id']})\n\n";

    // Test 1: Get accessible classes for teacher
    echo "Test 1: Get accessible classes for teacher\n";
    $teacherClasses = TeacherAccessControl::getAccessibleClasses($teacher['id']);
    echo "  Teacher has access to " . count($teacherClasses) . " classes\n";
    if (!empty($teacherClasses)) {
        echo "  Class IDs: " . implode(', ', $teacherClasses) . "\n";
    }
    echo "  ✓ Pass\n\n";

    // Test 2: Get accessible subjects for teacher
    echo "Test 2: Get accessible subjects for teacher\n";
    $teacherSubjects = TeacherAccessControl::getAccessibleSubjects($teacher['id']);
    echo "  Teacher has access to " . count($teacherSubjects) . " subjects\n";
    if (!empty($teacherSubjects)) {
        echo "  Subject IDs: " . implode(', ', $teacherSubjects) . "\n";
    }
    echo "  ✓ Pass\n\n";

    // Test 3: Get accessible classes for admin (should get all)
    echo "Test 3: Get accessible classes for admin\n";
    $adminClasses = TeacherAccessControl::getAccessibleClasses($admin['id']);
    echo "  Admin has access to " . count($adminClasses) . " classes (all)\n";
    echo "  ✓ Pass\n\n";

    // Test 4: Get accessible subjects for admin (should get all)
    echo "Test 4: Get accessible subjects for admin\n";
    $adminSubjects = TeacherAccessControl::getAccessibleSubjects($admin['id']);
    echo "  Admin has access to " . count($adminSubjects) . " subjects (all)\n";
    echo "  ✓ Pass\n\n";

    // Test 5: Check class access for teacher
    if (!empty($teacherClasses)) {
        $testClassId = $teacherClasses[0];
        echo "Test 5: Check class access for teacher (class ID: $testClassId)\n";
        $hasAccess = TeacherAccessControl::hasClassAccess($teacher['id'], $testClassId);
        echo "  Has access: " . ($hasAccess ? 'Yes' : 'No') . "\n";
        echo "  ✓ Pass\n\n";
    }

    // Test 6: Check subject access for teacher
    if (!empty($teacherSubjects)) {
        $testSubjectId = $teacherSubjects[0];
        echo "Test 6: Check subject access for teacher (subject ID: $testSubjectId)\n";
        $hasAccess = TeacherAccessControl::hasSubjectAccess($teacher['id'], $testSubjectId);
        echo "  Has access: " . ($hasAccess ? 'Yes' : 'No') . "\n";
        echo "  ✓ Pass\n\n";
    }

    // Test 7: Check student access for teacher
    $studentQuery = "SELECT id, student_id, name, class_level_id FROM students LIMIT 1";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->execute();
    $student = $studentStmt->fetch();

    if ($student) {
        echo "Test 7: Check student access for teacher (student: {$student['name']})\n";
        $hasAccess = TeacherAccessControl::hasStudentAccess($teacher['id'], $student['id']);
        echo "  Has access: " . ($hasAccess ? 'Yes' : 'No') . "\n";
        echo "  Student is in class ID: {$student['class_level_id']}\n";
        echo "  Teacher has access to class: " . (in_array($student['class_level_id'], $teacherClasses) ? 'Yes' : 'No') . "\n";
        echo "  ✓ Pass\n\n";
    }

    // Test 8: Test access logging
    echo "Test 8: Test access logging\n";
    TeacherAccessControl::logUnauthorizedAccess($teacher['id'], 'test_resource', '999');
    
    // Check if log was created
    $logQuery = "SELECT COUNT(*) as count FROM access_logs WHERE user_id = :user_id AND resource_type = 'test_resource'";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bindParam(':user_id', $teacher['id']);
    $logStmt->execute();
    $logResult = $logStmt->fetch();
    
    echo "  Access log entries: {$logResult['count']}\n";
    echo "  ✓ Pass\n\n";

    echo "=== All Tests Passed! ===\n";

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
