<?php

/**
 * Integration Tests for Report Generation
 * 
 * Tests report generation functionality:
 * - Student report generation with complete data
 * - Student report with partial data
 * - Class report generation
 * - Assessment summary calculations
 * - Term average calculation
 * 
 * Run: php backend/test_report_generation.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ReportController.php';
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

echo "=== REPORT GENERATION - INTEGRATION TESTS ===\n\n";

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

// Get test data from database and create test assessments
echo "\nFetching test data and creating test assessments\n";
try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get a student
    $stmt = $conn->query("SELECT student_id, class_level_id FROM students LIMIT 1");
    $student = $stmt->fetch();
    if ($student) {
        $testData['studentId'] = $student['student_id'];
        $testData['classLevelId'] = $student['class_level_id'];
        echo "✓ Test student: " . $testData['studentId'] . "\n";
    } else {
        echo "✗ No students found in database\n";
        exit(1);
    }
    
    // Get another student in the same class
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id != :student_id AND class_level_id = :class_level_id LIMIT 1");
    $stmt->bindParam(':student_id', $testData['studentId']);
    $stmt->bindParam(':class_level_id', $testData['classLevelId']);
    $stmt->execute();
    $student2 = $stmt->fetch();
    if ($student2) {
        $testData['studentId2'] = $student2['student_id'];
        echo "✓ Second test student: " . $testData['studentId2'] . "\n";
    }
    
    // Get subjects
    $stmt = $conn->query("SELECT id FROM subjects ORDER BY id LIMIT 3");
    $subjects = $stmt->fetchAll();
    if (count($subjects) >= 2) {
        $testData['subjectId1'] = $subjects[0]['id'];
        $testData['subjectId2'] = $subjects[1]['id'];
        if (isset($subjects[2])) {
            $testData['subjectId3'] = $subjects[2]['id'];
        }
        echo "✓ Test subjects: " . $testData['subjectId1'] . ", " . $testData['subjectId2'] . "\n";
    } else {
        echo "✗ Not enough subjects found in database\n";
        exit(1);
    }
    
    // Get a term
    $stmt = $conn->query("SELECT id FROM terms LIMIT 1");
    $term = $stmt->fetch();
    if ($term) {
        $testData['termId'] = $term['id'];
        echo "✓ Test term: " . $testData['termId'] . "\n";
    } else {
        echo "✗ No terms found in database\n";
        exit(1);
    }
    
    // Clean up any existing test assessments
    $stmt = $conn->prepare("DELETE FROM term_assessments WHERE student_id = :student_id OR student_id = :student_id2");
    $stmt->bindParam(':student_id', $testData['studentId']);
    $stmt->bindParam(':student_id2', $testData['studentId2']);
    $stmt->execute();
    
    // Create test assessments for student 1 - complete data
    $stmt = $conn->prepare("INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark) 
                           VALUES (:student_id, :subject_id, :term_id, :ca_mark, :exam_mark)");
    
    // Subject 1: Complete assessment
    $stmt->execute([
        ':student_id' => $testData['studentId'],
        ':subject_id' => $testData['subjectId1'],
        ':term_id' => $testData['termId'],
        ':ca_mark' => 35.0,
        ':exam_mark' => 55.0
    ]);
    
    // Subject 2: Complete assessment
    $stmt->execute([
        ':student_id' => $testData['studentId'],
        ':subject_id' => $testData['subjectId2'],
        ':term_id' => $testData['termId'],
        ':ca_mark' => 38.0,
        ':exam_mark' => 52.0
    ]);
    
    echo "✓ Created complete test assessments for student 1\n";
    
    // Create test assessments for student 2 if available
    if (isset($testData['studentId2'])) {
        // Subject 1: Complete assessment
        $stmt->execute([
            ':student_id' => $testData['studentId2'],
            ':subject_id' => $testData['subjectId1'],
            ':term_id' => $testData['termId'],
            ':ca_mark' => 30.0,
            ':exam_mark' => 50.0
        ]);
        
        // Subject 2: Partial assessment (only CA)
        $stmt = $conn->prepare("INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark) 
                               VALUES (:student_id, :subject_id, :term_id, :ca_mark, NULL)");
        $stmt->execute([
            ':student_id' => $testData['studentId2'],
            ':subject_id' => $testData['subjectId2'],
            ':term_id' => $testData['termId'],
            ':ca_mark' => 35.0
        ]);
        
        echo "✓ Created test assessments for student 2 (complete and partial)\n";
    }
    
    // Create a student with partial data for testing
    if (isset($testData['subjectId3'])) {
        $stmt = $conn->prepare("INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark) 
                               VALUES (:student_id, :subject_id, :term_id, :ca_mark, NULL)");
        $stmt->execute([
            ':student_id' => $testData['studentId'],
            ':subject_id' => $testData['subjectId3'],
            ':term_id' => $testData['termId'],
            ':ca_mark' => 32.0
        ]);
        echo "✓ Created partial assessment for testing\n";
    }
    
} catch (Exception $e) {
    echo "✗ Setup exception: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================================
// SECTION 1: STUDENT REPORT GENERATION
// ============================================================================
echo "\n\nSECTION 1: STUDENT REPORT GENERATION\n";
echo str_repeat("-", 50) . "\n";

// Test 1.1: Generate student report with complete data
echo "\nTest 1.1: Generate student report with complete assessment data\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getStudentTermReport($testData['studentId'], $testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $data = $response['data'];
        
        // Verify structure
        if (isset($data['student']) && isset($data['term']) && isset($data['assessments']) && isset($data['term_average'])) {
            // Verify student info
            if ($data['student']['student_id'] === $testData['studentId']) {
                pass("Student report generated with correct structure and student info");
            } else {
                fail("Student report has incorrect student ID");
            }
            
            // Verify assessments
            if (count($data['assessments']) >= 2) {
                pass("Student report includes multiple assessments");
            } else {
                fail("Student report should include at least 2 assessments", "Found: " . count($data['assessments']));
            }
            
            // Verify term average calculation
            if ($data['term_average'] !== null && is_numeric($data['term_average'])) {
                pass("Term average calculated: " . $data['term_average']);
            } else {
                fail("Term average not calculated correctly");
            }
        } else {
            fail("Student report missing required fields", $output);
        }
    } else {
        fail("Student report generation failed", $output);
    }
} catch (Exception $e) {
    fail("Student report generation exception", $e->getMessage());
}

// Test 1.2: Generate student report with partial data
echo "\nTest 1.2: Generate student report with partial assessment data\n";
try {
    if (isset($testData['studentId2'])) {
        $reportController = new ReportController();
        ob_start();
        $reportController->getStudentTermReport($testData['studentId2'], $testData['termId']);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && $response['success'] === true) {
            $data = $response['data'];
            
            // Check for partial assessments
            $hasPartial = false;
            foreach ($data['assessments'] as $assessment) {
                if ($assessment['ca_mark'] !== null && $assessment['exam_mark'] === null) {
                    $hasPartial = true;
                    break;
                }
            }
            
            if ($hasPartial) {
                pass("Student report handles partial assessment data correctly");
            } else {
                fail("Student report should include partial assessment");
            }
        } else {
            fail("Student report with partial data failed", $output);
        }
    } else {
        echo "⊘ SKIP: No second student available\n";
    }
} catch (Exception $e) {
    fail("Student report with partial data exception", $e->getMessage());
}

// Test 1.3: Handle student with no assessments
echo "\nTest 1.3: Handle student with no assessments\n";
try {
    // Get a student without assessments
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT s.student_id FROM students s 
                           LEFT JOIN term_assessments ta ON s.student_id = ta.student_id AND ta.term_id = :term_id
                           WHERE ta.id IS NULL LIMIT 1");
    $stmt->bindParam(':term_id', $testData['termId']);
    $stmt->execute();
    $emptyStudent = $stmt->fetch();
    
    if ($emptyStudent) {
        $reportController = new ReportController();
        ob_start();
        $reportController->getStudentTermReport($emptyStudent['student_id'], $testData['termId']);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        if ($response && $response['success'] === true) {
            $data = $response['data'];
            if (count($data['assessments']) === 0 && $data['term_average'] === null) {
                pass("Student report handles no assessments correctly");
            } else {
                fail("Student report should show empty assessments and null average");
            }
        } else {
            fail("Student report with no assessments failed", $output);
        }
    } else {
        echo "⊘ SKIP: All students have assessments\n";
    }
} catch (Exception $e) {
    fail("Student report with no assessments exception", $e->getMessage());
}

// Test 1.4: Handle invalid student ID
echo "\nTest 1.4: Handle invalid student ID\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getStudentTermReport('INVALID_STUDENT_ID', $testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Invalid student ID correctly rejected");
    } else {
        fail("Should reject invalid student ID", $output);
    }
} catch (Exception $e) {
    fail("Invalid student ID exception", $e->getMessage());
}

// ============================================================================
// SECTION 2: CLASS REPORT GENERATION
// ============================================================================
echo "\n\nSECTION 2: CLASS REPORT GENERATION\n";
echo str_repeat("-", 50) . "\n";

// Test 2.1: Generate class reports
echo "\nTest 2.1: Generate reports for all students in a class\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getClassTermReports($testData['classLevelId'], $testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $data = $response['data'];
        
        // Verify structure
        if (isset($data['class_level']) && isset($data['term']) && isset($data['reports'])) {
            pass("Class reports generated with correct structure");
            
            // Verify reports array
            if (is_array($data['reports']) && count($data['reports']) > 0) {
                pass("Class reports include multiple students: " . count($data['reports']));
                
                // Verify each report has required fields
                $firstReport = $data['reports'][0];
                if (isset($firstReport['student']) && isset($firstReport['term_average']) && isset($firstReport['assessments'])) {
                    pass("Individual reports in class have correct structure");
                } else {
                    fail("Individual reports missing required fields");
                }
            } else {
                fail("Class reports should include student reports");
            }
        } else {
            fail("Class reports missing required fields", $output);
        }
    } else {
        fail("Class report generation failed", $output);
    }
} catch (Exception $e) {
    fail("Class report generation exception", $e->getMessage());
}

// Test 2.2: Handle invalid class level ID
echo "\nTest 2.2: Handle invalid class level ID\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getClassTermReports(99999, $testData['termId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
        pass("Invalid class level ID correctly rejected");
    } else {
        fail("Should reject invalid class level ID", $output);
    }
} catch (Exception $e) {
    fail("Invalid class level ID exception", $e->getMessage());
}

// ============================================================================
// SECTION 3: ASSESSMENT SUMMARY
// ============================================================================
echo "\n\nSECTION 3: ASSESSMENT SUMMARY\n";
echo str_repeat("-", 50) . "\n";

// Test 3.1: Generate assessment summary
echo "\nTest 3.1: Generate assessment summary for term and class\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getAssessmentSummary($testData['termId'], $testData['classLevelId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $data = $response['data'];
        
        // Verify structure
        if (isset($data['summary']) && isset($data['grid'])) {
            pass("Assessment summary generated with correct structure");
            
            // Verify summary statistics
            $summary = $data['summary'];
            if (isset($summary['total_students']) && isset($summary['total_subjects']) && 
                isset($summary['complete_assessments']) && isset($summary['partial_assessments']) && 
                isset($summary['missing_assessments']) && isset($summary['completion_percentage'])) {
                pass("Summary statistics include all required fields");
                
                // Verify calculations
                $total = $summary['complete_assessments'] + $summary['partial_assessments'] + $summary['missing_assessments'];
                $expected = $summary['total_possible_assessments'];
                if ($total === $expected) {
                    pass("Assessment counts sum correctly");
                } else {
                    fail("Assessment counts don't sum correctly", "Total: $total, Expected: $expected");
                }
            } else {
                fail("Summary statistics missing required fields");
            }
            
            // Verify grid structure
            if (is_array($data['grid']) && count($data['grid']) > 0) {
                $firstStudent = $data['grid'][0];
                if (isset($firstStudent['student_id']) && isset($firstStudent['subjects'])) {
                    pass("Grid structure is correct");
                    
                    // Verify subject status
                    if (is_array($firstStudent['subjects']) && count($firstStudent['subjects']) > 0) {
                        $firstSubject = $firstStudent['subjects'][0];
                        if (isset($firstSubject['status']) && isset($firstSubject['has_ca']) && isset($firstSubject['has_exam'])) {
                            pass("Subject status includes completion indicators");
                        } else {
                            fail("Subject status missing required fields");
                        }
                    }
                } else {
                    fail("Grid student data missing required fields");
                }
            } else {
                fail("Grid should include student data");
            }
        } else {
            fail("Assessment summary missing required fields", $output);
        }
    } else {
        fail("Assessment summary generation failed", $output);
    }
} catch (Exception $e) {
    fail("Assessment summary generation exception", $e->getMessage());
}

// Test 3.2: Verify status indicators
echo "\nTest 3.2: Verify assessment status indicators (complete/partial/missing)\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->getAssessmentSummary($testData['termId'], $testData['classLevelId']);
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success'] === true) {
        $data = $response['data'];
        
        $foundComplete = false;
        $foundPartial = false;
        $foundMissing = false;
        
        foreach ($data['grid'] as $student) {
            foreach ($student['subjects'] as $subject) {
                if ($subject['status'] === 'complete') $foundComplete = true;
                if ($subject['status'] === 'partial') $foundPartial = true;
                if ($subject['status'] === 'missing') $foundMissing = true;
            }
        }
        
        if ($foundComplete) {
            pass("Found 'complete' status in summary");
        }
        if ($foundPartial || $foundMissing) {
            pass("Found 'partial' or 'missing' status in summary");
        }
    }
} catch (Exception $e) {
    fail("Status indicators verification exception", $e->getMessage());
}

// ============================================================================
// SECTION 4: TERM AVERAGE CALCULATION
// ============================================================================
echo "\n\nSECTION 4: TERM AVERAGE CALCULATION\n";
echo str_repeat("-", 50) . "\n";

// Test 4.1: Calculate term average with complete data
echo "\nTest 4.1: Calculate term average with complete assessment data\n";
try {
    $reportController = new ReportController();
    
    // Create test assessments array
    $testAssessments = [
        ['final_mark' => 90.0],
        ['final_mark' => 85.0],
        ['final_mark' => 92.0]
    ];
    
    $average = $reportController->calculateTermAverage($testAssessments);
    $expectedAverage = 89.0; // (90 + 85 + 92) / 3
    
    if ($average !== null && abs($average - $expectedAverage) < 0.01) {
        pass("Term average calculated correctly: $average");
    } else {
        fail("Term average calculation incorrect", "Expected: $expectedAverage, Got: $average");
    }
} catch (Exception $e) {
    fail("Term average calculation exception", $e->getMessage());
}

// Test 4.2: Calculate term average with partial data
echo "\nTest 4.2: Calculate term average with partial assessment data\n";
try {
    $reportController = new ReportController();
    
    // Create test assessments array with some null final marks
    $testAssessments = [
        ['final_mark' => 90.0],
        ['final_mark' => null],
        ['final_mark' => 80.0]
    ];
    
    $average = $reportController->calculateTermAverage($testAssessments);
    $expectedAverage = 85.0; // (90 + 80) / 2
    
    if ($average !== null && abs($average - $expectedAverage) < 0.01) {
        pass("Term average with partial data calculated correctly: $average");
    } else {
        fail("Term average with partial data incorrect", "Expected: $expectedAverage, Got: $average");
    }
} catch (Exception $e) {
    fail("Term average with partial data exception", $e->getMessage());
}

// Test 4.3: Handle empty assessments
echo "\nTest 4.3: Handle empty assessments array\n";
try {
    $reportController = new ReportController();
    
    $average = $reportController->calculateTermAverage([]);
    
    if ($average === null) {
        pass("Empty assessments return null average");
    } else {
        fail("Empty assessments should return null", "Got: $average");
    }
} catch (Exception $e) {
    fail("Empty assessments exception", $e->getMessage());
}

// ============================================================================
// SECTION 5: PDF GENERATION
// ============================================================================
echo "\n\nSECTION 5: PDF GENERATION\n";
echo str_repeat("-", 50) . "\n";

// Test 5.1: Generate single student PDF
echo "\nTest 5.1: Generate PDF report for a single student\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->generateStudentPDF($testData['studentId'], $testData['termId']);
    $output = ob_get_clean();
    
    // Check if output is a PDF (starts with %PDF)
    if (substr($output, 0, 4) === '%PDF') {
        pass("Student PDF generated successfully");
        
        // Check PDF size is reasonable (should be at least 1KB)
        if (strlen($output) > 1000) {
            pass("PDF has reasonable size: " . strlen($output) . " bytes");
        } else {
            fail("PDF size too small", "Size: " . strlen($output) . " bytes");
        }
        
        // Verify PDF structure (contains common PDF elements)
        if (strpos($output, '/Type') !== false && strpos($output, 'endobj') !== false) {
            pass("PDF has valid structure");
        } else {
            fail("PDF structure appears invalid");
        }
    } else {
        fail("Student PDF generation failed - not a valid PDF", substr($output, 0, 100));
    }
} catch (Exception $e) {
    fail("Student PDF generation exception", $e->getMessage());
}

// Test 5.2: Generate PDF with partial assessment data
echo "\nTest 5.2: Generate PDF with partial assessment data\n";
try {
    if (isset($testData['studentId2'])) {
        $reportController = new ReportController();
        ob_start();
        $reportController->generateStudentPDF($testData['studentId2'], $testData['termId']);
        $output = ob_get_clean();
        
        if (substr($output, 0, 4) === '%PDF') {
            pass("PDF with partial data generated successfully");
        } else {
            fail("PDF with partial data generation failed");
        }
    } else {
        echo "⊘ SKIP: No second student available\n";
    }
} catch (Exception $e) {
    fail("PDF with partial data exception", $e->getMessage());
}

// Test 5.3: Generate PDF for student with no assessments
echo "\nTest 5.3: Generate PDF for student with no assessments\n";
try {
    // Get a student without assessments
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT s.student_id FROM students s 
                           LEFT JOIN term_assessments ta ON s.student_id = ta.student_id AND ta.term_id = :term_id
                           WHERE ta.id IS NULL LIMIT 1");
    $stmt->bindParam(':term_id', $testData['termId']);
    $stmt->execute();
    $emptyStudent = $stmt->fetch();
    
    if ($emptyStudent) {
        $reportController = new ReportController();
        ob_start();
        $reportController->generateStudentPDF($emptyStudent['student_id'], $testData['termId']);
        $output = ob_get_clean();
        
        if (substr($output, 0, 4) === '%PDF') {
            pass("PDF for student with no assessments generated successfully");
        } else {
            fail("PDF for empty student generation failed");
        }
    } else {
        echo "⊘ SKIP: All students have assessments\n";
    }
} catch (Exception $e) {
    fail("PDF for empty student exception", $e->getMessage());
}

// Test 5.4: Test batch PDF generation (class)
echo "\nTest 5.4: Generate batch PDFs for a class\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->generateClassPDFBatch($testData['classLevelId'], $testData['termId']);
    $output = ob_get_clean();
    
    // Check if output is a ZIP file (starts with PK)
    if (substr($output, 0, 2) === 'PK') {
        pass("Class batch PDF (ZIP) generated successfully");
        
        // Check ZIP size is reasonable (should be at least 1KB)
        if (strlen($output) > 1000) {
            pass("ZIP file has reasonable size: " . strlen($output) . " bytes");
        } else {
            fail("ZIP file size too small", "Size: " . strlen($output) . " bytes");
        }
    } else {
        fail("Class batch PDF generation failed - not a valid ZIP", substr($output, 0, 100));
    }
} catch (Exception $e) {
    fail("Class batch PDF generation exception", $e->getMessage());
}

// Test 5.5: Test PDF content and formatting
echo "\nTest 5.5: Verify PDF content and formatting\n";
try {
    require_once __DIR__ . '/utils/PDFGenerator.php';
    
    // Create test data
    $testStudent = [
        'student_id' => 'TEST001',
        'name' => 'Test Student',
        'class_level_name' => 'Grade 10'
    ];
    
    $testTerm = [
        'id' => 1,
        'name' => 'Term 1',
        'academic_year' => '2024/2025'
    ];
    
    $testAssessments = [
        [
            'subject_name' => 'Mathematics',
            'ca_percentage' => 40.00,
            'exam_percentage' => 60.00,
            'ca_mark' => 35.50,
            'exam_mark' => 52.00,
            'final_mark' => 87.50
        ],
        [
            'subject_name' => 'English',
            'ca_percentage' => 40.00,
            'exam_percentage' => 60.00,
            'ca_mark' => 38.00,
            'exam_mark' => 55.00,
            'final_mark' => 93.00
        ]
    ];
    
    $testAverage = 90.25;
    
    // Generate PDF
    $pdfGenerator = new PDFGenerator();
    $pdfContent = $pdfGenerator->generateStudentReport($testStudent, $testTerm, $testAssessments, $testAverage);
    
    if (substr($pdfContent, 0, 4) === '%PDF') {
        pass("PDFGenerator class generates valid PDF");
        
        // Check for expected content in PDF
        if (strpos($pdfContent, 'TEST001') !== false) {
            pass("PDF contains student ID");
        }
        
        if (strpos($pdfContent, 'Mathematics') !== false || strpos($pdfContent, 'English') !== false) {
            pass("PDF contains subject names");
        }
        
        if (strpos($pdfContent, '90.25') !== false) {
            pass("PDF contains term average");
        }
    } else {
        fail("PDFGenerator class failed to generate valid PDF");
    }
} catch (Exception $e) {
    fail("PDF content verification exception", $e->getMessage());
}

// Test 5.6: Handle invalid student ID for PDF
echo "\nTest 5.6: Handle invalid student ID for PDF generation\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->generateStudentPDF('INVALID_STUDENT_ID', $testData['termId']);
    $output = ob_get_clean();
    
    // Should return JSON error, not PDF
    if (substr($output, 0, 4) !== '%PDF') {
        $response = json_decode($output, true);
        if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
            pass("Invalid student ID for PDF correctly rejected");
        } else {
            fail("Should reject invalid student ID for PDF", $output);
        }
    } else {
        fail("Should not generate PDF for invalid student");
    }
} catch (Exception $e) {
    fail("Invalid student ID for PDF exception", $e->getMessage());
}

// Test 5.7: Handle invalid class level ID for batch PDF
echo "\nTest 5.7: Handle invalid class level ID for batch PDF generation\n";
try {
    $reportController = new ReportController();
    ob_start();
    $reportController->generateClassPDFBatch(99999, $testData['termId']);
    $output = ob_get_clean();
    
    // Should return JSON error, not ZIP
    if (substr($output, 0, 2) !== 'PK') {
        $response = json_decode($output, true);
        if ($response && $response['success'] === false && strpos($response['error'], 'not found') !== false) {
            pass("Invalid class level ID for batch PDF correctly rejected");
        } else {
            fail("Should reject invalid class level ID for batch PDF", $output);
        }
    } else {
        fail("Should not generate ZIP for invalid class level");
    }
} catch (Exception $e) {
    fail("Invalid class level ID for batch PDF exception", $e->getMessage());
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
