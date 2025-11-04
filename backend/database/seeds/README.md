# Database Seeding Guide

## Overview

This directory contains seed files to populate your Senior High School database with realistic class levels and subjects.

## Available Seed Files

### 1. `seed_class_levels.php`
Seeds the `class_levels` table with course-based sections:

**STEM (Science, Technology, Engineering, Mathematics)**
- STEM 1A, STEM 1B, STEM 2A, STEM 2B

**Business (ABM - Accountancy, Business, and Management)**
- Business 1A, Business 1B, Business 2A, Business 2B

**Humanities (HUMSS - Humanities and Social Sciences)**
- Humanities 1A, Humanities 1B, Humanities 2A, Humanities 2B

**General Arts (GAS - General Academic Strand)**
- General Arts 1A, General Arts 1B, General Arts 2A, General Arts 2B

**ICT (Information and Communications Technology)**
- ICT 1A, ICT 1B, ICT 2A, ICT 2B

**Home Economics**
- Home Economics 1A, Home Economics 2A

**Arts & Design**
- Arts & Design 1A, Arts & Design 2A

**Total:** 26 class levels

### 2. `seed_subjects.php`
Seeds the `subjects` table with typical SHS subjects:

**Core Subjects** (Common to all strands)
- Oral Communication, Reading and Writing
- Komunikasyon at Pananaliksik, Pagbasa at Pagsusuri
- General Mathematics, Statistics and Probability
- Earth and Life Science, Physical Science
- Personal Development, Understanding Culture
- Philosophy, PE & Health, Contemporary Arts
- Media and Information Literacy

**STEM Specialized Subjects**
- Pre-Calculus, Basic Calculus
- General Biology 1 & 2
- General Physics 1 & 2
- General Chemistry 1 & 2

**ABM Specialized Subjects**
- Fundamentals of Accountancy, Business Math
- Business Finance, Marketing, Ethics
- Organization and Management
- Applied Economics, Business Enterprise Simulation

**HUMSS Specialized Subjects**
- Creative Writing, Creative Nonfiction
- World Religions, Philippine Politics
- Community Engagement, Social Sciences
- Trends and Critical Thinking

**ICT Specialized Subjects**
- Computer Programming, Web Development
- Computer Systems Servicing
- Animation, Technical Drafting

**Home Economics Subjects**
- Bread and Pastry Production, Cookery
- Food and Beverage Services, Housekeeping

**Arts and Design Subjects**
- Contemporary Philippine Arts from the Regions
- Art Appreciation, Creative Industries, Exhibit Design

**Total:** 60+ subjects

### 3. `seed_all.php`
Master script that runs all seed files in the correct order.

## Usage

### Option 1: Run All Seeds (Recommended)

```bash
php backend/database/seeds/seed_all.php
```

This will seed both class levels and subjects in one go.

### Option 2: Run Individual Seeds

Seed only class levels:
```bash
php backend/database/seeds/seed_class_levels.php
```

Seed only subjects:
```bash
php backend/database/seeds/seed_subjects.php
```

## Features

✅ **Duplicate Prevention:** Scripts check for existing records and skip duplicates  
✅ **Safe to Re-run:** You can run the scripts multiple times without issues  
✅ **Detailed Output:** Shows what was created, skipped, or failed  
✅ **Summary Report:** Displays all records after seeding  

## Example Output

```
==========================================================
           SENIOR HIGH SCHOOL DATABASE SEEDER
==========================================================

📚 STEP 1: Seeding Class Levels
----------------------------------------------------------
✅ Created: 'STEM 1A'
✅ Created: 'STEM 1B'
✅ Created: 'Business 1A'
⚠️  Skipped: 'Business 1B' (already exists)
...

==================================================
Seeding completed!
✅ Inserted: 24 class levels
⚠️  Skipped: 2 class levels (already exist)
==================================================

📖 STEP 2: Seeding Subjects
----------------------------------------------------------
✅ Created: 'Oral Communication'
✅ Created: 'General Mathematics'
...

==================================================
           ✅ ALL SEEDING COMPLETED!
==================================================
```

## Customization

### Adding More Class Levels

Edit `seed_class_levels.php` and add to the `$classLevels` array:

```php
$classLevels = [
    // ... existing classes
    'Your Custom Class 1A',
    'Your Custom Class 1B',
];
```

### Adding More Subjects

Edit `seed_subjects.php` and add to the `$subjects` array:

```php
$subjects = [
    // ... existing subjects
    'Your Custom Subject',
];
```

### Modifying Naming Convention

If you want different naming (e.g., "STEM-11A" instead of "STEM 1A"), simply update the strings in the arrays.

## Prerequisites

- Database connection configured in `backend/config/database.php`
- `class_levels` table exists
- `subjects` table exists

## Troubleshooting

**Error: "Table 'class_levels' doesn't exist"**
- Run your database migrations first
- Ensure the database schema is set up

**Error: "Connection failed"**
- Check your database credentials in `config/database.php`
- Ensure MySQL server is running

**No output or script hangs**
- Check PHP error logs
- Verify file permissions
- Ensure PHP CLI is installed

## Next Steps After Seeding

1. **Verify Data**
   - Check your database to confirm records were created
   - Review class level and subject names

2. **Create Teacher Accounts**
   - Add users with role "user" (teachers)
   - Ensure they have proper credentials

3. **Assign Teachers**
   - Use the Teacher Assignments UI
   - Assign teachers to their respective classes and subjects

4. **Add Students**
   - Create student records
   - Assign them to appropriate class levels

5. **Test Access Control**
   - Log in as a teacher
   - Verify they only see their assigned classes and subjects

## Database Schema Reference

### class_levels Table
```sql
CREATE TABLE class_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
```

### subjects Table
```sql
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
```

## Support

For issues or questions:
- Review the main documentation in `.kiro/specs/teacher-assignments/`
- Check the API documentation in `backend/api/TEACHER_ASSIGNMENTS_API.md`
- Review the user guide in `.kiro/specs/teacher-assignments/USER_GUIDE.md`

## Version

**Version:** 1.0  
**Last Updated:** November 3, 2024  
**Compatible With:** Teacher Assignments Feature v1.0
