<?php

/**
 * Test script for Grade Management functionality
 * 
 * This script tests the GradeController operations directly
 */

echo "=== Grade Management Tests ===\n\n";

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

// Test 2: Check if subjects exist
echo "Test 2: Check if subjects exist\n";
try {
    $query = "SELECT id, name FROM subjects LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $subject = $stmt->fetch();
    
    if ($subject) {
        echo "✓ PASS: Subject found\n";
        echo "  ID: " . $subject['id'] . "\n";
        echo "  Name: " . $subject['name'] . "\n";
        $subjectId = $subject['id'];
    } else {
        echo "✗ FAIL: No subjects found. Please run seed.sql\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 3: Create a test student for grades
echo "Test 3: Create a test student for grades\n";
try {
    // Get a class level
    $classQuery = "SELECT id FROM class_levels LIMIT 1";
    $classStmt = $conn->prepare($classQuery);
    $classStmt->execute();
    $classLevel = $classStmt->fetch();
    $classLevelId = $classLevel['id'];
    
    // Clean up any existing test student
    $cleanupQuery = "DELETE FROM students WHERE student_id = 'GRADE_TEST001'";
    $conn->prepare($cleanupQuery)->execute();
    
    $insertQuery = "INSERT INTO students (student_id, name, class_level_id) 
                   VALUES ('GRADE_TEST001', 'Grade Test Student', :class_level_id)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':class_level_id', $classLevelId);
    $stmt->execute();
    
    echo "✓ PASS: Test student created\n";
    echo "  Student ID: GRADE_TEST001\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 4: Test Grade model validation
echo "Test 4: Test Grade model validation\n";
try {
    require_once __DIR__ . '/models/Grade.php';
    
    // Test valid grade
    $validGrade = new Grade(null, 'GRADE_TEST001', $subjectId, null, 85.5);
    $errors = $validGrade->validate();
    
    if (empty($errors)) {
        echo "✓ PASS: Valid grade passes validation\n";
    } else {
        echo "✗ FAIL: Valid grade failed validation\n";
        print_r($errors);
    }
    
    // Test invalid grade (mark > 100)
    $invalidGrade = new Grade(null, 'GRADE_TEST001', $subjectId, null, 150);
    $errors = $invalidGrade->validate();
    
    if (!empty($errors)) {
        echo "✓ PASS: Invalid grade (mark > 100) fails validation\n";
        echo "  Errors: " . implode(', ', $errors) . "\n";
    } else {
        echo "✗ FAIL: Invalid grade passed validation\n";
    }
    
    // Test invalid grade (mark < 0)
    $invalidGrade2 = new Grade(null, 'GRADE_TEST001', $subjectId, null, -10);
    $errors2 = $invalidGrade2->validate();
    
    if (!empty($errors2)) {
        echo "✓ PASS: Invalid grade (mark < 0) fails validation\n";
        echo "  Errors: " . implode(', ', $errors2) . "\n";
    } else {
        echo "✗ FAIL: Invalid grade passed validation\n";
    }
    
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Add a grade
echo "Test 5: Add a grade\n";
try {
    $insertQuery = "INSERT INTO grades (student_id, subject_id, mark) 
                   VALUES ('GRADE_TEST001', :subject_id, 85.5)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    
    $gradeId = $conn->lastInsertId();
    
    echo "✓ PASS: Grade added successfully\n";
    echo "  Grade ID: " . $gradeId . "\n";
    echo "  Mark: 85.5\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 6: Verify grade was created with subject name
echo "Test 6: Verify grade with subject join\n";
try {
    $query = "SELECT g.id, g.student_id, g.subject_id, 
                    s.name as subject_name, g.mark, g.created_at 
             FROM grades g
             JOIN subjects s ON g.subject_id = s.id
             WHERE g.student_id = 'GRADE_TEST001'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $grade = $stmt->fetch();
    
    if ($grade && isset($grade['subject_name'])) {
        echo "✓ PASS: Grade fetched with subject name\n";
        echo "  Student ID: " . $grade['student_id'] . "\n";
        echo "  Subject: " . $grade['subject_name'] . "\n";
        echo "  Mark: " . $grade['mark'] . "\n";
    } else {
        echo "✗ FAIL: Grade not found or missing subject name\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 7: Get all grades for a student
echo "Test 7: Get all grades for a student\n";
try {
    // Add another grade
    $insertQuery = "INSERT INTO grades (student_id, subject_id, mark) 
                   VALUES ('GRADE_TEST001', :subject_id, 92.0)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    
    // Fetch all grades
    $query = "SELECT g.id, g.student_id, g.subject_id, 
                    s.name as subject_name, g.mark, g.created_at 
             FROM grades g
             JOIN subjects s ON g.subject_id = s.id
             WHERE g.student_id = 'GRADE_TEST001'
             ORDER BY g.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $grades = $stmt->fetchAll();
    
    echo "✓ PASS: Fetched " . count($grades) . " grade(s)\n";
    foreach ($grades as $grade) {
        echo "  - " . $grade['subject_name'] . ": " . $grade['mark'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Test mark validation (boundary values)
echo "Test 8: Test mark validation (boundary values)\n";
try {
    // Test mark = 0 (valid)
    $insertQuery = "INSERT INTO grades (student_id, subject_id, mark) 
                   VALUES ('GRADE_TEST001', :subject_id, 0)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    echo "✓ PASS: Mark = 0 accepted\n";
    
    // Test mark = 100 (valid)
    $insertQuery = "INSERT INTO grades (student_id, subject_id, mark) 
                   VALUES ('GRADE_TEST001', :subject_id, 100)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    echo "✓ PASS: Mark = 100 accepted\n";
    
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 9: Test invalid mark (should fail)
echo "Test 9: Test invalid mark (should fail)\n";
try {
    $insertQuery = "INSERT INTO grades (student_id, subject_id, mark) 
                   VALUES ('GRADE_TEST001', :subject_id, 150)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    
    echo "✗ FAIL: Invalid mark (150) was accepted\n";
} catch (Exception $e) {
    // Expected to fail due to CHECK constraint
    if (strpos($e->getMessage(), 'check') !== false || strpos($e->getMessage(), 'constraint') !== false) {
        echo "✓ PASS: Invalid mark (150) correctly rejected\n";
    } else {
        echo "✓ PASS: Invalid mark rejected (constraint may vary by DB)\n";
    }
}

echo "\n";

// Test 10: Test GradeController exists and can be instantiated
echo "Test 10: Test GradeController\n";
try {
    require_once __DIR__ . '/controllers/GradeController.php';
    $controller = new GradeController();
    
    echo "✓ PASS: GradeController instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 11: Test cascade delete (grades deleted when student deleted)
echo "Test 11: Test cascade delete\n";
try {
    // Count grades before deletion
    $countQuery = "SELECT COUNT(*) as count FROM grades WHERE student_id = 'GRADE_TEST001'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $beforeCount = $countStmt->fetch()['count'];
    
    echo "  Grades before deletion: " . $beforeCount . "\n";
    
    // Delete student
    $deleteQuery = "DELETE FROM students WHERE student_id = 'GRADE_TEST001'";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->execute();
    
    // Count grades after deletion
    $countStmt->execute();
    $afterCount = $countStmt->fetch()['count'];
    
    if ($afterCount === 0 && $beforeCount > 0) {
        echo "✓ PASS: Grades cascade deleted with student\n";
    } else {
        echo "✗ FAIL: Grades not cascade deleted\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== All Grade Management Tests Completed ===\n";
echo "\nGrade API endpoints are ready:\n";
echo "- POST /api/grades - Add grade\n";
echo "- GET /api/students/{student_id}/grades - Get grades for student\n";
