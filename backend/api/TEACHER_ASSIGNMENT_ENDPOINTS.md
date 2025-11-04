# Teacher Assignment API Endpoints

This document describes the API endpoints for managing teacher assignments to classes and subjects.

## Authentication

All endpoints require JWT authentication via the `Authorization: Bearer <token>` header.
- Admin-only endpoints require the user to have `role: "admin"`
- Teacher endpoints are accessible to both admins and teachers

## Endpoints

### 1. Assign Teacher to Class

**POST** `/api/teacher-assignments/class`

Assigns a teacher to a specific class level.

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "class_level_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Teacher assigned to class successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "class_level_id": 1,
    "created_at": "2025-11-03 13:15:55",
    "username": "teacher1",
    "class_level_name": "Grade 7"
  }
}
```

**Error Responses:**
- `400` - Invalid input (missing fields, invalid IDs, user not a teacher)
- `403` - Not authorized (non-admin user)
- `404` - User or class level not found
- `409` - Duplicate assignment (teacher already assigned to this class)

---

### 2. Assign Teacher to Subject

**POST** `/api/teacher-assignments/subject`

Assigns a teacher to a specific subject.

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "subject_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Teacher assigned to subject successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "subject_id": 1,
    "created_at": "2025-11-03 13:15:55",
    "username": "teacher1",
    "subject_name": "Mathematics"
  }
}
```

**Error Responses:**
- `400` - Invalid input (missing fields, invalid IDs, user not a teacher)
- `403` - Not authorized (non-admin user)
- `404` - User or subject not found
- `409` - Duplicate assignment (teacher already assigned to this subject)

---

### 3. Bulk Assign Teacher

**POST** `/api/teacher-assignments/bulk`

Assigns a teacher to multiple classes and/or subjects in a single operation.

**Authorization:** Admin only

**Request Body:**
```json
{
  "user_id": 2,
  "class_level_ids": [1, 2, 3],
  "subject_ids": [1, 2]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Bulk assignments created successfully",
  "data": {
    "classes_assigned": 3,
    "subjects_assigned": 2
  }
}
```

**Notes:**
- Skips duplicate assignments (doesn't fail if some already exist)
- Validates all IDs before creating any assignments
- Uses database transaction (all or nothing if validation fails)

**Error Responses:**
- `400` - Invalid input (missing user_id, invalid IDs, user not a teacher, class/subject not found)
- `403` - Not authorized (non-admin user)
- `404` - User not found

---

### 4. Get All Teacher Assignments

**GET** `/api/teacher-assignments`

Retrieves all teacher assignments with optional filtering.

**Authorization:** Admin only

**Query Parameters:**
- `user_id` (optional) - Filter by specific teacher
- `class_level_id` (optional) - Filter by specific class
- `subject_id` (optional) - Filter by specific subject

**Example:** `/api/teacher-assignments?user_id=2`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 2,
      "username": "teacher1",
      "classes": [
        {"id": 1, "name": "Grade 7"},
        {"id": 2, "name": "Grade 8"}
      ],
      "subjects": [
        {"id": 1, "name": "Mathematics"},
        {"id": 2, "name": "Science"}
      ]
    }
  ]
}
```

**Error Responses:**
- `403` - Not authorized (non-admin user)

---

### 5. Get My Assignments

**GET** `/api/teacher-assignments/my-assignments`

Retrieves the current user's teaching assignments.

**Authorization:** Authenticated user (teacher or admin)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "classes": [
      {
        "id": 1,
        "name": "Grade 7",
        "student_count": 25
      },
      {
        "id": 2,
        "name": "Grade 8",
        "student_count": 30
      }
    ],
    "subjects": [
      {"id": 1, "name": "Mathematics"},
      {"id": 2, "name": "Science"}
    ]
  }
}
```

**Notes:**
- Includes student count for each assigned class
- Returns empty arrays if teacher has no assignments

---

### 6. Delete Class Assignment

**DELETE** `/api/teacher-assignments/class/:id`

Removes a teacher's assignment to a class.

**Authorization:** Admin only

**URL Parameters:**
- `id` - The assignment ID (not the class_level_id)

**Example:** `/api/teacher-assignments/class/5`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "message": "Class assignment removed successfully"
  }
}
```

**Error Responses:**
- `400` - Invalid assignment ID
- `403` - Not authorized (non-admin user)
- `404` - Assignment not found

---

### 7. Delete Subject Assignment

**DELETE** `/api/teacher-assignments/subject/:id`

Removes a teacher's assignment to a subject.

**Authorization:** Admin only

**URL Parameters:**
- `id` - The assignment ID (not the subject_id)

**Example:** `/api/teacher-assignments/subject/5`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "message": "Subject assignment removed successfully"
  }
}
```

**Error Responses:**
- `400` - Invalid assignment ID
- `403` - Not authorized (non-admin user)
- `404` - Assignment not found

---

## Database Tables

### teacher_class_assignments

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to users table |
| class_level_id | INT | Foreign key to class_levels table |
| created_at | TIMESTAMP | Assignment creation timestamp |

**Constraints:**
- Unique constraint on (user_id, class_level_id)
- Cascade delete on user or class deletion

### teacher_subject_assignments

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to users table |
| subject_id | INT | Foreign key to subjects table |
| created_at | TIMESTAMP | Assignment creation timestamp |

**Constraints:**
- Unique constraint on (user_id, subject_id)
- Cascade delete on user or subject deletion

---

## Testing

A test script is available at `backend/test_direct_assignment.php` for testing the controller methods directly.

To run tests:
```bash
php backend/test_direct_assignment.php
```

## Implementation Notes

1. **Duplicate Prevention:** All assignment endpoints check for existing assignments and return 409 Conflict if attempting to create a duplicate.

2. **Validation:** All endpoints validate that:
   - User exists and has role "user" (teacher)
   - Class levels and subjects exist
   - IDs are positive integers

3. **Admin Bypass:** Admins can view all assignments and are not restricted by their own assignments.

4. **Transaction Safety:** Bulk assignment uses database transactions to ensure atomicity.

5. **Student Counts:** The "my-assignments" endpoint includes student counts for each class using a LEFT JOIN with the students table.
