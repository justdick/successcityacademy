# Requirements Document

## Introduction

The Student Management System is a software application designed to record and manage student grades (marks). The system will allow users to store student information, record grades for various subjects or assessments, and retrieve grade information as needed.

## Glossary

- **Student Management System (SMS)**: The software application that manages student information and grades
- **Student Record**: A data entity containing information about a single student including their class level
- **Grade Entry**: A recorded mark or score for a student in a specific subject or assessment
- **Class Level**: The academic level or grade of a student (e.g., Grade 1, Grade 2, Form 1, Year 10)
- **Subject**: An academic course or discipline for which grades are recorded
- **System User**: An authenticated person who interacts with the Student Management System to manage student data
- **Admin User**: A System User with privileges to create and manage other System Users
- **Authentication Session**: A verified login session that grants access to the Student Management System

## Requirements

### Requirement 1

**User Story:** As a system user, I want to log in to the system, so that I can securely access student management features.

#### Acceptance Criteria

1. THE Student Management System SHALL accept login credentials including username and password
2. WHEN a System User submits valid credentials, THE Student Management System SHALL create an Authentication Session
3. IF a System User submits invalid credentials, THEN THE Student Management System SHALL reject the login attempt and display an error message
4. THE Student Management System SHALL restrict access to all student management features until a valid Authentication Session exists
5. WHEN a System User logs out, THE Student Management System SHALL terminate the Authentication Session

### Requirement 2

**User Story:** As an admin user, I want to add new system users, so that authorized people can access the system.

#### Acceptance Criteria

1. THE Student Management System SHALL accept new System User information including username, password, and role
2. WHEN an Admin User submits valid System User information, THE Student Management System SHALL create a new System User account
3. IF an Admin User attempts to create a System User with a duplicate username, THEN THE Student Management System SHALL reject the submission and display an error message
4. THE Student Management System SHALL hash and securely store System User passwords
5. WHERE a System User is not an Admin User, THE Student Management System SHALL deny access to user management features

### Requirement 8

**User Story:** As an admin user, I want to manage subjects and class levels, so that the system has consistent data for grade entry.

#### Acceptance Criteria

1. THE Student Management System SHALL accept new Subject information including subject name
2. THE Student Management System SHALL accept new Class Level information including level name
3. WHEN an Admin User creates a Subject or Class Level, THE Student Management System SHALL store it for use in student and grade management
4. THE Student Management System SHALL provide pre-filled subjects and class levels during initial setup
5. WHERE a System User is not an Admin User, THE Student Management System SHALL deny access to subject and class level management features
6. THE Student Management System SHALL prevent deletion of subjects or class levels that are in use by existing students or grades

### Requirement 3

**User Story:** As a user, I want to add new students to the system, so that I can track their academic performance.

#### Acceptance Criteria

1. THE Student Management System SHALL accept student information including name, unique identifier, and class level
2. WHEN a user submits valid student information, THE Student Management System SHALL create a new Student Record
3. IF a user attempts to create a Student Record with a duplicate identifier, THEN THE Student Management System SHALL reject the submission and display an error message
4. THE Student Management System SHALL store the Student Record for future retrieval

### Requirement 4

**User Story:** As a user, I want to record grades for students, so that I can maintain an accurate record of their academic performance.

#### Acceptance Criteria

1. THE Student Management System SHALL accept Grade Entry information including student identifier, subject name, and numeric mark
2. WHEN a user submits a valid Grade Entry, THE Student Management System SHALL associate the grade with the corresponding Student Record
3. THE Student Management System SHALL validate that numeric marks fall within an acceptable range of 0 to 100
4. IF a user submits a Grade Entry with an invalid mark value, THEN THE Student Management System SHALL reject the entry and display an error message

### Requirement 5

**User Story:** As a user, I want to view a student's grades, so that I can review their academic performance.

#### Acceptance Criteria

1. WHEN a user requests grades for a specific student identifier, THE Student Management System SHALL retrieve all Grade Entries associated with that Student Record
2. THE Student Management System SHALL display the student name, subject names, and corresponding marks
3. IF a user requests grades for a non-existent student identifier, THEN THE Student Management System SHALL display an appropriate error message

### Requirement 6

**User Story:** As a user, I want to view a list of all students, so that I can see who is enrolled in the system.

#### Acceptance Criteria

1. WHEN a user requests the student list, THE Student Management System SHALL retrieve all Student Records
2. THE Student Management System SHALL display each student's name, unique identifier, and class level
3. WHERE no Student Records exist, THE Student Management System SHALL display an empty list or appropriate message
4. THE Student Management System SHALL allow filtering students by class level

### Requirement 7

**User Story:** As a user, I want to update student information, so that I can correct errors or reflect changes.

#### Acceptance Criteria

1. WHEN a user submits updated information for an existing student identifier, THE Student Management System SHALL modify the corresponding Student Record
2. THE Student Management System SHALL preserve the student's unique identifier during updates
3. IF a user attempts to update a non-existent student identifier, THEN THE Student Management System SHALL display an error message
