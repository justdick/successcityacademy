# Requirements Document

## Introduction

The Termly Exam Reports feature extends the Student Management System to support structured academic assessment reporting. The system will allow teachers to record both continuous assessment marks and final exam marks for students across different subjects and academic terms. Each subject will have a configurable weighting system where continuous assessment and exam marks combine to total 100%. The system will generate comprehensive term reports showing student performance across all subjects.

## Glossary

- **Student Management System (SMS)**: The software application that manages student information and grades
- **Term**: An academic period (e.g., Term 1, Term 2, Term 3) during which student assessments are conducted
- **Continuous Assessment (CA)**: Ongoing evaluation of student performance through assignments, quizzes, and classwork during a term
- **Exam Score**: The mark obtained by a student in the final examination for a subject at the end of a term
- **Subject Weighting**: The percentage distribution between continuous assessment and exam score that totals 100% for a subject
- **Term Report**: A comprehensive document showing a student's performance across all subjects for a specific term
- **Final Mark**: The calculated total mark for a subject, derived from weighted continuous assessment and exam score
- **System User**: An authenticated person who interacts with the Student Management System to manage student data
- **Teacher**: A System User who records assessments and generates reports for students

## Requirements

### Requirement 1

**User Story:** As an admin user, I want to configure subject weightings, so that continuous assessment and exam scores are properly balanced for each subject.

#### Acceptance Criteria

1. THE Student Management System SHALL accept subject weighting configuration including continuous assessment percentage and exam percentage
2. THE Student Management System SHALL validate that continuous assessment percentage and exam percentage sum to exactly 100
3. WHEN an Admin User configures subject weighting, THE Student Management System SHALL store the weighting for use in grade calculations
4. THE Student Management System SHALL allow different weightings for different subjects
5. WHERE a subject has no configured weighting, THE Student Management System SHALL apply a default weighting of 40% continuous assessment and 60% exam

### Requirement 2

**User Story:** As an admin user, I want to create and manage academic terms, so that assessments can be organized by term periods.

#### Acceptance Criteria

1. THE Student Management System SHALL accept term information including term name and academic year
2. WHEN an Admin User creates a term, THE Student Management System SHALL store it for use in assessment recording
3. THE Student Management System SHALL prevent creation of duplicate terms with the same name and academic year
4. THE Student Management System SHALL allow marking a term as active or inactive
5. THE Student Management System SHALL display terms in chronological order by academic year and term sequence

### Requirement 3

**User Story:** As a teacher, I want to record continuous assessment marks for students, so that I can track their ongoing performance during the term.

#### Acceptance Criteria

1. THE Student Management System SHALL accept continuous assessment information including student identifier, subject identifier, term identifier, and mark value
2. THE Student Management System SHALL validate that continuous assessment marks fall within the range of 0 to the configured continuous assessment percentage for that subject
3. WHEN a teacher submits a valid continuous assessment entry, THE Student Management System SHALL associate it with the corresponding student, subject, and term
4. IF a teacher submits a continuous assessment mark exceeding the subject's continuous assessment weighting, THEN THE Student Management System SHALL reject the entry and display an error message
5. THE Student Management System SHALL allow updating existing continuous assessment marks for a student-subject-term combination

### Requirement 4

**User Story:** As a teacher, I want to record exam scores for students, so that I can capture their final examination performance.

#### Acceptance Criteria

1. THE Student Management System SHALL accept exam score information including student identifier, subject identifier, term identifier, and mark value
2. THE Student Management System SHALL validate that exam scores fall within the range of 0 to the configured exam percentage for that subject
3. WHEN a teacher submits a valid exam score entry, THE Student Management System SHALL associate it with the corresponding student, subject, and term
4. IF a teacher submits an exam score exceeding the subject's exam weighting, THEN THE Student Management System SHALL reject the entry and display an error message
5. THE Student Management System SHALL allow updating existing exam scores for a student-subject-term combination

### Requirement 5

**User Story:** As a teacher, I want the system to automatically calculate final marks, so that I don't have to manually compute weighted totals.

#### Acceptance Criteria

1. WHEN both continuous assessment and exam score exist for a student-subject-term combination, THE Student Management System SHALL calculate the final mark by summing the two values
2. THE Student Management System SHALL display the final mark alongside continuous assessment and exam scores
3. WHERE only continuous assessment or only exam score exists, THE Student Management System SHALL display the available mark without calculating a final mark
4. THE Student Management System SHALL recalculate final marks automatically when continuous assessment or exam scores are updated

### Requirement 6

**User Story:** As a teacher, I want to generate a term report for a student, so that I can review their complete academic performance for the term.

#### Acceptance Criteria

1. WHEN a teacher requests a term report for a specific student and term, THE Student Management System SHALL retrieve all assessment data for that student-term combination
2. THE Student Management System SHALL display student information including name, student identifier, class level, and term details
3. THE Student Management System SHALL display a table showing each subject with continuous assessment mark, exam score, and final mark
4. THE Student Management System SHALL calculate and display the overall term average based on final marks across all subjects
5. IF a student has no assessment data for the requested term, THEN THE Student Management System SHALL display an appropriate message

### Requirement 7

**User Story:** As a teacher, I want to generate term reports for all students in a class, so that I can efficiently review class performance.

#### Acceptance Criteria

1. WHEN a teacher requests term reports for a specific class level and term, THE Student Management System SHALL retrieve assessment data for all students in that class
2. THE Student Management System SHALL generate individual report data for each student in the class
3. THE Student Management System SHALL display reports in a format suitable for review or printing
4. THE Student Management System SHALL allow filtering and sorting students by name or overall average
5. WHERE a class has no students or no assessment data, THE Student Management System SHALL display an appropriate message

### Requirement 8

**User Story:** As a teacher, I want to view a summary of assessments entered for a term, so that I can identify missing or incomplete data.

#### Acceptance Criteria

1. WHEN a teacher requests an assessment summary for a specific term and class, THE Student Management System SHALL display a grid showing students and subjects
2. THE Student Management System SHALL indicate which students have complete assessments (both CA and exam), partial assessments, or missing assessments for each subject
3. THE Student Management System SHALL highlight incomplete assessment entries with visual indicators
4. THE Student Management System SHALL allow teachers to navigate directly to data entry forms for missing assessments
5. THE Student Management System SHALL display the count of complete and incomplete assessments for the term

### Requirement 9

**User Story:** As a teacher, I want to export term reports to PDF, so that I can print and distribute them to students and parents.

#### Acceptance Criteria

1. WHEN a teacher requests a PDF export of a term report, THE Student Management System SHALL generate a formatted PDF document
2. THE Student Management System SHALL include student information, term details, subject assessments, and overall average in the PDF
3. THE Student Management System SHALL format the PDF for standard paper size with appropriate margins and styling
4. THE Student Management System SHALL allow exporting individual student reports or batch exporting for an entire class
5. THE Student Management System SHALL include the generation date and school information in the PDF header or footer
