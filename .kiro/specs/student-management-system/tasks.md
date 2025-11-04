# Implementation Plan

- [x] 1. Set up project structure and database

  - Create backend and frontend directory structure
  - Create database configuration file with JWT secret
  - Write SQL schema for users, students, and grades tables
  - Create seed.sql file for default admin user
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 2. Implement database layer

- [x] 2.1 Create database connection class

  - Write Database class with PDO connection
  - Implement error handling for connection failures
  - _Requirements: 1.2, 2.2_

- [x] 2.2 Create SQL schema file

  - Write CREATE TABLE statement for users table
  - Write CREATE TABLE statements for subjects and class_levels tables
  - Write CREATE TABLE statements for students and grades tables with foreign keys
  - Add constraints and foreign keys
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 2.3, 2.4, 8.1, 8.2_

- [x] 2.3 Create seed file for initial data

  - Write INSERT statement for default admin user (username: admin, password: admin123)
  - Write INSERT statements for pre-filled subjects (Mathematics, English, Science, History, Geography, etc.)
  - Write INSERT statements for pre-filled class levels (Grade 9, Grade 10, Grade 11, Grade 12)
  - Use password_hash() for secure password storage
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 8.1, 8.2, 8.3, 8.4_

- [x] 3. Implement JWT utility and authentication middleware

- [x] 3.1 Create JWT utility class

  - Write JWT encode function
  - Write JWT decode function with error handling
  - Use secret key from environment/config
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 3.2 Create AuthMiddleware class

  - Implement authenticate method to verify JWT token
  - Implement requireAdmin method to check admin role
  - Return appropriate error responses for unauthorized access
  - _Requirements: 1.1, 1.4, 2.5_

- [x] 4. Implement backend models

- [x] 4.1 Create User model class

  - Write User class with properties (id, username, password_hash, role)
  - Implement password verification method
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 2.3_

- [x] 4.2 Create Subject model class

  - Write Subject class with properties (id, name)

  - _Requirements: 8.1, 8.2, 8.3_

- [x] 4.3 Create ClassLevel model class

  - Write ClassLevel class with properties (id, name)
  - _Requirements: 8.1, 8.2, 8.3_

- [x] 4.4 Create Student model class

  - Write Student class with properties (id, student_id, name, class_level_id)
  - Implement data validation methods
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [x] 4.5 Create Grade model class

  - Write Grade class with properties (id, student_id, subject_id, mark)
  - Implement mark validation (0-100 range)
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 5. Implement Authentication API endpoints

- [x] 5.1 Create AuthController

  - Implement login method with username/password validation
  - Generate and return JWT token on successful login
  - Implement logout method
  - Add error handling for invalid credentials
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 5.2 Create API router for auth endpoints

  - Set up routing for POST /api/auth/login
  - Set up routing for POST /api/auth/logout
  - Configure CORS headers
  - _Requirements: 1.1, 1.2, 1.5_

- [x] 6. Implement User Management API endpoints

- [x] 6.1 Create UserController

  - Implement createUser method with duplicate username check
  - Implement getAllUsers method
  - Hash passwords using password_hash()
  - Add admin-only access validation
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 6.2 Create API router for user endpoints

  - Set up routing for POST /api/users (admin only)
  - Set up routing for GET /api/users (admin only)
  - Apply AuthMiddleware to protect routes
  - Configure CORS headers
  - _Requirements: 2.1, 2.2, 2.5_

- [x] 6.3 Implement Subject Management API endpoints

- [x] 6.3.1 Create SubjectController

  - Implement createSubject method with duplicate name check
  - Implement getAllSubjects method
  - Implement deleteSubject method with usage check
  - Add admin-only access validation for create/delete
  - _Requirements: 8.1, 8.2, 8.3, 8.5, 8.6_

- [x] 6.3.2 Create API router for subject endpoints

  - Set up routing for GET /api/subjects (authenticated)
  - Set up routing for POST /api/subjects (admin only)
  - Set up routing for DELETE /api/subjects/{id} (admin only)
  - Apply AuthMiddleware to protect routes
  - Configure CORS headers
  - _Requirements: 8.1, 8.2, 8.3, 8.5_

- [ ] 6.4 Implement Class Level Management API endpoints
- [x] 6.4.1 Create ClassLevelController

  - Implement createClassLevel method with duplicate name check
  - Implement getAllClassLevels method
  - Implement deleteClassLevel method with usage check
  - Add admin-only access validation for create/delete
  - _Requirements: 8.1, 8.2, 8.3, 8.5, 8.6_

- [x] 6.4.2 Create API router for class level endpoints

  - Set up routing for GET /api/class-levels (authenticated)
  - Set up routing for POST /api/class-levels (admin only)
  - Set up routing for DELETE /api/class-levels/{id} (admin only)
  - Apply AuthMiddleware to protect routes
  - Configure CORS headers
  - _Requirements: 8.1, 8.2, 8.3, 8.5_

- [x] 7. Implement Student API endpoints

- [x] 7.1 Create StudentController with CRUD operations

  - Implement createStudent method with duplicate ID check
  - Implement getAllStudents method
  - Implement getStudent method with not found handling
  - Implement updateStudent method
  - Implement deleteStudent method
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 6.1, 6.2, 7.1, 7.2, 7.3_

- [x] 7.2 Create API router for student endpoints

  - Set up routing for POST /api/students
  - Set up routing for GET /api/students
  - Set up routing for GET /api/students/{student_id}
  - Set up routing for PUT /api/students/{student_id}
  - Set up routing for DELETE /api/students/{student_id}
  - Apply AuthMiddleware to protect all routes
  - Configure CORS headers
  - _Requirements: 3.1, 3.2, 6.1, 6.2, 7.1_

- [x] 8. Implement Grade API endpoints

- [x] 8.1 Create GradeController with operations

  - Implement addGrade method with validation
  - Implement getStudentGrades method
  - Add error handling for invalid marks and non-existent students
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3_

- [x] 8.2 Create API router for grade endpoints

  - Set up routing for POST /api/grades
  - Set up routing for GET /api/students/{student_id}/grades
  - Apply AuthMiddleware to protect all routes
  - Configure CORS headers
  - _Requirements: 4.1, 4.2, 5.1, 5.2_

- [x] 9. Set up React frontend project

- [x] 9.1 Initialize React app with Vite

  - Create React project structure
  - Install dependencies (React, React Router, Axios)
  - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

- [x] 9.2 Configure Tailwind CSS

  - Install Tailwind CSS and dependencies
  - Create tailwind.config.js
  - Add Tailwind directives to CSS
  - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

- [x] 10. Implement authentication context and services

- [x] 10.1 Create AuthContext

  - Implement context for authentication state
  - Provide login, logout, and user state
  - Store JWT token in localStorage
  - _Requirements: 1.1, 1.2, 1.5_

- [x] 10.2 Create authentication service

  - Write login function that calls API and stores token
  - Write logout function that clears token
  - Write function to get current user from token
  - _Requirements: 1.1, 1.2, 1.5_

- [x] 10.3 Create ProtectedRoute component

  - Check for valid authentication token
  - Redirect to login if not authenticated
  - Support admin-only routes
  - _Requirements: 1.4, 2.5_

- [x] 11. Implement API service layer

- [x] 11.1 Create API service functions

  - Write functions for auth endpoints (login, logout)
  - Write functions for user endpoints (create, getAll) - admin only
  - Write functions for subject endpoints (create, getAll, delete) - admin for create/delete
  - Write functions for class level endpoints (create, getAll, delete) - admin for create/delete
  - Write functions for student endpoints (create, getAll, getOne, update, delete)
  - Write functions for grade endpoints (add, getByStudent)
  - Add JWT token to all authenticated requests
  - Implement error handling for API calls including 401 responses
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 3.1, 3.2, 4.1, 4.2, 5.1, 6.1, 7.1, 8.1, 8.2, 8.3_

- [x] 12. Implement authentication UI components

- [x] 12.1 Create Login component

  - Build login form with username and password fields
  - Handle form submission and call login API
  - Store JWT token on successful login
  - Display error messages for invalid credentials
  - Redirect to dashboard after login
  - Style with Tailwind CSS
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 13. Implement user management UI components (Admin only)

- [x] 13.1 Create UserManagement component

  - Display list of all users in a table
  - Show username and role for each user
  - Add button to create new user
  - Restrict access to admin users only
  - Style with Tailwind CSS
  - _Requirements: 2.1, 2.2, 2.5_

- [x] 13.2 Create UserForm component

  - Build form with username, password, and role fields
  - Implement form validation
  - Handle form submission to create user
  - Display error messages from API
  - Style with Tailwind CSS
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [x] 13.3 Create SubjectManagement component (Admin only)

  - Display list of all subjects in a table
  - Add button and inline form to create new subject
  - Add delete button for each subject
  - Show error if subject is in use and cannot be deleted
  - Restrict access to admin users only
  - Style with Tailwind CSS
  - _Requirements: 8.1, 8.2, 8.3, 8.5, 8.6_

- [x] 13.4 Create ClassLevelManagement component (Admin only)

  - Display list of all class levels in a table
  - Add button and inline form to create new class level
  - Add delete button for each class level
  - Show error if class level is in use and cannot be deleted
  - Restrict access to admin users only
  - Style with Tailwind CSS
  - _Requirements: 8.1, 8.2, 8.3, 8.5, 8.6_

- [x] 14. Implement React components for student management

- [x] 14.1 Create Layout component

  - Build main layout with navigation
  - Display current username
  - Add logout button
  - Show admin menu for user management (admin only)
  - Style with Tailwind CSS
  - _Requirements: 1.5, 2.5, 6.1, 6.2_

- [x] 14.2 Create StudentList component

  - Display students in a table with columns: Student ID, Name, Class Level
  - Add filter/search by class level
  - Add action buttons (View Grades, Edit, Delete)
  - Implement delete functionality with confirmation
  - Style with Tailwind CSS

  - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 14.3 Create StudentForm component

  - Build form with Student ID, Name, and Class Level (dropdown) fields
  - Load class levels from API for dropdown
  - Implement form validation
  - Handle create and update modes
  - Display error messages from API
  - Style with Tailwind CSS
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 7.1, 7.2, 7.3, 8.4_

- [x] 15. Implement React components for grade management

- [x] 15.1 Create GradeForm component

  - Build form with student selection (dropdown), subject (dropdown), and mark fields
  - Load students and subjects from API for dropdowns
  - Implement mark validation (0-100)
  - Display error messages from API
  - Style with Tailwind CSS
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 8.4_

- [x] 15.2 Create GradeList component

  - Display grades for a specific student
  - Show student name and all grades in a table
  - Handle empty state when no grades exist
  - Style with Tailwind CSS
  - _Requirements: 5.1, 5.2, 5.3_

- [x] 16. Implement main App component and routing

- [x] 16.1 Set up React Router with authentication

  - Configure route for login page (public)
  - Configure protected routes for student list, add student, edit student, and view grades
  - Configure admin-only routes for user management, subject management, and class level management
  - Implement navigation between views
  - Wrap app with AuthContext provider
  - _Requirements: 1.1, 1.4, 2.1, 2.5, 3.1, 4.1, 5.1, 8.1, 8.5_

- [x] 16.2 Wire up all components in App.jsx

  - Import and configure all components
  - Set up state management for data flow
  - Implement loading and error states
  - Handle token expiration and redirect to login
  - _Requirements: 1.1, 1.5, 2.1, 3.1, 4.1, 5.1_

- [x] 17. Create integration tests

  - Test authentication flow (login, logout, token validation)
  - Test user management (admin creates user, non-admin denied access)
  - Test subject management (admin creates/deletes subject, prevent deletion if in use)
  - Test class level management (admin creates/deletes class level, prevent deletion if in use)
  - Test complete user workflows (create student with class level, add grades with subjects, view grades)
  - Test error scenarios (duplicate ID, invalid marks, unauthorized access)
  - Test API endpoint responses
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 6.1, 6.2, 6.3, 6.4, 7.1, 7.2, 7.3, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_
