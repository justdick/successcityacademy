<?php

/**
 * Integration Tests for Subject Weighting Management
 * 
 * Tests subject weighting functionality:
 * - Create weighting with valid percentages
 * - Validation for percentages not summing to 100%
 * - Default weighting retrieval
 * - Admin-only access restrictions
 * - Get all weightings
 * - Update weighting
 * 
 * Run: php backend/test_subject_weighting.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/SubjectController.php';
require_once __DIR__ . '/controllers/SubjectWeightingController.php';
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

echo "=== SUBJECT WEIGHTING MANAGEMENT - INTEGRATION TESTS ===\n\n";

// ============================================================================
// SETUP: Login as admin and create test subjects
// ============================================================================
echo "SETUP: Logging in as admin\n";
echo str_repeat("-", 50) . "\n";

try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'admin', 'password' => 'admin123']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['token'])) {
        echo "✓ Admin login successful\n";
        $testData['adminToken'] = $response['data']['token'];
        setTestToken($testData['adminToken']);
    } else {
        echo "✗ Admin login failed - cannot continue tests\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Admin login exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Create test subjects
echo "\nCreating test subjects\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Clean up test subjects if they exist
    $conn->prepare("DELETE FROM subjects WHERE name LIKE 'Test Subject%'")->execute();
    
    $subjectController = new SubjectController();
    
    // Create first test subject
    ob_start();
    $subjectController->createSubject(['name' => 'Test Subject 1']);
    $output = ob_get_clean();
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $testData['subjectId1'] = $response['data']['id'];
        echo "✓ Test Subject 1 created (ID: {$testData['subjectId1']})\n";
    }
    
    // Create second test subject
    ob_start();
    $subjectController->createSubject(['name' => 'Test Subject 2']);
    $output = ob_get_clean();
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $testData['subjectId2'] = $response['data']['id'];
        echo "✓ Test Subject 2 created (ID: {$testData['subjectId2']})\n";
    }
} catch (Exception $e) {
    echo "✗ Create test subjects exception: " . $e->getMessage() . "\n";
    exit(1);
}

// Create a regular user for testing non-admin access
echo "\nCreating regular user for testing\n";
try {
    // Clean up test user if exists
    $db = new Database();
    $conn = $db->connect();
    $conn->prepare("DELETE FROM users WHERE username = 'testuser'")->execute();
    
    $userController = new UserController();
    ob_start();
    $userController->createUser([
        'username' => 'testuser',
        'password' => 'user123',
        'role' => 'user'
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        echo "✓ Test user created\n";
    }
} catch (Exception $e) {
    echo "✗ Create user exception: " . $e->getMessage() . "\n";
}

// Login as regular user
try {
    $authController = new AuthController();
    ob_start();
    $authController->login(['username' => 'testuser', 'password' => 'user123']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['token'])) {
        echo "✓ User login successful\n";
        $testData['userToken'] = $response['data']['token'];
    }
} catch (Exception $e) {
    echo "✗ User login exception: " . $e->getMessage() . "\n";
}

// Reset to admin token
$GLOBALS['TEST_AUTH_TOKEN'] = $testData['adminToken'];

// ============================================================================
// SECTION 1: DEFAULT WEIGHTING RETRIEVAL
// ============================================================================
echo "\n\nSECTION 1: DEFAULT WEIGHTING RETRIEVAL\n";
echo str_repeat("-", 50) . "\n";

// Test 1.1: Get default weighting for subject without custom weighting
echo "\nTest 1.1: Get default weighting (40% CA, 60% Exam)\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getWeighting($testData['subjectId1']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && 
        $response['data']['ca_percentage'] == 40.00 && 
        $response['data']['exam_percentage'] == 60.00) {
        pass("Default weighting retrieved correctly (40% CA, 60% Exam)");
    } else {
        fail("Failed to retrieve default weighting", $output);
    }
} catch (Exception $e) {
    fail("Get default weighting exception", $e->getMessage());
}

// Test 1.2: Get all weightings includes subjects with defaults
echo "\nTest 1.2: Get all weightings includes subjects with defaults\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getAllWeightings();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data']) && count($response['data']) >= 2) {
        pass("Retrieved all weightings including defaults");
    } else {
        fail("Failed to retrieve all weightings", $output);
    }
} catch (Exception $e) {
    fail("Get all weightings exception", $e->getMessage());
}

// ============================================================================
// SECTION 2: WEIGHTING CREATION WITH VALIDATION
// ============================================================================
echo "\n\nSECTION 2: WEIGHTING CREATION WITH VALIDATION\n";
echo str_repeat("-", 50) . "\n";

// Test 2.1: Create weighting with valid percentages
echo "\nTest 2.1: Create weighting with valid percentages (30% CA, 70% Exam)\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId1'],
        'ca_percentage' => 30.00,
        'exam_percentage' => 70.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && 
        $response['data']['ca_percentage'] == 30.00 && 
        $response['data']['exam_percentage'] == 70.00) {
        pass("Weighting created with valid percentages");
        $testData['weightingId1'] = $response['data']['id'];
    } else {
        fail("Failed to create weighting", $output);
    }
} catch (Exception $e) {
    fail("Create weighting exception", $e->getMessage());
}

// Test 2.2: Reject percentages not summing to 100%
echo "\nTest 2.2: Reject percentages not summing to 100%\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId2'],
        'ca_percentage' => 50.00,
        'exam_percentage' => 60.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], '100') !== false) {
        pass("Correctly rejected percentages not summing to 100%");
    } else {
        fail("Should reject percentages not summing to 100%", $output);
    }
} catch (Exception $e) {
    fail("Invalid percentage sum exception", $e->getMessage());
}

// Test 2.3: Reject negative percentages
echo "\nTest 2.3: Reject negative percentages\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId2'],
        'ca_percentage' => -10.00,
        'exam_percentage' => 110.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false) {
        pass("Correctly rejected negative percentages");
    } else {
        fail("Should reject negative percentages", $output);
    }
} catch (Exception $e) {
    fail("Negative percentage exception", $e->getMessage());
}

// Test 2.4: Reject percentages over 100
echo "\nTest 2.4: Reject percentages over 100\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId2'],
        'ca_percentage' => 150.00,
        'exam_percentage' => -50.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false) {
        pass("Correctly rejected percentages over 100");
    } else {
        fail("Should reject percentages over 100", $output);
    }
} catch (Exception $e) {
    fail("Over 100 percentage exception", $e->getMessage());
}

// Test 2.5: Create another valid weighting
echo "\nTest 2.5: Create weighting for second subject (50% CA, 50% Exam)\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId2'],
        'ca_percentage' => 50.00,
        'exam_percentage' => 50.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && 
        $response['data']['ca_percentage'] == 50.00 && 
        $response['data']['exam_percentage'] == 50.00) {
        pass("Second weighting created successfully");
        $testData['weightingId2'] = $response['data']['id'];
    } else {
        fail("Failed to create second weighting", $output);
    }
} catch (Exception $e) {
    fail("Create second weighting exception", $e->getMessage());
}

// ============================================================================
// SECTION 3: WEIGHTING UPDATE
// ============================================================================
echo "\n\nSECTION 3: WEIGHTING UPDATE\n";
echo str_repeat("-", 50) . "\n";

// Test 3.1: Update existing weighting
echo "\nTest 3.1: Update existing weighting\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->updateWeighting($testData['subjectId1'], [
        'ca_percentage' => 35.00,
        'exam_percentage' => 65.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && 
        $response['data']['ca_percentage'] == 35.00 && 
        $response['data']['exam_percentage'] == 65.00) {
        pass("Weighting updated successfully");
    } else {
        fail("Failed to update weighting", $output);
    }
} catch (Exception $e) {
    fail("Update weighting exception", $e->getMessage());
}

// Test 3.2: Update with invalid percentages
echo "\nTest 3.2: Reject update with invalid percentages\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->updateWeighting($testData['subjectId1'], [
        'ca_percentage' => 40.00,
        'exam_percentage' => 70.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], '100') !== false) {
        pass("Correctly rejected invalid update");
    } else {
        fail("Should reject invalid update", $output);
    }
} catch (Exception $e) {
    fail("Invalid update exception", $e->getMessage());
}

// ============================================================================
// SECTION 4: ADMIN-ONLY ACCESS
// ============================================================================
echo "\n\nSECTION 4: ADMIN-ONLY ACCESS RESTRICTIONS\n";
echo str_repeat("-", 50) . "\n";

// Test 4.1: Non-admin cannot create weighting
echo "\nTest 4.1: Non-admin user cannot create weighting\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->createOrUpdateWeighting([
        'subject_id' => $testData['subjectId1'],
        'ca_percentage' => 20.00,
        'exam_percentage' => 80.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied weighting creation");
    } else {
        fail("Should deny non-admin weighting creation", $output);
    }
} catch (Exception $e) {
    fail("Non-admin create weighting exception", $e->getMessage());
}

// Test 4.2: Non-admin cannot update weighting
echo "\nTest 4.2: Non-admin user cannot update weighting\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->updateWeighting($testData['subjectId1'], [
        'ca_percentage' => 10.00,
        'exam_percentage' => 90.00
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied weighting update");
    } else {
        fail("Should deny non-admin weighting update", $output);
    }
} catch (Exception $e) {
    fail("Non-admin update weighting exception", $e->getMessage());
}

// Test 4.3: Non-admin can view weightings
echo "\nTest 4.3: Non-admin user can view weightings\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getAllWeightings();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Non-admin can view weightings");
    } else {
        fail("Non-admin should be able to view weightings", $output);
    }
} catch (Exception $e) {
    fail("Non-admin view weightings exception", $e->getMessage());
}

// Test 4.4: Non-admin can view specific weighting
echo "\nTest 4.4: Non-admin user can view specific weighting\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getWeighting($testData['subjectId1']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Non-admin can view specific weighting");
    } else {
        fail("Non-admin should be able to view specific weighting", $output);
    }
} catch (Exception $e) {
    fail("Non-admin view specific weighting exception", $e->getMessage());
}

// Reset to admin token
$GLOBALS['TEST_AUTH_TOKEN'] = $testData['adminToken'];

// ============================================================================
// SECTION 5: WEIGHTING RETRIEVAL
// ============================================================================
echo "\n\nSECTION 5: WEIGHTING RETRIEVAL\n";
echo str_repeat("-", 50) . "\n";

// Test 5.1: Get specific weighting
echo "\nTest 5.1: Get specific weighting by subject ID\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getWeighting($testData['subjectId1']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && 
        $response['data']['subject_id'] == $testData['subjectId1']) {
        pass("Retrieved specific weighting successfully");
    } else {
        fail("Failed to retrieve specific weighting", $output);
    }
} catch (Exception $e) {
    fail("Get specific weighting exception", $e->getMessage());
}

// Test 5.2: Get all weightings
echo "\nTest 5.2: Get all weightings\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getAllWeightings();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data']) && count($response['data']) >= 2) {
        pass("Retrieved all weightings successfully");
    } else {
        fail("Failed to retrieve all weightings", $output);
    }
} catch (Exception $e) {
    fail("Get all weightings exception", $e->getMessage());
}

// Test 5.3: Get weighting for non-existent subject
echo "\nTest 5.3: Get weighting for non-existent subject\n";
try {
    $weightingController = new SubjectWeightingController();
    ob_start();
    $weightingController->getWeighting(99999);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Correctly handled non-existent subject");
    } else {
        fail("Should return error for non-existent subject", $output);
    }
} catch (Exception $e) {
    fail("Non-existent subject exception", $e->getMessage());
}

// ============================================================================
// CLEANUP
// ============================================================================
echo "\n\nCLEANUP\n";
echo str_repeat("-", 50) . "\n";

// Clean up test data
echo "\nCleaning up test data\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Delete test subjects (weightings will cascade delete)
    $stmt = $conn->prepare("DELETE FROM subjects WHERE name LIKE 'Test Subject%'");
    $stmt->execute();
    
    echo "✓ Test subjects and weightings cleaned up\n";
} catch (Exception $e) {
    echo "✗ Cleanup exception: " . $e->getMessage() . "\n";
}

// ============================================================================
// TEST SUMMARY
// ============================================================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n\n";

if ($testsFailed === 0) {
    echo "✓ ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED\n";
    exit(1);
}
