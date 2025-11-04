# Authentication API Endpoints

## Overview
The authentication API provides endpoints for user login and logout using JWT (JSON Web Tokens).

## Base URL
```
http://localhost/backend/api
```

## Endpoints

### 1. Login
Authenticate a user and receive a JWT token.

**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
    "username": "admin",
    "password": "admin123"
}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "user": {
            "id": 1,
            "username": "admin",
            "role": "admin"
        }
    }
}
```

**Error Responses:**

- **400 Bad Request** - Missing or empty credentials
```json
{
    "success": false,
    "error": "Username and password are required"
}
```

- **401 Unauthorized** - Invalid credentials
```json
{
    "success": false,
    "error": "Invalid username or password"
}
```

- **500 Internal Server Error** - Server error
```json
{
    "success": false,
    "error": "An error occurred during login"
}
```

### 2. Logout
Logout the current user (client-side token removal).

**Endpoint:** `POST /auth/logout`

**Request Body:** None required

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "message": "Logged out successfully"
    }
}
```

## Authentication
After successful login, include the JWT token in the `Authorization` header for protected endpoints:

```
Authorization: Bearer <token>
```

## CORS Configuration
The API is configured to accept requests from `http://localhost:5173` (React frontend).
This can be changed in `backend/config/config.php`.

## Testing
You can test the endpoints using curl:

```bash
# Login
curl -X POST http://localhost/backend/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Logout
curl -X POST http://localhost/backend/api/auth/logout \
  -H "Content-Type: application/json"
```

## Default Credentials
- **Username:** admin
- **Password:** admin123
- **Role:** admin

These credentials are seeded in the database via `backend/database/seed.sql`.
