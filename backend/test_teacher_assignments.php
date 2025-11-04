<?php

/**
 * Test script for Teacher Assignment API endpoints
 * 
 * This script tests the CRUD operations for teacher assignments
 */

require_once __DIR__ . '/config/database.php';

// ANSI color codes for output
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('RESET', "\033[0m");

class TeacherAssignmentTest {
    private $baseUrl = 'http://localhost/studentmgt/backend/api';
    private $adminToken = null;
    private $teacherToken = null;

    public function __construct() {
        echo "\n" . YELLOW . "=== Teacher Assignment API Tests ===" . RESET . "\n\n";
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($method, $endpoint, $data = null, $token = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        // Debug: print raw response if JSON decode fails
        if ($decoded === null && $response !== null && $response !== '') {
            echo "\n" . YELLOW . "DEBUG: Raw response: " . substr($response, 0, 200) . "..." . RESET . "\n";
        }
        
        return [
            'code' => $httpCode,
            'body' => $decoded
        ];
    }

    /**
     * Login as admin
     */
    public function loginAsAdmin() {
        echo "Test: Login as admin... ";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        
        if ($response['code'] === 200 && isset($response['body']['data']['token'])) {
            $this->adminToken = $response['body']['data']['token'];
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . "\n";
            return false;
        }
    }

    /**
     * Test: Assign teacher to class
     */
    public function testAssignClass() {
        echo "Test: Assign teacher to class... ";
        
        $response = $this->makeRequest('POST', '/teacher-assignments/class', [
            'user_id' => 2,
            'class_level_id' => 1
        ], $this->adminToken);
        
        if ($response['code'] === 201 && $response['body']['success'] === true) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return $response['body']['data']['id'];
        } elseif ($response['code'] === 409) {
            // Assignment already exists, that's okay for testing
            echo YELLOW . "⊘ SKIPPED" . RESET . " (Assignment already exists)\n";
            return null;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            if (isset($response['body']['error'])) {
                echo "  Error: " . $response['body']['error'] . "\n";
            }
            return null;
        }
    }

    /**
     * Test: Assign teacher to subject
     */
    public function testAssignSubject() {
        echo "Test: Assign teacher to subject... ";
        
        $response = $this->makeRequest('POST', '/teacher-assignments/subject', [
            'user_id' => 2,
            'subject_id' => 1
        ], $this->adminToken);
        
        if ($response['code'] === 201 && $response['body']['success'] === true) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return $response['body']['data']['id'];
        } elseif ($response['code'] === 409) {
            // Assignment already exists, that's okay for testing
            echo YELLOW . "⊘ SKIPPED" . RESET . " (Assignment already exists)\n";
            return null;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            if (isset($response['body']['error'])) {
                echo "  Error: " . $response['body']['error'] . "\n";
            }
            return null;
        }
    }

    /**
     * Test: Duplicate assignment prevention
     */
    public function testDuplicatePrevention() {
        echo "Test: Duplicate assignment prevention... ";
        
        $response = $this->makeRequest('POST', '/teacher-assignments/class', [
            'user_id' => 2,
            'class_level_id' => 1
        ], $this->adminToken);
        
        if ($response['code'] === 409) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Expected 409, got {$response['code']})\n";
            return false;
        }
    }

    /**
     * Test: Bulk assignment
     */
    public function testBulkAssignment() {
        echo "Test: Bulk assignment... ";
        
        $response = $this->makeRequest('POST', '/teacher-assignments/bulk', [
            'user_id' => 2,
            'class_level_ids' => [2, 3],
            'subject_ids' => [2, 3]
        ], $this->adminToken);
        
        if ($response['code'] === 201 && $response['body']['success'] === true) {
            $data = $response['body']['data'];
            if ($data['classes_assigned'] >= 0 && $data['subjects_assigned'] >= 0) {
                echo GREEN . "✓ PASSED" . RESET . " ({$data['classes_assigned']} classes, {$data['subjects_assigned']} subjects)\n";
                return true;
            }
        }
        
        echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
        return false;
    }

    /**
     * Test: Get all assignments
     */
    public function testGetAllAssignments() {
        echo "Test: Get all assignments... ";
        
        $response = $this->makeRequest('GET', '/teacher-assignments', null, $this->adminToken);
        
        if ($response['code'] === 200 && $response['body']['success'] === true) {
            $count = count($response['body']['data']);
            echo GREEN . "✓ PASSED" . RESET . " (Found {$count} teachers)\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            return false;
        }
    }

    /**
     * Test: Get my assignments
     */
    public function testGetMyAssignments() {
        echo "Test: Get my assignments (as teacher)... ";
        
        // First login as teacher
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'username' => 'teacher1',
            'password' => 'teacher123'
        ]);
        
        if ($loginResponse['code'] !== 200) {
            echo YELLOW . "⊘ SKIPPED" . RESET . " (Teacher login failed)\n";
            return false;
        }
        
        $teacherToken = $loginResponse['body']['data']['token'];
        
        $response = $this->makeRequest('GET', '/teacher-assignments/my-assignments', null, $teacherToken);
        
        if ($response['code'] === 200 && $response['body']['success'] === true) {
            $data = $response['body']['data'];
            $classCount = count($data['classes']);
            $subjectCount = count($data['subjects']);
            echo GREEN . "✓ PASSED" . RESET . " ({$classCount} classes, {$subjectCount} subjects)\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            return false;
        }
    }

    /**
     * Test: Delete class assignment
     */
    public function testDeleteClassAssignment($assignmentId) {
        echo "Test: Delete class assignment... ";
        
        if (!$assignmentId) {
            echo YELLOW . "⊘ SKIPPED" . RESET . " (No assignment ID)\n";
            return false;
        }
        
        $response = $this->makeRequest('DELETE', "/teacher-assignments/class/{$assignmentId}", null, $this->adminToken);
        
        if ($response['code'] === 200 && $response['body']['success'] === true) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            return false;
        }
    }

    /**
     * Test: Delete subject assignment
     */
    public function testDeleteSubjectAssignment($assignmentId) {
        echo "Test: Delete subject assignment... ";
        
        if (!$assignmentId) {
            echo YELLOW . "⊘ SKIPPED" . RESET . " (No assignment ID)\n";
            return false;
        }
        
        $response = $this->makeRequest('DELETE', "/teacher-assignments/subject/{$assignmentId}", null, $this->adminToken);
        
        if ($response['code'] === 200 && $response['body']['success'] === true) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Code: {$response['code']})\n";
            return false;
        }
    }

    /**
     * Test: Admin-only access
     */
    public function testAdminOnlyAccess() {
        echo "Test: Admin-only access control... ";
        
        // Login as teacher
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'username' => 'teacher1',
            'password' => 'teacher123'
        ]);
        
        if ($loginResponse['code'] !== 200) {
            echo YELLOW . "⊘ SKIPPED" . RESET . " (Teacher login failed)\n";
            return false;
        }
        
        $teacherToken = $loginResponse['body']['data']['token'];
        
        // Try to create assignment as teacher (should fail)
        $response = $this->makeRequest('POST', '/teacher-assignments/class', [
            'user_id' => 2,
            'class_level_id' => 1
        ], $teacherToken);
        
        if ($response['code'] === 403) {
            echo GREEN . "✓ PASSED" . RESET . "\n";
            return true;
        } else {
            echo RED . "✗ FAILED" . RESET . " (Expected 403, got {$response['code']})\n";
            return false;
        }
    }

    /**
     * Run all tests
     */
    public function runAll() {
        $passed = 0;
        $failed = 0;
        $skipped = 0;

        // Login
        if (!$this->loginAsAdmin()) {
            echo RED . "\nCannot proceed without admin login\n" . RESET;
            return;
        }

        echo "\n";

        // Run tests
        $classAssignmentId = $this->testAssignClass();
        if ($classAssignmentId !== null) {
            $passed++;
        }

        $subjectAssignmentId = $this->testAssignSubject();
        if ($subjectAssignmentId !== null) {
            $passed++;
        }

        $result = $this->testDuplicatePrevention();
        $passed += $result ? 1 : 0;
        $failed += !$result ? 1 : 0;

        $result = $this->testBulkAssignment();
        $passed += $result ? 1 : 0;
        $failed += !$result ? 1 : 0;

        $result = $this->testGetAllAssignments();
        $passed += $result ? 1 : 0;
        $failed += !$result ? 1 : 0;

        $result = $this->testGetMyAssignments();
        $passed += $result ? 1 : 0;
        $failed += !$result ? 1 : 0;

        $result = $this->testAdminOnlyAccess();
        $passed += $result ? 1 : 0;
        $failed += !$result ? 1 : 0;

        // Cleanup tests
        echo "\n" . YELLOW . "--- Cleanup ---" . RESET . "\n";
        $this->testDeleteClassAssignment($classAssignmentId);
        $this->testDeleteSubjectAssignment($subjectAssignmentId);

        // Summary
        echo "\n" . YELLOW . "=== Test Summary ===" . RESET . "\n";
        echo "Passed: " . GREEN . $passed . RESET . "\n";
        echo "Failed: " . RED . $failed . RESET . "\n";
        
        if ($failed === 0) {
            echo "\n" . GREEN . "All tests passed! ✓" . RESET . "\n\n";
        } else {
            echo "\n" . RED . "Some tests failed." . RESET . "\n\n";
        }
    }
}

// Run tests
$test = new TeacherAssignmentTest();
$test->runAll();
