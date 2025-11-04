<?php

/**
 * Integration Tests for Assessment Management
 * 
 * Tests assessment management functionality:
 * - Create assessment with valid marks
 * - CA mark validation against weighting
 * - Exam mark validation against weighting
 * - Final mark calculation
 * - Assessment update and recalculation
 * - Get student term assessments
 * - Get class term assessments
 * - Delete assessment
 * 
 * Run: php backend/test_assessment_management.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AssessmentController.php';
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

echo "=== ASSESSMENT MANAGEMENT - INTEGRATION TESTS ===\n\n";

// ============================================================================
// SETUP: Login and prepare test data
// ============================================================================
echo "SETUP: Logging in and preparing test data\n";
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

// Get test data from database
echo "\nFetching test data from database\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get a student
    $stmt = $conn->query("SELECT student_id FROM students LIMIT 1");
    $student = $stmt->fetch();
    if ($student) {
        $testData['studentId'] = $student['student_id'];
        echo "✓ Test student: " . $testData['studentId'] . "\n";
    } else {
        echo "✗ No students found in database\n";
        exit(1);
    }
    
    // Get another student for class tests
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id != :student_id LIMIT 1");
    $stmt->bindParam(':student_id', $testData['studentId']);
    $stmt->execute();
    $student2 = $stmt->fetch();
    if ($student2) {
        $testData['studentId2'] = $student2['student_id'];
        echo "✓ Second test student: " . $testData['studentId2'] . "\n";
    }
    
    // Get class level
    $stmt = $conn->prepare("SELECT class_level_id FROM students WHERE student_id = :student_id LIMIT 1");
    $stmt->bindParam(':student_id', $testData['studentId']);
    $stmt->execute();
    $classLevel = $stmt->fetch();
    if ($classLevel) {
        $testData['classLevelId'] = $classLevel['class_level_id'];
        echo "✓ Test class level: " . $testData['classLevelId'] . "\n";
    }
    
    // Get a subject
    $stmt = $conn->query("SELECT id FROM subjects LIMIT 1");
    $subject = $stmt->fetch();
    if ($subject) {
        $testData['subjectId'] = $subject['id'];
        echo "✓ Test subject: " . $testData['subjectId'] . "\n";
    } else {
        echo "✗ No subjects found in database\n";
        exit(1);
    }
    
    // Get another subject
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE id != :subject_id LIMIT 1");
    $stmt->bindParam(':subject_id', $testData['subjectId']);
    $stmt->execute();
    $subject2 = $stmt->fetch();
    if ($subject2) {
        $testData['subjectId2'] = $subject2['id'];
        echo "✓ Second test subject: " . $testData['subjectId2'] . "\n";
    }
    
    // Get an active term or any term
    $stmt = $conn->query("SELECT id FROM terms WHERE is_active = 1 LIMIT 1");
    $term = $stmt->fetch();
    if (!$term) {
        // Try to get any term
        $stmt = $conn->query("SELECT id FROM terms LIMIT 1");
        $term = $stmt->fetch();
    }
    if ($term) {
        $testData['termId'] = $term['id'];
        echo "✓ Test term: " . $testData['termId'] . "\n";
    } else {
        echo "✗ No terms found in database\n";
        exit(1);
    }
    
    // Get subject weighting
    $stmt = $conn->prepare("SELECT COALESCE(sw.ca_percentage, 40.00) as ca_percentage, 
                                   COALESCE(sw.exam_percentage, 60.00) as exam_percentage
                           FROM subjects s
                           LEFT JOIN subject_weightings sw ON s.id = sw.subject_id
                           WHERE s.id = :subject_id");
    $stmt->bindParam(':subject_id', $testData['subjectId']);
    $stmt->execute();
    $weighting = $stmt->fetch();
    if ($weighting) {
        $testData['caPercentage'] = floatval($weighting['ca_percentage']);
        $testData['examPercentage'] = floatval($weighting['exam_percentage']);
        echo "✓ Subject weighting: CA {$testData['caPercentage']}%, Exam {$testData['examPercentage']}%\n";
    }
    
} catch (Exception $e) {
    echo "✗ Setup exception: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================================
// SECTION 1: ASSESSMENT CREATION
// ============================================================================
echo "\n\nSECTION 1: ASSESSMENT CREATION\n";
echo str_repeat("-", 50) . "\n";

// Test 1.1: Create assessment with valid marks
echo "\nTest 1.1: Create assessment with valid CA and exam marks\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => 35.5,
        'exam_mark' => 52.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['id'])) {
        $testData['assessmentId'] = $response['data']['id'];
        $finalMark = $response['data']['final_mark'];
        if (abs($finalMark - 87.5) < 0.01) {
            pass("Assessment created with correct final mark calculation (87.5)");
        } else {
            fail("Assessment created but final mark incorrect", "Expected 87.5, got $finalMark");
        }
    } else {
        fail("Assessment creation failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment creation exception", $e->getMessage());
}

// Test 1.2: Create assessment with only CA mark
echo "\nTest 1.2: Create assessment with only CA mark\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId'],
        'subject_id' => $testData['subjectId2'],
        'term_id' => $testData['termId'],
        'ca_mark' => 30.0,
        'exam_mark' => null
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && isset($response['data']['id'])) {
        $testData['assessmentId2'] = $response['data']['id'];
        pass("Assessment created with only CA mark");
    } else {
        fail("Assessment with only CA mark failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment with only CA mark exception", $e->getMessage());
}

// Test 1.3: Reject assessment with no marks
echo "\nTest 1.3: Reject assessment with no marks provided\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId2'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => null,
        'exam_mark' => null
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'At least one mark') !== false) {
        pass("Assessment with no marks correctly rejected");
    } else {
        fail("Should reject assessment with no marks", $output);
    }
} catch (Exception $e) {
    fail("Assessment with no marks exception", $e->getMessage());
}

// ============================================================================
// SECTION 2: MARK VALIDATION
// ============================================================================
echo "\n\nSECTION 2: MARK VALIDATION\n";
echo str_repeat("-", 50) . "\n";

// Test 2.1: Reject CA mark exceeding weighting
echo "\nTest 2.1: Reject CA mark exceeding subject weighting\n";
try {
    $assessmentController = new AssessmentController();
    $invalidCaMark = $testData['caPercentage'] + 5;
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId2'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => $invalidCaMark,
        'exam_mark' => 50.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'CA mark cannot exceed') !== false) {
        pass("CA mark exceeding weighting correctly rejected");
    } else {
        fail("Should reject CA mark exceeding weighting", $output);
    }
} catch (Exception $e) {
    fail("CA mark validation exception", $e->getMessage());
}

// Test 2.2: Reject exam mark exceeding weighting
echo "\nTest 2.2: Reject exam mark exceeding subject weighting\n";
try {
    $assessmentController = new AssessmentController();
    $invalidExamMark = $testData['examPercentage'] + 5;
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId2'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => 30.0,
        'exam_mark' => $invalidExamMark
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'Exam mark cannot exceed') !== false) {
        pass("Exam mark exceeding weighting correctly rejected");
    } else {
        fail("Should reject exam mark exceeding weighting", $output);
    }
} catch (Exception $e) {
    fail("Exam mark validation exception", $e->getMessage());
}

// Test 2.3: Reject negative CA mark
echo "\nTest 2.3: Reject negative CA mark\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId2'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => -5.0,
        'exam_mark' => 50.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'negative') !== false) {
        pass("Negative CA mark correctly rejected");
    } else {
        fail("Should reject negative CA mark", $output);
    }
} catch (Exception $e) {
    fail("Negative CA mark exception", $e->getMessage());
}

// Test 2.4: Accept CA mark at maximum weighting
echo "\nTest 2.4: Accept CA mark at maximum weighting\n";
try {
    if (isset($testData['studentId2'])) {
        $assessmentController = new AssessmentController();
        ob_start();
        $assessmentController->createOrUpdateAssessment([
            'student_id' => $testData['studentId2'],
            'subject_id' => $testData['subjectId'],
            'term_id' => $testData['termId'],
            'ca_mark' => $testData['caPercentage'],
            'exam_mark' => $testData['examPercentage']
        ]);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && $response['success'] === true) {
            $testData['assessmentId3'] = $response['data']['id'];
            pass("CA mark at maximum weighting accepted");
        } else {
            fail("Should accept CA mark at maximum weighting", $output);
        }
    } else {
        echo "⊘ SKIP: No second student available\n";
    }
} catch (Exception $e) {
    fail("CA mark at maximum exception", $e->getMessage());
}

// ============================================================================
// SECTION 3: ASSESSMENT UPDATE
// ============================================================================
echo "\n\nSECTION 3: ASSESSMENT UPDATE\n";
echo str_repeat("-", 50) . "\n";

// Test 3.1: Update assessment marks
echo "\nTest 3.1: Update assessment marks\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->updateAssessment($testData['assessmentId'], [
        'ca_mark' => 38.0,
        'exam_mark' => 55.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $finalMark = $response['data']['final_mark'];
        if (abs($finalMark - 93.0) < 0.01) {
            pass("Assessment updated with recalculated final mark (93.0)");
        } else {
            fail("Assessment updated but final mark incorrect", "Expected 93.0, got $finalMark");
        }
    } else {
        fail("Assessment update failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment update exception", $e->getMessage());
}

// Test 3.2: Update using createOrUpdate (upsert)
echo "\nTest 3.2: Update existing assessment using createOrUpdate\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->createOrUpdateAssessment([
        'student_id' => $testData['studentId'],
        'subject_id' => $testData['subjectId'],
        'term_id' => $testData['termId'],
        'ca_mark' => 36.0,
        'exam_mark' => 58.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $finalMark = $response['data']['final_mark'];
        if (abs($finalMark - 94.0) < 0.01) {
            pass("Assessment upserted with recalculated final mark (94.0)");
        } else {
            fail("Assessment upserted but final mark incorrect", "Expected 94.0, got $finalMark");
        }
    } else {
        fail("Assessment upsert failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment upsert exception", $e->getMessage());
}

// Test 3.3: Update with invalid mark
echo "\nTest 3.3: Reject update with invalid mark\n";
try {
    $assessmentController = new AssessmentController();
    $invalidCaMark = $testData['caPercentage'] + 10;
    ob_start();
    $assessmentController->updateAssessment($testData['assessmentId'], [
        'ca_mark' => $invalidCaMark,
        'exam_mark' => 50.0
    ]);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'cannot exceed') !== false) {
        pass("Update with invalid mark correctly rejected");
    } else {
        fail("Should reject update with invalid mark", $output);
    }
} catch (Exception $e) {
    fail("Update with invalid mark exception", $e->getMessage());
}

// ============================================================================
// SECTION 4: ASSESSMENT RETRIEVAL
// ============================================================================
echo "\n\nSECTION 4: ASSESSMENT RETRIEVAL\n";
echo str_repeat("-", 50) . "\n";

// Test 4.1: Get student term assessments
echo "\nTest 4.1: Get all assessments for a student in a term\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->getStudentTermAssessments($testData['studentId'], $testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data']) && count($response['data']) >= 2) {
        pass("Retrieved student term assessments successfully");
    } else {
        fail("Failed to retrieve student term assessments", $output);
    }
} catch (Exception $e) {
    fail("Get student term assessments exception", $e->getMessage());
}

// Test 4.2: Get class term assessments
echo "\nTest 4.2: Get all assessments for a class in a term\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->getClassTermAssessments($testData['termId'], $testData['classLevelId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true && is_array($response['data'])) {
        pass("Retrieved class term assessments successfully");
    } else {
        fail("Failed to retrieve class term assessments", $output);
    }
} catch (Exception $e) {
    fail("Get class term assessments exception", $e->getMessage());
}

// ============================================================================
// SECTION 5: ASSESSMENT DELETION
// ============================================================================
echo "\n\nSECTION 5: ASSESSMENT DELETION\n";
echo str_repeat("-", 50) . "\n";

// Test 5.1: Delete assessment
echo "\nTest 5.1: Delete assessment successfully\n";
try {
    $assessmentController = new AssessmentController();
    ob_start();
    $assessmentController->deleteAssessment($testData['assessmentId2']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        pass("Assessment deleted successfully");
    } else {
        fail("Assessment deletion failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment deletion exception", $e->getMessage());
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
    
    // Delete test assessments
    $stmt = $conn->prepare("DELETE FROM term_assessments WHERE student_id = :student_id OR student_id = :student_id2");
    $stmt->bindParam(':student_id', $testData['studentId']);
    $stmt->bindParam(':student_id2', $testData['studentId2']);
    $stmt->execute();
    
    echo "✓ Test assessments cleaned up\n";
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
