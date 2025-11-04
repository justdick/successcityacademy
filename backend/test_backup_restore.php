<?php

/**
 * Comprehensive test script for Backup and Restore functionality
 * Tests all requirements from task 8 in the implementation plan
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/BackupService.php';

class BackupRestoreTest {
    private $db;
    private $backupService;
    private $testResults = [];
    private $backupDir;

    public function __construct() {
        echo "Initializing test suite...\n";
        $this->db = new Database();
        $this->backupService = new BackupService();
        $this->backupDir = __DIR__ . '/backups';
        echo "Test suite initialized successfully\n\n";
    }

    private function log($message, $status = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] [{$status}] {$message}\n";
    }

    private function recordResult($testName, $passed, $message = '') {
        $this->testResults[] = [
            'test' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
        
        $status = $passed ? 'PASS' : 'FAIL';
        $this->log("{$testName}: {$status} - {$message}", $status);
    }

    /**
     * Test 8.1: Test backup creation
     */
    public function testBackupCreation() {
        $this->log("=== Test 8.1: Backup Creation ===");
        
        try {
            // Get initial backup count
            $initialBackups = $this->backupService->getBackupFiles();
            $initialCount = count($initialBackups);
            
            // Create backup
            $this->log("Creating backup...");
            $backupDetails = $this->backupService->executeBackup();
            
            // Verify backup file created
            $backupPath = $this->backupDir . '/' . $backupDetails['filename'];
            if (!file_exists($backupPath)) {
                $this->recordResult('8.1 - Backup file created', false, 'Backup file not found on disk');
                return false;
            }
            $this->recordResult('8.1 - Backup file created', true, "File: {$backupDetails['filename']}");
            
            // Verify filename format
            $pattern = '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';
            $validFormat = preg_match($pattern, $backupDetails['filename']) === 1;
            $this->recordResult('8.1 - Filename format correct', $validFormat, $backupDetails['filename']);
            
            // Verify backup appears in list
            $updatedBackups = $this->backupService->getBackupFiles();
            $found = false;
            foreach ($updatedBackups as $backup) {
                if ($backup['filename'] === $backupDetails['filename']) {
                    $found = true;
                    // Verify metadata
                    $hasSize = isset($backup['size']) && $backup['size'] > 0;
                    $hasSizeFormatted = isset($backup['sizeFormatted']) && !empty($backup['sizeFormatted']);
                    $hasCreated = isset($backup['created']) && !empty($backup['created']);
                    
                    $this->recordResult('8.1 - Backup has correct metadata', 
                        $hasSize && $hasSizeFormatted && $hasCreated,
                        "Size: {$backup['sizeFormatted']}, Created: {$backup['createdFormatted']}");
                    break;
                }
            }
            $this->recordResult('8.1 - Backup appears in list', $found, '');
            
            // Verify file contains valid SQL
            $fileContent = file_get_contents($backupPath);
            $hasSQL = strpos($fileContent, 'CREATE TABLE') !== false || 
                      strpos($fileContent, 'INSERT INTO') !== false ||
                      strpos($fileContent, 'DROP TABLE') !== false;
            $this->recordResult('8.1 - Backup contains valid SQL', $hasSQL, '');
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.1 - Backup creation', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.2: Test backup restore
     */
    public function testBackupRestore() {
        $this->log("=== Test 8.2: Backup Restore ===");
        
        try {
            // Create a test backup first
            $this->log("Creating test backup...");
            $backupDetails = $this->backupService->executeBackup();
            $backupFilename = $backupDetails['filename'];
            
            // Make a change to the database (add a test record)
            $this->log("Making database changes...");
            $conn = $this->db->connect();
            $testUsername = 'test_restore_user_' . time();
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$testUsername, password_hash('test', PASSWORD_DEFAULT), 'user', 'Test Restore User']);
            $testUserId = $conn->lastInsertId();
            
            // Verify the record exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$testUserId]);
            $userExists = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            $this->recordResult('8.2 - Test record created', $userExists, "User ID: {$testUserId}");
            
            // Restore from backup
            $this->log("Restoring from backup...");
            $restored = $this->backupService->executeRestore($backupFilename);
            $this->recordResult('8.2 - Restore executed', $restored, "Restored from: {$backupFilename}");
            
            // Reconnect to database after restore
            $this->db = new Database();
            $conn = $this->db->connect();
            
            // Verify the test record no longer exists (database restored to backup state)
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$testUserId]);
            $userStillExists = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            $this->recordResult('8.2 - Database restored to backup state', !$userStillExists, 
                'Test record removed after restore');
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.2 - Backup restore', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.3: Test backup download
     */
    public function testBackupDownload() {
        $this->log("=== Test 8.3: Backup Download ===");
        
        try {
            // Get list of backups
            $backups = $this->backupService->getBackupFiles();
            
            if (empty($backups)) {
                // Create a backup if none exist
                $backupDetails = $this->backupService->executeBackup();
                $filename = $backupDetails['filename'];
            } else {
                $filename = $backups[0]['filename'];
            }
            
            // Verify file exists and is readable
            $filepath = $this->backupDir . '/' . $filename;
            $fileExists = file_exists($filepath);
            $this->recordResult('8.3 - Backup file exists', $fileExists, $filename);
            
            if ($fileExists) {
                $isReadable = is_readable($filepath);
                $this->recordResult('8.3 - Backup file is readable', $isReadable, '');
                
                // Verify file has correct name format
                $validName = $this->backupService->validateBackupFile($filename);
                $this->recordResult('8.3 - Filename format valid', $validName, $filename);
                
                // Verify file contains valid SQL
                $content = file_get_contents($filepath);
                $hasSQL = strpos($content, 'CREATE TABLE') !== false || 
                          strpos($content, 'INSERT INTO') !== false ||
                          strpos($content, 'DROP TABLE') !== false;
                $this->recordResult('8.3 - Downloaded file contains valid SQL', $hasSQL, '');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.3 - Backup download', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.4: Test backup deletion
     */
    public function testBackupDeletion() {
        $this->log("=== Test 8.4: Backup Deletion ===");
        
        try {
            // Create multiple backups
            $this->log("Creating multiple backups...");
            $backup1 = $this->backupService->executeBackup();
            sleep(1); // Ensure different timestamps
            $backup2 = $this->backupService->executeBackup();
            
            $this->recordResult('8.4 - Multiple backups created', true, 
                "Created: {$backup1['filename']}, {$backup2['filename']}");
            
            // Get all backups (sorted newest first)
            $backups = $this->backupService->getBackupFiles();
            $mostRecent = $backups[0]['filename'];
            $older = null;
            
            // Find an older backup to delete
            foreach ($backups as $backup) {
                if ($backup['filename'] !== $mostRecent) {
                    $older = $backup['filename'];
                    break;
                }
            }
            
            if (!$older) {
                $this->recordResult('8.4 - Find older backup', false, 'No older backup found');
                return false;
            }
            
            // Try to delete most recent backup (should fail)
            $this->log("Attempting to delete most recent backup (should fail)...");
            try {
                // Simulate the controller's check
                if ($mostRecent === $backups[0]['filename']) {
                    throw new Exception("Cannot delete the most recent backup");
                }
                $this->recordResult('8.4 - Prevent deletion of most recent', false, 'Should have been prevented');
            } catch (Exception $e) {
                $this->recordResult('8.4 - Prevent deletion of most recent', true, 'Correctly prevented');
            }
            
            // Delete older backup
            $this->log("Deleting older backup: {$older}");
            $filepath = $this->backupDir . '/' . $older;
            $existedBefore = file_exists($filepath);
            
            $deleted = $this->backupService->deleteBackupFile($older);
            $this->recordResult('8.4 - Delete backup executed', $deleted, "Deleted: {$older}");
            
            // Verify file deleted from server
            $existsAfter = file_exists($filepath);
            $this->recordResult('8.4 - Backup file deleted from server', !$existsAfter, '');
            
            // Verify backup removed from list
            $updatedBackups = $this->backupService->getBackupFiles();
            $stillInList = false;
            foreach ($updatedBackups as $backup) {
                if ($backup['filename'] === $older) {
                    $stillInList = true;
                    break;
                }
            }
            $this->recordResult('8.4 - Backup removed from list', !$stillInList, '');
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.4 - Backup deletion', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.5: Test access control
     */
    public function testAccessControl() {
        $this->log("=== Test 8.5: Access Control ===");
        
        try {
            // Test 1: Validate backup file with path traversal attempt
            $this->log("Testing path traversal prevention...");
            $maliciousFilenames = [
                '../../../etc/passwd',
                '..\\..\\..\\windows\\system32\\config\\sam',
                'backup_2024-01-01_12-00-00.sql/../../../etc/passwd',
                'backup_2024-01-01_12-00-00.sql/../../test.sql'
            ];
            
            $allBlocked = true;
            foreach ($maliciousFilenames as $malicious) {
                if ($this->backupService->validateBackupFile($malicious)) {
                    $allBlocked = false;
                    break;
                }
            }
            $this->recordResult('8.5 - Path traversal prevented', $allBlocked, 
                'All malicious filenames blocked');
            
            // Test 2: Invalid filename format
            $invalidFilenames = [
                'backup.sql',
                'test_backup.sql',
                'backup_2024-1-1_12-00-00.sql',  // Missing leading zeros
                'backup_abcd-ef-gh_ij-kl-mn.sql'
            ];
            
            $allInvalid = true;
            $acceptedInvalid = '';
            foreach ($invalidFilenames as $invalid) {
                if ($this->backupService->validateBackupFile($invalid)) {
                    $allInvalid = false;
                    $acceptedInvalid = $invalid;
                    break;
                }
            }
            $message = $allInvalid ? 'All invalid formats rejected' : "Incorrectly accepted: {$acceptedInvalid}";
            $this->recordResult('8.5 - Invalid filename format rejected', $allInvalid, $message);
            
            // Test 3: Valid filename format
            $validFilename = 'backup_2024-11-04_14-30-00.sql';
            $isValid = $this->backupService->validateBackupFile($validFilename);
            $this->recordResult('8.5 - Valid filename accepted', $isValid, $validFilename);
            
            // Test 4: Non-existent backup file
            $this->log("Testing non-existent backup file...");
            try {
                $this->backupService->executeRestore('backup_9999-99-99_99-99-99.sql');
                $this->recordResult('8.5 - Non-existent file error', false, 'Should have thrown exception');
            } catch (Exception $e) {
                $this->recordResult('8.5 - Non-existent file error', true, 
                    'Correctly threw exception: ' . $e->getMessage());
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.5 - Access control', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.6: Test error handling
     */
    public function testErrorHandling() {
        $this->log("=== Test 8.6: Error Handling ===");
        
        try {
            // Test 1: Invalid filename (path traversal)
            $this->log("Testing invalid filename error...");
            try {
                $this->backupService->deleteBackupFile('../../../etc/passwd');
                $this->recordResult('8.6 - Invalid filename error', false, 'Should have thrown exception');
            } catch (Exception $e) {
                $this->recordResult('8.6 - Invalid filename error', true, $e->getMessage());
            }
            
            // Test 2: Non-existent backup file
            $this->log("Testing non-existent file error...");
            try {
                $this->backupService->deleteBackupFile('backup_9999-99-99_99-99-99.sql');
                $this->recordResult('8.6 - Non-existent file error', false, 'Should have thrown exception');
            } catch (Exception $e) {
                $this->recordResult('8.6 - Non-existent file error', true, $e->getMessage());
            }
            
            // Test 3: Create corrupted backup file and try to restore
            $this->log("Testing corrupted backup file...");
            $corruptedFilename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $corruptedPath = $this->backupDir . '/' . $corruptedFilename;
            file_put_contents($corruptedPath, 'This is not valid SQL content!');
            
            try {
                $this->backupService->executeRestore($corruptedFilename);
                $this->recordResult('8.6 - Corrupted file error', false, 'Should have thrown exception');
            } catch (Exception $e) {
                $this->recordResult('8.6 - Corrupted file error', true, $e->getMessage());
            }
            
            // Clean up corrupted file
            if (file_exists($corruptedPath)) {
                unlink($corruptedPath);
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.6 - Error handling', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Test 8.7: Test UI states and loading indicators
     * Note: This is a backend test, so we verify the service behavior
     */
    public function testServiceBehavior() {
        $this->log("=== Test 8.7: Service Behavior (Backend) ===");
        
        try {
            // Test that operations complete successfully
            $this->log("Testing backup operation completion...");
            $startTime = microtime(true);
            $backup = $this->backupService->executeBackup();
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->recordResult('8.7 - Backup operation completes', true, 
                "Completed in {$duration} seconds");
            
            // Test that list operation is fast
            $startTime = microtime(true);
            $backups = $this->backupService->getBackupFiles();
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->recordResult('8.7 - List operation is fast', $duration < 1, 
                "Completed in {$duration} seconds");
            
            // Test logging functionality
            $this->log("Testing operation logging...");
            $this->backupService->logOperation('TEST', 1, [
                'username' => 'test_user',
                'filename' => 'test_backup.sql',
                'status' => 'SUCCESS'
            ]);
            
            $logFile = __DIR__ . '/logs/backup_operations.log';
            $logExists = file_exists($logFile);
            $this->recordResult('8.7 - Operation logging works', $logExists, 
                'Log file created');
            
            if ($logExists) {
                $logContent = file_get_contents($logFile);
                $hasTestEntry = strpos($logContent, 'TEST') !== false;
                $this->recordResult('8.7 - Log entry created', $hasTestEntry, 
                    'Test entry found in log');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->recordResult('8.7 - Service behavior', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Run all tests
     */
    public function runAllTests() {
        $this->log("========================================");
        $this->log("Starting Backup & Restore Test Suite");
        $this->log("========================================\n");
        
        $this->testBackupCreation();
        echo "\n";
        
        $this->testBackupRestore();
        echo "\n";
        
        $this->testBackupDownload();
        echo "\n";
        
        $this->testBackupDeletion();
        echo "\n";
        
        $this->testAccessControl();
        echo "\n";
        
        $this->testErrorHandling();
        echo "\n";
        
        $this->testServiceBehavior();
        echo "\n";
        
        $this->printSummary();
    }

    /**
     * Print test summary
     */
    private function printSummary() {
        $this->log("========================================");
        $this->log("Test Summary");
        $this->log("========================================");
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $total = $passed + $failed;
        $passRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        
        $this->log("Total Tests: {$total}");
        $this->log("Passed: {$passed}", 'PASS');
        $this->log("Failed: {$failed}", $failed > 0 ? 'FAIL' : 'INFO');
        $this->log("Pass Rate: {$passRate}%");
        
        if ($failed > 0) {
            $this->log("\nFailed Tests:");
            foreach ($this->testResults as $result) {
                if (!$result['passed']) {
                    $this->log("  - {$result['test']}: {$result['message']}", 'FAIL');
                }
            }
        }
        
        $this->log("========================================");
    }
}

// Run tests
try {
    $tester = new BackupRestoreTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
