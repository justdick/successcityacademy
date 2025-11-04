# Database Seeding Guide

Complete guide for database migrations and seeding in the Student Management System.

## Quick Start

### Fresh Install (Recommended)

Drop all tables, recreate schema, run migrations, and seed data:

```bash
php backend/migrate_fresh_seed.php
```

This will:
1. Drop and recreate the database
2. Create base schema
3. Run all migrations
4. Seed all data

### Seed Only

If you just want to reseed data without dropping tables:

```bash
php backend/seed.php
```

### Seed Specific Feature

To seed only a specific feature:

```bash
php backend/seed.php Users
php backend/seed.php Students
php backend/seed.php TermAssessments
```

## Seeded Data Overview

### Users (3 records)
- **Admin**: username: `admin`, password: `admin123`
- **Teacher 1**: username: `teacher1`, password: `teacher123`
- **Teacher 2**: username: `teacher2`, password: `teacher123`

### Subjects (10 records)
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

### Class Levels (6 records)
- Grade 7 through Grade 12

### Students (30 records)
- 5 students per grade level
- Student IDs: STU2024001 - STU2024030
- Realistic names distributed across all grades

### Legacy Grades (50 records)
- Sample grades for first 10 students
- 5 subjects per student
- Random marks between 50-100

### Terms (6 records)
**2024/2025 Academic Year:**
- Term 1: Sep 1, 2024 - Dec 15, 2024 (Active)
- Term 2: Jan 6, 2025 - Apr 15, 2025 (Active)
- Term 3: Apr 28, 2025 - Jul 20, 2025 (Inactive)

**2023/2024 Academic Year:**
- Term 1, 2, 3 (All Inactive - Historical)

### Subject Weightings (5 custom + defaults)
**Custom Weightings:**
- Mathematics: 30% CA, 70% Exam
- Science: 35% CA, 65% Exam
- Physical Education: 60% CA, 40% Exam
- Art: 70% CA, 30% Exam
- Music: 70% CA, 30% Exam

**Default Weighting** (for other subjects):
- 40% CA, 60% Exam

### Term Assessments (72 records)
- Assessments for first 15 students
- 6 subjects per student
- Mix of data types:
  - Complete assessments (both CA and Exam)
  - Partial assessments (CA only)
  - Partial assessments (Exam only)
  - Missing assessments
- Realistic marks based on subject weightings

## Seeder Architecture

### Directory Structure

```
backend/
├── database/
│   ├── seeders/
│   │   ├── DatabaseSeeder.php          # Main orchestrator
│   │   ├── UsersSeeder.php             # User accounts
│   │   ├── SubjectsSeeder.php          # Academic subjects
│   │   ├── ClassLevelsSeeder.php       # Grade levels
│   │   ├── StudentsSeeder.php          # Student records
│   │   ├── GradesSeeder.php            # Legacy grades
│   │   ├── TermsSeeder.php             # Academic terms
│   │   ├── SubjectWeightingsSeeder.php # CA/Exam percentages
│   │   ├── TermAssessmentsSeeder.php   # Term assessments
│   │   └── README.md                   # Seeder documentation
│   ├── migrations/
│   │   └── add_termly_reports.sql      # Termly reports migration
│   ├── schema.sql                      # Base database schema
│   └── seed.sql                        # Legacy seed file (deprecated)
├── migrate_fresh_seed.php              # Fresh migration + seed
├── seed.php                            # Seed only script
└── setup_database.php                  # Legacy setup (deprecated)
```

### Seeder Execution Order

Seeders run in dependency order:

1. **UsersSeeder** - No dependencies
2. **SubjectsSeeder** - No dependencies
3. **ClassLevelsSeeder** - No dependencies
4. **StudentsSeeder** - Depends on ClassLevels
5. **GradesSeeder** - Depends on Students, Subjects
6. **TermsSeeder** - No dependencies
7. **SubjectWeightingsSeeder** - Depends on Subjects
8. **TermAssessmentsSeeder** - Depends on Students, Subjects, Terms

## Creating Custom Seeders

### Step 1: Create Seeder File

Create a new file in `backend/database/seeders/`:

```php
<?php
// backend/database/seeders/MyFeatureSeeder.php

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
        
        $data = [
            ['field1' => 'value1', 'field2' => 'value2'],
            ['field1' => 'value3', 'field2' => 'value4'],
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO my_table (field1, field2) 
            VALUES (:field1, :field2)
            ON DUPLICATE KEY UPDATE field2 = :field2
        ");

        foreach ($data as $item) {
            $stmt->execute($item);
        }

        echo "    ✓ Seeded " . count($data) . " records\n";
    }
}
```

### Step 2: Register in DatabaseSeeder

Edit `backend/database/seeders/DatabaseSeeder.php`:

```php
require_once __DIR__ . '/MyFeatureSeeder.php';

// In the run() method, add to the $seeders array:
$seeders = [
    // ... existing seeders ...
    new MyFeatureSeeder($this->conn),
];
```

### Step 3: Run Your Seeder

```bash
# Run all seeders (including yours)
php backend/seed.php

# Or run just your seeder
php backend/seed.php MyFeature
```

## Best Practices

### 1. Use ON DUPLICATE KEY UPDATE

Make seeders idempotent (safe to run multiple times):

```php
$stmt = $this->conn->prepare("
    INSERT INTO table (id, name) 
    VALUES (:id, :name)
    ON DUPLICATE KEY UPDATE name = :name
");
```

### 2. Check Dependencies

Verify required data exists before seeding:

```php
$subjects = $this->conn->query("SELECT id FROM subjects")->fetchAll();
if (empty($subjects)) {
    echo "    ⚠ No subjects found, skipping\n";
    return;
}
```

### 3. Provide Feedback

Give clear output about what's being seeded:

```php
echo "  Seeding students...\n";
// ... seeding logic ...
echo "    ✓ Seeded $count students\n";
echo "    ℹ Distributed across 6 grade levels\n";
```

### 4. Use Realistic Data

- Use realistic names, IDs, and values
- Vary the data (don't make everything the same)
- Include edge cases (partial data, missing data)

### 5. Respect Foreign Keys

Always seed parent tables before child tables:
- Seed `class_levels` before `students`
- Seed `subjects` before `subject_weightings`
- Seed `terms` before `term_assessments`

## Testing After Seeding

### Verify Data

```bash
# Run end-to-end tests
php backend/test_termly_reports_e2e.php

# Run component tests
php backend/test_term_management.php
php backend/test_subject_weighting.php
php backend/test_assessment_management.php
php backend/test_report_generation.php
```

### Check Database

```sql
-- Check record counts
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'subjects', COUNT(*) FROM subjects
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'terms', COUNT(*) FROM terms
UNION ALL
SELECT 'term_assessments', COUNT(*) FROM term_assessments;

-- Check active terms
SELECT * FROM terms WHERE is_active = 1;

-- Check subject weightings
SELECT s.name, sw.ca_percentage, sw.exam_percentage
FROM subjects s
LEFT JOIN subject_weightings sw ON s.id = sw.subject_id;

-- Check sample assessments
SELECT 
    s.student_id,
    s.name,
    sub.name as subject,
    ta.ca_mark,
    ta.exam_mark,
    ta.final_mark
FROM term_assessments ta
JOIN students s ON ta.student_id = s.student_id
JOIN subjects sub ON ta.subject_id = sub.id
LIMIT 10;
```

## Troubleshooting

### Error: Column not found

Check that your migration has run:
```bash
php backend/migrate_fresh_seed.php
```

### Error: Foreign key constraint fails

Ensure seeders run in the correct order. Check `DatabaseSeeder.php`.

### Error: Duplicate entry

This is usually OK - the seeder is designed to update existing records.

### No data after seeding

Check for errors in the seeder output. Verify database connection settings in `backend/config/database.php`.

## Migration vs Seeding

### Migrations
- **Purpose**: Change database structure (add tables, columns, indexes)
- **Location**: `backend/database/migrations/`
- **When**: Schema changes, new features requiring DB changes
- **Run**: Automatically by `migrate_fresh_seed.php`

### Seeders
- **Purpose**: Populate database with data
- **Location**: `backend/database/seeders/`
- **When**: Initial setup, testing, demos
- **Run**: `php backend/seed.php` or `migrate_fresh_seed.php`

## Production Considerations

### Don't Use in Production

These seeders are for **development and testing only**. They:
- Create test accounts with known passwords
- Generate sample data
- May overwrite existing data

### Production Data

For production:
1. Run migrations only: `php backend/run_migration.php`
2. Create admin user manually
3. Let users create their own data

### Backup Before Seeding

Always backup before running `migrate_fresh_seed.php`:

```bash
# Backup database
mysqldump -u root student_management > backup.sql

# Run fresh migration and seed
php backend/migrate_fresh_seed.php

# Restore if needed
mysql -u root student_management < backup.sql
```

## Summary

The seeding system provides:
- ✓ Organized, modular seeders for each feature
- ✓ Realistic test data for development
- ✓ Idempotent operations (safe to re-run)
- ✓ Clear dependency management
- ✓ Easy to extend with new seeders
- ✓ Comprehensive test data coverage

Use `php backend/migrate_fresh_seed.php` for a clean start anytime!
