# Design Document

## Overview

The Database Backup and Restore feature provides administrators with manual database management capabilities through a web-based interface. The system will execute MySQL backup and restore operations using PHP's `exec()` function to run `mysqldump` and `mysql` commands. The feature integrates with the existing authentication and authorization system, ensuring only admin users can perform these operations.

The implementation follows the existing application architecture with a dedicated controller, API endpoints, and React frontend component. Backup files are stored in a designated directory on the server with timestamped filenames for easy identification and management.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (React)                         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  BackupManagement Component                            │ │
│  │  - Display backup list                                 │ │
│  │  - Create backup button                                │ │
│  │  - Restore/Download/Delete actions                     │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ HTTP/JSON
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Backend API (PHP)                         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  BackupController                                      │ │
│  │  - createBackup()                                      │ │
│  │  - listBackups()                                       │ │
│  │  - restoreBackup($filename)                            │ │
│  │  - downloadBackup($filename)                           │ │
│  │  - deleteBackup($filename)                             │ │
│  └────────────────────────────────────────────────────────┘ │
│                            │                                 │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  BackupService                                         │ │
│  │  - executeBackup()                                     │ │
│  │  - executeRestore($filename)                           │ │
│  │  - getBackupFiles()                                    │ │
│  │  - validateBackupFile($filename)                       │ │
│  │  - deleteBackupFile($filename)                         │ │
│  │  - logOperation($action, $user, $details)              │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ Shell Commands
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  MySQL Database                              │
│  - mysqldump (backup)                                        │
│  - mysql (restore)                                           │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│              File System (Backup Storage)                    │
│  backend/backups/                                            │
│  - backup_2024-11-04_14-30-00.sql                           │
│  - backup_2024-11-03_10-15-30.sql                           │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: React 18+
- **HTTP Client**: Axios
- **Authentication**: Session-based (existing system)
- **Authorization**: Role-based (admin only)

## Components and Interfaces

### Backend Components

#### 1. BackupController (backend/controllers/BackupController.php)

Handles HTTP requests for backup operations and enforces admin-only access.

**Methods:**

```php
class BackupController {
    private $backupService;
    
    public function __construct()
    
    // POST /api/backups - Create new backup
    public function createBackup()
    
    // GET /api/backups - List all backups
    public function listBackups()
    
    // POST /api/backups/restore - Restore from backup
    public function restoreBackup($data)
    
    // GET /api/backups/download/{filename} - Download backup file
    public function downloadBackup($filename)
    
    // DELETE /api/backups/{filename} - Delete backup file
    public function deleteBackup($filename)
    
    // Helper methods
    private function verifyAdminAccess()
    private function sendSuccessResponse($data, $statusCode = 200)
    private function sendErrorResponse($message, $statusCode = 400)
}
```

#### 2. BackupService (backend/services/BackupService.php)

Executes backup and restore operations using MySQL command-line tools.

**Methods:**

```php
class BackupService {
    private $backupDir;
    private $dbConfig;
    
    public function __construct()
    
    // Execute database backup
    public function executeBackup(): array
    
    // Execute database restore
    public function executeRestore(string $filename): bool
    
    // Get list of backup files with metadata
    public function getBackupFiles(): array
    
    // Validate backup filename
    public function validateBackupFile(string $filename): bool
    
    // Delete backup file
    public function deleteBackupFile(string $filename): bool
    
    // Check if backup directory exists and is writable
    public function ensureBackupDirectory(): bool
    
    // Log backup/restore operations
    public function logOperation(string $action, int $userId, array $details): void
    
    // Get database configuration
    private function getDatabaseConfig(): array
    
    // Build mysqldump command
    private function buildBackupCommand(string $outputFile): string
    
    // Build mysql restore command
    private function buildRestoreCommand(string $inputFile): string
    
    // Format file size for display
    private function formatFileSize(int $bytes): string
}
```

#### 3. API Routes (backend/api/index.php)

New routes to be added:

```php
// Backup routes
if ($segments[0] === 'backups') {
    require_once __DIR__ . '/../controllers/BackupController.php';
    $controller = new BackupController();
    
    if ($method === 'POST' && count($segments) === 1) {
        // POST /api/backups - Create backup (admin only)
        $controller->createBackup();
    } elseif ($method === 'GET' && count($segments) === 1) {
        // GET /api/backups - List backups (admin only)
        $controller->listBackups();
    } elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'restore') {
        // POST /api/backups/restore - Restore backup (admin only)
        $input = json_decode(file_get_contents('php://input'), true);
        $controller->restoreBackup($input);
    } elseif ($method === 'GET' && count($segments) === 3 && $segments[1] === 'download') {
        // GET /api/backups/download/{filename} - Download backup (admin only)
        $controller->downloadBackup($segments[2]);
    } elseif ($method === 'DELETE' && count($segments) === 2) {
        // DELETE /api/backups/{filename} - Delete backup (admin only)
        $controller->deleteBackup($segments[1]);
    } else {
        sendMethodNotAllowedResponse();
    }
}
```

### Frontend Components

#### 1. BackupManagement Component (frontend/src/components/BackupManagement.jsx)

Main component for backup management interface.

**Features:**
- Display list of backups in a table
- Create backup button with loading state
- Restore button with confirmation dialog
- Download button for each backup
- Delete button with confirmation dialog
- File size and timestamp display
- Success/error notifications
- Loading indicators during operations

**State:**
```javascript
{
    backups: [],           // Array of backup objects
    loading: false,        // Global loading state
    operationInProgress: false,  // Disable buttons during operations
    error: null,          // Error message
    successMessage: null  // Success message
}
```

#### 2. backupService (frontend/src/services/backupService.js)

API client for backup operations.

**Methods:**
```javascript
export const backupService = {
    // Create new backup
    createBackup: async () => Promise<{success: boolean, data: object}>
    
    // Get list of backups
    listBackups: async () => Promise<{success: boolean, data: array}>
    
    // Restore from backup
    restoreBackup: async (filename: string) => Promise<{success: boolean}>
    
    // Download backup file
    downloadBackup: async (filename: string) => Promise<void>
    
    // Delete backup file
    deleteBackup: async (filename: string) => Promise<{success: boolean}>
}
```

## Data Models

### Backup File Object

```javascript
{
    filename: string,        // e.g., "backup_2024-11-04_14-30-00.sql"
    size: number,           // File size in bytes
    sizeFormatted: string,  // e.g., "2.5 MB"
    created: string,        // ISO 8601 timestamp
    createdFormatted: string // e.g., "Nov 4, 2024 2:30 PM"
}
```

### API Response Format

**Success Response:**
```json
{
    "success": true,
    "data": {
        "message": "Backup created successfully",
        "filename": "backup_2024-11-04_14-30-00.sql",
        "size": 2621440,
        "sizeFormatted": "2.5 MB"
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "error": "Failed to create backup: Permission denied"
}
```

### Backup Log Entry

Backup operations will be logged to a file for audit purposes:

```
backend/logs/backup_operations.log
```

Log format:
```
[2024-11-04 14:30:00] BACKUP - User: admin (ID: 1) - File: backup_2024-11-04_14-30-00.sql - Status: SUCCESS
[2024-11-04 15:45:00] RESTORE - User: admin (ID: 1) - File: backup_2024-11-04_14-30-00.sql - Status: SUCCESS
[2024-11-04 16:00:00] DELETE - User: admin (ID: 1) - File: backup_2024-11-03_10-15-30.sql - Status: SUCCESS
```

## Error Handling

### Backend Error Scenarios

1. **Permission Errors**
   - Backup directory not writable
   - MySQL user lacks necessary privileges
   - Response: 500 Internal Server Error with descriptive message

2. **Authentication/Authorization Errors**
   - User not logged in
   - User is not admin
   - Response: 403 Forbidden

3. **File System Errors**
   - Backup file not found
   - Disk space full
   - Response: 404 Not Found or 500 Internal Server Error

4. **Database Errors**
   - MySQL connection failure
   - Restore SQL syntax errors
   - Response: 500 Internal Server Error

5. **Validation Errors**
   - Invalid filename (path traversal attempt)
   - Missing required parameters
   - Response: 400 Bad Request

### Frontend Error Handling

1. **Network Errors**
   - Display error notification
   - Retry option for failed operations

2. **Server Errors**
   - Display server error message
   - Log error to console

3. **Validation Errors**
   - Display validation message
   - Prevent invalid operations

### Error Recovery

- All operations are atomic (complete or fail entirely)
- Failed backups don't leave partial files
- Failed restores don't corrupt the database (MySQL transaction handling)
- Detailed error messages logged for debugging

## Testing Strategy

### Backend Testing

#### Unit Tests (Optional)

Test individual methods in isolation:

1. **BackupService Tests**
   - Test backup command generation
   - Test restore command generation
   - Test file validation logic
   - Test backup file listing
   - Mock file system operations

2. **BackupController Tests**
   - Test admin authorization
   - Test request validation
   - Test response formatting
   - Mock BackupService

#### Integration Tests

Test complete workflows:

1. **Backup Creation Flow**
   - Admin creates backup
   - Verify file created in backups directory
   - Verify correct filename format
   - Verify file contains valid SQL

2. **Backup Restore Flow**
   - Create test backup
   - Modify database
   - Restore from backup
   - Verify data restored correctly

3. **Access Control**
   - Non-admin user attempts backup operations
   - Verify 403 Forbidden response

4. **File Operations**
   - List backups
   - Download backup
   - Delete backup
   - Verify file system changes

### Frontend Testing

#### Component Tests (Optional)

1. **BackupManagement Component**
   - Renders backup list correctly
   - Handles loading states
   - Displays error messages
   - Confirmation dialogs work
   - Buttons disabled during operations

#### Manual Testing Checklist

1. **Create Backup**
   - Click create backup button
   - Verify loading indicator appears
   - Verify success message displays
   - Verify new backup appears in list
   - Verify backup file exists on server

2. **Restore Backup**
   - Click restore button
   - Verify confirmation dialog appears
   - Confirm restore
   - Verify loading indicator
   - Verify success message
   - Verify data restored correctly
   - Verify session logout/redirect

3. **Download Backup**
   - Click download button
   - Verify file downloads to browser
   - Verify filename correct
   - Verify file can be opened

4. **Delete Backup**
   - Click delete button
   - Verify confirmation dialog
   - Confirm deletion
   - Verify backup removed from list
   - Verify file deleted from server

5. **Access Control**
   - Login as non-admin user
   - Verify backup menu not visible
   - Attempt direct API access
   - Verify 403 response

6. **Error Scenarios**
   - Simulate disk full
   - Simulate permission errors
   - Simulate invalid filename
   - Verify appropriate error messages

### Security Testing

1. **Path Traversal Prevention**
   - Attempt to access files outside backup directory
   - Verify validation blocks malicious filenames

2. **SQL Injection Prevention**
   - Attempt SQL injection in filename
   - Verify proper escaping/validation

3. **Authorization Bypass**
   - Attempt to access endpoints without authentication
   - Attempt to access endpoints as non-admin
   - Verify proper authorization checks

## Security Considerations

### Access Control

- All backup endpoints require admin role
- Session validation on every request
- No public access to backup files
- Backup directory outside web root (if possible)

### Input Validation

- Filename validation (whitelist pattern: `backup_YYYY-MM-DD_HH-MM-SS.sql`)
- Path traversal prevention (no `..`, `/`, `\` in filenames)
- File existence verification before operations

### File System Security

- Backup directory permissions: 750 (rwxr-x---)
- Backup file permissions: 640 (rw-r-----)
- Prevent directory listing
- Store backups outside public web directory

### Database Security

- Use database credentials from config
- Don't expose credentials in error messages
- Log operations for audit trail
- Validate SQL file before restore (basic checks)

### Command Injection Prevention

- Use `escapeshellarg()` for all command parameters
- Validate all inputs before shell execution
- Use absolute paths for commands
- Limit command execution to specific operations

## Implementation Notes

### Database Configuration

Read from existing `backend/config/database.php`:
- Host: localhost
- Database: student_management
- Username: root
- Password: (empty)

### Backup Directory Structure

```
backend/
├── backups/
│   ├── .htaccess (deny web access)
│   ├── backup_2024-11-04_14-30-00.sql
│   ├── backup_2024-11-03_10-15-30.sql
│   └── ...
└── logs/
    └── backup_operations.log
```

### MySQL Command Examples

**Backup Command:**
```bash
mysqldump -h localhost -u root --single-transaction --routines --triggers student_management > backup_2024-11-04_14-30-00.sql
```

**Restore Command:**
```bash
mysql -h localhost -u root student_management < backup_2024-11-04_14-30-00.sql
```

### Frontend Integration

Add to `frontend/src/components/Layout.jsx`:
```javascript
{user.role === 'admin' && (
    <Link to="/backup-management">
        <Database className="w-5 h-5" />
        <span>Backup & Restore</span>
    </Link>
)}
```

Add route to `frontend/src/App.jsx`:
```javascript
<Route path="/backup-management" element={<BackupManagement />} />
```

### Performance Considerations

- Large databases may take time to backup/restore
- Show progress indicators for long operations
- Consider timeout limits for PHP execution
- May need to increase `max_execution_time` in php.ini

### Limitations

- Backup/restore operations are synchronous (blocking)
- No progress percentage during operations
- No automatic backup scheduling (manual only)
- No backup compression (future enhancement)
- No backup encryption (future enhancement)
- No remote backup storage (future enhancement)
