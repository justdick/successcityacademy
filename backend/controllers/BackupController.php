<?php

require_once __DIR__ . '/../services/BackupService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class BackupController {
    private $backupService;

    public function __construct() {
        $this->backupService = new BackupService();
    }

    /**
     * Create a new database backup
     * POST /api/backups
     * 
     * @return void Sends JSON response
     */
    public function createBackup() {
        try {
            // Verify admin access
            $currentUser = $this->verifyAdminAccess();
            if (!$currentUser) {
                return;
            }

            // Execute backup
            $backupDetails = $this->backupService->executeBackup();

            // Log operation
            $this->backupService->logOperation(
                'BACKUP',
                $currentUser->id,
                [
                    'username' => $currentUser->username,
                    'filename' => $backupDetails['filename'],
                    'status' => 'SUCCESS'
                ]
            );

            // Return success response
            $this->sendSuccessResponse([
                'message' => 'Backup created successfully',
                'filename' => $backupDetails['filename'],
                'size' => $backupDetails['size'],
                'sizeFormatted' => $backupDetails['sizeFormatted'],
                'created' => $backupDetails['created'],
                'createdFormatted' => $backupDetails['createdFormatted']
            ], 201);

        } catch (Exception $e) {
            error_log("Create Backup Error: " . $e->getMessage());
            $this->sendErrorResponse("Failed to create backup: " . $e->getMessage(), 500);
        }
    }

    /**
     * Get list of all backup files
     * GET /api/backups
     * 
     * @return void Sends JSON response
     */
    public function listBackups() {
        try {
            // Verify admin access
            $currentUser = $this->verifyAdminAccess();
            if (!$currentUser) {
                return;
            }

            // Get backup files
            $backups = $this->backupService->getBackupFiles();

            // Return formatted list
            $this->sendSuccessResponse([
                'backups' => $backups,
                'count' => count($backups)
            ], 200);

        } catch (Exception $e) {
            error_log("List Backups Error: " . $e->getMessage());
            $this->sendErrorResponse("Failed to list backups: " . $e->getMessage(), 500);
        }
    }

    /**
     * Restore database from backup file
     * POST /api/backups/restore
     * 
     * @param array $data Request data containing filename
     * @return void Sends JSON response
     */
    public function restoreBackup($data) {
        try {
            // Verify admin access
            $currentUser = $this->verifyAdminAccess();
            if (!$currentUser) {
                return;
            }

            // Validate filename parameter
            if (!isset($data['filename']) || empty($data['filename'])) {
                $this->sendErrorResponse("Filename is required", 400);
                return;
            }

            $filename = $data['filename'];

            // Execute restore
            $this->backupService->executeRestore($filename);

            // Log operation
            $this->backupService->logOperation(
                'RESTORE',
                $currentUser->id,
                [
                    'username' => $currentUser->username,
                    'filename' => $filename,
                    'status' => 'SUCCESS'
                ]
            );

            // Return success response
            $this->sendSuccessResponse([
                'message' => 'Database restored successfully',
                'filename' => $filename
            ], 200);

        } catch (Exception $e) {
            error_log("Restore Backup Error: " . $e->getMessage());
            
            // Log failed operation
            if (isset($currentUser) && isset($filename)) {
                $this->backupService->logOperation(
                    'RESTORE',
                    $currentUser->id,
                    [
                        'username' => $currentUser->username,
                        'filename' => $filename,
                        'status' => 'FAILED'
                    ]
                );
            }
            
            $this->sendErrorResponse("Failed to restore backup: " . $e->getMessage(), 500);
        }
    }

    /**
     * Download backup file
     * GET /api/backups/download/{filename}
     * 
     * @param string $filename Backup filename
     * @return void Streams file to browser
     */
    public function downloadBackup($filename) {
        try {
            // Verify admin access
            $currentUser = $this->verifyAdminAccess();
            if (!$currentUser) {
                return;
            }

            // Validate filename
            if (!$this->backupService->validateBackupFile($filename)) {
                $this->sendErrorResponse("Invalid backup filename", 400);
                return;
            }

            // Get backup directory path
            $backupDir = __DIR__ . '/../backups';
            $filepath = $backupDir . '/' . $filename;

            // Verify file exists
            if (!file_exists($filepath)) {
                $this->sendErrorResponse("Backup file not found", 404);
                return;
            }

            // Set headers for file download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');

            // Stream file to browser
            readfile($filepath);
            exit;

        } catch (Exception $e) {
            error_log("Download Backup Error: " . $e->getMessage());
            $this->sendErrorResponse("Failed to download backup: " . $e->getMessage(), 500);
        }
    }

    /**
     * Delete backup file
     * DELETE /api/backups/{filename}
     * 
     * @param string $filename Backup filename
     * @return void Sends JSON response
     */
    public function deleteBackup($filename) {
        try {
            // Verify admin access
            $currentUser = $this->verifyAdminAccess();
            if (!$currentUser) {
                return;
            }

            // Validate filename
            if (!$this->backupService->validateBackupFile($filename)) {
                $this->sendErrorResponse("Invalid backup filename", 400);
                return;
            }

            // Get all backups to check if this is the most recent
            $backups = $this->backupService->getBackupFiles();
            
            if (empty($backups)) {
                $this->sendErrorResponse("No backups available", 404);
                return;
            }

            // Prevent deletion of most recent backup
            $mostRecentBackup = $backups[0]['filename'];
            if ($filename === $mostRecentBackup) {
                $this->sendErrorResponse("Cannot delete the most recent backup", 403);
                return;
            }

            // Delete the backup file
            $this->backupService->deleteBackupFile($filename);

            // Log operation
            $this->backupService->logOperation(
                'DELETE',
                $currentUser->id,
                [
                    'username' => $currentUser->username,
                    'filename' => $filename,
                    'status' => 'SUCCESS'
                ]
            );

            // Return success response
            $this->sendSuccessResponse([
                'message' => 'Backup deleted successfully',
                'filename' => $filename
            ], 200);

        } catch (Exception $e) {
            error_log("Delete Backup Error: " . $e->getMessage());
            $this->sendErrorResponse("Failed to delete backup: " . $e->getMessage(), 500);
        }
    }

    /**
     * Verify admin access for backup operations
     * 
     * @return object|null The authenticated admin user or null on failure
     */
    private function verifyAdminAccess() {
        return AuthMiddleware::requireAdmin();
    }

    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    private function sendSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    private function sendErrorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
