<?php
/**
 * Complete Fresh Migration Script
 * This script drops and recreates ALL tables, then seeds with SHS data
 * 
 * WARNING: This will DELETE EVERYTHING and recreate from scratch!
 * 
 * Usage: php backend/migrate_fresh_all.php
 */

echo "\n" . str_repeat("=", 60) . "\n";
echo "     COMPLETE FRESH MIGRATION - ALL FEATURES\n";
echo str_repeat("=", 60) . "\n\n";

echo "⚠️  WARNING: This will DELETE EVERYTHING!\n";
echo "   - All tables will be dropped\n";
echo "   - All data will be lost\n";
echo "   - Database will be recreated from scratch\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

try {
    // Connect to database
    echo "Step 1: Connecting to database...\n";
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database: $database\n\n";

    // Disable foreign key checks
    echo "Step 2: Disabling foreign key checks...\n";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✓ Foreign key checks disabled\n\n";

    // Drop all tables
    echo "Step 3: Dropping existing tables...\n";
    $tables = [
        'term_assessments',
        'subject_weightings',
        'terms',
        'access_logs',
        'teacher_subject_assignments',
        'teacher_class_assignments',
        'students',
        'class_levels',
        'subjects',
        'users'
    ];
    
    foreach ($tables as $table) {
        try {
            echo "  Dropping $table...\n";
            $conn->exec("DROP TABLE IF EXISTS $table");
            echo "  ✓ Dropped $table\n";
        } catch (PDOException $e) {
            echo "  ⚠ Warning: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // Re-enable foreign key checks
    echo "Step 4: Re-enabling foreign key checks...\n";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✓ Foreign key checks re-enabled\n\n";

    // Create base tables
    echo "Step 5: Creating base tables...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Users table
    echo "  Creating users table...\n";
    $conn->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user' NOT NULL,
            full_name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created users table\n";

    // Subjects table
    echo "  Creating subjects table...\n";
    $conn->exec("
        CREATE TABLE subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created subjects table\n";

    // Class Levels table
    echo "  Creating class_levels table...\n";
    $conn->exec("
        CREATE TABLE class_levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created class_levels table\n";

    // Students table
    echo "  Creating students table...\n";
    $conn->exec("
        CREATE TABLE students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            class_level_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (class_level_id) REFERENCES class_levels(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX idx_student_id (student_id),
            INDEX idx_class_level (class_level_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created students table\n";

    echo str_repeat("-", 60) . "\n\n";

    // Create teacher assignment tables
    echo "Step 6: Creating teacher assignment tables...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Teacher Class Assignments
    echo "  Creating teacher_class_assignments table...\n";
    $conn->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created teacher_class_assignments table\n";

    // Teacher Subject Assignments
    echo "  Creating teacher_subject_assignments table...\n";
    $conn->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created teacher_subject_assignments table\n";

    echo str_repeat("-", 60) . "\n\n";

    // Create assessment tables
    echo "Step 6b: Creating assessment tables...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Terms table
    echo "  Creating terms table...\n";
    $conn->exec("
        CREATE TABLE terms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            academic_year VARCHAR(20) NOT NULL,
            start_date DATE,
            end_date DATE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_term (name, academic_year),
            INDEX idx_academic_year (academic_year),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created terms table\n";

    // Subject weightings table
    echo "  Creating subject_weightings table...\n";
    $conn->exec("
        CREATE TABLE subject_weightings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT NOT NULL,
            ca_percentage DECIMAL(5,2) NOT NULL DEFAULT 40.00,
            exam_percentage DECIMAL(5,2) NOT NULL DEFAULT 60.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            UNIQUE KEY unique_subject_weighting (subject_id),
            CONSTRAINT chk_ca_percentage CHECK (ca_percentage >= 0 AND ca_percentage <= 100),
            CONSTRAINT chk_exam_percentage CHECK (exam_percentage >= 0 AND exam_percentage <= 100),
            CONSTRAINT chk_percentage_sum CHECK (ca_percentage + exam_percentage = 100.00),
            INDEX idx_subject_id (subject_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created subject_weightings table\n";

    // Term assessments table
    echo "  Creating term_assessments table...\n";
    $conn->exec("
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
            UNIQUE KEY unique_assessment (student_id, subject_id, term_id),
            INDEX idx_student_id (student_id),
            INDEX idx_subject_id (subject_id),
            INDEX idx_term_id (term_id),
            INDEX idx_student_term (student_id, term_id),
            INDEX idx_term_subject (term_id, subject_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created term_assessments table\n";

    // Access logs table
    echo "  Creating access_logs table...\n";
    $conn->exec("
        CREATE TABLE access_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            resource_type VARCHAR(50) NOT NULL,
            resource_id VARCHAR(100) NOT NULL,
            access_denied TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_resource_type (resource_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ Created access_logs table\n";

    echo str_repeat("-", 60) . "\n\n";

    // Seed users
    echo "Step 7: Seeding default users...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Admin user (password: admin123)
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->exec("
        INSERT INTO users (username, password_hash, role, full_name) 
        VALUES ('admin', '$adminHash', 'admin', 'System Administrator')
    ");
    echo "  ✓ Created admin user (username: admin, password: admin123)\n";
    
    // Sample teacher users
    $teacherHash = password_hash('teacher123', PASSWORD_DEFAULT);
    $conn->exec("
        INSERT INTO users (username, password_hash, role, full_name) VALUES
        ('teacher1', '$teacherHash', 'user', 'Maria Santos'),
        ('teacher2', '$teacherHash', 'user', 'Juan Dela Cruz'),
        ('teacher3', '$teacherHash', 'user', 'Ana Reyes')
    ");
    echo "  ✓ Created 3 sample teachers (password: teacher123)\n";
    
    echo str_repeat("-", 60) . "\n\n";

    // Seed class levels
    echo "Step 8: Seeding Class Levels...\n";
    echo str_repeat("-", 60) . "\n";
    
    $classLevels = [
        'STEM 1A', 'STEM 1B', 'STEM 2A', 'STEM 2B',
        'Business 1A', 'Business 1B', 'Business 2A', 'Business 2B',
        'Humanities 1A', 'Humanities 1B', 'Humanities 2A', 'Humanities 2B',
        'General Arts 1A', 'General Arts 1B', 'General Arts 2A', 'General Arts 2B',
        'ICT 1A', 'ICT 1B', 'ICT 2A', 'ICT 2B',
        'Home Economics 1A', 'Home Economics 2A',
        'Arts & Design 1A', 'Arts & Design 2A'
    ];
    
    $classInsertStmt = $conn->prepare("INSERT INTO class_levels (name) VALUES (:name)");
    $classInserted = 0;
    
    foreach ($classLevels as $className) {
        $classInsertStmt->execute([':name' => $className]);
        $classInserted++;
    }
    
    echo "  ✓ Created $classInserted class levels\n";
    echo str_repeat("-", 60) . "\n\n";

    // Seed subjects
    echo "Step 9: Seeding Subjects...\n";
    echo str_repeat("-", 60) . "\n";
    
    $subjects = [
        // Core Subjects
        'Oral Communication', 'Reading and Writing',
        'Komunikasyon at Pananaliksik', 'Pagbasa at Pagsusuri',
        'General Mathematics', 'Statistics and Probability',
        'Earth and Life Science', 'Physical Science',
        'Personal Development', 'Understanding Culture, Society and Politics',
        'Introduction to Philosophy', 'Physical Education and Health',
        'Contemporary Philippine Arts', 'Media and Information Literacy',
        // STEM
        'Pre-Calculus', 'Basic Calculus',
        'General Biology 1', 'General Biology 2',
        'General Physics 1', 'General Physics 2',
        'General Chemistry 1', 'General Chemistry 2',
        // ABM
        'Fundamentals of Accountancy', 'Business Math',
        'Business Finance', 'Business Marketing', 'Business Ethics',
        'Organization and Management', 'Applied Economics',
        'Business Enterprise Simulation',
        // HUMSS
        'Creative Writing', 'Creative Nonfiction',
        'World Religions and Belief Systems', 'Philippine Politics and Governance',
        'Community Engagement', 'Disciplines and Ideas in Social Sciences',
        'Trends, Networks and Critical Thinking',
        // ICT
        'Computer Programming', 'Web Development',
        'Computer Systems Servicing', 'Animation', 'Technical Drafting',
        // Home Economics
        'Bread and Pastry Production', 'Cookery',
        'Food and Beverage Services', 'Housekeeping',
        // Arts and Design
        'Contemporary Philippine Arts from the Regions',
        'Art Appreciation', 'Creative Industries', 'Exhibit Design'
    ];
    
    $subjectInsertStmt = $conn->prepare("INSERT INTO subjects (name) VALUES (:name)");
    $subjectInserted = 0;
    
    foreach ($subjects as $subjectName) {
        $subjectInsertStmt->execute([':name' => $subjectName]);
        $subjectInserted++;
    }
    
    echo "  ✓ Created $subjectInserted subjects\n";
    echo str_repeat("-", 60) . "\n\n";

    // Seed sample students
    echo "Step 10: Seeding Sample Students...\n";
    echo str_repeat("-", 60) . "\n";
    
    $sampleStudents = [
        // STEM 1A
        ['2024-STEM-001', 'Juan Dela Cruz', 1],
        ['2024-STEM-002', 'Maria Santos', 1],
        ['2024-STEM-003', 'Pedro Reyes', 1],
        ['2024-STEM-004', 'Ana Garcia', 1],
        ['2024-STEM-005', 'Jose Ramos', 1],
        // Business 1A
        ['2024-BUS-001', 'Sofia Martinez', 5],
        ['2024-BUS-002', 'Miguel Torres', 5],
        ['2024-BUS-003', 'Isabella Cruz', 5],
        ['2024-BUS-004', 'Carlos Mendoza', 5],
        ['2024-BUS-005', 'Lucia Fernandez', 5],
        // Humanities 1A
        ['2024-HUM-001', 'Diego Lopez', 9],
        ['2024-HUM-002', 'Valentina Gomez', 9],
        ['2024-HUM-003', 'Mateo Diaz', 9],
        ['2024-HUM-004', 'Camila Morales', 9],
        ['2024-HUM-005', 'Santiago Herrera', 9],
        // ICT 1A
        ['2024-ICT-001', 'Gabriel Silva', 17],
        ['2024-ICT-002', 'Emma Castillo', 17],
        ['2024-ICT-003', 'Lucas Vargas', 17],
        ['2024-ICT-004', 'Mia Ortiz', 17],
        ['2024-ICT-005', 'Daniel Navarro', 17],
    ];
    
    $studentInsertStmt = $conn->prepare("
        INSERT INTO students (student_id, name, class_level_id) 
        VALUES (:student_id, :name, :class_level_id)
    ");
    
    $studentInserted = 0;
    foreach ($sampleStudents as $student) {
        $studentInsertStmt->execute([
            ':student_id' => $student[0],
            ':name' => $student[1],
            ':class_level_id' => $student[2]
        ]);
        $studentInserted++;
    }
    
    echo "  ✓ Created $studentInserted sample students\n";
    echo str_repeat("-", 60) . "\n\n";

    // Seed teacher assignments
    echo "Step 11: Seeding Teacher Assignments...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Teacher 1 (Maria Santos) - STEM teacher
    // Assign to STEM classes
    $conn->exec("INSERT INTO teacher_class_assignments (user_id, class_level_id) VALUES (2, 1), (2, 2)"); // STEM 1A, 1B
    // Assign to STEM subjects
    $conn->exec("INSERT INTO teacher_subject_assignments (user_id, subject_id) VALUES (2, 15), (2, 16), (2, 5)"); // Pre-Calculus, Basic Calculus, General Math
    echo "  ✓ Assigned Teacher 1 (Maria Santos) to STEM classes and subjects\n";
    
    // Teacher 2 (Juan Dela Cruz) - Business teacher
    // Assign to Business classes
    $conn->exec("INSERT INTO teacher_class_assignments (user_id, class_level_id) VALUES (3, 5), (3, 6)"); // Business 1A, 1B
    // Assign to Business subjects
    $conn->exec("INSERT INTO teacher_subject_assignments (user_id, subject_id) VALUES (3, 23), (3, 24), (3, 25)"); // Accountancy, Business Math, Business Finance
    echo "  ✓ Assigned Teacher 2 (Juan Dela Cruz) to Business classes and subjects\n";
    
    // Teacher 3 (Ana Reyes) - ICT teacher
    // Assign to ICT classes
    $conn->exec("INSERT INTO teacher_class_assignments (user_id, class_level_id) VALUES (4, 17), (4, 18)"); // ICT 1A, 1B
    // Assign to ICT subjects
    $conn->exec("INSERT INTO teacher_subject_assignments (user_id, subject_id) VALUES (4, 38), (4, 39), (4, 40)"); // Programming, Web Dev, Computer Systems
    echo "  ✓ Assigned Teacher 3 (Ana Reyes) to ICT classes and subjects\n";
    
    echo str_repeat("-", 60) . "\n\n";

    // Seed terms
    echo "Step 12: Seeding Academic Terms...\n";
    echo str_repeat("-", 60) . "\n";
    
    $conn->exec("
        INSERT INTO terms (name, academic_year, start_date, end_date, is_active) VALUES
        ('Term 1', '2024/2025', '2024-09-01', '2024-12-15', TRUE),
        ('Term 2', '2024/2025', '2025-01-06', '2025-04-10', FALSE),
        ('Term 3', '2024/2025', '2025-04-21', '2025-07-25', FALSE)
    ");
    echo "  ✓ Created 3 academic terms for 2024/2025\n";
    echo str_repeat("-", 60) . "\n\n";

    // Seed subject weightings
    echo "Step 13: Seeding Subject Weightings...\n";
    echo str_repeat("-", 60) . "\n";
    
    $conn->exec("
        INSERT INTO subject_weightings (subject_id, ca_percentage, exam_percentage)
        SELECT id, 40.00, 60.00 FROM subjects
    ");
    echo "  ✓ Set default weightings (40% CA, 60% Exam) for all subjects\n";
    echo str_repeat("-", 60) . "\n\n";

    // Seed sample assessments
    echo "Step 14: Seeding Sample Assessments...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Comprehensive assessments for all students in Term 1
    $sampleAssessments = [
        // STEM 1A Students - Pre-Calculus (Subject ID: 15), Basic Calculus (16), General Math (5)
        // Juan Dela Cruz
        ['2024-STEM-001', 15, 1, 35.50, 55.00], // Pre-Calculus
        ['2024-STEM-001', 16, 1, 34.00, 52.50], // Basic Calculus
        ['2024-STEM-001', 5, 1, 37.00, 56.00],  // General Math
        
        // Maria Santos
        ['2024-STEM-002', 15, 1, 38.00, 58.50], // Pre-Calculus
        ['2024-STEM-002', 16, 1, 39.50, 59.00], // Basic Calculus
        ['2024-STEM-002', 5, 1, 40.00, 60.00],  // General Math
        
        // Pedro Reyes
        ['2024-STEM-003', 15, 1, 32.00, 50.00], // Pre-Calculus
        ['2024-STEM-003', 16, 1, 30.50, 48.00], // Basic Calculus
        ['2024-STEM-003', 5, 1, 33.00, 51.00],  // General Math
        
        // Ana Garcia
        ['2024-STEM-004', 15, 1, 36.50, 54.50], // Pre-Calculus
        ['2024-STEM-004', 16, 1, 37.00, 55.00], // Basic Calculus
        ['2024-STEM-004', 5, 1, 38.50, 57.50],  // General Math
        
        // Jose Ramos
        ['2024-STEM-005', 15, 1, 33.00, 51.50], // Pre-Calculus
        ['2024-STEM-005', 16, 1, 32.50, 50.50], // Basic Calculus
        ['2024-STEM-005', 5, 1, 34.50, 53.00],  // General Math
        
        // Business 1A Students - Accountancy (23), Business Math (24), Business Finance (25)
        // Sofia Martinez
        ['2024-BUS-001', 23, 1, 36.00, 54.00], // Accountancy
        ['2024-BUS-001', 24, 1, 37.50, 56.50], // Business Math
        ['2024-BUS-001', 25, 1, 35.00, 53.00], // Business Finance
        
        // Miguel Torres
        ['2024-BUS-002', 23, 1, 34.50, 52.00], // Accountancy
        ['2024-BUS-002', 24, 1, 36.00, 54.50], // Business Math
        ['2024-BUS-002', 25, 1, 33.50, 51.50], // Business Finance
        
        // Isabella Cruz
        ['2024-BUS-003', 23, 1, 38.50, 58.00], // Accountancy
        ['2024-BUS-003', 24, 1, 39.00, 59.00], // Business Math
        ['2024-BUS-003', 25, 1, 37.50, 57.00], // Business Finance
        
        // Carlos Mendoza
        ['2024-BUS-004', 23, 1, 31.00, 49.00], // Accountancy
        ['2024-BUS-004', 24, 1, 32.50, 50.50], // Business Math
        ['2024-BUS-004', 25, 1, 30.00, 48.00], // Business Finance
        
        // Lucia Fernandez
        ['2024-BUS-005', 23, 1, 35.50, 54.50], // Accountancy
        ['2024-BUS-005', 24, 1, 36.50, 55.50], // Business Math
        ['2024-BUS-005', 25, 1, 34.00, 52.50], // Business Finance
        
        // Humanities 1A Students - Creative Writing (31), World Religions (33)
        // Diego Lopez
        ['2024-HUM-001', 31, 1, 36.00, 55.00], // Creative Writing
        ['2024-HUM-001', 33, 1, 37.50, 56.50], // World Religions
        
        // Valentina Gomez
        ['2024-HUM-002', 31, 1, 38.50, 58.00], // Creative Writing
        ['2024-HUM-002', 33, 1, 39.00, 59.00], // World Religions
        
        // Mateo Diaz
        ['2024-HUM-003', 31, 1, 33.00, 51.00], // Creative Writing
        ['2024-HUM-003', 33, 1, 34.50, 52.50], // World Religions
        
        // Camila Morales
        ['2024-HUM-004', 31, 1, 37.00, 56.00], // Creative Writing
        ['2024-HUM-004', 33, 1, 38.00, 57.50], // World Religions
        
        // Santiago Herrera
        ['2024-HUM-005', 31, 1, 35.00, 53.50], // Creative Writing
        ['2024-HUM-005', 33, 1, 36.00, 54.50], // World Religions
        
        // ICT 1A Students - Programming (38), Web Development (39), Computer Systems (40)
        // Gabriel Silva
        ['2024-ICT-001', 38, 1, 37.00, 56.00], // Programming
        ['2024-ICT-001', 39, 1, 38.50, 58.00], // Web Development
        ['2024-ICT-001', 40, 1, 36.00, 55.00], // Computer Systems
        
        // Emma Castillo
        ['2024-ICT-002', 38, 1, 39.00, 59.00], // Programming
        ['2024-ICT-002', 39, 1, 40.00, 60.00], // Web Development
        ['2024-ICT-002', 40, 1, 38.50, 58.50], // Computer Systems
        
        // Lucas Vargas
        ['2024-ICT-003', 38, 1, 34.00, 52.00], // Programming
        ['2024-ICT-003', 39, 1, 35.50, 54.00], // Web Development
        ['2024-ICT-003', 40, 1, 33.50, 51.50], // Computer Systems
        
        // Mia Ortiz
        ['2024-ICT-004', 38, 1, 36.50, 55.50], // Programming
        ['2024-ICT-004', 39, 1, 37.50, 57.00], // Web Development
        ['2024-ICT-004', 40, 1, 35.50, 54.00], // Computer Systems
        
        // Daniel Navarro
        ['2024-ICT-005', 38, 1, 35.00, 53.50], // Programming
        ['2024-ICT-005', 39, 1, 36.00, 55.00], // Web Development
        ['2024-ICT-005', 40, 1, 34.50, 52.50], // Computer Systems
    ];
    
    $assessmentInsertStmt = $conn->prepare("
        INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark)
        VALUES (:student_id, :subject_id, :term_id, :ca_mark, :exam_mark)
    ");
    
    $assessmentInserted = 0;
    foreach ($sampleAssessments as $assessment) {
        $assessmentInsertStmt->execute([
            ':student_id' => $assessment[0],
            ':subject_id' => $assessment[1],
            ':term_id' => $assessment[2],
            ':ca_mark' => $assessment[3],
            ':exam_mark' => $assessment[4]
        ]);
        $assessmentInserted++;
    }
    
    echo "  ✓ Created $assessmentInserted sample assessments\n";
    echo str_repeat("-", 60) . "\n\n";

    // Summary
    echo str_repeat("=", 60) . "\n";
    echo "✓ COMPLETE FRESH MIGRATION COMPLETED!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Summary:\n";
    echo "  📊 Tables Created (10 total):\n";
    echo "     - users\n";
    echo "     - subjects\n";
    echo "     - class_levels\n";
    echo "     - students\n";
    echo "     - teacher_class_assignments\n";
    echo "     - teacher_subject_assignments\n";
    echo "     - terms\n";
    echo "     - subject_weightings\n";
    echo "     - term_assessments\n";
    echo "     - access_logs\n\n";
    
    echo "  � Usears Created:\n";
    echo "     - 1 admin (username: admin, password: admin123)\n";
    echo "     - 3 teachers (username: teacher1-3, password: teacher123)\n\n";
    
    echo "  📚 Data Seeded:\n";
    echo "     - $classInserted class levels\n";
    echo "     - $subjectInserted subjects\n";
    echo "     - $studentInserted students\n";
    echo "     - 6 teacher-to-class assignments\n";
    echo "     - 9 teacher-to-subject assignments\n";
    echo "     - 3 academic terms\n";
    echo "     - $subjectInserted subject weightings\n";
    echo "     - $assessmentInserted sample assessments\n\n";
    
    echo "Next steps:\n";
    echo "  1. Log in as admin (admin/admin123)\n";
    echo "  2. Assign teachers to classes and subjects\n";
    echo "  3. Add students to class levels\n";
    echo "  4. Start entering assessments\n\n";
    
    echo "To verify the data, run:\n";
    echo "  php backend/verify_seeding.php\n\n";

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
