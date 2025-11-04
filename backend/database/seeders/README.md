# Database Seeders

This directory contains organized seeders for all features of the Student Management System.

## Available Seeders

### 1. UsersSeeder
Seeds admin and regular users.

**Data:**
- 1 admin user (admin/admin123)
- 2 teacher users (teacher1/teacher123, teacher2/teacher123)

### 2. SubjectsSeeder
Seeds academic subjects.

**Data:**
- Mathematics
- English
- Science
- Social Studies
- French
- Physical Education
- Art
- Music
- Computer Science
- Religious Studies

### 3. ClassLevelsSeeder
Seeds class levels/grades.

**Data:**
- Grade 7 through Grade 12 (6 levels)

### 4. StudentsSeeder
Seeds sample students across different class levels.

**Data:**
- 30 students distributed across all grade levels
- 5 students per grade
- Realistic names and student IDs (STU2024001-STU2024030)

### 5. GradesSeeder
Seeds legacy grades (old grading system).

**Data:**
- Sample grades for first 10 students
- 5 subjects per student
- Random marks between 50-100

### 6. TermsSeeder
Seeds academic terms for the termly exam reports feature.

**Data:**
- 3 terms for 2024/2025 academic year (2 active)
- 3 terms for 2023/2024 academic year (archived)

### 7. SubjectWeightingsSeeder
Seeds custom subject weightings for CA and Exam percentages.

**Data:**
- Mathematics: 30% CA, 70% Exam
- Science: 35% CA, 65% Exam
- Physical Education: 60% CA, 40% Exam
- Art: 70% CA, 30% Exam
- Music: 70% CA, 30% Exam
- Other subjects use default: 40% CA, 60% Exam

### 8. TermAssessmentsSeeder
Seeds sample term assessments with CA and exam marks.

**Data:**
- Assessments for first 15 students
- 6 subjects per student
- Mix of complete, partial (CA only/Exam only), and missing assessments
- Realistic marks based on subject weightings

## Usage

### Run All Seeders

```bash
php backend/seed.php
```

### Run Specific Seeder

```bash
php backend/seed.php Users
php backend/seed.php Students
php backend/seed.php TermAssessments
```

### Fresh Migration and Seed

To drop all tables, recreate schema, and seed:

```bash
php backend/migrate_fresh_seed.php
```

## Seeder Order

Seeders must run in this order due to foreign key dependencies:

1. UsersSeeder (no dependencies)
2. SubjectsSeeder (no dependencies)
3. ClassLevelsSeeder (no dependencies)
4. StudentsSeeder (depends on ClassLevels)
5. GradesSeeder (depends on Students, Subjects)
6. TermsSeeder (no dependencies)
7. SubjectWeightingsSeeder (depends on Subjects)
8. TermAssessmentsSeeder (depends on Students, Subjects, Terms)

The `DatabaseSeeder` class handles this order automatically.

## Creating New Seeders

To create a new seeder:

1. Create a new file in `backend/database/seeders/` (e.g., `MyFeatureSeeder.php`)
2. Follow this template:

```php
<?php

class MyFeatureSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding my feature...\n";
        
        // Your seeding logic here
        
        echo "    ✓ Seeded X records\n";
    }
}
```

3. Add it to `DatabaseSeeder.php`:

```php
require_once __DIR__ . '/MyFeatureSeeder.php';

// In the run() method:
new MyFeatureSeeder($this->conn),
```

## Notes

- All seeders use `ON DUPLICATE KEY UPDATE` to allow re-running without errors
- Seeders are idempotent - safe to run multiple times
- Test data is realistic and suitable for demos
- Foreign key constraints are respected
