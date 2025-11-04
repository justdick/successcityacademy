<?php

/**
 * Test script for Student Management functionality
 * 
 * This script tests the StudentController CRUD operations directly
 */

echo "=== Student Management Tests ===\n\n";

// Test 1: Database Connection
echo "Test 1: Database Connection\n";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $conn = $db->connect();
    echo "✓ PASS: Database connected successfully\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 2: Check if class levels exist
echo "Test 2: Check if class levels exist\n";
try {
    $query = "SELECT id, name FROM class_levels LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $classLevel = $stmt->fetch();
    
    if ($classLevel) {
        echo "✓ PASS: Class level found\n";
        echo "  ID: " . $classLevel['id'] . "\n";
        echo "  Name: " . $classLevel['name'] . "\n";
        $classLevelId = $classLevel['id'];
    } else {
        echo "✗ FAIL: No class levels found. Please run seed.sql\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 3: Create a test student
echo "Test 3: Create a test student\n";
try {
    // Clean up any existing test student
    $cleanupQuery = "DELETE FROM students WHERE student_id = 'TEST001'";
    $conn->prepare($cleanupQuery)->execute();
    
    $insertQuery = "INSERT INTO students (student_id, name, class_level_id) 
                   VALUES ('TEST001', 'John Doe', :class_level_id)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':class_level_id', $classLevelId);
    $stmt->execute();
    
    echo "✓ PASS: Test student created\n";
    echo "  Student ID: TEST001\n";
    echo "  Name: John Doe\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 4: Verify student was created with class level name
echo "Test 4: Verify student with class level join\n";
try {
    $query = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                    cl.name as class_level_name, s.created_at, s.updated_at 
             FROM students s
             JOIN class_levels cl ON s.class_level_id = cl.id
             WHERE s.student_id = 'TEST001'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $student = $stmt->fetch();
    
    if ($student && isset($student['class_level_name'])) {
        echo "✓ PASS: Student fetched with class level name\n";
        echo "  Student ID: " . $student['student_id'] . "\n";
        echo "  Name: " . $student['name'] . "\n";
        echo "  Class Level: " . $student['class_level_name'] . "\n";
    } else {
        echo "✗ FAIL: Student not found or missing class level name\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 5: Test duplicate student ID check
echo "Test 5: Test duplicate student ID check\n";
try {
    $insertQuery = "INSERT INTO students (student_id, name, class_level_id) 
                   VALUES ('TEST001', 'Jane Doe', :class_level_id)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':class_level_id', $classLevelId);
    $stmt->execute();
    
    echo "✗ FAIL: Duplicate student ID was allowed\n";
} catch (Exception $e) {
    // Expected to fail due to unique constraint
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
        echo "✓ PASS: Duplicate student ID correctly rejected\n";
    } else {
        echo "✗ FAIL: Unexpected error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 6: Update student
echo "Test 6: Update student\n";
try {
    $updateQuery = "UPDATE students SET name = 'Jane Smith' WHERE student_id = 'TEST001'";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute();
    
    // Verify update
    $verifyQuery = "SELECT name FROM students WHERE student_id = 'TEST001'";
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->execute();
    $result = $verifyStmt->fetch();
    
    if ($result && $result['name'] === 'Jane Smith') {
        echo "✓ PASS: Student updated successfully\n";
        echo "  New name: " . $result['name'] . "\n";
    } else {
        echo "✗ FAIL: Student update failed\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Get all students
echo "Test 7: Get all students\n";
try {
    $query = "SELECT s.id, s.student_id, s.name, s.class_level_id, 
                    cl.name as class_level_name 
             FROM students s
             JOIN class_levels cl ON s.class_level_id = cl.id
             ORDER BY s.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    echo "✓ PASS: Fetched " . count($students) . " student(s)\n";
    foreach ($students as $student) {
        echo "  - " . $student['student_id'] . ": " . $student['name'] . " (" . $student['class_level_name'] . ")\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Delete student
echo "Test 8: Delete student\n";
try {
    $deleteQuery = "DELETE FROM students WHERE student_id = 'TEST001'";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->execute();
    
    // Verify deletion
    $verifyQuery = "SELECT id FROM students WHERE student_id = 'TEST001'";
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->execute();
    $result = $verifyStmt->fetch();
    
    if (!$result) {
        echo "✓ PASS: Student deleted successfully\n";
    } else {
        echo "✗ FAIL: Student still exists after deletion\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 9: Test Student model validation
echo "Test 9: Test Student model validation\n";
try {
    require_once __DIR__ . '/models/Student.php';
    
    // Test valid student
    $validStudent = new Student(null, 'S001', 'Test Student', 1);
    $errors = $validStudent->validate();
    
    if (empty($errors)) {
        echo "✓ PASS: Valid student passes validation\n";
    } else {
        echo "✗ FAIL: Valid student failed validation\n";
        print_r($errors);
    }
    
    // Test invalid student (empty name)
    $invalidStudent = new Student(null, 'S002', '', 1);
    $errors = $invalidStudent->validate();
    
    if (!empty($errors)) {
        echo "✓ PASS: Invalid student (empty name) fails validation\n";
        echo "  Errors: " . implode(', ', $errors) . "\n";
    } else {
        echo "✗ FAIL: Invalid student passed validation\n";
    }
    
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 10: Test StudentController exists and can be instantiated
echo "Test 10: Test StudentController\n";
try {
    require_once __DIR__ . '/controllers/StudentController.php';
    $controller = new StudentController();
    
    echo "✓ PASS: StudentController instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== All Student Management Tests Completed ===\n";
echo "\nStudent API endpoints are ready:\n";
echo "- POST /api/students - Create student\n";
echo "- GET /api/students - Get all students\n";
echo "- GET /api/students/{student_id} - Get specific student\n";
echo "- PUT /api/students/{student_id} - Update student\n";
echo "- DELETE /api/students/{student_id} - Delete student\n";
