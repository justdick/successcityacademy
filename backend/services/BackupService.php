<?php

require_once __DIR__ . '/../config/database.php';

class BackupService {
    private $backupDir;
    private $dbConfig;
    private $logDir;

    public function __construct() {
        $this->backupDir = __DIR__ . '/../backups';
        $this->logDir = __DIR__ . '/../logs';
        $this->dbConfig = $this->getDatabaseConfig();
        $this->ensureBackupDirectory();
    }

    /**
     * Get database configuration from Database class
     * @return array Database configuration
     */
    private function getDatabaseConfig() {
        // Use reflection to access private properties of Database class
        $database = new Database();
        $reflection = new ReflectionClass($database);
        
        $host = $reflection->getProperty('host');
        $host->setAccessible(true);
        
        $dbName = $reflection->getProperty('db_name');
        $dbName->setAccessible(true);
        
        $username = $reflection->getProperty('username');
        $username->setAccessible(true);
        
        $password = $reflection->getProperty('password');
        $password->setAccessible(true);
        
        return [
            'host' => $host->getValue($database),
            'database' => $dbName->getValue($database),
            'username' => $username->getValue($database),
            'password' => $password->getValue($database)
        ];
    }

    /**
     * Ensure backup directory exists and is writable
     * @return bool True if directory is ready, false otherwise
     */
    public function ensureBackupDirectory() {
        if (!file_exists($this->backupDir)) {
            if (!mkdir($this->backupDir, 0750, true)) {
                error_log("Failed to create backup directory: " . $this->backupDir);
                return false;
            }
        }

        if (!is_writable($this->backupDir)) {
            error_log("Backup directory is not writable: " . $this->backupDir);
            return false;
        }

        // Ensure log directory exists
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0750, true);
        }

        return true;
    }

    /**
     * Execute database backup
     * @return array Backup file details on success
     * @throws Exception on failure
     */
    public function executeBackup() {
        if (!$this->ensureBackupDirectory()) {
            throw new Exception("Backup directory is not accessible");
        }

        // Generate timestamped filename
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        // Build and execute backup command
        $command = $this->buildBackupCommand($filepath);
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = "Backup failed with return code {$returnCode}";
            if (!empty($output)) {
                $errorMsg .= ": " . implode("\n", $output);
            }
            error_log($errorMsg);
            throw new Exception("Failed to create backup");
        }

        // Verify backup file was created
        if (!file_exists($filepath)) {
            throw new Exception("Backup file was not created");
        }

        $fileSize = filesize($filepath);
        
        return [
            'filename' => $filename,
            'size' => $fileSize,
            'sizeFormatted' => $this->formatFileSize($fileSize),
            'created' => date('c'),
            'createdFormatted' => date('M j, Y g:i A')
        ];
    }

    /**
     * Build mysqldump command with proper escaping
     * @param string $outputFile Path to output file
     * @return string Complete command string
     */
    private function buildBackupCommand($outputFile) {
        $host = escapeshellarg($this->dbConfig['host']);
        $username = escapeshellarg($this->dbConfig['username']);
        $database = escapeshellarg($this->dbConfig['database']);
        $outputFile = escapeshellarg($outputFile);
        
        // Determine mysqldump path based on OS
        $mysqldumpPath = $this->getMysqldumpPath();
        
        $command = "{$mysqldumpPath} -h {$host} -u {$username}";
        
        // Add password if not empty
        if (!empty($this->dbConfig['password'])) {
            $password = escapeshellarg($this->dbConfig['password']);
            $command .= " -p{$password}";
        }
        
        $command .= " --single-transaction --routines --triggers {$database} > {$outputFile} 2>&1";
        
        return $command;
    }
    
    /**
     * Get mysqldump executable path
     * @return string Path to mysqldump
     */
    private function getMysqldumpPath() {
        // Check if running on Windows with WAMP
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common WAMP paths
            $wampPaths = [
                'C:/wamp64/bin/mysql/mysql8.4.0/bin/mysqldump.exe',
                'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
                'C:/wamp/bin/mysql/mysql8.4.0/bin/mysqldump.exe',
                'C:/wamp/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
                'C:/xampp/mysql/bin/mysqldump.exe'
            ];
            
            foreach ($wampPaths as $path) {
                if (file_exists($path)) {
                    return escapeshellarg($path);
                }
            }
        }
        
        // Default to system PATH
        return 'mysqldump';
    }
    
    /**
     * Get mysql executable path
     * @return string Path to mysql
     */
    private function getMysqlPath() {
        // Check if running on Windows with WAMP
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common WAMP paths
            $wampPaths = [
                'C:/wamp64/bin/mysql/mysql8.4.0/bin/mysql.exe',
                'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysql.exe',
                'C:/wamp/bin/mysql/mysql8.4.0/bin/mysql.exe',
                'C:/wamp/bin/mysql/mysql8.0.31/bin/mysql.exe',
                'C:/xampp/mysql/bin/mysql.exe'
            ];
            
            foreach ($wampPaths as $path) {
                if (file_exists($path)) {
                    return escapeshellarg($path);
                }
            }
        }
        
        // Default to system PATH
        return 'mysql';
    }

    /**
     * Execute database restore from backup file
     * @param string $filename Backup filename
     * @return bool True on success
     * @throws Exception on failure
     */
    public function executeRestore($filename) {
        // Validate backup file
        if (!$this->validateBackupFile($filename)) {
            throw new Exception("Invalid backup filename");
        }

        $filepath = $this->backupDir . '/' . $filename;

        // Verify file exists
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }

        // Build and execute restore command
        $command = $this->buildRestoreCommand($filepath);
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = "Restore failed with return code {$returnCode}";
            if (!empty($output)) {
                $errorMsg .= ": " . implode("\n", $output);
            }
            error_log($errorMsg);
            throw new Exception("Failed to restore backup");
        }

        return true;
    }

    /**
     * Build mysql restore command with proper escaping
     * @param string $inputFile Path to input file
     * @return string Complete command string
     */
    private function buildRestoreCommand($inputFile) {
        $host = escapeshellarg($this->dbConfig['host']);
        $username = escapeshellarg($this->dbConfig['username']);
        $database = escapeshellarg($this->dbConfig['database']);
        $inputFile = escapeshellarg($inputFile);
        
        // Determine mysql path based on OS
        $mysqlPath = $this->getMysqlPath();
        
        $command = "{$mysqlPath} -h {$host} -u {$username}";
        
        // Add password if not empty
        if (!empty($this->dbConfig['password'])) {
            $password = escapeshellarg($this->dbConfig['password']);
            $command .= " -p{$password}";
        }
        
        $command .= " {$database} < {$inputFile} 2>&1";
        
        return $command;
    }

    /**
     * Get list of backup files with metadata
     * @return array Array of backup file objects
     */
    public function getBackupFiles() {
        $backups = [];
        
        if (!is_dir($this->backupDir)) {
            return $backups;
        }

        $files = scandir($this->backupDir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess') {
                continue;
            }

            $filepath = $this->backupDir . '/' . $file;
            
            // Only include .sql files
            if (!is_file($filepath) || pathinfo($file, PATHINFO_EXTENSION) !== 'sql') {
                continue;
            }

            // Validate filename format
            if (!$this->validateBackupFile($file)) {
                continue;
            }

            $fileSize = filesize($filepath);
            $fileTime = filemtime($filepath);
            
            $backups[] = [
                'filename' => $file,
                'size' => $fileSize,
                'sizeFormatted' => $this->formatFileSize($fileSize),
                'created' => date('c', $fileTime),
                'createdFormatted' => date('M j, Y g:i A', $fileTime)
            ];
        }

        // Sort by creation time, newest first
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return $backups;
    }

    /**
     * Validate backup filename format and prevent path traversal
     * @param string $filename Filename to validate
     * @return bool True if valid, false otherwise
     */
    public function validateBackupFile($filename) {
        // Check for path traversal attempts
        if (strpos($filename, '..') !== false || 
            strpos($filename, '/') !== false || 
            strpos($filename, '\\') !== false) {
            return false;
        }

        // Validate filename format: backup_YYYY-MM-DD_HH-MM-SS.sql
        $pattern = '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';
        return preg_match($pattern, $filename) === 1;
    }

    /**
     * Delete backup file
     * @param string $filename Backup filename
     * @return bool True on success
     * @throws Exception on failure
     */
    public function deleteBackupFile($filename) {
        // Validate filename
        if (!$this->validateBackupFile($filename)) {
            throw new Exception("Invalid backup filename");
        }

        $filepath = $this->backupDir . '/' . $filename;

        // Verify file exists
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }

        // Delete the file
        if (!unlink($filepath)) {
            throw new Exception("Failed to delete backup file");
        }

        return true;
    }

    /**
     * Format file size to human-readable format
     * @param int $bytes File size in bytes
     * @return string Formatted file size
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log backup/restore operations
     * @param string $action Action performed (BACKUP, RESTORE, DELETE)
     * @param int $userId User ID who performed the action
     * @param array $details Additional details (filename, status, etc.)
     * @return void
     */
    public function logOperation($action, $userId, $details) {
        // Ensure log directory exists
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0750, true);
        }

        $logFile = $this->logDir . '/backup_operations.log';
        
        $timestamp = date('Y-m-d H:i:s');
        $filename = isset($details['filename']) ? $details['filename'] : 'N/A';
        $status = isset($details['status']) ? $details['status'] : 'UNKNOWN';
        $username = isset($details['username']) ? $details['username'] : 'Unknown';
        
        $logEntry = "[{$timestamp}] {$action} - User: {$username} (ID: {$userId}) - File: {$filename} - Status: {$status}\n";
        
        // Append to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

