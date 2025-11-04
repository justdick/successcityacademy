# Grade Management API Endpoints

This document describes the Grade Management API endpoints for the Student Management System.

## Authentication

All grade endpoints require authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

## Endpoints

### 1. Add Grade

Add a new grade for a student.

**Endpoint:** `POST /api/grades`

**Authentication:** Required (any authenticated user)

**Request Body:**
```json
{
    "student_id": "S001",
    "subject_id": 1,
    "mark": 85.5
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "student_id": "S001",
        "subject_id": 1,
        "subject_name": "Mathematics",
        "mark": "85.50",
        "created_at": "2025-10-31 10:00:00"
    }
}
```

**Error Responses:**

- **400 Bad Request** - Missing required fields:
```json
{
    "success": false,
    "error": "Student ID, subject ID, and mark are required"
}
```

- **400 Bad Request** - Invalid mark value:
```json
{
    "success": false,
    "error": "Mark must be between 0 and 100"
}
```

- **404 Not Found** - Student not found:
```json
{
    "success": false,
    "error": "Student with ID S001 not found"
}
```

- **404 Not Found** - Subject not found:
```json
{
    "success": false,
    "error": "Subject not found"
}
```

- **401 Unauthorized** - Missing or invalid token:
```json
{
    "success": false,
    "error": "Unauthorized access - please login"
}
```

### 2. Get Student Grades

Retrieve all grades for a specific student.

**Endpoint:** `GET /api/students/{student_id}/grades`

**Authentication:** Required (any authenticated user)

**URL Parameters:**
- `student_id` (string) - The unique identifier of the student

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "student_id": "S001",
            "name": "John Doe",
            "class_level_id": 1,
            "class_level_name": "Grade 10"
        },
        "grades": [
            {
                "id": 1,
                "student_id": "S001",
                "subject_id": 1,
                "subject_name": "Mathematics",
                "mark": "85.50",
                "created_at": "2025-10-31 10:00:00"
            },
            {
                "id": 2,
                "student_id": "S001",
                "subject_id": 2,
                "subject_name": "English",
                "mark": "92.00",
                "created_at": "2025-10-31 10:05:00"
            }
        ]
    }
}
```

**Error Responses:**

- **400 Bad Request** - Missing student ID:
```json
{
    "success": false,
    "error": "Student ID is required"
}
```

- **404 Not Found** - Student not found:
```json
{
    "success": false,
    "error": "Student with ID S001 not found"
}
```

- **401 Unauthorized** - Missing or invalid token:
```json
{
    "success": false,
    "error": "Unauthorized access - please login"
}
```

## Validation Rules

### Mark Validation
- **Required:** Yes
- **Type:** Numeric (integer or decimal)
- **Range:** 0 to 100 (inclusive)
- **Examples:** 
  - Valid: 0, 50, 85.5, 100
  - Invalid: -1, 150, "abc"

### Student ID Validation
- **Required:** Yes
- **Type:** String
- **Must exist:** Student must exist in the database

### Subject ID Validation
- **Required:** Yes
- **Type:** Positive integer
- **Must exist:** Subject must exist in the database

## Example Usage

### Add a Grade

```bash
curl -X POST http://localhost/backend/api/grades \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-jwt-token>" \
  -d '{
    "student_id": "S001",
    "subject_id": 1,
    "mark": 85.5
  }'
```

### Get Student Grades

```bash
curl -X GET http://localhost/backend/api/students/S001/grades \
  -H "Authorization: Bearer <your-jwt-token>"
```

## Notes

- Grades are automatically deleted when the associated student is deleted (cascade delete)
- Marks are stored with 2 decimal places precision
- Grades are returned in descending order by creation date (most recent first)
- The response includes both student information and their grades for convenience
- Multiple grades can be added for the same student and subject (e.g., for different assessments)
