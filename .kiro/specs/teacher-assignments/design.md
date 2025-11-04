# Design Document

## Overview

The Teacher Assignments feature implements role-based access control by introducing teacher-to-class and teacher-to-subject assignment relationships. This design ensures that teachers can only access students and enter grades for their assigned classes and subjects, while admins retain unrestricted access. The implementation includes new database tables, API endpoints for assignment management, middleware for access control validation, and UI components for both admin assignment management and teacher-restricted views.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                         Frontend                             │
├─────────────────────────────────────────────────────────────┤
│  Admin Components          │  Teacher Components            │
│  - Teacher Assignments UI  │  - Filtered Student List       │
│  - Bulk Assignment Form    │  - Filtered Assessment Entry   │
│  - Assignment List View    │  - Filtered Reports            │
│                            │  - My Assignments Dashboard    │
└──────────────┬─────────────┴────────────────┬───────────────┘
               │                              │
               │         API Layer            │
               │                              │
┌──────────────┴──────────────────────────────┴───────────────┐
│                      Backend API                             │
├─────────────────────────────────────────────────────────────┤
│  Assignment Endpoints      │  Access Control Middleware     │
│  - Create Assignment       │  - Validate Teacher Access     │
│  - List Assignments        │  - Check Class Assignment      │
│  - Delete Assignment       │  - Check Subject Assignment    │
│  - Bulk Assign             │  - Admin Bypass                │
└──────────────┬─────────────┴────────────────┬───────────────┘
               │                              │
               │        Database Layer        │
               │                              │
┌──────────────┴──────────────────────────────┴───────────────┐
│                        Database                              │
├─────────────────────────────────────────────────────────────┤
│  New Tables:                                                 │
│  - teacher_class_assignments                                 │
│  - teacher_subject_assignments                               │
│                                                              │
│  Existing Tables (referenced):                               │
│  - users (teachers)                                          │
│  - class_levels                                              │
│  - subjects                                                  │
│  - students                                                  │
│  - assessments                                               │
└─────────────────────────────────────────────────────────────┘
```

### Access Control Flow

```
Teacher Request → API Endpoint → Access Control Middleware
                                         ↓
                                  Check User Role
                                         ↓
                        ┌────────────────┴────────────────┐
                        │                                 │
                    Is Admin?                         Is Teacher?
                        │                                 │
                        ↓                                 ↓
                  Allow Access                  Check Assignments
                                                         ↓
                                         ┌───────────────┴───────────────┐
                                         │                               │
                                  Has Assignment?                  No Assignment?
                                         │                               │
                                         ↓                               ↓
                                   Allow Access                    Deny (403)
```

## Components and Interfaces

### Database Schema

#### teacher_class_assignments Table

```sql
CREATE TABLE teacher_class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_level_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_level_id) REFERENCES class_levels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_class (user_id, class_level_id)
);
```

**Purpose:** Links teachers to the classes they teach

**Fields:**
- `id`: Primary key
- `user_id`: Foreign key to users table (teacher)
- `class_level_id`: Foreign key to class_levels table
- `created_at`: Timestamp of assignment creation

**Constraints:**
- Unique constraint prevents duplicate assignments
- Cascade delete removes assignments when teacher or class is deleted

#### teacher_subject_assignments Table

```sql
CREATE TABLE teacher_subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (user_id, subject_id)
);
```

**Purpose:** Links teachers to the subjects they teach

**Fields:**
- `id`: Primary key
- `user_id`: Foreign key to users table (teacher)
- `subject_id`: Foreign key to subjects table
- `created_at`: Timestamp of assignment creation

**Constraints:**
- Unique constraint prevents duplicate assignments
- Cascade delete removes assignments when teacher or subject is deleted

### API Endpoints

#### Teacher Assignment Management

**POST /api/teacher-assignments/class**
- **Purpose:** Assign a teacher to a class
- **Auth:** Admin only
- **Request Body:**
  ```json
  {
    "user_id": 2,
    "class_level_id": 5
  }
  ```
- **Response:**
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

**POST /api/teacher-assignments/subject**
- **Purpose:** Assign a teacher to a subject
- **Auth:** Admin only
- **Request Body:**
  ```json
  {
    "user_id": 2,
    "subject_id": 3
  }
  ```
- **Response:**
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

**POST /api/teacher-assignments/bulk**
- **Purpose:** Bulk assign classes and subjects to a teacher
- **Auth:** Admin only
- **Request Body:**
  ```json
  {
    "user_id": 2,
    "class_level_ids": [5, 6, 7],
    "subject_ids": [1, 2, 3]
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "message": "Bulk assignments created successfully",
    "data": {
      "classes_assigned": 3,
      "subjects_assigned": 3
    }
  }
  ```

**GET /api/teacher-assignments**
- **Purpose:** List all teacher assignments
- **Auth:** Admin only
- **Query Parameters:** `user_id` (optional), `class_level_id` (optional), `subject_id` (optional)
- **Response:**
  ```json
  {
    "success": true,
    "data": [
      {
        "user_id": 2,
        "username": "teacher1",
        "classes": [
          {"id": 5, "name": "Grade 9"},
          {"id": 6, "name": "Grade 10"}
        ],
        "subjects": [
          {"id": 1, "name": "Mathematics"},
          {"id": 2, "name": "Science"}
        ]
      }
    ]
  }
  ```

**GET /api/teacher-assignments/my-assignments**
- **Purpose:** Get current teacher's assignments
- **Auth:** Teacher or Admin
- **Response:**
  ```json
  {
    "success": true,
    "data": {
      "classes": [
        {"id": 5, "name": "Grade 9", "student_count": 25},
        {"id": 6, "name": "Grade 10", "student_count": 30}
      ],
      "subjects": [
        {"id": 1, "name": "Mathematics"},
        {"id": 2, "name": "Science"}
      ]
    }
  }
  ```

**DELETE /api/teacher-assignments/class/:id**
- **Purpose:** Remove a class assignment
- **Auth:** Admin only
- **Response:**
  ```json
  {
    "success": true,
    "message": "Class assignment removed successfully"
  }
  ```

**DELETE /api/teacher-assignments/subject/:id**
- **Purpose:** Remove a subject assignment
- **Auth:** Admin only
- **Response:**
  ```json
  {
    "success": true,
    "message": "Subject assignment removed successfully"
  }
  ```

### Access Control Middleware

#### TeacherAccessControl Class

**Location:** `backend/middleware/TeacherAccessControl.php`

**Methods:**

```php
class TeacherAccessControl {
    /**
     * Check if user has access to a specific class
     */
    public static function hasClassAccess($userId, $classLevelId) {
        // Admins have access to all classes
        if (self::isAdmin($userId)) {
            return true;
        }
        
        // Check if teacher has class assignment
        $query = "SELECT COUNT(*) as count 
                  FROM teacher_class_assignments 
                  WHERE user_id = ? AND class_level_id = ?";
        // Execute and return true if count > 0
    }
    
    /**
     * Check if user has access to a specific subject
     */
    public static function hasSubjectAccess($userId, $subjectId) {
        // Admins have access to all subjects
        if (self::isAdmin($userId)) {
            return true;
        }
        
        // Check if teacher has subject assignment
        $query = "SELECT COUNT(*) as count 
                  FROM teacher_subject_assignments 
                  WHERE user_id = ? AND subject_id = ?";
        // Execute and return true if count > 0
    }
    
    /**
     * Get all class IDs accessible by user
     */
    public static function getAccessibleClasses($userId) {
        // Admins get all classes
        if (self::isAdmin($userId)) {
            return self::getAllClassIds();
        }
        
        // Teachers get assigned classes
        $query = "SELECT class_level_id 
                  FROM teacher_class_assignments 
                  WHERE user_id = ?";
        // Execute and return array of class IDs
    }
    
    /**
     * Get all subject IDs accessible by user
     */
    public static function getAccessibleSubjects($userId) {
        // Admins get all subjects
        if (self::isAdmin($userId)) {
            return self::getAllSubjectIds();
        }
        
        // Teachers get assigned subjects
        $query = "SELECT subject_id 
                  FROM teacher_subject_assignments 
                  WHERE user_id = ?";
        // Execute and return array of subject IDs
    }
    
    /**
     * Check if user can access a specific student
     */
    public static function hasStudentAccess($userId, $studentId) {
        // Admins have access to all students
        if (self::isAdmin($userId)) {
            return true;
        }
        
        // Get student's class level
        $studentClass = self::getStudentClassLevel($studentId);
        
        // Check if teacher has access to that class
        return self::hasClassAccess($userId, $studentClass);
    }
}
```

### Frontend Components

#### Admin: Teacher Assignments Management

**Component:** `TeacherAssignments.jsx`

**Features:**
- List all teachers with their assignments
- Add class assignment to teacher
- Add subject assignment to teacher
- Bulk assign classes and subjects
- Remove assignments
- Filter by teacher, class, or subject

**UI Layout:**
```
┌─────────────────────────────────────────────────────────┐
│  Teacher Assignments                                     │
├─────────────────────────────────────────────────────────┤
│  [Select Teacher ▼] [Add Assignment]                    │
├─────────────────────────────────────────────────────────┤
│  Teacher: teacher1                                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Assigned Classes:                                 │  │
│  │ • Grade 9 (25 students)              [Remove]    │  │
│  │ • Grade 10 (30 students)             [Remove]    │  │
│  │ [+ Add Class]                                     │  │
│  └───────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Assigned Subjects:                                │  │
│  │ • Mathematics                        [Remove]    │  │
│  │ • Science                            [Remove]    │  │
│  │ [+ Add Subject]                                   │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

#### Teacher: My Assignments Dashboard

**Component:** `MyAssignments.jsx`

**Features:**
- Display teacher's assigned classes
- Display teacher's assigned subjects
- Show student count per class
- Link to filtered student list
- Link to assessment entry

**UI Layout:**
```
┌─────────────────────────────────────────────────────────┐
│  My Teaching Assignments                                 │
├─────────────────────────────────────────────────────────┤
│  My Classes:                                             │
│  ┌─────────────────┐  ┌─────────────────┐              │
│  │ Grade 9         │  │ Grade 10        │              │
│  │ 25 students     │  │ 30 students     │              │
│  │ [View Students] │  │ [View Students] │              │
│  └─────────────────┘  └─────────────────┘              │
│                                                          │
│  My Subjects:                                            │
│  • Mathematics                                           │
│  • Science                                               │
│  • English                                               │
└─────────────────────────────────────────────────────────┘
```

#### Teacher: Filtered Student List

**Modification to:** `Students.jsx`

**Changes:**
- Filter students by teacher's assigned classes
- Show message if no assignments
- Display which classes are being shown

#### Teacher: Filtered Assessment Entry

**Modification to:** `AssessmentEntry.jsx` and `AssessmentGrid.jsx`

**Changes:**
- Filter subject dropdown to teacher's assigned subjects
- Filter student list to teacher's assigned classes
- Validate on submit

## Data Models

### TeacherClassAssignment Model

```javascript
{
  id: number,
  user_id: number,
  class_level_id: number,
  created_at: string,
  // Joined data
  teacher_name: string,
  class_name: string
}
```

### TeacherSubjectAssignment Model

```javascript
{
  id: number,
  user_id: number,
  subject_id: number,
  created_at: string,
  // Joined data
  teacher_name: string,
  subject_name: string
}
```

### TeacherAssignmentSummary Model

```javascript
{
  user_id: number,
  username: string,
  classes: [
    {
      id: number,
      name: string,
      student_count: number
    }
  ],
  subjects: [
    {
      id: number,
      name: string
    }
  ]
}
```

## Error Handling

### Access Denied Errors

**HTTP 403 Forbidden**
- Returned when teacher attempts to access data outside assignments
- Logged for security auditing
- User-friendly message displayed in UI

**Example Response:**
```json
{
  "success": false,
  "error": "Access denied. You do not have permission to access this resource."
}
```

### Validation Errors

**HTTP 400 Bad Request**
- Invalid user_id (not a teacher)
- Invalid class_level_id or subject_id
- Duplicate assignment attempt

**Example Response:**
```json
{
  "success": false,
  "error": "Invalid assignment. Teacher is already assigned to this class."
}
```

### Not Found Errors

**HTTP 404 Not Found**
- Assignment ID doesn't exist
- Teacher, class, or subject doesn't exist

## Testing Strategy

### Unit Tests

**Backend:**
- Test TeacherAccessControl methods
- Test assignment creation/deletion
- Test validation logic
- Test admin bypass logic

**Frontend:**
- Test component rendering with/without assignments
- Test filtering logic
- Test form validation

### Integration Tests

**API Tests:**
- Test assignment CRUD operations
- Test access control on student endpoints
- Test access control on assessment endpoints
- Test access control on report endpoints
- Test bulk assignment operations

**End-to-End Tests:**
- Admin creates assignments
- Teacher views filtered student list
- Teacher enters grades for assigned subjects
- Teacher attempts to access unassigned data (should fail)
- Admin accesses all data (should succeed)

### Access Control Test Cases

1. **Teacher with no assignments:**
   - Should see empty student list
   - Should see no subjects in assessment form
   - Should see message about no assignments

2. **Teacher with class assignments only:**
   - Should see students from assigned classes
   - Should see all subjects (or none if no subject assignments)

3. **Teacher with subject assignments only:**
   - Should see all students (or none if no class assignments)
   - Should see only assigned subjects in forms

4. **Teacher with both assignments:**
   - Should see students from assigned classes
   - Should see only assigned subjects in forms
   - Should be able to enter grades for assigned combinations

5. **Admin user:**
   - Should see all students
   - Should see all subjects
   - Should not be restricted by assignments

6. **API bypass attempts:**
   - Direct API calls with unassigned IDs should return 403
   - Should be logged for security audit

## Security Considerations

### Server-Side Validation

- All access control checks MUST be performed on the server
- Never rely solely on UI filtering
- Validate assignments on every API request
- Log unauthorized access attempts

### SQL Injection Prevention

- Use prepared statements for all queries
- Validate and sanitize all input
- Use parameterized queries in access control checks

### Session Security

- Verify user session on every request
- Check user role before applying access control
- Invalidate sessions on role changes

## Performance Considerations

### Caching

- Cache teacher assignments in session
- Refresh cache when assignments change
- Use database indexes on foreign keys

### Database Indexes

```sql
-- Indexes for teacher_class_assignments
CREATE INDEX idx_user_id ON teacher_class_assignments(user_id);
CREATE INDEX idx_class_level_id ON teacher_class_assignments(class_level_id);

-- Indexes for teacher_subject_assignments
CREATE INDEX idx_user_id ON teacher_subject_assignments(user_id);
CREATE INDEX idx_subject_id ON teacher_subject_assignments(subject_id);
```

### Query Optimization

- Use JOINs to fetch assignments with related data
- Limit result sets appropriately
- Use EXISTS for access checks instead of COUNT when possible

## Migration Strategy

### Database Migration

1. Create new tables (teacher_class_assignments, teacher_subject_assignments)
2. Add indexes
3. No data migration needed (fresh start for assignments)

### Backward Compatibility

- Existing API endpoints continue to work
- Add access control middleware gradually
- Admin users unaffected
- Teachers without assignments see empty lists (not errors)

### Rollout Plan

1. **Phase 1:** Create database tables and API endpoints
2. **Phase 2:** Implement access control middleware
3. **Phase 3:** Update frontend components
4. **Phase 4:** Admin assigns teachers to classes/subjects
5. **Phase 5:** Enable access control enforcement

## Future Enhancements

### Potential Additions

1. **Time-based assignments:** Assign teachers for specific terms/semesters
2. **Class sections:** Support multiple sections per grade level
3. **Co-teaching:** Multiple teachers for same class-subject combination
4. **Assignment history:** Track changes to assignments over time
5. **Notification system:** Notify teachers when assigned to new classes
6. **Assignment templates:** Save and reuse assignment patterns
7. **Bulk import:** Import assignments from CSV/Excel
8. **Assignment reports:** Analytics on teacher workload distribution