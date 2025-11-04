# Integration Tests

This document describes the comprehensive integration tests for the Student Management System.

## Overview

The integration tests cover all major workflows and requirements of the system:

- **Authentication Flow** (5 tests)
  - Login with valid credentials
  - Login with invalid credentials
  - Login with missing fields
  - JWT token validation
  - Logout functionality

- **User Management** (5 tests)
  - Admin creates new user
  - Regular user login
  - Non-admin denied access to user management
  - Duplicate username rejection
  - Get all users (admin only)

- **Subject Management** (5 tests)
  - Admin creates new subject
  - Non-admin denied access to create subject
  - Get all subjects (authenticated users)
  - Delete unused subject
  - Prevent deletion of subject in use

- **Class Level Management** (5 tests)
  - Admin creates new class level
  - Non-admin denied access to create class level
  - Get all class levels (authenticated users)
  - Delete unused class level
  - Prevent deletion of class level in use

- **Complete User Workflow** (6 tests)
  - Create student with class level
  - Get all students
  - Get specific student
  - Add grades with subjects
  - View grades for student
  - Update student information

- **Error Scenarios** (7 tests)
  - Duplicate student ID rejection
  - Invalid mark (> 100) rejection
  - Invalid mark (< 0) rejection
  - Unauthorized access (no token)
  - Invalid token rejection
  - Non-existent student error
  - Add grade for non-existent student rejection

- **API Endpoint Responses** (3 tests)
  - Verify response format for successful requests
  - Verify response format for error requests
  - Verify JSON responses are valid

- **Cleanup** (1 test)
  - Clean up test data

## Running the Tests

### Prerequisites

1. Ensure the database is set up and seeded with initial data:
   ```bash
   php backend/setup_database.php
   ```

2. Make sure the following are configured:
   - Database connection in `backend/config/database.php`
   - JWT secret in `backend/config/database.php`

### Execute Integration Tests

Run the comprehensive integration test suite:

```bash
php backend/integration_tests.php
```

### Expected Output

When all tests pass, you should see:

```
=== STUDENT MANAGEMENT SYSTEM - INTEGRATION TESTS ===

[Test sections with ✓ PASS indicators]

==================================================
TEST SUMMARY
==================================================
Tests Passed: 37
Tests Failed: 0
Total Tests: 37

✓ ALL TESTS PASSED!
```

### Exit Codes

- `0`: All tests passed
- `1`: One or more tests failed

## Individual Component Tests

You can also run individual component tests:

- **Authentication**: `php backend/test_auth.php`
- **User Management**: `php backend/test_user_management.php`
- **Student Management**: `php backend/test_student_management.php`
- **Grade Management**: `php backend/test_grade_management.php`

## Test Implementation Details

### CLI Testing Mode

The integration tests run in CLI mode and use a special testing mechanism:

- The `AuthMiddleware` detects CLI mode and uses a global variable `$GLOBALS['TEST_AUTH_TOKEN']` for authentication
- The middleware does not call `exit()` in CLI mode, allowing tests to continue after authentication failures
- Output buffering is used to capture API responses for validation

### Test Data

The tests create temporary test data:
- Test user: `testuser1`
- Test student: `INT_TEST_001`
- Test subject: `Test Subject`
- Test class level: `Test Grade`

All test data is automatically cleaned up at the end of the test suite.

## Troubleshooting

### Database Connection Errors

If you see database connection errors:
1. Check that MySQL/MariaDB is running
2. Verify database credentials in `backend/config/database.php`
3. Ensure the database exists and is seeded

### Missing Seed Data

If tests fail due to missing subjects or class levels:
```bash
php backend/setup_database.php
```

This will recreate the database schema and seed initial data.

### Permission Errors

Ensure the PHP process has permission to:
- Connect to the database
- Read/write to the database tables
- Execute the test scripts

## Continuous Integration

These tests can be integrated into a CI/CD pipeline:

```bash
# Example CI script
php backend/integration_tests.php
if [ $? -eq 0 ]; then
    echo "All tests passed!"
else
    echo "Tests failed!"
    exit 1
fi
```

## Requirements Coverage

The integration tests cover all requirements from the requirements document:

- **Requirement 1**: Authentication (Tests 1.1-1.5)
- **Requirement 2**: User Management (Tests 2.1-2.5)
- **Requirement 3**: Student Creation (Tests 5.1, 6.1)
- **Requirement 4**: Grade Recording (Tests 5.4, 6.2, 6.3, 6.7)
- **Requirement 5**: View Grades (Test 5.5)
- **Requirement 6**: View Students (Tests 5.2, 5.3)
- **Requirement 7**: Update Students (Test 5.6)
- **Requirement 8**: Subject and Class Level Management (Tests 3.1-3.5, 4.1-4.5)
