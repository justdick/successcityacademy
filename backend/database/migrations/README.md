# Database Migrations

This directory contains database migration scripts for the Student Management System.

## Available Migrations

### add_termly_reports.sql
Adds support for term-based assessments with continuous assessment (CA) and exam marks.

**Tables Created:**
- `terms` - Manages academic terms (e.g., Term 1, Term 2, Term 3)
- `subject_weightings` - Configures CA and exam percentage weightings per subject
- `term_assessments` - Stores CA marks, exam marks, and calculated final marks

**Features:**
- Default weighting of 40% CA and 60% Exam for all subjects
- Sample terms for 2023/2024 and 2024/2025 academic years
- Automatic final mark calculation using generated columns
- Foreign key constraints for data integrity
- Indexes for optimal query performance
- Validation constraints (percentages must sum to 100%)

## Running Migrations

### Using the Migration Runner

Run a specific migration:
```bash
php backend/run_migration.php migrations/add_termly_reports.sql
```

Run the default migration (termly reports):
```bash
php backend/run_migration.php
```

### Verifying Migration

After running the migration, verify it was successful:
```bash
php backend/verify_migration.php
```

This will check:
- Table structures
- Sample data
- Foreign key constraints
- Indexes
- Data integrity

## Migration Details

### Terms Table
Stores academic term information with the following fields:
- `id` - Primary key
- `name` - Term name (e.g., "Term 1")
- `academic_year` - Academic year (e.g., "2024/2025")
- `start_date` - Term start date
- `end_date` - Term end date
- `is_active` - Whether the term is currently active
- `created_at` - Timestamp of creation

**Constraints:**
- Unique combination of name and academic year
- Indexed on academic_year and is_active for fast queries

### Subject Weightings Table
Configures how CA and exam marks are weighted for each subject:
- `id` - Primary key
- `subject_id` - Foreign key to subjects table
- `ca_percentage` - Continuous assessment percentage (default: 40.00)
- `exam_percentage` - Exam percentage (default: 60.00)
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

**Constraints:**
- CA and exam percentages must sum to exactly 100.00
- Each percentage must be between 0 and 100
- One weighting configuration per subject (unique constraint)
- Cascading delete when subject is deleted

### Term Assessments Table
Stores student assessment data for each term:
- `id` - Primary key
- `student_id` - Foreign key to students table
- `subject_id` - Foreign key to subjects table
- `term_id` - Foreign key to terms table
- `ca_mark` - Continuous assessment mark (nullable)
- `exam_mark` - Exam mark (nullable)
- `final_mark` - Automatically calculated as ca_mark + exam_mark (generated column)
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

**Constraints:**
- Unique combination of student_id, subject_id, and term_id
- Cascading delete when student, subject, or term is deleted
- Multiple indexes for efficient querying by student, subject, term, or combinations

**Generated Column:**
The `final_mark` column is automatically calculated and updated whenever `ca_mark` or `exam_mark` changes. It uses `COALESCE` to treat NULL values as 0.

## Sample Data

The migration includes:

**6 Sample Terms:**
- Term 1, 2, 3 for 2024/2025 (Term 1 is active)
- Term 1, 2, 3 for 2023/2024 (all inactive)

**Default Subject Weightings:**
All existing subjects get default weightings:
- CA: 40%
- Exam: 60%

## Rollback

To rollback this migration, execute:

```sql
USE student_management;
DROP TABLE IF EXISTS term_assessments;
DROP TABLE IF EXISTS subject_weightings;
DROP TABLE IF EXISTS terms;
```

**Warning:** This will permanently delete all term-based assessment data.

## Notes

- The migration is idempotent - it uses `CREATE TABLE IF NOT EXISTS` so it can be run multiple times safely
- Foreign key constraints ensure referential integrity
- The generated `final_mark` column automatically updates when CA or exam marks change
- Indexes are optimized for common query patterns (student reports, class reports, assessment summaries)
