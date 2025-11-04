# Student Management System - Design Document

## Overview

The Student Management System is a secure application for recording and managing student grades. The system requires user authentication with an admin-managed user system. A seeded admin account will be created during setup, and the admin can add additional users. The system uses MySQL for persistent storage with a modular architecture that separates authentication, data models, business logic, and user interface concerns.

## Architecture

The system follows a client-server architecture with RESTful API:

```
┌─────────────────────────────┐
│   Frontend (React + Tailwind)│
│   - Login Page              │
│   - Student Management UI   │
│   - Grade Entry Forms       │
│   - User Management (Admin) │
└─────────────┬───────────────┘
              │ HTTP/REST API (with JWT)
┌─────────────▼───────────────┐
│   Backend (PHP)             │
│   - Authentication          │
│   - API Controllers         │
│   - Business Logic          │
│   - Authorization Middleware│
└─────────────┬───────────────┘
              │ SQL Queries
┌─────────────▼───────────────┐
│   Database (MySQL)          │
│   - users table             │
│   - students table          │
│   - grades table            │
└─────────────────────────────┘
```

### Technology Stack

- **Frontend**: React with Tailwind CSS
- **Backend**: PHP (RESTful API)
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Tokens) with PHP
- **Password Hashing**: PHP password_hash() function
- **API Communication**: JSON over HTTP

## Components and Interfaces

### 1. Database Schema (MySQL)

#### users table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### subjects table
```sql
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### class_levels table
```sql
CREATE TABLE class_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### students table
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

#### grades table
```sql
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    subject_id INT NOT NULL,
    mark DECIMAL(5,2) NOT NULL CHECK (mark >= 0 AND mark <= 100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
```

### 2. Backend API (PHP)

#### API Endpoints

**Authentication** (Public)
- `POST /api/auth/login` - Login with username and password, returns JWT token
- `POST /api/auth/logout` - Logout and invalidate session

**Users** (Admin only)
- `POST /api/users` - Create a new user (admin only)
- `GET /api/users` - Get all users (admin only)

**Subjects** (Authenticated, Admin for create/delete)
- `GET /api/subjects` - Get all subjects
- `POST /api/subjects` - Create a new subject (admin only)
- `DELETE /api/subjects/{id}` - Delete a subject (admin only)

**Class Levels** (Authenticated, Admin for create/delete)
- `GET /api/class-levels` - Get all class levels
- `POST /api/class-levels` - Create a new class level (admin only)
- `DELETE /api/class-levels/{id}` - Delete a class level (admin only)

**Students** (Authenticated)
- `POST /api/students` - Create a new student
- `GET /api/students` - Get all students
- `GET /api/students/{student_id}` - Get a specific student
- `PUT /api/students/{student_id}` - Update student information
- `DELETE /api/students/{student_id}` - Delete a student

**Grades** (Authenticated)
- `POST /api/grades` - Add a grade for a student
- `GET /api/students/{student_id}/grades` - Get all grades for a student

#### PHP Classes

```php
class Database {
    + connect(): PDO
}

class User {
    + id: int
    + username: string
    + password_hash: string
    + role: string
    + created_at: string
    + updated_at: string
}

class Subject {
    + id: int
    + name: string
    + created_at: string
}

class ClassLevel {
    + id: int
    + name: string
    + created_at: string
}

class Student {
    + id: int
    + student_id: string
    + name: string
    + class_level_id: int
    + class_level_name: string (from join)
    + created_at: string
    + updated_at: string
}

class Grade {
    + id: int
    + student_id: string
    + subject_id: int
    + subject_name: string (from join)
    + mark: float
    + created_at: string
}

class AuthController {
    - db: PDO
    
    + login(username, password): Response (with JWT token)
    + logout(): Response
    + verifyToken(token): User | null
}

class AuthMiddleware {
    + authenticate(request): bool
    + requireAdmin(request): bool
}

class UserController {
    - db: PDO
    
    + createUser(data): Response (admin only)
    + getAllUsers(): Response (admin only)
}

class StudentController {
    - db: PDO
    
    + createStudent(data): Response
    + getAllStudents(): Response
    + getStudent(student_id): Response
    + updateStudent(student_id, data): Response
    + deleteStudent(student_id): Response
}

class GradeController {
    - db: PDO
    
    + addGrade(data): Response
    + getStudentGrades(student_id): Response
}

class SubjectController {
    - db: PDO
    
    + createSubject(data): Response (admin only)
    + getAllSubjects(): Response
    + deleteSubject(id): Response (admin only)
}

class ClassLevelController {
    - db: PDO
    
    + createClassLevel(data): Response (admin only)
    + getAllClassLevels(): Response
    + deleteClassLevel(id): Response (admin only)
}

class JWT {
    + encode(payload, secret): string
    + decode(token, secret): object | null
}
```

### 3. Frontend (React + Tailwind)

#### Components Structure

```
src/
├── components/
│   ├── Login.jsx                  - Login form
│   ├── StudentList.jsx            - Display all students
│   ├── StudentForm.jsx            - Add/Edit student form
│   ├── GradeForm.jsx              - Add grade form
│   ├── GradeList.jsx              - Display student grades
│   ├── UserManagement.jsx         - Admin user management
│   ├── UserForm.jsx               - Add user form (admin)
│   ├── SubjectManagement.jsx      - Admin subject management
│   ├── ClassLevelManagement.jsx   - Admin class level management
│   ├── ProtectedRoute.jsx         - Route guard for authentication
│   └── Layout.jsx                 - Main layout wrapper with logout
├── services/
│   ├── api.js                     - API service functions
│   └── auth.js                    - Authentication service
├── context/
│   └── AuthContext.jsx            - Authentication context
├── App.jsx                        - Main app component with routing
└── index.js                       - Entry point
```

#### Key React Components

**Login Component**
- Login form with username and password fields
- Handles authentication and stores JWT token
- Redirects to dashboard on successful login
- Displays error messages for invalid credentials

**ProtectedRoute Component**
- Wraps routes that require authentication
- Redirects to login if no valid token
- Checks admin role for admin-only routes

**AuthContext**
- Provides authentication state across the app
- Stores current user and token
- Provides login/logout functions

**Layout Component**
- Main layout with navigation
- Shows username and logout button
- Admin menu for user management (admin only)

**StudentList Component**
- Displays table of all students with columns: Student ID, Name, Class Level
- Filter/search by class level
- Actions: View grades, Edit, Delete
- Uses Tailwind for styling

**StudentForm Component**
- Form for adding/editing students
- Fields: Student ID, Name, Class Level (dropdown from class_levels table)
- Validation and error display

**GradeForm Component**
- Form for adding grades
- Fields: Student selection (dropdown), Subject (dropdown from subjects table), Mark
- Mark validation (0-100)

**GradeList Component**
- Displays grades for a specific student
- Shows subject and mark in a table format

**UserManagement Component** (Admin only)
- Displays list of all users
- Shows username and role

**UserForm Component** (Admin only)
- Form for adding new users
- Fields: Username, Password, Role
- Only accessible to admin users

**SubjectManagement Component** (Admin only)
- Displays list of all subjects
- Add and delete subject functionality
- Prevent deletion if subject is in use

**ClassLevelManagement Component** (Admin only)
- Displays list of all class levels
- Add and delete class level functionality
- Prevent deletion if class level is in use

## Data Models

### API Request/Response Formats

#### Login Request
```json
{
    "username": "admin",
    "password": "admin123"
}
```

#### Login Response
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

#### Create User Request (Admin only)
```json
{
    "username": "teacher1",
    "password": "password123",
    "role": "user"
}
```

#### User Response
```json
{
    "id": 2,
    "username": "teacher1",
    "role": "user",
    "created_at": "2025-10-31 10:00:00"
}
```

#### Subject Response
```json
{
    "id": 1,
    "name": "Mathematics",
    "created_at": "2025-10-31 10:00:00"
}
```

#### Class Level Response
```json
{
    "id": 1,
    "name": "Grade 10",
    "created_at": "2025-10-31 10:00:00"
}
```

#### Create Student Request
```json
{
    "student_id": "S001",
    "name": "John Doe",
    "class_level_id": 1
}
```

#### Student Response
```json
{
    "id": 1,
    "student_id": "S001",
    "name": "John Doe",
    "class_level_id": 1,
    "class_level_name": "Grade 10",
    "created_at": "2025-10-31 10:00:00",
    "updated_at": "2025-10-31 10:00:00"
}
```

#### Add Grade Request
```json
{
    "student_id": "S001",
    "subject_id": 1,
    "mark": 85.5
}
```

#### Grade Response
```json
{
    "id": 1,
    "student_id": "S001",
    "subject_id": 1,
    "subject_name": "Mathematics",
    "mark": 85.5,
    "created_at": "2025-10-31 10:00:00"
}
```

#### Student with Grades Response
```json
{
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
            "subject_id": 1,
            "subject_name": "Mathematics",
            "mark": 85.5,
            "created_at": "2025-10-31 10:00:00"
        },
        {
            "id": 2,
            "subject_id": 2,
            "subject_name": "English",
            "mark": 92.0,
            "created_at": "2025-10-31 10:00:00"
        },
        {
            "id": 3,
            "subject_id": 3,
            "subject_name": "Science",
            "mark": 78.0,
            "created_at": "2025-10-31 10:00:00"
        }
    ]
}
```

## Error Handling

### HTTP Status Codes
- `200 OK` - Successful GET, PUT requests
- `201 Created` - Successful POST requests
- `400 Bad Request` - Validation errors
- `401 Unauthorized` - Missing or invalid authentication token
- `403 Forbidden` - Insufficient permissions (e.g., non-admin accessing admin routes)
- `404 Not Found` - Resource not found
- `409 Conflict` - Duplicate student ID or username
- `500 Internal Server Error` - Server errors

### Error Response Format
```json
{
    "success": false,
    "error": "Error message here"
}
```

### Success Response Format
```json
{
    "success": true,
    "data": { /* response data */ }
}
```

### Validation Rules

**Backend (PHP)**
- Username: Required, unique, 3-50 characters, alphanumeric
- Password: Required, minimum 6 characters
- Role: Required, must be 'admin' or 'user'
- Student ID: Required, unique, max 50 characters
- Name: Required, max 255 characters
- Class Level ID: Required, must exist in class_levels table
- Subject ID: Required, must exist in subjects table
- Subject Name: Required, unique, max 255 characters (for creating subjects)
- Class Level Name: Required, unique, max 50 characters (for creating class levels)
- Mark: Required, numeric, between 0 and 100
- JWT Token: Required in Authorization header for protected routes

**Frontend (React)**
- Client-side validation before API calls
- Display error messages from API responses
- Form field validation with visual feedback
- Token storage in localStorage or sessionStorage
- Automatic token inclusion in API requests

### Error Messages
- "Invalid username or password"
- "Unauthorized access - please login"
- "Access denied - admin privileges required"
- "Username already exists"
- "Student with ID {id} already exists"
- "Mark must be between 0 and 100"
- "Student with ID {id} not found"
- "Invalid input: {field} is required"
- "Session expired - please login again"
- "Database connection failed"

## Testing Strategy

### Backend Testing (PHP)
- Test API endpoints with various inputs
- Test database operations (CRUD)
- Test validation logic (mark range, duplicate IDs)
- Test error handling and response formats
- Test boundary values for marks (0, 100, -1, 101)

### Frontend Testing (React)
- Test component rendering
- Test form submissions
- Test API integration
- Test error display
- Test user interactions (add, edit, delete)

### Manual Testing Scenarios
1. **Authentication Flow**
   - Login with valid credentials (seeded admin)
   - Login with invalid credentials
   - Access protected routes without token
   - Logout and verify token is cleared

2. **User Management Flow** (Admin only)
   - Create new user with valid data
   - Attempt to create user with duplicate username
   - Verify non-admin cannot access user management

3. **Create Student Flow**
   - Add new student with valid data
   - Attempt to add duplicate student ID
   - Submit form with missing fields
   - Attempt to create student without authentication

4. **Add Grade Flow**
   - Add grade with valid mark (0-100)
   - Attempt to add grade with invalid mark
   - Add grade for non-existent student

5. **View Grades Flow**
   - View grades for student with multiple grades
   - View grades for student with no grades
   - View grades for non-existent student

6. **Update Student Flow**
   - Update student name
   - Verify grades are preserved after update

### Test Data
```json
{
    "valid_students": [
        {"student_id": "S001", "name": "Alice Brown", "class_level": "Grade 10"},
        {"student_id": "S002", "name": "Bob Wilson", "class_level": "Grade 11"},
        {"student_id": "S003", "name": "Carol Davis", "class_level": "Grade 10"}
    ],
    "valid_grades": [
        {"student_id": "S001", "subject": "Mathematics", "mark": 85.5},
        {"student_id": "S001", "subject": "Science", "mark": 100.0},
        {"student_id": "S001", "subject": "English", "mark": 92.0},
        {"student_id": "S002", "subject": "History", "mark": 78.0},
        {"student_id": "S002", "subject": "Mathematics", "mark": 88.5}
    ],
    "invalid_marks": [-1, 100.1, 150, -50],
    "duplicate_id": "S001",
    "class_levels": ["Grade 9", "Grade 10", "Grade 11", "Grade 12"]
}
```

## Implementation Notes

### Project Structure

```
student-management-system/
├── backend/
│   ├── config/
│   │   └── database.php          - Database connection
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── SubjectController.php
│   │   ├── ClassLevelController.php
│   │   ├── StudentController.php
│   │   └── GradeController.php
│   ├── middleware/
│   │   └── AuthMiddleware.php    - JWT verification
│   ├── models/
│   │   ├── User.php
│   │   ├── Subject.php
│   │   ├── ClassLevel.php
│   │   ├── Student.php
│   │   └── Grade.php
│   ├── utils/
│   │   └── JWT.php               - JWT helper functions
│   ├── api/
│   │   └── index.php             - API router
│   └── database/
│       ├── schema.sql            - Database schema
│       └── seed.sql              - Seed data (admin, subjects, class levels)
├── frontend/
│   ├── public/
│   │   └── index.html
│   ├── src/
│   │   ├── components/
│   │   │   ├── Login.jsx
│   │   │   ├── ProtectedRoute.jsx
│   │   │   ├── StudentList.jsx
│   │   │   ├── StudentForm.jsx
│   │   │   ├── GradeForm.jsx
│   │   │   ├── GradeList.jsx
│   │   │   ├── UserManagement.jsx
│   │   │   ├── UserForm.jsx
│   │   │   ├── SubjectManagement.jsx
│   │   │   ├── ClassLevelManagement.jsx
│   │   │   └── Layout.jsx
│   │   ├── context/
│   │   │   └── AuthContext.jsx
│   │   ├── services/
│   │   │   ├── api.js
│   │   │   └── auth.js
│   │   ├── App.jsx
│   │   └── index.js
│   ├── package.json
│   └── tailwind.config.js
└── README.md
```

### Development Setup
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB
- Node.js 16+ and npm for React development
- CORS configuration for API access from React app
- JWT secret key in environment variables

### Initial Setup
1. Run schema.sql to create database tables
2. Run seed.sql to create:
   - Default admin user (Username: `admin`, Password: `admin123`)
   - Pre-filled subjects (Mathematics, English, Science, History, Geography, etc.)
   - Pre-filled class levels (Grade 9, Grade 10, Grade 11, Grade 12)

### Security Considerations
- Use prepared statements to prevent SQL injection
- Hash passwords using PHP password_hash() with bcrypt
- Validate and sanitize all user inputs
- Implement CORS properly for API access
- Use environment variables for database credentials and JWT secret
- Set JWT expiration time (e.g., 24 hours)
- Use HTTPS in production
- Implement rate limiting for login attempts
- Store JWT tokens securely in httpOnly cookies or localStorage with XSS protection

### Future Enhancements (Out of Scope)
- Password reset functionality
- User profile management
- Change password feature
- Session management (track active sessions)
- Grade statistics and analytics (average, GPA)
- Multiple grade types (assignments, exams, quizzes)
- Grade weighting and final grade calculation
- Export grades to CSV/PDF
- Student profile pictures
- Email notifications
- Audit logs for user actions
