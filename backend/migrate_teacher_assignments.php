<?php

/**
 * Teacher Assignments Migration Script
 * This script creates the teacher_class_assignments and teacher_subject_assignments tables
 * 
 * Usage: 
 *   php backend/migrate_teacher_assignments.php          (run migration)
 *   php backend/migrate_teacher_assignments.php rollback (rollback migration)
 */

echo "=== Teacher Assignments Migration ===\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

// Check if rollback is requested
$isRollback = isset($argv[1]) && $argv[1] === 'rollback';

try {
    // Connect to database
    echo "Step 1: Connecting to database...\n";
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database: $database\n\n";

    if ($isRollback) {
        // Rollback: Drop the tables
        echo "Step 2: Rolling back migration...\n";
        
        echo "  Dropping teacher_subject_assignments table...\n";
        $conn->exec("DROP TABLE IF EXISTS teacher_subject_assignments");
        echo "  ✓ Dropped teacher_subject_assignments\n";
        
        echo "  Dropping teacher_class_assignments table...\n";
        $conn->exec("DROP TABLE IF EXISTS teacher_class_assignments");
        echo "  ✓ Dropped teacher_class_assignments\n";
        
        echo "\n✓ Rollback completed successfully!\n\n";
        echo "The teacher assignments tables have been removed.\n\n";
        
    } else {
        // Run migration
        echo "Step 2: Reading migration file...\n";
        $migrationPath = __DIR__ . '/database/migrations/add_teacher_assignments.sql';
        
        if (!file_exists($migrationPath)) {
            throw new Exception("Migration file not found: $migrationPath");
        }
        
        $migration = file_get_contents($migrationPath);
        echo "✓ Migration file loaded\n\n";

        echo "Step 3: Executing migration...\n";
        
        // Parse and execute SQL statements
        $lines = explode("\n", $migration);
        $cleanedLines = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines and comment-only lines
            if (empty($line) || strpos($line, '--') === 0) {
                continue;
            }
            // Remove inline comments
            $commentPos = strpos($line, '--');
            if ($commentPos !== false) {
                $line = trim(substr($line, 0, $commentPos));
            }
            if (!empty($line)) {
                $cleanedLines[] = $line;
            }
        }
        
        $cleanedSql = implode("\n", $cleanedLines);
        $statements = array_filter(array_map('trim', explode(';', $cleanedSql)));
        $executedCount = 0;
        
        foreach ($statements as $statement) {
            // Skip empty statements and USE statements
            if (empty($statement) || stripos($statement, 'USE ') === 0) {
                continue;
            }
            
            try {
                $conn->exec($statement);
                $executedCount++;
                
                // Show which table was created
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
                    if (isset($matches[1])) {
                        echo "  ✓ Created table: {$matches[1]}\n";
                    }
                }
            } catch (PDOException $e) {
                // If error is about table already existing, just warn
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
                } else {
                    throw $e;
                }
            }
        }
        
        echo "✓ Executed $executedCount statement(s) successfully\n\n";

        echo "Step 4: Verifying migration...\n";
        
        // Check teacher_class_assignments table
        $result = $conn->query("SHOW TABLES LIKE 'teacher_class_assignments'")->fetch();
        if ($result) {
            echo "  ✓ teacher_class_assignments table exists\n";
            
            // Check indexes
            $indexes = $conn->query("SHOW INDEX FROM teacher_class_assignments")->fetchAll(PDO::FETCH_ASSOC);
            $indexNames = array_unique(array_column($indexes, 'Key_name'));
            echo "    Indexes: " . implode(', ', $indexNames) . "\n";
        } else {
            throw new Exception("teacher_class_assignments table was not created");
        }
        
        // Check teacher_subject_assignments table
        $result = $conn->query("SHOW TABLES LIKE 'teacher_subject_assignments'")->fetch();
        if ($result) {
            echo "  ✓ teacher_subject_assignments table exists\n";
            
            // Check indexes
            $indexes = $conn->query("SHOW INDEX FROM teacher_subject_assignments")->fetchAll(PDO::FETCH_ASSOC);
            $indexNames = array_unique(array_column($indexes, 'Key_name'));
            echo "    Indexes: " . implode(', ', $indexNames) . "\n";
        } else {
            throw new Exception("teacher_subject_assignments table was not created");
        }
        
        echo "\n✓ Migration completed successfully!\n\n";
        
        echo "New tables created:\n";
        echo "  - teacher_class_assignments (links teachers to classes)\n";
        echo "  - teacher_subject_assignments (links teachers to subjects)\n\n";
        
        // Run seeders
        echo "Step 5: Seeding database with class levels and subjects...\n";
        echo str_repeat("-", 60) . "\n";
        
        // Seed class levels
        echo "\n📚 Seeding Class Levels:\n";
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
        $classCheckStmt = $conn->prepare("SELECT id FROM class_levels WHERE name = :name");
        $classInserted = 0;
        $classSkipped = 0;
        
        foreach ($classLevels as $className) {
            $classCheckStmt->execute([':name' => $className]);
            if ($classCheckStmt->rowCount() > 0) {
                $classSkipped++;
            } else {
                $classInsertStmt->execute([':name' => $className]);
                echo "  ✓ Created: '$className'\n";
                $classInserted++;
            }
        }
        
        echo "  Summary: $classInserted created, $classSkipped skipped\n";
        
        // Seed subjects
        echo "\n📖 Seeding Subjects:\n";
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
        $subjectCheckStmt = $conn->prepare("SELECT id FROM subjects WHERE name = :name");
        $subjectInserted = 0;
        $subjectSkipped = 0;
        
        foreach ($subjects as $subjectName) {
            $subjectCheckStmt->execute([':name' => $subjectName]);
            if ($subjectCheckStmt->rowCount() > 0) {
                $subjectSkipped++;
            } else {
                $subjectInsertStmt->execute([':name' => $subjectName]);
                $subjectInserted++;
            }
        }
        
        echo "  Summary: $subjectInserted created, $subjectSkipped skipped\n";
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✓ Seeding completed!\n";
        echo "  Class Levels: $classInserted created, $classSkipped skipped\n";
        echo "  Subjects: $subjectInserted created, $subjectSkipped skipped\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "To rollback this migration, run:\n";
        echo "  php backend/migrate_teacher_assignments.php rollback\n\n";
    }

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
