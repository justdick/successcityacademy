<?php

/**
 * Comprehensive Integration Tests for Student Management System
 * 
 * Tests all major workflows:
 * - Authentication flow (login, logout, token validation)
 * - User management (admin creates user, non-admin denied access)
 * - Subject management (admin creates/deletes subject, prevent deletion if in use)
 * - Class level management (admin creates/deletes class level, prevent deletion if in use)
 * - Complete user workflows (create student with class level, add grades with subjects, view grades)
 * - Error scenarios (duplicate ID, invalid marks, unauthorized access)
 * - API endpoint responses
 * 
 * Run: php backend/integration_tests.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/SubjectController.php';
require_once __DIR__ . '/controllers/ClassLevelController.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/GradeController.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/utils/JWT.php';

// Suppress output buffering warnings
error_reporting(E_ALL & ~E_WARNING);

$testsPassed = 0;
$testsFailed = 0;
$testData = [];

function pass($message) {
    global $testsPassed;
    $testsPassed++;
    echo "✓ PASS: $message\n";
}

function fail($message, $details = '') {
    global $testsFailed;
    $testsFailed++;
    echo "✗ FAIL: $message\n";
    if ($details) {
        echo "  Details: $details\n";
    }
}

function setTestToken($token) {
    $GLOBALS['TEST_AUTH_TOKEN'] = $token;
}


echo "=== STUDENT MANAGEMENT SYSTEM - INTEGRATION TESTS ===\n\n";

// ============================================================================
// SECTION 1: AUTHENTICATION FLOW
// ============================================================================
echo "SECTION 1: AUTHENTICATION FLOW\n";
echo str_repeat("-", 50) . "\n";

// Test 1.1: Login with valid credentials
echo "\nTest 1.1: Login with valid admin credentials\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'admin', 'password' => 'admin123']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['token'])) {
        pass("Admin login successful, JWT token generated");
        $testData['adminToken'] = $response['data']['token'];
        $testData['adminUser'] = $response['data']['user'];
    } else {
        fail("Admin login failed", $output);
    }
} catch (Exception $e) {
    fail("Admin login exception", $e->getMessage());
}

// Test 1.2: Login with invalid credentials
echo "\nTest 1.2: Login with invalid credentials\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'admin', 'password' => 'wrongpassword']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'Invalid') !== false) {
        pass("Invalid credentials correctly rejected");
    } else {
        fail("Should reject invalid credentials", $output);
    }
} catch (Exception $e) {
    fail("Invalid login exception", $e->getMessage());
}

// Test 1.3: Login with missing fields
echo "\nTest 1.3: Login with missing password field\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'admin']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'required') !== false) {
        pass("Missing fields validation works");
    } else {
        fail("Should validate required fields", $output);
    }
} catch (Exception $e) {
    fail("Missing fields exception", $e->getMessage());
}


// Test 1.4: JWT token validation
echo "\nTest 1.4: JWT token validation\n";
try {
    $decoded = JWT::decode($testData['adminToken']);
    if ($decoded && $decoded->username === 'admin' && $decoded->role === 'admin') {
        pass("JWT token is valid and contains correct user data");
    } else {
        fail("JWT token validation failed");
    }
} catch (Exception $e) {
    fail("JWT token validation exception", $e->getMessage());
}

// Test 1.5: Logout
echo "\nTest 1.5: Logout\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->logout();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Logout successful");
    } else {
        fail("Logout failed", $output);
    }
} catch (Exception $e) {
    fail("Logout exception", $e->getMessage());
}

// ============================================================================
// SECTION 2: USER MANAGEMENT (ADMIN ONLY)
// ============================================================================
echo "\n\nSECTION 2: USER MANAGEMENT\n";
echo str_repeat("-", 50) . "\n";

// Test 2.1: Admin creates new user
echo "\nTest 2.1: Admin creates new user\n";
try {
    $userController = new UserController();
    
    // Clean up test user if exists
    $db = new Database();
    $conn = $db->connect();
    $conn->prepare("DELETE FROM users WHERE username = 'testuser1'")->execute();
    
    // Set token for CLI testing
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['adminToken'];
    
    ob_start();
    $userController->createUser([
        'username' => 'testuser1',
        'password' => 'password123',
        'role' => 'user'
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Admin successfully created new user");
        $testData['regularUserId'] = $response['data']['id'];
    } else {
        fail("Admin failed to create user", $output);
    }
} catch (Exception $e) {
    fail("Create user exception", $e->getMessage());
}


// Test 2.2: Login as regular user
echo "\nTest 2.2: Login as regular user\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'testuser1', 'password' => 'password123']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['token'])) {
        pass("Regular user login successful");
        $testData['userToken'] = $response['data']['token'];
    } else {
        fail("Regular user login failed", $output);
    }
} catch (Exception $e) {
    fail("Regular user login exception", $e->getMessage());
}

// Test 2.3: Non-admin denied access to user management
echo "\nTest 2.3: Non-admin denied access to user management\n";
try {
    $userController = new UserController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $userController->createUser([
        'username' => 'testuser2',
        'password' => 'password123',
        'role' => 'user'
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied access to user management");
    } else {
        fail("Non-admin should be denied access", $output);
    }
} catch (Exception $e) {
    fail("Non-admin access exception", $e->getMessage());
}

// Test 2.4: Duplicate username rejected
echo "\nTest 2.4: Duplicate username rejected\n";
try {
    $userController = new UserController();
    setTestToken($testData['adminToken']);
    
    ob_start();
    $userController->createUser([
        'username' => 'testuser1',
        'password' => 'password456',
        'role' => 'user'
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'exists') !== false) {
        pass("Duplicate username correctly rejected");
    } else {
        fail("Should reject duplicate username", $output);
    }
} catch (Exception $e) {
    fail("Duplicate username exception", $e->getMessage());
}


// Test 2.5: Get all users (admin only)
echo "\nTest 2.5: Get all users (admin only)\n";
try {
    $userController = new UserController();
    setTestToken($testData['adminToken']);
    
    ob_start();
    $userController->getAllUsers();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Admin successfully retrieved all users");
    } else {
        fail("Admin failed to get users", $output);
    }
} catch (Exception $e) {
    fail("Get users exception", $e->getMessage());
}

// ============================================================================
// SECTION 3: SUBJECT MANAGEMENT
// ============================================================================
echo "\n\nSECTION 3: SUBJECT MANAGEMENT\n";
echo str_repeat("-", 50) . "\n";

// Test 3.1: Admin creates new subject
echo "\nTest 3.1: Admin creates new subject\n";
try {
    $subjectController = new SubjectController();
    
    // Clean up test subject if exists
    $db = new Database();
    $conn = $db->connect();
    $conn->prepare("DELETE FROM subjects WHERE name = 'Test Subject'")->execute();
    
    setTestToken($testData['adminToken']);
    
    ob_start();
    $subjectController->createSubject(['name' => 'Test Subject']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Admin successfully created new subject");
        $testData['testSubjectId'] = $response['data']['id'];
    } else {
        fail("Admin failed to create subject", $output);
    }
} catch (Exception $e) {
    fail("Create subject exception", $e->getMessage());
}

// Test 3.2: Non-admin denied access to create subject
echo "\nTest 3.2: Non-admin denied access to create subject\n";
try {
    $subjectController = new SubjectController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $subjectController->createSubject(['name' => 'Another Subject']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied access to create subject");
    } else {
        fail("Non-admin should be denied access", $output);
    }
} catch (Exception $e) {
    fail("Non-admin create subject exception", $e->getMessage());
}


// Test 3.3: Get all subjects (authenticated users)
echo "\nTest 3.3: Get all subjects (authenticated users)\n";
try {
    $subjectController = new SubjectController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $subjectController->getAllSubjects();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Regular user successfully retrieved all subjects");
    } else {
        fail("Regular user failed to get subjects", $output);
    }
} catch (Exception $e) {
    fail("Get subjects exception", $e->getMessage());
}

// Test 3.4: Delete unused subject
echo "\nTest 3.4: Delete unused subject\n";
try {
    $subjectController = new SubjectController();
    setTestToken($testData['adminToken']);
    
    ob_start();
    $subjectController->deleteSubject($testData['testSubjectId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Admin successfully deleted unused subject");
    } else {
        fail("Admin failed to delete subject", $output);
    }
} catch (Exception $e) {
    fail("Delete subject exception", $e->getMessage());
}

// Test 3.5: Prevent deletion of subject in use
echo "\nTest 3.5: Prevent deletion of subject in use\n";
try {
    // Get a subject that's likely in use (from seed data)
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE name = 'Mathematics' LIMIT 1");
    $stmt->execute();
    $mathSubject = $stmt->fetch();
    
    if ($mathSubject) {
        // Try to add a grade with this subject first to ensure it's in use
        $stmt = $conn->prepare("SELECT student_id FROM students LIMIT 1");
        $stmt->execute();
        $student = $stmt->fetch();
        
        if ($student) {
            $conn->prepare("INSERT IGNORE INTO grades (student_id, subject_id, mark) VALUES (?, ?, 85)")->execute([
                $student['student_id'], $mathSubject['id']
            ]);
            
            $subjectController = new SubjectController();
            setTestToken($testData['adminToken']);
            
            ob_start();
            $subjectController->deleteSubject($mathSubject['id']);
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            if ($response && $response['success'] === false && strpos($response['error'], 'in use') !== false) {
                pass("Subject in use correctly prevented from deletion");
            } else {
                fail("Should prevent deletion of subject in use", $output);
            }
        } else {
            pass("Subject in use test skipped (no students)");
        }
    } else {
        pass("Subject in use test skipped (Mathematics not found)");
    }
} catch (Exception $e) {
    fail("Prevent subject deletion exception", $e->getMessage());
}


// ============================================================================
// SECTION 4: CLASS LEVEL MANAGEMENT
// ============================================================================
echo "\n\nSECTION 4: CLASS LEVEL MANAGEMENT\n";
echo str_repeat("-", 50) . "\n";

// Test 4.1: Admin creates new class level
echo "\nTest 4.1: Admin creates new class level\n";
try {
    $classLevelController = new ClassLevelController();
    
    // Clean up test class level if exists
    $db = new Database();
    $conn = $db->connect();
    $conn->prepare("DELETE FROM class_levels WHERE name = 'Test Grade'")->execute();
    
    setTestToken($testData['adminToken']);
    
    ob_start();
    $classLevelController->createClassLevel(['name' => 'Test Grade']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Admin successfully created new class level");
        $testData['testClassLevelId'] = $response['data']['id'];
    } else {
        fail("Admin failed to create class level", $output);
    }
} catch (Exception $e) {
    fail("Create class level exception", $e->getMessage());
}

// Test 4.2: Non-admin denied access to create class level
echo "\nTest 4.2: Non-admin denied access to create class level\n";
try {
    $classLevelController = new ClassLevelController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $classLevelController->createClassLevel(['name' => 'Another Grade']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied access to create class level");
    } else {
        fail("Non-admin should be denied access", $output);
    }
} catch (Exception $e) {
    fail("Non-admin create class level exception", $e->getMessage());
}

// Test 4.3: Get all class levels (authenticated users)
echo "\nTest 4.3: Get all class levels (authenticated users)\n";
try {
    $classLevelController = new ClassLevelController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $classLevelController->getAllClassLevels();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Regular user successfully retrieved all class levels");
    } else {
        fail("Regular user failed to get class levels", $output);
    }
} catch (Exception $e) {
    fail("Get class levels exception", $e->getMessage());
}


// Test 4.4: Delete unused class level
echo "\nTest 4.4: Delete unused class level\n";
try {
    $classLevelController = new ClassLevelController();
    setTestToken($testData['adminToken']);
    
    ob_start();
    $classLevelController->deleteClassLevel($testData['testClassLevelId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Admin successfully deleted unused class level");
    } else {
        fail("Admin failed to delete class level", $output);
    }
} catch (Exception $e) {
    fail("Delete class level exception", $e->getMessage());
}

// Test 4.5: Prevent deletion of class level in use
echo "\nTest 4.5: Prevent deletion of class level in use\n";
try {
    // Get a class level that's likely in use
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT id FROM class_levels WHERE name = 'Grade 10' LIMIT 1");
    $stmt->execute();
    $classLevel = $stmt->fetch();
    
    if ($classLevel) {
        // Ensure there's a student using this class level
        $stmt = $conn->prepare("SELECT id FROM students WHERE class_level_id = ? LIMIT 1");
        $stmt->execute([$classLevel['id']]);
        $student = $stmt->fetch();
        
        if (!$student) {
            // Create a test student with this class level
            $conn->prepare("INSERT INTO students (student_id, name, class_level_id) VALUES ('TEST_CL_001', 'Test Student', ?)")->execute([
                $classLevel['id']
            ]);
        }
        
        $classLevelController = new ClassLevelController();
        setTestToken($testData['adminToken']);
        
        ob_start();
        $classLevelController->deleteClassLevel($classLevel['id']);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && $response['success'] === false && strpos($response['error'], 'in use') !== false) {
            pass("Class level in use correctly prevented from deletion");
        } else {
            fail("Should prevent deletion of class level in use", $output);
        }
    } else {
        pass("Class level in use test skipped (Grade 10 not found)");
    }
} catch (Exception $e) {
    fail("Prevent class level deletion exception", $e->getMessage());
}


// ============================================================================
// SECTION 5: COMPLETE USER WORKFLOW
// ============================================================================
echo "\n\nSECTION 5: COMPLETE USER WORKFLOW\n";
echo str_repeat("-", 50) . "\n";

// Test 5.1: Create student with class level
echo "\nTest 5.1: Create student with class level\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get a class level
    $stmt = $conn->prepare("SELECT id FROM class_levels LIMIT 1");
    $stmt->execute();
    $classLevel = $stmt->fetch();
    
    if (!$classLevel) {
        fail("No class levels available for testing");
    } else {
        // Clean up test student if exists
        $conn->prepare("DELETE FROM students WHERE student_id = 'INT_TEST_001'")->execute();
        
        $studentController = new StudentController();
        setTestToken($testData['userToken']);
        
        ob_start();
        $studentController->createStudent([
            'student_id' => 'INT_TEST_001',
            'name' => 'Integration Test Student',
            'class_level_id' => $classLevel['id']
        ]);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && $response['success'] === true) {
            pass("Student created successfully with class level");
            $testData['testStudentId'] = 'INT_TEST_001';
        } else {
            fail("Failed to create student", $output);
        }
    }
} catch (Exception $e) {
    fail("Create student exception", $e->getMessage());
}

// Test 5.2: Get all students
echo "\nTest 5.2: Get all students\n";
try {
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Successfully retrieved all students");
    } else {
        fail("Failed to get students", $output);
    }
} catch (Exception $e) {
    fail("Get students exception", $e->getMessage());
}

// Test 5.3: Get specific student
echo "\nTest 5.3: Get specific student\n";
try {
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->getStudent($testData['testStudentId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && $response['data']['student_id'] === $testData['testStudentId']) {
        pass("Successfully retrieved specific student");
    } else {
        fail("Failed to get specific student", $output);
    }
} catch (Exception $e) {
    fail("Get specific student exception", $e->getMessage());
}


// Test 5.4: Add grades with subjects
echo "\nTest 5.4: Add grades with subjects\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get subjects
    $stmt = $conn->prepare("SELECT id, name FROM subjects LIMIT 3");
    $stmt->execute();
    $subjects = $stmt->fetchAll();
    
    if (count($subjects) < 2) {
        fail("Not enough subjects available for testing");
    } else {
        $gradeController = new GradeController();
        $gradesAdded = 0;
        setTestToken($testData['userToken']);
        
        foreach ($subjects as $subject) {
            ob_start();
            $gradeController->addGrade([
                'student_id' => $testData['testStudentId'],
                'subject_id' => $subject['id'],
                'mark' => 85 + $gradesAdded * 5
            ]);
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            if ($response && $response['success'] === true) {
                $gradesAdded++;
            }
        }
        
        if ($gradesAdded >= 2) {
            pass("Successfully added multiple grades with subjects");
        } else {
            fail("Failed to add grades", "Only added $gradesAdded grades");
        }
    }
} catch (Exception $e) {
    fail("Add grades exception", $e->getMessage());
}

// Test 5.5: View grades for student
echo "\nTest 5.5: View grades for student\n";
try {
    $gradeController = new GradeController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $gradeController->getStudentGrades($testData['testStudentId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['grades']) && count($response['data']['grades']) > 0) {
        pass("Successfully retrieved student grades");
    } else {
        fail("Failed to get student grades", $output);
    }
} catch (Exception $e) {
    fail("Get student grades exception", $e->getMessage());
}

// Test 5.6: Update student information
echo "\nTest 5.6: Update student information\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get a class level
    $stmt = $conn->prepare("SELECT id FROM class_levels LIMIT 1");
    $stmt->execute();
    $classLevel = $stmt->fetch();
    
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->updateStudent($testData['testStudentId'], [
        'name' => 'Updated Test Student',
        'class_level_id' => $classLevel['id']
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Successfully updated student information");
    } else {
        fail("Failed to update student", $output);
    }
} catch (Exception $e) {
    fail("Update student exception", $e->getMessage());
}


// ============================================================================
// SECTION 6: ERROR SCENARIOS
// ============================================================================
echo "\n\nSECTION 6: ERROR SCENARIOS\n";
echo str_repeat("-", 50) . "\n";

// Test 6.1: Duplicate student ID
echo "\nTest 6.1: Duplicate student ID rejected\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT class_level_id FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$testData['testStudentId']]);
    $existingStudent = $stmt->fetch();
    
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->createStudent([
        'student_id' => $testData['testStudentId'],
        'name' => 'Duplicate Student',
        'class_level_id' => $existingStudent['class_level_id']
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'exists') !== false) {
        pass("Duplicate student ID correctly rejected");
    } else {
        fail("Should reject duplicate student ID", $output);
    }
} catch (Exception $e) {
    fail("Duplicate student ID exception", $e->getMessage());
}

// Test 6.2: Invalid mark (> 100)
echo "\nTest 6.2: Invalid mark (> 100) rejected\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id FROM subjects LIMIT 1");
    $stmt->execute();
    $subject = $stmt->fetch();
    
    $gradeController = new GradeController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $gradeController->addGrade([
        'student_id' => $testData['testStudentId'],
        'subject_id' => $subject['id'],
        'mark' => 150
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && (strpos($response['error'], '100') !== false || strpos($response['error'], 'range') !== false)) {
        pass("Invalid mark (> 100) correctly rejected");
    } else {
        fail("Should reject mark > 100", $output);
    }
} catch (Exception $e) {
    fail("Invalid mark exception", $e->getMessage());
}

// Test 6.3: Invalid mark (< 0)
echo "\nTest 6.3: Invalid mark (< 0) rejected\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id FROM subjects LIMIT 1");
    $stmt->execute();
    $subject = $stmt->fetch();
    
    $gradeController = new GradeController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $gradeController->addGrade([
        'student_id' => $testData['testStudentId'],
        'subject_id' => $subject['id'],
        'mark' => -10
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && (strpos($response['error'], '0') !== false || strpos($response['error'], 'range') !== false)) {
        pass("Invalid mark (< 0) correctly rejected");
    } else {
        fail("Should reject mark < 0", $output);
    }
} catch (Exception $e) {
    fail("Invalid mark exception", $e->getMessage());
}


// Test 6.4: Unauthorized access (no token)
echo "\nTest 6.4: Unauthorized access (no token)\n";
try {
    $studentController = new StudentController();
    unset($GLOBALS['TEST_AUTH_TOKEN']);
    
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && (strpos($response['error'], 'nauthorized') !== false || strpos($response['error'], 'Missing') !== false)) {
        pass("Unauthorized access correctly rejected");
    } else {
        fail("Should reject access without token", $output);
    }
} catch (Exception $e) {
    fail("Unauthorized access exception", $e->getMessage());
}

// Test 6.5: Invalid token
echo "\nTest 6.5: Invalid token rejected\n";
try {
    $studentController = new StudentController();
    setTestToken('invalid.token.here');
    
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'nvalid') !== false) {
        pass("Invalid token correctly rejected");
    } else {
        fail("Should reject invalid token", $output);
    }
} catch (Exception $e) {
    fail("Invalid token exception", $e->getMessage());
}

// Test 6.6: Non-existent student
echo "\nTest 6.6: Non-existent student returns error\n";
try {
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->getStudent('NONEXISTENT999');
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Non-existent student correctly returns error");
    } else {
        fail("Should return error for non-existent student", $output);
    }
} catch (Exception $e) {
    fail("Non-existent student exception", $e->getMessage());
}

// Test 6.7: Add grade for non-existent student
echo "\nTest 6.7: Add grade for non-existent student rejected\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id FROM subjects LIMIT 1");
    $stmt->execute();
    $subject = $stmt->fetch();
    
    $gradeController = new GradeController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $gradeController->addGrade([
        'student_id' => 'NONEXISTENT999',
        'subject_id' => $subject['id'],
        'mark' => 85
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Grade for non-existent student correctly rejected");
    } else {
        fail("Should reject grade for non-existent student", $output);
    }
} catch (Exception $e) {
    fail("Grade for non-existent student exception", $e->getMessage());
}


// ============================================================================
// SECTION 7: API ENDPOINT RESPONSES
// ============================================================================
echo "\n\nSECTION 7: API ENDPOINT RESPONSES\n";
echo str_repeat("-", 50) . "\n";

// Test 7.1: Verify response format for successful requests
echo "\nTest 7.1: Verify response format for successful requests\n";
try {
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && isset($response['success']) && isset($response['data'])) {
        pass("Successful response has correct format (success, data)");
    } else {
        fail("Response format incorrect", $output);
    }
} catch (Exception $e) {
    fail("Response format exception", $e->getMessage());
}

// Test 7.2: Verify response format for error requests
echo "\nTest 7.2: Verify response format for error requests\n";
try {
    $studentController = new StudentController();
    setTestToken($testData['userToken']);
    
    ob_start();
    $studentController->getStudent('NONEXISTENT999');
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && isset($response['success']) && $response['success'] === false && isset($response['error'])) {
        pass("Error response has correct format (success: false, error)");
    } else {
        fail("Error response format incorrect", $output);
    }
} catch (Exception $e) {
    fail("Error response format exception", $e->getMessage());
}

// Test 7.3: Verify JSON content type
echo "\nTest 7.3: Verify JSON responses are valid\n";
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'admin', 'password' => 'admin123']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response !== null && json_last_error() === JSON_ERROR_NONE) {
        pass("API responses are valid JSON");
    } else {
        fail("API response is not valid JSON", json_last_error_msg());
    }
} catch (Exception $e) {
    fail("JSON validation exception", $e->getMessage());
}


// ============================================================================
// CLEANUP
// ============================================================================
echo "\n\nCLEANUP\n";
echo str_repeat("-", 50) . "\n";

// Clean up test data
echo "\nCleaning up test data...\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Delete test student (grades will cascade delete)
    if (isset($testData['testStudentId'])) {
        $conn->prepare("DELETE FROM students WHERE student_id = ?")->execute([$testData['testStudentId']]);
    }
    
    // Delete test user
    $conn->prepare("DELETE FROM users WHERE username = 'testuser1'")->execute();
    
    // Delete test class level student if exists
    $conn->prepare("DELETE FROM students WHERE student_id = 'TEST_CL_001'")->execute();
    
    pass("Test data cleaned up successfully");
} catch (Exception $e) {
    fail("Cleanup exception", $e->getMessage());
}

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n✓ ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "\n✗ SOME TESTS FAILED\n";
    exit(1);
}

