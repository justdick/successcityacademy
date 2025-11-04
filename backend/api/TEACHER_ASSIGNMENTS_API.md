# Teacher Assignments API Documentation

## Overview

The Teacher Assignments API provides endpoints for managing teacher-to-class and teacher-to-subject assignments, implementing role-based access control for teachers in the Student Management System.

**Base URL:** `/api/teacher-assignments`

**Authentication:** All endpoints require authentication via session token.

**Authorization:** 
- Admin-only endpoints are marked with 🔒
- Teacher-accessible endpoints are marked with 👤

---

## Endpoints

### 1. Create Class Assignment 🔒

Assign a teacher to a specific class level.

**Endpoint:** `POST /api/teacher-assignments/class`

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "class_level_id": 5
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | ID of the teacher (must have role "user") |
| class_level_id | integer | Yes | ID of the class level to assign |

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Teacher assigned to class successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "class_level_id": 5,
    "created_at": "2024-11-03 10:00:00"
  }
}
```

**Error Responses:**

**400 Bad Request** - Invalid input or duplicate assignment
```json
{
  "success": false,
  "error": "Teacher is already assigned to this class"
}
```

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

**404 Not Found** - Teacher or class not found
```json
{
  "success": false,
  "error": "Teacher or class level not found"
}
```

---

### 2. Create Subject Assignment 🔒

Assign a teacher to a specific subject.

**Endpoint:** `POST /api/teacher-assignments/subject`

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "subject_id": 3
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | ID of the teacher (must have role "user") |
| subject_id | integer | Yes | ID of the subject to assign |

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Teacher assigned to subject successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "subject_id": 3,
    "created_at": "2024-11-03 10:00:00"
  }
}
```

**Error Responses:**

**400 Bad Request** - Invalid input or duplicate assignment
```json
{
  "success": false,
  "error": "Teacher is already assigned to this subject"
}
```

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

**404 Not Found** - Teacher or subject not found
```json
{
  "success": false,
  "error": "Teacher or subject not found"
}
```

---

### 3. Bulk Assignment 🔒

Assign multiple classes and subjects to a teacher in a single operation.

**Endpoint:** `POST /api/teacher-assignments/bulk`

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "class_level_ids": [5, 6, 7],
  "subject_ids": [1, 2, 3]
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | ID of the teacher |
| class_level_ids | array | No | Array of class level IDs to assign |
| subject_ids | array | No | Array of subject IDs to assign |

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Bulk assignments created successfully",
  "data": {
    "classes_assigned": 3,
    "subjects_assigned": 3,
    "total_assignments": 6
  }
}
```

**Error Responses:**

**400 Bad Request** - Invalid input
```json
{
  "success": false,
  "error": "At least one class or subject must be provided"
}
```

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

**Note:** Duplicate assignments are silently skipped. The response indicates how many new assignments were created.

---

### 4. List All Assignments 🔒

Retrieve all teacher assignments with optional filtering.

**Endpoint:** `GET /api/teacher-assignments`

**Authorization:** Admin only

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | No | Filter by teacher ID |
| class_level_id | integer | No | Filter by class level ID |
| subject_id | integer | No | Filter by subject ID |

**Example Request:**
```
GET /api/teacher-assignments?user_id=2
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 2,
      "username": "teacher1",
      "full_name": "John Smith",
      "classes": [
        {
          "id": 5,
          "assignment_id": 1,
          "name": "Grade 9",
          "student_count": 25
        },
        {
          "id": 6,
          "assignment_id": 2,
          "name": "Grade 10",
          "student_count": 30
        }
      ],
      "subjects": [
        {
          "id": 1,
          "assignment_id": 3,
          "name": "Mathematics"
        },
        {
          "id": 2,
          "assignment_id": 4,
          "name": "Science"
        }
      ]
    }
  ]
}
```

**Error Responses:**

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

---

### 5. Get My Assignments 👤

Retrieve the current user's teaching assignments.

**Endpoint:** `GET /api/teacher-assignments/my-assignments`

**Authorization:** Teacher or Admin

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "classes": [
      {
        "id": 5,
        "name": "Grade 9",
        "student_count": 25
      },
      {
        "id": 6,
        "name": "Grade 10",
        "student_count": 30
      }
    ],
    "subjects": [
      {
        "id": 1,
        "name": "Mathematics"
      },
      {
        "id": 2,
        "name": "Science"
      }
    ]
  }
}
```

**Error Responses:**

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**Note:** If the user has no assignments, empty arrays are returned for classes and subjects.

---

### 6. Delete Class Assignment 🔒

Remove a teacher's assignment to a class.

**Endpoint:** `DELETE /api/teacher-assignments/class/:id`

**Authorization:** Admin only

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID of the class assignment to delete |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Class assignment removed successfully"
}
```

**Error Responses:**

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

**404 Not Found** - Assignment not found
```json
{
  "success": false,
  "error": "Class assignment not found"
}
```

---

### 7. Delete Subject Assignment 🔒

Remove a teacher's assignment to a subject.

**Endpoint:** `DELETE /api/teacher-assignments/subject/:id`

**Authorization:** Admin only

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID of the subject assignment to delete |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Subject assignment removed successfully"
}
```

**Error Responses:**

**401 Unauthorized** - Not authenticated
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden** - Not an admin
```json
{
  "success": false,
  "error": "Admin access required"
}
```

**404 Not Found** - Assignment not found
```json
{
  "success": false,
  "error": "Subject assignment not found"
}
```

---

## Access Control Behavior

### Overview

The Teacher Assignments feature implements role-based access control that restricts teacher access to students and assessments based on their assignments.

### Admin Users

**Role:** `admin`

**Access Level:** Unrestricted

- Can view all students regardless of class
- Can enter assessments for any student in any subject
- Can generate reports for any student or class
- Can manage teacher assignments
- **No assignment restrictions apply to admins**

### Teacher Users

**Role:** `user`

**Access Level:** Restricted by assignments

#### Class Assignments

When a teacher is assigned to a class:
- Can view students in that class
- Can access student details for students in that class
- Can generate reports for students in that class

**Without class assignments:**
- Student list will be empty
- Cannot access any student details
- Cannot generate student reports

#### Subject Assignments

When a teacher is assigned to a subject:
- Can enter assessments for that subject
- Subject appears in assessment entry dropdowns
- Can view grades for that subject

**Without subject assignments:**
- Subject dropdown will be empty
- Cannot enter assessments
- Assessment entry forms show "no assignments" message

#### Combined Access

Teachers need **both** class and subject assignments to fully function:
- **Class assignment** grants access to students
- **Subject assignment** grants permission to enter grades
- Teachers can only enter grades for students in their assigned classes AND for their assigned subjects

### Access Control Enforcement

#### API Level

All data access is validated at the API level:

1. **Student Endpoints** (`/api/students`)
   - Filtered by teacher's class assignments
   - Returns 403 for unauthorized student access

2. **Assessment Endpoints** (`/api/assessments`)
   - Validates subject assignment before allowing grade entry
   - Validates student access (via class assignment)
   - Returns 403 for unauthorized access

3. **Report Endpoints** (`/api/reports`)
   - Validates class assignment for class reports
   - Validates student access for individual reports
   - Returns 403 for unauthorized access

#### Middleware

The `TeacherAccessControl` middleware provides:

- `hasClassAccess($userId, $classLevelId)` - Check class access
- `hasSubjectAccess($userId, $subjectId)` - Check subject access
- `hasStudentAccess($userId, $studentId)` - Check student access
- `getAccessibleClasses($userId)` - Get all accessible class IDs
- `getAccessibleSubjects($userId)` - Get all accessible subject IDs

**Admin Bypass:** All access control checks automatically return `true` for admin users.

#### Security Logging

Unauthorized access attempts are logged with:
- User ID
- Attempted resource (student ID, class ID, etc.)
- Timestamp
- Action attempted

Logs are stored in the `access_logs` table for security auditing.

---

## Error Response Format

All API endpoints follow a consistent error response format:

```json
{
  "success": false,
  "error": "Human-readable error message"
}
```

### Common HTTP Status Codes

| Status Code | Meaning | When Used |
|-------------|---------|-----------|
| 200 | OK | Successful GET or DELETE request |
| 201 | Created | Successful POST request creating new resource |
| 400 | Bad Request | Invalid input, validation failure, duplicate entry |
| 401 | Unauthorized | Not authenticated (no valid session) |
| 403 | Forbidden | Authenticated but not authorized (wrong role or no assignment) |
| 404 | Not Found | Resource doesn't exist |
| 500 | Internal Server Error | Server-side error (database failure, etc.) |

### Access Denied (403) Scenarios

Teachers receive 403 errors when attempting to:

1. **Access unassigned students**
   ```json
   {
     "success": false,
     "error": "Access denied. You do not have permission to access this student."
   }
   ```

2. **Enter grades for unassigned subjects**
   ```json
   {
     "success": false,
     "error": "Access denied. You are not assigned to teach this subject."
   }
   ```

3. **Generate reports for unassigned classes**
   ```json
   {
     "success": false,
     "error": "Access denied. You do not have permission to access this class."
   }
   ```

4. **Access admin-only endpoints**
   ```json
   {
     "success": false,
     "error": "Admin access required"
   }
   ```

---

## Usage Examples

### Example 1: Admin Assigns Teacher to Classes and Subjects

```javascript
// Step 1: Assign teacher to multiple classes and subjects
const response = await fetch('/api/teacher-assignments/bulk', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    user_id: 2,
    class_level_ids: [5, 6, 7],  // Grade 9, 10, 11
    subject_ids: [1, 2]           // Mathematics, Science
  })
});

const result = await response.json();
// Result: { success: true, data: { classes_assigned: 3, subjects_assigned: 2 } }
```

### Example 2: Teacher Views Their Assignments

```javascript
// Teacher checks their assignments
const response = await fetch('/api/teacher-assignments/my-assignments');
const result = await response.json();

console.log(result.data.classes);  // [{ id: 5, name: "Grade 9", student_count: 25 }, ...]
console.log(result.data.subjects); // [{ id: 1, name: "Mathematics" }, ...]
```

### Example 3: Admin Views All Assignments

```javascript
// Get all assignments for a specific teacher
const response = await fetch('/api/teacher-assignments?user_id=2');
const result = await response.json();

// Result includes teacher info with all their classes and subjects
```

### Example 4: Admin Removes an Assignment

```javascript
// Remove a specific class assignment
const response = await fetch('/api/teacher-assignments/class/1', {
  method: 'DELETE'
});

const result = await response.json();
// Result: { success: true, message: "Class assignment removed successfully" }
```

### Example 5: Teacher Attempts Unauthorized Access

```javascript
// Teacher tries to access a student not in their assigned classes
const response = await fetch('/api/students/999');
const result = await response.json();

// Result: { success: false, error: "Access denied. You do not have permission to access this student." }
// Status: 403 Forbidden
```

---

## Database Schema Reference

### teacher_class_assignments

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Assignment ID |
| user_id | INT | FOREIGN KEY (users.id), NOT NULL | Teacher ID |
| class_level_id | INT | FOREIGN KEY (class_levels.id), NOT NULL | Class level ID |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |

**Indexes:**
- `idx_user_id` on `user_id`
- `idx_class_level_id` on `class_level_id`
- `unique_teacher_class` on `(user_id, class_level_id)`

### teacher_subject_assignments

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Assignment ID |
| user_id | INT | FOREIGN KEY (users.id), NOT NULL | Teacher ID |
| subject_id | INT | FOREIGN KEY (subjects.id), NOT NULL | Subject ID |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |

**Indexes:**
- `idx_user_id` on `user_id`
- `idx_subject_id` on `subject_id`
- `unique_teacher_subject` on `(user_id, subject_id)`

---

## Testing

### Manual Testing Checklist

1. **Assignment Creation**
   - [ ] Create class assignment as admin
   - [ ] Create subject assignment as admin
   - [ ] Attempt duplicate assignment (should fail)
   - [ ] Create bulk assignments

2. **Access Control**
   - [ ] Teacher with no assignments sees empty lists
   - [ ] Teacher with assignments sees filtered data
   - [ ] Teacher cannot access unassigned data via API
   - [ ] Admin has unrestricted access

3. **Assignment Management**
   - [ ] List all assignments as admin
   - [ ] Filter assignments by teacher
   - [ ] Delete class assignment
   - [ ] Delete subject assignment

4. **Error Handling**
   - [ ] Invalid user_id returns 404
   - [ ] Non-admin user cannot create assignments (403)
   - [ ] Unauthorized access returns 403
   - [ ] Invalid input returns 400

### Automated Test Files

- `backend/test_teacher_assignments.php` - Assignment CRUD operations
- `backend/test_access_control.php` - Access control enforcement
- `backend/test_access_control_api.php` - API-level access validation
- `backend/test_access_control_e2e.php` - End-to-end access control scenarios

---

## Migration and Deployment

### Database Migration

Run the migration script to create the required tables:

```bash
php backend/migrate_teacher_assignments.php
```

This creates:
- `teacher_class_assignments` table
- `teacher_subject_assignments` table
- Required indexes and constraints

### Verification

Verify the migration:

```bash
php backend/verify_teacher_assignments.php
```

### Rollback

To rollback the migration:

```sql
DROP TABLE IF EXISTS teacher_subject_assignments;
DROP TABLE IF EXISTS teacher_class_assignments;
```

---

## Support and Troubleshooting

### Common Issues

**Issue:** Teacher sees empty student list
- **Cause:** No class assignments
- **Solution:** Admin must assign teacher to classes

**Issue:** Teacher cannot enter grades
- **Cause:** No subject assignments
- **Solution:** Admin must assign teacher to subjects

**Issue:** 403 errors when accessing data
- **Cause:** Attempting to access unassigned resources
- **Solution:** Verify teacher has appropriate assignments

**Issue:** Duplicate assignment error
- **Cause:** Assignment already exists
- **Solution:** Check existing assignments before creating new ones

### Debug Mode

Enable debug logging in `TeacherAccessControl.php` to see detailed access control decisions.

---

## Version History

- **v1.0** (2024-11-03) - Initial release
  - Teacher-to-class assignments
  - Teacher-to-subject assignments
  - Access control middleware
  - Admin management UI
  - Teacher dashboard
