# Implementation Plan

- [x] 1. Create database schema and migrations for termly reports





  - Write SQL migration script to create terms, subject_weightings, and term_assessments tables
  - Add appropriate indexes and foreign key constraints
  - Create seed data for default subject weightings (40% CA, 60% Exam)
  - Create sample terms for testing
  - _Requirements: 1.1, 1.3, 2.1, 2.2_

- [x] 2. Implement Term management backend




- [x] 2.1 Create Term model and controller


  - Write Term model class with properties matching database schema
  - Implement TermController with CRUD methods
  - Add validation for term name, academic year format, and duplicate prevention
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 2.2 Add Term API endpoints


  - Implement GET /api/terms endpoint to retrieve all terms
  - Implement POST /api/terms endpoint for creating terms (admin only)
  - Implement PUT /api/terms/{id} endpoint for updating terms (admin only)
  - Implement DELETE /api/terms/{id} endpoint for deleting terms (admin only)
  - Implement GET /api/terms/active endpoint for active terms
  - Add authentication and authorization middleware
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [x] 2.3 Write unit tests for Term controller


  - Test term creation with valid data
  - Test duplicate term prevention
  - Test term update and delete operations
  - Test admin-only access restrictions
  - _Requirements: 2.1, 2.2, 2.3_

- [x] 3. Implement Subject Weighting management backend






- [x] 3.1 Create SubjectWeighting model and controller

  - Write SubjectWeighting model class
  - Implement SubjectWeightingController with CRUD methods
  - Add validation to ensure CA + Exam percentages = 100%
  - Implement default weighting logic (40% CA, 60% Exam)
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 3.2 Add Subject Weighting API endpoints


  - Implement GET /api/subject-weightings endpoint
  - Implement GET /api/subject-weightings/{subject_id} endpoint
  - Implement POST /api/subject-weightings endpoint (admin only)
  - Implement PUT /api/subject-weightings/{subject_id} endpoint (admin only)
  - Add validation middleware for percentage sum
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 3.3 Write unit tests for Subject Weighting controller


  - Test weighting creation with valid percentages
  - Test validation for percentages not summing to 100%
  - Test default weighting retrieval
  - Test admin-only access
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 4. Implement Term Assessment management backend
- [x] 4.1 Create TermAssessment model and controller


  - Write TermAssessment model class with CA, exam, and final mark properties
  - Implement AssessmentController with create/update methods
  - Add validation logic to check marks against subject weightings
  - Implement automatic final mark calculation
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 5.4_



- [x] 4.2 Add Term Assessment API endpoints
  - Implement POST /api/assessments endpoint for creating/updating assessments
  - Implement GET /api/assessments/student/{student_id}/term/{term_id} endpoint
  - Implement GET /api/assessments/term/{term_id}/class/{class_level_id} endpoint
  - Implement PUT /api/assessments/{id} endpoint for updates
  - Implement DELETE /api/assessments/{id} endpoint
  - Add mark validation against subject weightings


  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5_

- [x] 4.3 Write unit tests for Assessment controller
  - Test assessment creation with valid marks
  - Test CA mark validation against weighting
  - Test exam mark validation against weighting
  - Test final mark calculation
  - Test assessment update and recalculation
  - _Requirements: 3.2, 3.3, 3.4, 4.2, 4.3, 4.4, 5.1, 5.4_

- [x] 5. Implement Report generation backend




- [x] 5.1 Create ReportController with report generation logic


  - Write ReportController class
  - Implement getStudentTermReport method with data aggregation
  - Implement getClassTermReports method for multiple students
  - Implement getAssessmentSummary method for completion tracking
  - Add term average calculation logic
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 5.2 Add Report API endpoints


  - Implement GET /api/reports/student/{student_id}/term/{term_id} endpoint
  - Implement GET /api/reports/class/{class_level_id}/term/{term_id} endpoint
  - Implement GET /api/reports/summary/term/{term_id}/class/{class_level_id} endpoint
  - Add error handling for missing data scenarios
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 5.3 Write unit tests for Report controller


  - Test student report generation with complete data
  - Test student report with partial data
  - Test class report generation
  - Test assessment summary calculations
  - Test term average calculation
  - _Requirements: 6.1, 6.4, 6.5, 7.1, 7.2, 8.1, 8.5_

- [x] 6. Implement PDF generation backend





- [x] 6.1 Install and configure TCPDF library


  - Install TCPDF via Composer
  - Create PDFGenerator utility class
  - Configure PDF settings (page size, margins, fonts)
  - _Requirements: 9.1, 9.2, 9.3_

- [x] 6.2 Create PDF report templates

  - Implement generateStudentReport method with formatting
  - Add header with student info and term details
  - Create formatted assessment table
  - Add footer with generation date
  - Include term average display
  - _Requirements: 9.1, 9.2, 9.3, 9.5_

- [x] 6.3 Add PDF generation API endpoints


  - Implement GET /api/reports/pdf/student/{student_id}/term/{term_id} endpoint
  - Implement POST /api/reports/pdf/class/{class_level_id}/term/{term_id} endpoint for batch export
  - Add proper headers for PDF download
  - Implement error handling for PDF generation failures
  - _Requirements: 9.1, 9.2, 9.4_

- [x] 6.4 Write unit tests for PDF generation


  - Test single student PDF generation
  - Test batch PDF generation
  - Test PDF content and formatting
  - _Requirements: 9.1, 9.2, 9.4_

- [x] 7. Implement Term management frontend




- [x] 7.1 Create TermManagement component


  - Build TermManagement component with term list display
  - Add create, edit, delete functionality
  - Implement term active/inactive toggle
  - Add sorting by academic year
  - Style with Tailwind CSS
  - _Requirements: 2.1, 2.2, 2.4_

- [x] 7.2 Create TermForm component


  - Build form for creating/editing terms
  - Add input fields for name, academic year, dates
  - Implement validation for academic year format
  - Add error display
  - _Requirements: 2.1, 2.2, 2.3_

- [x] 7.3 Create TermSelector component


  - Build dropdown component for term selection
  - Fetch and display active terms
  - Implement onChange handler
  - Make reusable across other components
  - _Requirements: 2.4_

- [x] 7.4 Create term service functions


  - Write termService.js with API call functions
  - Implement getAllTerms, createTerm, updateTerm, deleteTerm
  - Add error handling and response parsing
  - _Requirements: 2.1, 2.2_

- [x] 8. Implement Subject Weighting management frontend





- [x] 8.1 Create SubjectWeightingManagement component


  - Build component to display subjects with weightings
  - Show CA and exam percentages for each subject
  - Add edit functionality
  - Display default weighting indicator
  - Style with Tailwind CSS
  - _Requirements: 1.1, 1.3, 1.4, 1.5_

- [x] 8.2 Create WeightingForm component


  - Build form for configuring CA and exam percentages
  - Add real-time validation for sum = 100%
  - Display error messages for invalid percentages
  - Show calculated sum as user types
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 8.3 Create weighting service functions


  - Write weightingService.js with API functions
  - Implement getWeightings, updateWeighting
  - Add validation helpers
  - _Requirements: 1.1, 1.3_

- [x] 9. Implement Assessment entry frontend




- [x] 9.1 Create AssessmentEntry component


  - Build form for entering CA and exam marks
  - Add student, subject, and term selectors
  - Implement separate input fields for CA and exam marks
  - Display max allowed values based on subject weighting
  - Show calculated final mark in real-time
  - Add validation with error messages
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2_

- [x] 9.2 Create AssessmentGrid component


  - Build spreadsheet-like grid for bulk entry
  - Display students as rows and subjects as columns
  - Add CA and exam sub-columns for each subject
  - Implement inline editing with auto-save
  - Add visual indicators for complete/incomplete entries
  - Add class level and term filters
  - _Requirements: 3.1, 3.2, 4.1, 4.2, 8.2, 8.3_

- [x] 9.3 Create assessment service functions


  - Write assessmentService.js with API functions
  - Implement createAssessment, updateAssessment, getAssessments
  - Add mark validation helpers
  - _Requirements: 3.1, 3.2, 4.1, 4.2_

- [x] 10. Implement Assessment summary dashboard frontend




- [x] 10.1 Create AssessmentSummary component


  - Build dashboard showing completion status
  - Display grid with students and subjects
  - Implement color-coded cells (green/yellow/red)
  - Add click navigation to assessment entry
  - Display completion statistics and percentages
  - Add class and term filters
  - Style with Tailwind CSS
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 10.2 Create summary service functions


  - Write functions to fetch assessment summary data
  - Add helper functions for status calculations
  - _Requirements: 8.1, 8.5_

- [x] 11. Implement Report viewing frontend





- [x] 11.1 Create StudentTermReport component


  - Build component to display individual student report
  - Add student info header section
  - Create assessment table with CA, exam, and final marks
  - Display subject weightings
  - Show term average calculation
  - Add PDF export button
  - Style for print-friendly display
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 9.1_

- [x] 11.2 Create ClassTermReports component


  - Build component for class-wide reports
  - Add class level and term filters
  - Display list of student report cards
  - Implement sorting by name or average
  - Add batch PDF export button
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 9.4_

- [x] 11.3 Create ReportCard component


  - Build reusable report card component
  - Format student information section
  - Create styled assessment table
  - Display term average prominently
  - Make print-friendly
  - _Requirements: 6.2, 6.3, 6.4_

- [x] 11.4 Create report service functions


  - Write reportService.js with API functions
  - Implement getStudentReport, getClassReports
  - Add PDF download functions
  - Handle blob responses for PDF downloads
  - _Requirements: 6.1, 7.1, 9.1, 9.4_

- [x] 12. Implement PDF export frontend




- [x] 12.1 Create PDFExportButton component


  - Build button component to trigger PDF generation
  - Add loading state during generation
  - Implement automatic download on success
  - Add error handling and display
  - Support both single and batch export modes
  - _Requirements: 9.1, 9.4_

- [x] 12.2 Integrate PDF export with reports


  - Add PDF export button to StudentTermReport
  - Add batch PDF export to ClassTermReports
  - Handle PDF download and save
  - Display success/error messages
  - _Requirements: 9.1, 9.4_

- [x] 13. Update navigation and routing





  - Add new routes for term management, weighting config, assessment entry, and reports
  - Update Layout component with navigation links
  - Add admin-only menu items for term and weighting management
  - Add teacher menu items for assessment entry and reports
  - Implement route guards for authentication and authorization
  - _Requirements: All_

- [x] 14. Integration and end-to-end testing





  - Test complete workflow from term creation to report generation
  - Verify data flow between all components
  - Test PDF generation with real data
  - Verify calculations and validations
  - Test error scenarios and edge cases
  - Verify responsive design on different screen sizes
  - _Requirements: All_
