# Termly Exam Reports - Design Document

## Overview

The Termly Exam Reports feature extends the existing Student Management System to support structured academic term-based assessments. The system introduces the concept of academic terms, subject weightings (continuous assessment + exam = 100%), and comprehensive term report generation. This feature integrates with the existing student, subject, and authentication systems while adding new data models for terms, weightings, and term-based assessments.

## Architecture

The feature extends the existing client-server architecture:

```
┌─────────────────────────────────────┐
│   Frontend (React + Tailwind)      │
│   - Term Management (Admin)        │
│   - Subject Weighting Config (Admin)│
│   - CA & Exam Entry Forms          │
│   - Term Report Viewer             │
│   - Assessment Summary Dashboard   │
│   - PDF Export                     │
└─────────────┬───────────────────────┘
              │ HTTP/REST API (with JWT)
┌─────────────▼───────────────────────┐
│   Backend (PHP)                     │
│   - Term Controllers                │
│   - Assessment Controllers          │
│   - Report Generation Logic         │
│   - PDF Generation (TCPDF/FPDF)    │
└─────────────┬───────────────────────┘
              │ SQL Queries
┌─────────────▼───────────────────────┐
│   Database (MySQL)                  │
│   - terms table                     │
│   - subject_weightings table        │
│   - term_assessments table          │
└─────────────────────────────────────┘
```

### Technology Stack

- **Frontend**: React with Tailwind CSS (existing)
- **Backend**: PHP (existing)
- **Database**: MySQL (existing)
- **PDF Generation**: TCPDF or FPDF library for PHP
- **Authentication**: JWT (existing)

## Components and Interfaces

### 1. Database Schema (MySQL)

#### terms table
```sql
CREATE TABLE terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_term (name, academic_year)
);
```

#### subject_weightings table
```sql
CREATE TABLE subject_weightings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    ca_percentage DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    exam_percentage DECIMAL(5,2) NOT NULL DEFAULT 60.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_weighting (subject_id),
    CHECK (ca_percentage + exam_percentage = 100.00),
    CHECK (ca_percentage >= 0 AND ca_percentage <= 100),
    CHECK (exam_percentage >= 0 AND exam_percentage <= 100)
);
```

#### term_assessments table
```sql
CREATE TABLE term_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    subject_id INT NOT NULL,
    term_id INT NOT NULL,
    ca_mark DECIMAL(5,2) DEFAULT NULL,
    exam_mark DECIMAL(5,2) DEFAULT NULL,
    final_mark DECIMAL(5,2) GENERATED ALWAYS AS (COALESCE(ca_mark, 0) + COALESCE(exam_mark, 0)) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assessment (student_id, subject_id, term_id)
);
```

### 2. Backend API (PHP)

#### New API Endpoints

**Terms** (Admin only for create/update/delete, Authenticated for read)
- `GET /api/terms` - Get all terms
- `GET /api/terms/{id}` - Get a specific term
- `POST /api/terms` - Create a new term (admin only)
- `PUT /api/terms/{id}` - Update a term (admin only)
- `DELETE /api/terms/{id}` - Delete a term (admin only)
- `GET /api/terms/active` - Get currently active terms

**Subject Weightings** (Admin only for create/update, Authenticated for read)
- `GET /api/subject-weightings` - Get all subject weightings
- `GET /api/subject-weightings/{subject_id}` - Get weighting for a specific subject
- `POST /api/subject-weightings` - Create or update subject weighting (admin only)
- `PUT /api/subject-weightings/{subject_id}` - Update subject weighting (admin only)

**Term Assessments** (Authenticated)
- `POST /api/assessments` - Create or update assessment (CA or exam mark)
- `GET /api/assessments/student/{student_id}/term/{term_id}` - Get all assessments for a student in a term
- `GET /api/assessments/term/{term_id}/class/{class_level_id}` - Get all assessments for a class in a term
- `PUT /api/assessments/{id}` - Update an assessment
- `DELETE /api/assessments/{id}` - Delete an assessment

**Reports** (Authenticated)
- `GET /api/reports/student/{student_id}/term/{term_id}` - Get term report data for a student
- `GET /api/reports/class/{class_level_id}/term/{term_id}` - Get term reports for all students in a class
- `GET /api/reports/summary/term/{term_id}/class/{class_level_id}` - Get assessment completion summary
- `GET /api/reports/pdf/student/{student_id}/term/{term_id}` - Generate and download PDF report for a student
- `POST /api/reports/pdf/class/{class_level_id}/term/{term_id}` - Generate and download batch PDF reports for a class

#### PHP Classes

```php
class Term {
    + id: int
    + name: string
    + academic_year: string
    + start_date: string
    + end_date: string
    + is_active: boolean
    + created_at: string
}

class SubjectWeighting {
    + id: int
    + subject_id: int
    + subject_name: string (from join)
    + ca_percentage: float
    + exam_percentage: float
    + created_at: string
    + updated_at: string
}

class TermAssessment {
    + id: int
    + student_id: string
    + student_name: string (from join)
    + subject_id: int
    + subject_name: string (from join)
    + term_id: int
    + term_name: string (from join)
    + ca_mark: float | null
    + exam_mark: float | null
    + final_mark: float
    + created_at: string
    + updated_at: string
}

class TermController {
    - db: PDO
    
    + createTerm(data): Response (admin only)
    + getAllTerms(): Response
    + getTerm(id): Response
    + updateTerm(id, data): Response (admin only)
    + deleteTerm(id): Response (admin only)
    + getActiveTerms(): Response
}

class SubjectWeightingController {
    - db: PDO
    
    + createOrUpdateWeighting(data): Response (admin only)
    + getAllWeightings(): Response
    + getWeighting(subject_id): Response
    + updateWeighting(subject_id, data): Response (admin only)
}

class AssessmentController {
    - db: PDO
    
    + createOrUpdateAssessment(data): Response
    + getStudentTermAssessments(student_id, term_id): Response
    + getClassTermAssessments(term_id, class_level_id): Response
    + updateAssessment(id, data): Response
    + deleteAssessment(id): Response
    + validateAssessmentMark(mark, max_mark, type): boolean
}

class ReportController {
    - db: PDO
    
    + getStudentTermReport(student_id, term_id): Response
    + getClassTermReports(class_level_id, term_id): Response
    + getAssessmentSummary(term_id, class_level_id): Response
    + generateStudentPDF(student_id, term_id): PDFResponse
    + generateClassPDFBatch(class_level_id, term_id): PDFResponse
    + calculateTermAverage(assessments): float
}

class PDFGenerator {
    - tcpdf: TCPDF
    
    + generateStudentReport(student, term, assessments, average): string
    + formatReportTable(assessments): string
    + addHeader(student, term): void
    + addFooter(date): void
}
```

### 3. Frontend (React + Tailwind)

#### New Components Structure

```
src/
├── components/
│   ├── terms/
│   │   ├── TermManagement.jsx         - Admin term CRUD
│   │   ├── TermForm.jsx               - Create/Edit term form
│   │   └── TermSelector.jsx           - Dropdown for selecting active term
│   ├── weightings/
│   │   ├── SubjectWeightingManagement.jsx  - Admin weighting config
│   │   └── WeightingForm.jsx          - Configure CA/Exam percentages
│   ├── assessments/
│   │   ├── AssessmentEntry.jsx        - Form to enter CA and exam marks
│   │   ├── AssessmentGrid.jsx         - Grid view for bulk entry
│   │   └── AssessmentSummary.jsx      - Dashboard showing completion status
│   ├── reports/
│   │   ├── StudentTermReport.jsx      - Individual student report view
│   │   ├── ClassTermReports.jsx       - Class-wide reports view
│   │   ├── ReportCard.jsx             - Formatted report card component
│   │   └── PDFExportButton.jsx        - Button to trigger PDF generation
│   └── [existing components...]
├── services/
│   ├── termService.js                 - Term API calls
│   ├── assessmentService.js           - Assessment API calls
│   └── reportService.js               - Report API calls
└── [existing files...]
```

#### Key React Components

**TermManagement Component** (Admin only)
- Display list of all terms with academic year
- Create, edit, delete terms
- Toggle term active/inactive status
- Sort by academic year and term

**TermSelector Component**
- Dropdown to select active term
- Used across assessment entry and report views
- Filters data based on selected term

**SubjectWeightingManagement Component** (Admin only)
- Display table of subjects with current weightings
- Edit CA and exam percentages
- Validate that percentages sum to 100%
- Show default weighting for subjects without custom config

**AssessmentEntry Component**
- Form to enter/update CA and exam marks
- Student selector dropdown
- Subject selector dropdown
- Term selector
- Separate input fields for CA mark and exam mark
- Display max allowed values based on subject weighting
- Show calculated final mark in real-time
- Validation with error messages

**AssessmentGrid Component**
- Spreadsheet-like grid for bulk data entry
- Rows: Students in selected class
- Columns: Subjects with CA and Exam sub-columns
- Inline editing with auto-save
- Visual indicators for complete/incomplete entries
- Filter by class level and term

**AssessmentSummary Component**
- Dashboard showing assessment completion status
- Grid with students (rows) and subjects (columns)
- Color-coded cells: green (complete), yellow (partial), red (missing)
- Click cell to navigate to assessment entry
- Display counts and percentages of completion

**StudentTermReport Component**
- Display comprehensive term report for one student
- Student info header (name, ID, class, term)
- Table showing subjects with CA, exam, and final marks
- Display subject weightings
- Calculate and display term average
- Export to PDF button

**ClassTermReports Component**
- Display reports for all students in a class
- Filter by class level and term
- List of student report cards
- Sort by student name or average
- Batch PDF export button

**ReportCard Component**
- Reusable formatted report card
- Student information section
- Assessment table with subjects and marks
- Term average display
- Print-friendly styling

**PDFExportButton Component**
- Trigger PDF generation
- Show loading state during generation
- Download PDF automatically
- Handle errors gracefully

## Data Models

### API Request/Response Formats

#### Create Term Request (Admin only)
```json
{
    "name": "Term 1",
    "academic_year": "2024/2025",
    "start_date": "2024-09-01",
    "end_date": "2024-12-15",
    "is_active": true
}
```

#### Term Response
```json
{
    "id": 1,
    "name": "Term 1",
    "academic_year": "2024/2025",
    "start_date": "2024-09-01",
    "end_date": "2024-12-15",
    "is_active": true,
    "created_at": "2024-08-15 10:00:00"
}
```

#### Create/Update Subject Weighting Request (Admin only)
```json
{
    "subject_id": 1,
    "ca_percentage": 40.00,
    "exam_percentage": 60.00
}
```

#### Subject Weighting Response
```json
{
    "id": 1,
    "subject_id": 1,
    "subject_name": "Mathematics",
    "ca_percentage": 40.00,
    "exam_percentage": 60.00,
    "created_at": "2024-08-15 10:00:00",
    "updated_at": "2024-08-15 10:00:00"
}
```

#### Create/Update Assessment Request
```json
{
    "student_id": "S001",
    "subject_id": 1,
    "term_id": 1,
    "ca_mark": 35.50,
    "exam_mark": 52.00
}
```

#### Assessment Response
```json
{
    "id": 1,
    "student_id": "S001",
    "student_name": "John Doe",
    "subject_id": 1,
    "subject_name": "Mathematics",
    "term_id": 1,
    "term_name": "Term 1",
    "ca_mark": 35.50,
    "exam_mark": 52.00,
    "final_mark": 87.50,
    "created_at": "2024-10-15 10:00:00",
    "updated_at": "2024-10-15 10:00:00"
}
```

#### Student Term Report Response
```json
{
    "student": {
        "student_id": "S001",
        "name": "John Doe",
        "class_level_id": 1,
        "class_level_name": "Grade 10"
    },
    "term": {
        "id": 1,
        "name": "Term 1",
        "academic_year": "2024/2025"
    },
    "assessments": [
        {
            "subject_id": 1,
            "subject_name": "Mathematics",
            "ca_percentage": 40.00,
            "exam_percentage": 60.00,
            "ca_mark": 35.50,
            "exam_mark": 52.00,
            "final_mark": 87.50
        },
        {
            "subject_id": 2,
            "subject_name": "English",
            "ca_percentage": 40.00,
            "exam_percentage": 60.00,
            "ca_mark": 38.00,
            "exam_mark": 55.00,
            "final_mark": 93.00
        }
    ],
    "term_average": 90.25,
    "total_subjects": 2
}
```

#### Class Term Reports Response
```json
{
    "class_level": {
        "id": 1,
        "name": "Grade 10"
    },
    "term": {
        "id": 1,
        "name": "Term 1",
        "academic_year": "2024/2025"
    },
    "reports": [
        {
            "student": {
                "student_id": "S001",
                "name": "John Doe"
            },
            "term_average": 90.25,
            "assessments": [...]
        },
        {
            "student": {
                "student_id": "S002",
                "name": "Jane Smith"
            },
            "term_average": 85.50,
            "assessments": [...]
        }
    ]
}
```

#### Assessment Summary Response
```json
{
    "term": {
        "id": 1,
        "name": "Term 1",
        "academic_year": "2024/2025"
    },
    "class_level": {
        "id": 1,
        "name": "Grade 10"
    },
    "summary": {
        "total_students": 25,
        "total_subjects": 8,
        "total_possible_assessments": 200,
        "complete_assessments": 180,
        "partial_assessments": 15,
        "missing_assessments": 5,
        "completion_percentage": 90.00
    },
    "grid": [
        {
            "student_id": "S001",
            "student_name": "John Doe",
            "subjects": [
                {
                    "subject_id": 1,
                    "subject_name": "Mathematics",
                    "status": "complete",
                    "has_ca": true,
                    "has_exam": true
                },
                {
                    "subject_id": 2,
                    "subject_name": "English",
                    "status": "partial",
                    "has_ca": true,
                    "has_exam": false
                }
            ]
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
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `409 Conflict` - Duplicate term or validation constraint violation
- `500 Internal Server Error` - Server errors

### Validation Rules

**Backend (PHP)**
- Term Name: Required, max 100 characters
- Academic Year: Required, format YYYY/YYYY (e.g., 2024/2025)
- CA Percentage: Required, numeric, 0-100, must sum with exam percentage to 100
- Exam Percentage: Required, numeric, 0-100, must sum with CA percentage to 100
- CA Mark: Optional, numeric, 0 to subject's CA percentage
- Exam Mark: Optional, numeric, 0 to subject's exam percentage
- At least one of CA Mark or Exam Mark should be provided when creating assessment

**Frontend (React)**
- Real-time validation of percentage sums
- Mark validation against subject weightings
- Visual feedback for validation errors
- Prevent submission of invalid data

### Error Messages
- "CA and exam percentages must sum to exactly 100%"
- "CA mark cannot exceed {ca_percentage}% for this subject"
- "Exam mark cannot exceed {exam_percentage}% for this subject"
- "Term with this name and academic year already exists"
- "Invalid academic year format. Use YYYY/YYYY"
- "At least one mark (CA or Exam) must be provided"
- "Student has no assessments for the selected term"
- "Cannot delete term with existing assessments"

## Testing Strategy

### Backend Testing (PHP)
- Test term CRUD operations
- Test subject weighting validation (sum to 100%)
- Test assessment mark validation against weightings
- Test final mark calculation
- Test report generation with various data scenarios
- Test PDF generation
- Test assessment summary calculations

### Frontend Testing (React)
- Test term management UI
- Test weighting configuration with validation
- Test assessment entry forms
- Test assessment grid bulk entry
- Test report display and calculations
- Test PDF export functionality
- Test assessment summary dashboard

### Manual Testing Scenarios

1. **Term Management Flow**
   - Create new term with valid data
   - Attempt to create duplicate term
   - Update term details
   - Delete term without assessments
   - Attempt to delete term with assessments

2. **Subject Weighting Configuration**
   - Set custom weighting (e.g., 30% CA, 70% Exam)
   - Attempt invalid weighting (e.g., 50% CA, 60% Exam)
   - Update existing weighting
   - Verify default weighting for unconfigured subjects

3. **Assessment Entry Flow**
   - Enter CA mark within valid range
   - Enter exam mark within valid range
   - Attempt to enter CA mark exceeding weighting
   - Attempt to enter exam mark exceeding weighting
   - Update existing assessment
   - Verify final mark calculation

4. **Report Generation Flow**
   - Generate report for student with complete assessments
   - Generate report for student with partial assessments
   - Generate report for student with no assessments
   - Generate class reports
   - Verify term average calculation

5. **PDF Export Flow**
   - Export single student report to PDF
   - Export batch class reports to PDF
   - Verify PDF formatting and content
   - Test PDF download

6. **Assessment Summary Dashboard**
   - View completion status for a class and term
   - Verify color coding for complete/partial/missing
   - Navigate to assessment entry from summary
   - Verify completion statistics

## Implementation Notes

### Project Structure

```
student-management-system/
├── backend/
│   ├── controllers/
│   │   ├── TermController.php
│   │   ├── SubjectWeightingController.php
│   │   ├── AssessmentController.php
│   │   └── ReportController.php
│   ├── models/
│   │   ├── Term.php
│   │   ├── SubjectWeighting.php
│   │   └── TermAssessment.php
│   ├── utils/
│   │   └── PDFGenerator.php
│   ├── vendor/
│   │   └── tcpdf/                    - PDF library
│   └── database/
│       └── migrations/
│           └── add_termly_reports.sql
├── frontend/
│   └── src/
│       ├── components/
│       │   ├── terms/
│       │   ├── weightings/
│       │   ├── assessments/
│       │   └── reports/
│       └── services/
│           ├── termService.js
│           ├── assessmentService.js
│           └── reportService.js
└── [existing files...]
```

### Database Migration

Create migration script `add_termly_reports.sql` to:
1. Create new tables (terms, subject_weightings, term_assessments)
2. Add indexes for performance
3. Seed default subject weightings (40% CA, 60% Exam for all subjects)
4. Create sample terms for testing

### PDF Generation

- Use TCPDF library (lightweight, no external dependencies)
- Install via Composer: `composer require tecnickcom/tcpdf`
- Generate A4 size reports with school logo/header
- Include student photo placeholder
- Format assessment table with proper styling
- Add page numbers and generation timestamp
- Support batch generation with ZIP download for class reports

### Performance Considerations

- Index foreign keys in term_assessments table
- Cache subject weightings to reduce queries
- Optimize report queries with JOINs
- Implement pagination for class reports
- Use database transactions for bulk assessment updates
- Consider caching generated PDFs for frequently accessed reports

### Security Considerations

- Validate all mark inputs on both client and server
- Ensure teachers can only access their assigned classes (future enhancement)
- Validate term and subject IDs exist before creating assessments
- Sanitize all inputs for PDF generation
- Implement rate limiting for PDF generation endpoints
- Log all assessment modifications for audit trail

### Integration with Existing System

- Extends existing students, subjects, and class_levels tables
- Uses existing authentication and authorization middleware
- Maintains backward compatibility with existing grade system
- The old grades table remains for historical data or other use cases
- New term_assessments table is the primary storage for term-based assessments

### Future Enhancements (Out of Scope)

- Grade boundaries and letter grades (A, B, C, etc.)
- Class ranking and position calculation
- Teacher comments on reports
- Parent portal to view reports online
- SMS/Email notifications when reports are ready
- Historical trend analysis across terms
- Subject-specific grading rubrics
- Multiple assessment types within CA (assignments, quizzes, projects)
- Graphical performance charts
- Comparison with class average
- Attendance tracking on reports
