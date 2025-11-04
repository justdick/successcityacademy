# Access Control Middleware Implementation

## Overview

This document describes the implementation of role-based access control for the Teacher Assignments feature. The access control system ensures that teachers can only view and manage students and assessments for their assigned classes and subjects, while admins retain unrestricted access.

## Components Implemented

### 1. TeacherAccessControl Middleware

**Location:** `backend/middleware/TeacherAccessControl.php`

**Purpose:** Centralized access control logic for validating teacher permissions

**Key Methods:**

- `hasClassAccess($userId, $classLevelId)` - Check if user has access to a specific class
- `hasSubjectAccess($userId, $subjectId)` - Check if user has access to a specific subject
- `getAccessibleClasses($userId)` - Get all class IDs accessible by user
- `getAccessibleSubjects($userId)` - Get all subject IDs accessible by user
- `hasStudentAccess($userId, $studentId)` - Check if user can access a specific student
- `logUnauthorizedAccess($userId, $resource, $resourceId)` - Log unauthorized access attempts
- `sendForbiddenResponse($message)` - Send 403 Forbidden response

**Admin Bypass:** All methods automatically grant full access to users with role 'admin'

### 2. Access Logging

**Database Table:** `access_logs`

**Schema:**
```sql
CREATE TABLE access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id VARCHAR(100) NOT NULL,
    access_denied TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_resource_type (resource_type),
    INDEX idx_created_at (created_at)
);
```

**Purpose:** Track all unauthorized access attempts for security auditing

### 3. Controller Updates

#### StudentController

**Modified Methods:**

- `getAllStudents()` - Filters students by teacher's accessible classes
  - Teachers only see students in their assigned classes
  - Admins see all students
  - Returns empty array if teacher has no class assignments

- `getStudent($student_id)` - Validates student access before returning details
  - Checks if teacher has access to student's class
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

#### AssessmentController

**Modified Methods:**

- `createOrUpdateAssessment($data)` - Validates subject and student access
  - Checks teacher has access to the subject
  - Checks teacher has access to the student
  - Returns 403 Forbidden if either check fails
  - Logs unauthorized access attempts

- `getStudentTermAssessments($student_id, $term_id)` - Validates student access
  - Checks teacher has access to the student
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `getClassTermAssessments($term_id, $class_level_id)` - Validates class access
  - Checks teacher has access to the class
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `updateAssessment($id, $data)` - Validates subject and student access
  - Checks teacher has access to the subject
  - Checks teacher has access to the student
  - Returns 403 Forbidden if either check fails
  - Logs unauthorized access attempts

- `deleteAssessment($id)` - Validates subject and student access
  - Checks teacher has access to the subject
  - Checks teacher has access to the student
  - Returns 403 Forbidden if either check fails
  - Logs unauthorized access attempts

#### ReportController

**Modified Methods:**

- `getStudentTermReport($student_id, $term_id)` - Validates student access
  - Checks teacher has access to the student
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `getClassTermReports($class_level_id, $term_id)` - Validates class access
  - Checks teacher has access to the class
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `getAssessmentSummary($term_id, $class_level_id)` - Validates class access
  - Checks teacher has access to the class
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `generateStudentPDF($student_id, $term_id)` - Validates student access
  - Checks teacher has access to the student
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

- `generateClassPDFBatch($class_level_id, $term_id)` - Validates class access
  - Checks teacher has access to the class
  - Returns 403 Forbidden if access denied
  - Logs unauthorized access attempts

## Security Features

### Server-Side Validation

- All access control checks are performed on the server
- Never relies solely on UI filtering
- Validates assignments on every API request
- Logs unauthorized access attempts

### SQL Injection Prevention

- Uses prepared statements for all queries
- Validates and sanitizes all input
- Uses parameterized queries in access control checks

### Error Responses

**403 Forbidden Response Format:**
```json
{
  "success": false,
  "error": "Access denied. You do not have permission to access this resource."
}
```

## Testing

### Test Files Created

1. `backend/test_access_control.php` - Unit tests for TeacherAccessControl methods
2. `backend/test_access_control_simple.php` - Comprehensive verification tests
3. `backend/test_access_control_e2e.php` - End-to-end integration tests

### Test Results

All tests passing:
- ✓ TeacherAccessControl methods work correctly
- ✓ Admin bypass functions properly
- ✓ Access control filters data correctly
- ✓ Unauthorized access is denied with 403
- ✓ Access logging captures attempts
- ✓ Integration with controllers works as expected

## Usage Examples

### Checking Class Access

```php
require_once __DIR__ . '/middleware/TeacherAccessControl.php';

$userId = 2; // Teacher ID
$classLevelId = 5;

if (TeacherAccessControl::hasClassAccess($userId, $classLevelId)) {
    // Teacher has access to this class
} else {
    // Access denied
    TeacherAccessControl::logUnauthorizedAccess($userId, 'class', $classLevelId);
    TeacherAccessControl::sendForbiddenResponse();
}
```

### Getting Accessible Classes

```php
$userId = 2; // Teacher ID
$accessibleClasses = TeacherAccessControl::getAccessibleClasses($userId);

// Use in query
$placeholders = implode(',', array_fill(0, count($accessibleClasses), '?'));
$query = "SELECT * FROM students WHERE class_level_id IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->execute($accessibleClasses);
```

### Checking Student Access

```php
$userId = 2; // Teacher ID
$studentId = 10;

if (TeacherAccessControl::hasStudentAccess($userId, $studentId)) {
    // Teacher has access to this student
} else {
    // Access denied
    TeacherAccessControl::logUnauthorizedAccess($userId, 'student', $studentId);
    TeacherAccessControl::sendForbiddenResponse();
}
```

## Migration

### Database Migration

Run the following script to create the access_logs table:

```bash
php backend/migrate_access_logs.php
```

### Backward Compatibility

- Existing API endpoints continue to work
- Admin users are unaffected
- Teachers without assignments see empty lists (not errors)
- No breaking changes to API responses

## Performance Considerations

### Database Indexes

The following indexes are created for optimal performance:

```sql
-- teacher_class_assignments
CREATE INDEX idx_user_id ON teacher_class_assignments(user_id);
CREATE INDEX idx_class_level_id ON teacher_class_assignments(class_level_id);

-- teacher_subject_assignments
CREATE INDEX idx_user_id ON teacher_subject_assignments(user_id);
CREATE INDEX idx_subject_id ON teacher_subject_assignments(subject_id);

-- access_logs
CREATE INDEX idx_user_id ON access_logs(user_id);
CREATE INDEX idx_resource_type ON access_logs(resource_type);
CREATE INDEX idx_created_at ON access_logs(created_at);
```

### Query Optimization

- Uses prepared statements with parameter binding
- Leverages database indexes for fast lookups
- Minimizes database queries by caching accessible IDs
- Uses IN clauses for efficient filtering

## Requirements Satisfied

This implementation satisfies the following requirements from the design document:

- ✓ 4.1, 4.2, 4.3, 4.4 - Student access control
- ✓ 5.1, 5.2, 5.3, 5.4 - Assessment access control
- ✓ 6.1, 6.2, 6.3, 6.4 - Report access control
- ✓ 8.1, 8.2, 8.3, 8.4 - Admin bypass
- ✓ 10.1, 10.2, 10.3, 10.4 - API-level enforcement
- ✓ 10.5 - Access logging

## Next Steps

The following tasks remain to complete the Teacher Assignments feature:

1. Create admin UI for teacher assignment management (Task 4)
2. Create teacher dashboard for viewing assignments (Task 5)
3. Update student list for teacher filtering (Task 6)
4. Update assessment entry for teacher filtering (Task 7)
5. Update report generation for teacher filtering (Task 8)
6. Testing and validation (Task 9)
7. Documentation and deployment (Task 10)
