<?php

/**
 * Integration Tests for Term Management
 * 
 * Tests term management functionality:
 * - Create term with valid data
 * - Duplicate term prevention
 * - Update term
 * - Delete term
 * - Admin-only access restrictions
 * - Get all terms
 * - Get active terms
 * 
 * Run: php backend/test_term_management.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/TermController.php';
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

echo "=== TERM MANAGEMENT - INTEGRATION TESTS ===\n\n";

// ============================================================================
// SETUP: Login as admin
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
    } else {
        echo "✗ Failed to create test user: " . $output . "\n";
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
    } else {
        echo "✗ User login failed: " . $output . "\n";
    }
} catch (Exception $e) {
    echo "✗ User login exception: " . $e->getMessage() . "\n";
}

// Reset to admin token
$GLOBALS['TEST_AUTH_TOKEN'] = $testData['adminToken'];

// ============================================================================
// SECTION 1: TERM CREATION
// ============================================================================
echo "\n\nSECTION 1: TERM CREATION\n";
echo str_repeat("-", 50) . "\n";

// Test 1.1: Create term with valid data
echo "\nTest 1.1: Create term with valid data\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->createTerm([
        'name' => 'Term 1',
        'academic_year' => '2024/2025',
        'start_date' => '2024-09-01',
        'end_date' => '2024-12-15',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['id'])) {
        pass("Term created successfully");
        $testData['termId'] = $response['data']['id'];
    } else {
        fail("Term creation failed", $output);
    }
} catch (Exception $e) {
    fail("Term creation exception", $e->getMessage());
}

// Test 1.2: Duplicate term prevention
echo "\nTest 1.2: Prevent duplicate term creation\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->createTerm([
        'name' => 'Term 1',
        'academic_year' => '2024/2025',
        'start_date' => '2024-09-01',
        'end_date' => '2024-12-15',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'already exists') !== false) {
        pass("Duplicate term correctly rejected");
    } else {
        fail("Should reject duplicate term", $output);
    }
} catch (Exception $e) {
    fail("Duplicate term exception", $e->getMessage());
}

// Test 1.3: Invalid academic year format
echo "\nTest 1.3: Reject invalid academic year format\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->createTerm([
        'name' => 'Term 2',
        'academic_year' => '2024-2025',
        'start_date' => '2025-01-01',
        'end_date' => '2025-04-15',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'format') !== false) {
        pass("Invalid academic year format correctly rejected");
    } else {
        fail("Should reject invalid academic year format", $output);
    }
} catch (Exception $e) {
    fail("Invalid academic year exception", $e->getMessage());
}

// Test 1.4: Create another valid term
echo "\nTest 1.4: Create second term with different name\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->createTerm([
        'name' => 'Term 2',
        'academic_year' => '2024/2025',
        'start_date' => '2025-01-01',
        'end_date' => '2025-04-15',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['id'])) {
        pass("Second term created successfully");
        $testData['termId2'] = $response['data']['id'];
    } else {
        fail("Second term creation failed", $output);
    }
} catch (Exception $e) {
    fail("Second term creation exception", $e->getMessage());
}

// ============================================================================
// SECTION 2: TERM RETRIEVAL
// ============================================================================
echo "\n\nSECTION 2: TERM RETRIEVAL\n";
echo str_repeat("-", 50) . "\n";

// Test 2.1: Get all terms
echo "\nTest 2.1: Get all terms\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->getAllTerms();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data']) && count($response['data']) >= 2) {
        pass("Retrieved all terms successfully");
    } else {
        fail("Failed to retrieve all terms", $output);
    }
} catch (Exception $e) {
    fail("Get all terms exception", $e->getMessage());
}

// Test 2.2: Get specific term
echo "\nTest 2.2: Get specific term by ID\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->getTerm($testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && $response['data']['id'] == $testData['termId']) {
        pass("Retrieved specific term successfully");
    } else {
        fail("Failed to retrieve specific term", $output);
    }
} catch (Exception $e) {
    fail("Get specific term exception", $e->getMessage());
}

// Test 2.3: Get active terms
echo "\nTest 2.3: Get active terms\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->getActiveTerms();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Retrieved active terms successfully");
    } else {
        fail("Failed to retrieve active terms", $output);
    }
} catch (Exception $e) {
    fail("Get active terms exception", $e->getMessage());
}

// ============================================================================
// SECTION 3: TERM UPDATE
// ============================================================================
echo "\n\nSECTION 3: TERM UPDATE\n";
echo str_repeat("-", 50) . "\n";

// Test 3.1: Update term
echo "\nTest 3.1: Update term successfully\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->updateTerm($testData['termId'], [
        'name' => 'Term 1 Updated',
        'academic_year' => '2024/2025',
        'start_date' => '2024-09-01',
        'end_date' => '2024-12-20',
        'is_active' => false
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && $response['data']['name'] === 'Term 1 Updated') {
        pass("Term updated successfully");
    } else {
        fail("Term update failed", $output);
    }
} catch (Exception $e) {
    fail("Term update exception", $e->getMessage());
}

// Test 3.2: Prevent duplicate on update
echo "\nTest 3.2: Prevent duplicate term on update\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->updateTerm($testData['termId'], [
        'name' => 'Term 2',
        'academic_year' => '2024/2025',
        'start_date' => '2024-09-01',
        'end_date' => '2024-12-20',
        'is_active' => false
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'already exists') !== false) {
        pass("Duplicate term on update correctly rejected");
    } else {
        fail("Should reject duplicate term on update", $output);
    }
} catch (Exception $e) {
    fail("Duplicate term update exception", $e->getMessage());
}

// ============================================================================
// SECTION 4: ADMIN-ONLY ACCESS
// ============================================================================
echo "\n\nSECTION 4: ADMIN-ONLY ACCESS RESTRICTIONS\n";
echo str_repeat("-", 50) . "\n";

// Test 4.1: Non-admin cannot create term
echo "\nTest 4.1: Non-admin user cannot create term\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $termController = new TermController();
    ob_start();
    $termController->createTerm([
        'name' => 'Term 3',
        'academic_year' => '2024/2025',
        'start_date' => '2025-05-01',
        'end_date' => '2025-08-15',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied term creation");
    } else {
        fail("Should deny non-admin term creation", $output);
    }
} catch (Exception $e) {
    fail("Non-admin create term exception", $e->getMessage());
}

// Test 4.2: Non-admin cannot update term
echo "\nTest 4.2: Non-admin user cannot update term\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $termController = new TermController();
    ob_start();
    $termController->updateTerm($testData['termId'], [
        'name' => 'Hacked Term',
        'academic_year' => '2024/2025',
        'start_date' => '2024-09-01',
        'end_date' => '2024-12-20',
        'is_active' => true
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied term update");
    } else {
        fail("Should deny non-admin term update", $output);
    }
} catch (Exception $e) {
    fail("Non-admin update term exception", $e->getMessage());
}

// Test 4.3: Non-admin cannot delete term
echo "\nTest 4.3: Non-admin user cannot delete term\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $termController = new TermController();
    ob_start();
    $termController->deleteTerm($testData['termId2']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'admin') !== false) {
        pass("Non-admin correctly denied term deletion");
    } else {
        fail("Should deny non-admin term deletion", $output);
    }
} catch (Exception $e) {
    fail("Non-admin delete term exception", $e->getMessage());
}

// Test 4.4: Non-admin can view terms
echo "\nTest 4.4: Non-admin user can view terms\n";
try {
    $GLOBALS['TEST_AUTH_TOKEN'] = $testData['userToken'];
    $termController = new TermController();
    ob_start();
    $termController->getAllTerms();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Non-admin can view terms");
    } else {
        fail("Non-admin should be able to view terms", $output);
    }
} catch (Exception $e) {
    fail("Non-admin view terms exception", $e->getMessage());
}

// Reset to admin token
$GLOBALS['TEST_AUTH_TOKEN'] = $testData['adminToken'];

// ============================================================================
// SECTION 5: TERM DELETION
// ============================================================================
echo "\n\nSECTION 5: TERM DELETION\n";
echo str_repeat("-", 50) . "\n";

// Test 5.1: Delete term
echo "\nTest 5.1: Delete term successfully\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->deleteTerm($testData['termId2']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Term deleted successfully");
    } else {
        fail("Term deletion failed", $output);
    }
} catch (Exception $e) {
    fail("Term deletion exception", $e->getMessage());
}

// Test 5.2: Verify term is deleted
echo "\nTest 5.2: Verify deleted term cannot be retrieved\n";
try {
    $termController = new TermController();
    ob_start();
    $termController->getTerm($testData['termId2']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Deleted term correctly not found");
    } else {
        fail("Deleted term should not be retrievable", $output);
    }
} catch (Exception $e) {
    fail("Verify deleted term exception", $e->getMessage());
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
    
    // Delete test terms
    $stmt = $conn->prepare("DELETE FROM terms WHERE name LIKE '%Term%' AND academic_year = '2024/2025'");
    $stmt->execute();
    
    echo "✓ Test terms cleaned up\n";
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
