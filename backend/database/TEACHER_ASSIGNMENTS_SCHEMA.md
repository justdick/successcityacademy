# Teacher Assignments Database Schema Documentation

## Overview

This document describes the database schema for the Teacher Assignments feature, which implements role-based access control for teachers in the Student Management System.

**Version:** 1.0  
**Last Updated:** November 3, 2024  
**Migration File:** `backend/database/migrations/add_teacher_assignments.sql`

---

## Table of Contents

1. [Schema Overview](#schema-overview)
2. [Table Definitions](#table-definitions)
3. [Relationships](#relationships)
4. [Indexes](#indexes)
5. [Constraints](#constraints)
6. [Migration Scripts](#migration-scripts)
7. [Data Dictionary](#data-dictionary)
8. [Query Examples](#query-examples)

---

## Schema Overview

The Teacher Assignments feature introduces two new tables to manage teacher-to-class and teacher-to-subject relationships:

```
┌─────────────────────────────────────────────────────────────┐
│                     Existing Tables                          │
├─────────────────────────────────────────────────────────────┤
│  users                                                       │
│  class_levels                                                │
│  subjects                                                    │
│  students                                                    │
│  assessments                                                 │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ Referenced by
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      New Tables                              │
├─────────────────────────────────────────────────────────────┤
│  teacher_class_assignments                                   │
│  teacher_subject_assignments                                 │
└─────────────────────────────────────────────────────────────┘
```

### Entity Relationship Diagram

```
┌──────────────┐
│    users     │
│              │
│ id (PK)      │
│ username     │
│ role         │
└──────┬───────┘
       │
       │ 1:N
       │
       ├─────────────────────────────────────┐
       │                                     │
       ↓                                     ↓
┌──────────────────────────┐    ┌──────────────────────────┐
│ teacher_class_assignments│    │teacher_subject_assignments│
│                          │    │                           │
│ id (PK)                  │    │ id (PK)                   │
│ user_id (FK)             │    │ user_id (FK)              │
│ class_level_id (FK)      │    │ subject_id (FK)           │
│ created_at               │    │ created_at                │
└──────────┬───────────────┘    └──────────┬────────────────┘
           │                               │
           │ N:1                           │ N:1
           │                               │
           ↓                               ↓
    ┌──────────────┐              ┌──────────────┐
    │ class_levels │              │   subjects   │
    │              │              │              │
    │ id (PK)      │              │ id (PK)      │
    │ name         │              │ name         │
    └──────────────┘              └──────────────┘
```

---

## Table Definitions

### teacher_class_assignments

Links teachers to the classes they are assigned to teach.

**Table Name:** `teacher_class_assignments`

**Purpose:** Stores the relationship between teachers (users with role "user") and class levels, enabling class-based access control.

**SQL Definition:**

```sql
CREATE TABLE teacher_class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_level_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_level_id) REFERENCES class_levels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_class (user_id, class_level_id),
    INDEX idx_user_id (user_id),
    INDEX idx_class_level_id (class_level_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Column Details:**

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | INT | NO | AUTO_INCREMENT | Primary key, unique identifier for each assignment |
| user_id | INT | NO | - | Foreign key to users.id, identifies the teacher |
| class_level_id | INT | NO | - | Foreign key to class_levels.id, identifies the class |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Timestamp when the assignment was created |

**Storage Estimates:**
- Row size: ~20 bytes
- Expected rows: 50-500 (depending on school size)
- Estimated table size: 1-10 KB

---

### teacher_subject_assignments

Links teachers to the subjects they are assigned to teach.

**Table Name:** `teacher_subject_assignments`

**Purpose:** Stores the relationship between teachers (users with role "user") and subjects, enabling subject-based access control for grade entry.

**SQL Definition:**

```sql
CREATE TABLE teacher_subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (user_id, subject_id),
    INDEX idx_user_id (user_id),
    INDEX idx_subject_id (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Column Details:**

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| id | INT | NO | AUTO_INCREMENT | Primary key, unique identifier for each assignment |
| user_id | INT | NO | - | Foreign key to users.id, identifies the teacher |
| subject_id | INT | NO | - | Foreign key to subjects.id, identifies the subject |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Timestamp when the assignment was created |

**Storage Estimates:**
- Row size: ~20 bytes
- Expected rows: 50-500 (depending on school size)
- Estimated table size: 1-10 KB

---

## Relationships

### Foreign Key Relationships

#### teacher_class_assignments

**Relationship 1: Teacher (User)**
- **Type:** Many-to-One
- **Foreign Key:** `user_id` → `users.id`
- **On Delete:** CASCADE
- **Description:** Each assignment belongs to one teacher. When a user is deleted, all their class assignments are automatically deleted.

**Relationship 2: Class Level**
- **Type:** Many-to-One
- **Foreign Key:** `class_level_id` → `class_levels.id`
- **On Delete:** CASCADE
- **Description:** Each assignment references one class level. When a class level is deleted, all assignments to that class are automatically deleted.

#### teacher_subject_assignments

**Relationship 1: Teacher (User)**
- **Type:** Many-to-One
- **Foreign Key:** `user_id` → `users.id`
- **On Delete:** CASCADE
- **Description:** Each assignment belongs to one teacher. When a user is deleted, all their subject assignments are automatically deleted.

**Relationship 2: Subject**
- **Type:** Many-to-One
- **Foreign Key:** `subject_id` → `subjects.id`
- **On Delete:** CASCADE
- **Description:** Each assignment references one subject. When a subject is deleted, all assignments to that subject are automatically deleted.

### Cardinality

**Teacher to Classes:**
- One teacher can be assigned to many classes (1:N)
- One class can have many teachers assigned (N:1)
- Overall: Many-to-Many relationship via teacher_class_assignments

**Teacher to Subjects:**
- One teacher can be assigned to many subjects (1:N)
- One subject can have many teachers assigned (N:1)
- Overall: Many-to-Many relationship via teacher_subject_assignments

### Relationship Diagram

```
users (1) ──────< (N) teacher_class_assignments (N) >────── (1) class_levels
users (1) ──────< (N) teacher_subject_assignments (N) >───── (1) subjects
```

---

## Indexes

Indexes are created to optimize query performance for common access patterns.

### teacher_class_assignments Indexes

| Index Name | Type | Columns | Purpose |
|------------|------|---------|---------|
| PRIMARY | PRIMARY KEY | id | Unique identifier for each row |
| unique_teacher_class | UNIQUE | (user_id, class_level_id) | Prevents duplicate assignments |
| idx_user_id | INDEX | user_id | Fast lookup of all classes for a teacher |
| idx_class_level_id | INDEX | class_level_id | Fast lookup of all teachers for a class |

**Query Optimization:**

```sql
-- Optimized by idx_user_id
SELECT * FROM teacher_class_assignments WHERE user_id = ?;

-- Optimized by idx_class_level_id
SELECT * FROM teacher_class_assignments WHERE class_level_id = ?;

-- Optimized by unique_teacher_class
SELECT * FROM teacher_class_assignments 
WHERE user_id = ? AND class_level_id = ?;
```

### teacher_subject_assignments Indexes

| Index Name | Type | Columns | Purpose |
|------------|------|---------|---------|
| PRIMARY | PRIMARY KEY | id | Unique identifier for each row |
| unique_teacher_subject | UNIQUE | (user_id, subject_id) | Prevents duplicate assignments |
| idx_user_id | INDEX | user_id | Fast lookup of all subjects for a teacher |
| idx_subject_id | INDEX | subject_id | Fast lookup of all teachers for a subject |

**Query Optimization:**

```sql
-- Optimized by idx_user_id
SELECT * FROM teacher_subject_assignments WHERE user_id = ?;

-- Optimized by idx_subject_id
SELECT * FROM teacher_subject_assignments WHERE subject_id = ?;

-- Optimized by unique_teacher_subject
SELECT * FROM teacher_subject_assignments 
WHERE user_id = ? AND subject_id = ?;
```

### Index Performance

**Expected Performance:**
- Lookup by user_id: O(log n) - typically < 1ms
- Lookup by class_level_id/subject_id: O(log n) - typically < 1ms
- Duplicate check: O(log n) - typically < 1ms

**Index Maintenance:**
- Indexes are automatically maintained by MySQL
- INSERT operations slightly slower due to index updates
- DELETE operations benefit from CASCADE with indexes

---

## Constraints

### Primary Key Constraints

Both tables use auto-incrementing integer primary keys:

```sql
id INT AUTO_INCREMENT PRIMARY KEY
```

**Benefits:**
- Unique identifier for each assignment
- Fast joins and lookups
- Efficient storage

### Foreign Key Constraints

#### teacher_class_assignments

```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (class_level_id) REFERENCES class_levels(id) ON DELETE CASCADE
```

**Enforcement:**
- Cannot create assignment with non-existent user_id
- Cannot create assignment with non-existent class_level_id
- Deleting a user automatically deletes their assignments
- Deleting a class level automatically deletes assignments to that class

#### teacher_subject_assignments

```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
```

**Enforcement:**
- Cannot create assignment with non-existent user_id
- Cannot create assignment with non-existent subject_id
- Deleting a user automatically deletes their assignments
- Deleting a subject automatically deletes assignments to that subject

### Unique Constraints

#### teacher_class_assignments

```sql
UNIQUE KEY unique_teacher_class (user_id, class_level_id)
```

**Purpose:** Prevents duplicate assignments of the same teacher to the same class.

**Example:**
```sql
-- First insert succeeds
INSERT INTO teacher_class_assignments (user_id, class_level_id) VALUES (2, 5);

-- Second insert fails with duplicate key error
INSERT INTO teacher_class_assignments (user_id, class_level_id) VALUES (2, 5);
```

#### teacher_subject_assignments

```sql
UNIQUE KEY unique_teacher_subject (user_id, subject_id)
```

**Purpose:** Prevents duplicate assignments of the same teacher to the same subject.

**Example:**
```sql
-- First insert succeeds
INSERT INTO teacher_subject_assignments (user_id, subject_id) VALUES (2, 3);

-- Second insert fails with duplicate key error
INSERT INTO teacher_subject_assignments (user_id, subject_id) VALUES (2, 3);
```

### NOT NULL Constraints

All columns except `id` are NOT NULL:

```sql
user_id INT NOT NULL
class_level_id INT NOT NULL  -- or subject_id
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Enforcement:**
- Cannot insert NULL values for user_id or class_level_id/subject_id
- created_at automatically set to current timestamp if not provided

---

## Migration Scripts

### Creating the Tables

**File:** `backend/database/migrations/add_teacher_assignments.sql`

```sql
-- Create teacher_class_assignments table
CREATE TABLE IF NOT EXISTS teacher_class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_level_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_level_id) REFERENCES class_levels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_class (user_id, class_level_id),
    INDEX idx_user_id (user_id),
    INDEX idx_class_level_id (class_level_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create teacher_subject_assignments table
CREATE TABLE IF NOT EXISTS teacher_subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (user_id, subject_id),
    INDEX idx_user_id (user_id),
    INDEX idx_subject_id (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Running the Migration

**Script:** `backend/migrate_teacher_assignments.php`

```bash
php backend/migrate_teacher_assignments.php
```

**Output:**
```
Starting teacher assignments migration...
Creating teacher_class_assignments table...
Creating teacher_subject_assignments table...
Migration completed successfully!
```

### Verifying the Migration

**Script:** `backend/verify_teacher_assignments.php`

```bash
php backend/verify_teacher_assignments.php
```

**Verification Checks:**
- Tables exist
- Columns are correct
- Indexes are created
- Foreign keys are set up
- Constraints are enforced

### Rolling Back

To remove the tables:

```sql
DROP TABLE IF EXISTS teacher_subject_assignments;
DROP TABLE IF EXISTS teacher_class_assignments;
```

**Note:** Drop `teacher_subject_assignments` first, then `teacher_class_assignments`, to avoid foreign key constraint issues (though both reference users, not each other).

---

## Data Dictionary

### teacher_class_assignments

| Column | Data Type | Length | Precision | Scale | Nullable | Default | Auto Increment | Description |
|--------|-----------|--------|-----------|-------|----------|---------|----------------|-------------|
| id | INT | - | 10 | 0 | NO | NULL | YES | Primary key |
| user_id | INT | - | 10 | 0 | NO | NULL | NO | Teacher's user ID |
| class_level_id | INT | - | 10 | 0 | NO | NULL | NO | Class level ID |
| created_at | TIMESTAMP | - | - | - | NO | CURRENT_TIMESTAMP | NO | Creation timestamp |

**Valid Values:**
- `user_id`: Must exist in users table with role "user"
- `class_level_id`: Must exist in class_levels table
- `created_at`: Any valid timestamp

**Business Rules:**
- One teacher can have multiple class assignments
- One class can have multiple teacher assignments
- No duplicate (user_id, class_level_id) combinations

### teacher_subject_assignments

| Column | Data Type | Length | Precision | Scale | Nullable | Default | Auto Increment | Description |
|--------|-----------|--------|-----------|-------|----------|---------|----------------|-------------|
| id | INT | - | 10 | 0 | NO | NULL | YES | Primary key |
| user_id | INT | - | 10 | 0 | NO | NULL | NO | Teacher's user ID |
| subject_id | INT | - | 10 | 0 | NO | NULL | NO | Subject ID |
| created_at | TIMESTAMP | - | - | - | NO | CURRENT_TIMESTAMP | NO | Creation timestamp |

**Valid Values:**
- `user_id`: Must exist in users table with role "user"
- `subject_id`: Must exist in subjects table
- `created_at`: Any valid timestamp

**Business Rules:**
- One teacher can have multiple subject assignments
- One subject can have multiple teacher assignments
- No duplicate (user_id, subject_id) combinations

---

## Query Examples

### Common Queries

#### 1. Get all classes assigned to a teacher

```sql
SELECT 
    tca.id AS assignment_id,
    cl.id AS class_id,
    cl.name AS class_name,
    COUNT(s.id) AS student_count
FROM teacher_class_assignments tca
JOIN class_levels cl ON tca.class_level_id = cl.id
LEFT JOIN students s ON s.class_level_id = cl.id
WHERE tca.user_id = ?
GROUP BY tca.id, cl.id, cl.name
ORDER BY cl.name;
```

#### 2. Get all subjects assigned to a teacher

```sql
SELECT 
    tsa.id AS assignment_id,
    sub.id AS subject_id,
    sub.name AS subject_name
FROM teacher_subject_assignments tsa
JOIN subjects sub ON tsa.subject_id = sub.id
WHERE tsa.user_id = ?
ORDER BY sub.name;
```

#### 3. Check if teacher has access to a specific class

```sql
SELECT COUNT(*) AS has_access
FROM teacher_class_assignments
WHERE user_id = ? AND class_level_id = ?;
```

**Returns:** 1 if has access, 0 if no access

#### 4. Check if teacher has access to a specific subject

```sql
SELECT COUNT(*) AS has_access
FROM teacher_subject_assignments
WHERE user_id = ? AND subject_id = ?;
```

**Returns:** 1 if has access, 0 if no access

#### 5. Get all teachers assigned to a class

```sql
SELECT 
    u.id AS user_id,
    u.username,
    u.full_name,
    tca.created_at AS assigned_at
FROM teacher_class_assignments tca
JOIN users u ON tca.user_id = u.id
WHERE tca.class_level_id = ?
ORDER BY u.full_name;
```

#### 6. Get all teachers assigned to a subject

```sql
SELECT 
    u.id AS user_id,
    u.username,
    u.full_name,
    tsa.created_at AS assigned_at
FROM teacher_subject_assignments tsa
JOIN users u ON tsa.user_id = u.id
WHERE tsa.subject_id = ?
ORDER BY u.full_name;
```

#### 7. Get complete assignment summary for all teachers

```sql
SELECT 
    u.id AS user_id,
    u.username,
    u.full_name,
    GROUP_CONCAT(DISTINCT cl.name ORDER BY cl.name SEPARATOR ', ') AS classes,
    GROUP_CONCAT(DISTINCT sub.name ORDER BY sub.name SEPARATOR ', ') AS subjects,
    COUNT(DISTINCT tca.class_level_id) AS class_count,
    COUNT(DISTINCT tsa.subject_id) AS subject_count
FROM users u
LEFT JOIN teacher_class_assignments tca ON u.id = tca.user_id
LEFT JOIN class_levels cl ON tca.class_level_id = cl.id
LEFT JOIN teacher_subject_assignments tsa ON u.id = tsa.user_id
LEFT JOIN subjects sub ON tsa.subject_id = sub.id
WHERE u.role = 'user'
GROUP BY u.id, u.username, u.full_name
ORDER BY u.full_name;
```

#### 8. Get students accessible by a teacher

```sql
SELECT DISTINCT s.*
FROM students s
JOIN teacher_class_assignments tca ON s.class_level_id = tca.class_level_id
WHERE tca.user_id = ?
ORDER BY s.last_name, s.first_name;
```

#### 9. Check if teacher can access a specific student

```sql
SELECT COUNT(*) AS has_access
FROM students s
JOIN teacher_class_assignments tca ON s.class_level_id = tca.class_level_id
WHERE tca.user_id = ? AND s.id = ?;
```

#### 10. Create a new class assignment

```sql
INSERT INTO teacher_class_assignments (user_id, class_level_id)
VALUES (?, ?)
ON DUPLICATE KEY UPDATE id = id;
```

**Note:** `ON DUPLICATE KEY UPDATE id = id` prevents error on duplicate, but doesn't create a new row.

#### 11. Create a new subject assignment

```sql
INSERT INTO teacher_subject_assignments (user_id, subject_id)
VALUES (?, ?)
ON DUPLICATE KEY UPDATE id = id;
```

#### 12. Delete a class assignment

```sql
DELETE FROM teacher_class_assignments
WHERE id = ?;
```

#### 13. Delete a subject assignment

```sql
DELETE FROM teacher_subject_assignments
WHERE id = ?;
```

#### 14. Delete all assignments for a teacher

```sql
-- Delete class assignments
DELETE FROM teacher_class_assignments WHERE user_id = ?;

-- Delete subject assignments
DELETE FROM teacher_subject_assignments WHERE user_id = ?;
```

---

## Performance Considerations

### Query Performance

**Indexed Queries (Fast):**
- Lookup by user_id: < 1ms
- Lookup by class_level_id/subject_id: < 1ms
- Duplicate check: < 1ms

**Join Queries (Moderate):**
- Get teacher assignments with names: 1-5ms
- Get students accessible by teacher: 5-20ms (depends on student count)

**Aggregate Queries (Slower):**
- Complete assignment summary for all teachers: 10-50ms (depends on data size)

### Optimization Tips

1. **Use prepared statements** to avoid SQL injection and improve performance
2. **Cache teacher assignments** in session to reduce database queries
3. **Use EXISTS instead of COUNT** for access checks when possible
4. **Limit result sets** with appropriate WHERE clauses
5. **Use covering indexes** for frequently accessed columns

### Scaling Considerations

**Current Design:**
- Suitable for schools with up to 1000 teachers
- Handles up to 10,000 assignments efficiently
- Query performance remains good with proper indexing

**Future Enhancements:**
- Add caching layer for frequently accessed assignments
- Consider materialized views for complex reports
- Implement partitioning for very large datasets

---

## Maintenance

### Regular Maintenance Tasks

1. **Monitor index usage**
   ```sql
   SHOW INDEX FROM teacher_class_assignments;
   SHOW INDEX FROM teacher_subject_assignments;
   ```

2. **Check table statistics**
   ```sql
   ANALYZE TABLE teacher_class_assignments;
   ANALYZE TABLE teacher_subject_assignments;
   ```

3. **Verify referential integrity**
   ```sql
   -- Check for orphaned assignments (shouldn't happen with FK constraints)
   SELECT * FROM teacher_class_assignments tca
   LEFT JOIN users u ON tca.user_id = u.id
   WHERE u.id IS NULL;
   ```

4. **Monitor table size**
   ```sql
   SELECT 
       table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
   FROM information_schema.TABLES
   WHERE table_schema = DATABASE()
   AND table_name IN ('teacher_class_assignments', 'teacher_subject_assignments');
   ```

### Backup and Recovery

**Backup:**
```bash
mysqldump -u username -p database_name \
  teacher_class_assignments \
  teacher_subject_assignments \
  > teacher_assignments_backup.sql
```

**Restore:**
```bash
mysql -u username -p database_name < teacher_assignments_backup.sql
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024-11-03 | Initial schema creation |

---

## References

- **Migration Script:** `backend/migrate_teacher_assignments.php`
- **Verification Script:** `backend/verify_teacher_assignments.php`
- **API Documentation:** `backend/api/TEACHER_ASSIGNMENTS_API.md`
- **User Guide:** `.kiro/specs/teacher-assignments/USER_GUIDE.md`
- **Design Document:** `.kiro/specs/teacher-assignments/design.md`
