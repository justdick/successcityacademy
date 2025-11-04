# Student Management API Endpoints

This document describes the Student Management API endpoints implemented in the Student Management System.

## Authentication

All student endpoints require authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

## Endpoints

### 1. Create Student

**Endpoint:** `POST /api/students`

**Authentication:** Required (any authenticated user)

**Request Body:**
```json
{
    "student_id": "S001",
    "name": "John Doe",
    "class_level_id": 1
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "student_id": "S001",
        "name": "John Doe",
        "class_level_id": 1,
        "class_level_name": "Grade 10",
        "created_at": "2025-10-31 10:00:00",
        "updated_at": "2025-10-31 10:00:00"
    }
}
```

**Error Responses:**
- `400 Bad Request` - Missing required fields or validation error
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Class level not found
- `409 Conflict` - Student ID already exists

---

### 2. Get All Students

**Endpoint:** `GET /api/students`

**Authentication:** Required (any authenticated user)

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "student_id": "S001",
            "name": "John Doe",
            "class_level_id": 1,
            "class_level_name": "Grade 10",
            "created_at": "2025-10-31 10:00:00",
            "updated_at": "2025-10-31 10:00:00"
        },
        {
            "id": 2,
            "student_id": "S002",
            "name": "Jane Smith",
            "class_level_id": 2,
            "class_level_name": "Grade 11",
            "created_at": "2025-10-31 10:05:00",
            "updated_at": "2025-10-31 10:05:00"
        }
    ]
}
```

**Error Responses:**
- `401 Unauthorized` - Missing or invalid authentication token

---

### 3. Get Specific Student

**Endpoint:** `GET /api/students/{student_id}`

**Authentication:** Required (any authenticated user)

**URL Parameters:**
- `student_id` - The unique student identifier (e.g., "S001")

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "student_id": "S001",
        "name": "John Doe",
        "class_level_id": 1,
        "class_level_name": "Grade 10",
        "created_at": "2025-10-31 10:00:00",
        "updated_at": "2025-10-31 10:00:00"
    }
}
```

**Error Responses:**
- `400 Bad Request` - Student ID is empty
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Student not found

---

### 4. Update Student

**Endpoint:** `PUT /api/students/{student_id}`

**Authentication:** Required (any authenticated user)

**URL Parameters:**
- `student_id` - The unique student identifier (e.g., "S001")

**Request Body:**
```json
{
    "name": "Jane Doe",
    "class_level_id": 2
}
```

**Note:** The student_id cannot be changed. Only name and class_level_id can be updated.

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "student_id": "S001",
        "name": "Jane Doe",
        "class_level_id": 2,
        "class_level_name": "Grade 11",
        "created_at": "2025-10-31 10:00:00",
        "updated_at": "2025-10-31 10:15:00"
    }
}
```

**Error Responses:**
- `400 Bad Request` - Missing required fields or validation error
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Student or class level not found

---

### 5. Delete Student

**Endpoint:** `DELETE /api/students/{student_id}`

**Authentication:** Required (any authenticated user)

**URL Parameters:**
- `student_id` - The unique student identifier (e.g., "S001")

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "message": "Student deleted successfully"
    }
}
```

**Note:** Deleting a student will also cascade delete all associated grades due to the foreign key constraint.

**Error Responses:**
- `400 Bad Request` - Student ID is empty
- `401 Unauthorized` - Missing or invalid authentication token
- `404 Not Found` - Student not found

---

## Validation Rules

### Student ID
- Required
- Maximum 50 characters
- Must be unique

### Name
- Required
- Maximum 255 characters

### Class Level ID
- Required
- Must be a positive number
- Must reference an existing class level

---

## Error Response Format

All error responses follow this format:

```json
{
    "success": false,
    "error": "Error message describing what went wrong"
}
```

---

## Testing

You can test the student endpoints using the provided test script:

```bash
php backend/test_student_management.php
```

This script tests:
- Database connectivity
- Student creation with validation
- Duplicate student ID prevention
- Fetching all students
- Fetching specific student
- Updating student information
- Deleting students
- Student model validation

---

## Implementation Details

### Files
- **Controller:** `backend/controllers/StudentController.php`
- **Model:** `backend/models/Student.php`
- **Router:** `backend/api/index.php` (student routes section)
- **Test:** `backend/test_student_management.php`

### Database Table
The students table structure:
```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    class_level_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_level_id) REFERENCES class_levels(id)
);
```

### Features
- All endpoints require authentication via JWT token
- Student data includes class level name via JOIN query
- Duplicate student IDs are prevented by unique constraint
- Comprehensive validation on all inputs
- Cascade deletion of grades when student is deleted
- Proper HTTP status codes for all responses
- Detailed error messages for debugging
