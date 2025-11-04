# User Management API Endpoints

This document describes the newly implemented API endpoints for user management, subject management, and class level management.

## User Management Endpoints (Admin Only)

### Create User

- **Endpoint**: `POST /api/users`
- **Authentication**: Admin only (JWT token required)
- **Request Body**:

```json
{
  "username": "teacher1",
  "password": "password123",
  "role": "user"
}
```

- **Response** (201 Created):

```json
{
  "success": true,
  "data": {
    "id": 2,
    "username": "teacher1",
    "role": "user",
    "created_at": "2025-10-31 10:00:00",
    "updated_at": "2025-10-31 10:00:00"
  }
}
```

- **Validations**:
  - Username: 3-50 characters, alphanumeric
  - Password: Minimum 6 characters
  - Role: Must be 'admin' or 'user'
  - Duplicate username check (returns 409 Conflict)

### Get All Users

- **Endpoint**: `GET /api/users`
- **Authentication**: Admin only (JWT token required)
- **Response** (200 OK):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "username": "admin",
      "role": "admin",
      "created_at": "2025-10-31 09:00:00",
      "updated_at": "2025-10-31 09:00:00"
    },
    {
      "id": 2,
      "username": "teacher1",
      "role": "user",
      "created_at": "2025-10-31 10:00:00",
      "updated_at": "2025-10-31 10:00:00"
    }
  ]
}
```

## Subject Management Endpoints

### Get All Subjects

- **Endpoint**: `GET /api/subjects`
- **Authentication**: Required (any authenticated user)
- **Response** (200 OK):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Mathematics",
      "created_at": "2025-10-31 09:00:00"
    },
    {
      "id": 2,
      "name": "English",
      "created_at": "2025-10-31 09:00:00"
    }
  ]
}
```

### Create Subject

- **Endpoint**: `POST /api/subjects`
- **Authentication**: Admin only (JWT token required)
- **Request Body**:

```json
{
  "name": "Computer Science"
}
```

- **Response** (201 Created):

```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Computer Science",
    "created_at": "2025-10-31 10:00:00"
  }
}
```

- **Validations**:
  - Name: Required, max 255 characters
  - Duplicate name check (returns 409 Conflict)

### Delete Subject

- **Endpoint**: `DELETE /api/subjects/{id}`
- **Authentication**: Admin only (JWT token required)
- **Response** (200 OK):

```json
{
  "success": true,
  "data": {
    "message": "Subject deleted successfully"
  }
}
```

- **Validations**:
  - Subject must exist (returns 404 Not Found)
  - Subject must not be in use by any grades (returns 409 Conflict)

## Class Level Management Endpoints

### Get All Class Levels

- **Endpoint**: `GET /api/class-levels`
- **Authentication**: Required (any authenticated user)
- **Response** (200 OK):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Grade 9",
      "created_at": "2025-10-31 09:00:00"
    },
    {
      "id": 2,
      "name": "Grade 10",
      "created_at": "2025-10-31 09:00:00"
    }
  ]
}
```

### Create Class Level

- **Endpoint**: `POST /api/class-levels`
- **Authentication**: Admin only (JWT token required)
- **Request Body**:

```json
{
  "name": "Grade 13"
}
```

- **Response** (201 Created):

```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "Grade 13",
    "created_at": "2025-10-31 10:00:00"
  }
}
```

- **Validations**:
  - Name: Required, max 50 characters
  - Duplicate name check (returns 409 Conflict)

### Delete Class Level

- **Endpoint**: `DELETE /api/class-levels/{id}`
- **Authentication**: Admin only (JWT token required)
- **Response** (200 OK):

```json
{
  "success": true,
  "data": {
    "message": "Class level deleted successfully"
  }
}
```

- **Validations**:
  - Class level must exist (returns 404 Not Found)
  - Class level must not be in use by any students (returns 409 Conflict)

## Authentication

All protected endpoints require a JWT token in the Authorization header:

```
Authorization: Bearer <token>
```

Admin-only endpoints will return 403 Forbidden if accessed by non-admin users.

## Error Responses

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "error": "Error message here"
}
```

Common HTTP status codes:

- 200 OK - Successful GET/DELETE
- 201 Created - Successful POST
- 400 Bad Request - Validation errors
- 401 Unauthorized - Missing or invalid token
- 403 Forbidden - Insufficient permissions
- 404 Not Found - Resource not found
- 409 Conflict - Duplicate entry or resource in use
- 500 Internal Server Error - Server errors

## Files Created

1. `backend/controllers/UserController.php` - User management logic
2. `backend/controllers/SubjectController.php` - Subject management logic
3. `backend/controllers/ClassLevelController.php` - Class level management logic
4. `backend/api/index.php` - Updated with new routes

## Requirements Satisfied

- **Requirement 2.1-2.5**: User management with admin-only access
- **Requirement 8.1-8.6**: Subject and class level management with admin controls
- Password hashing using `password_hash()`
- Duplicate checks for usernames, subject names, and class level names
- Usage checks before deletion to maintain referential integrity
- Proper authentication and authorization middleware
