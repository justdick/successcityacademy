<?php
/**
 * Fresh Migration Script
 * This script clears existing class_levels and subjects, then seeds with SHS data
 * 
 * WARNING: This will DELETE all existing class levels and subjects!
 * 
 * Usage: php backend/migrate_fresh.php
 */

echo "\n" . str_repeat("=", 60) . "\n";
echo "           FRESH MIGRATION - SENIOR HIGH SCHOOL\n";
echo str_repeat("=", 60) . "\n\n";

echo "⚠️  WARNING: This will DELETE all existing data!\n";
echo "   - All class levels will be removed\n";
echo "   - All subjects will be removed\n";
echo "   - All student records will be removed\n";
echo "   - All assessments will be removed\n";
echo "   - All teacher assignments will be removed\n\n";

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

    // Disable foreign key checks temporarily
    echo "Step 2: Disabling foreign key checks...\n";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✓ Foreign key checks disabled\n\n";

    // Clear existing data
    echo "Step 3: Clearing existing data...\n";
    
    $tablesToClear = [
        'teacher_subject_assignments',
        'teacher_class_assignments',
        'assessments',
        'students',
        'subjects',
        'class_levels'
    ];
    
    foreach ($tablesToClear as $table) {
        try {
            echo "  Truncating $table...\n";
            $conn->exec("TRUNCATE TABLE $table");
            echo "  ✓ Cleared $table\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "  ⚠ Skipped $table (table doesn't exist)\n";
            } else {
                throw $e;
            }
        }
    }
    echo "\n";

    // Re-enable foreign key checks
    echo "Step 4: Re-enabling foreign key checks...\n";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✓ Foreign key checks re-enabled\n\n";

    // Seed class levels
    echo "Step 5: Seeding Class Levels...\n";
    echo str_repeat("-", 60) . "\n";
    
    $classLevels = [
        // STEM
        'STEM 1A', 'STEM 1B', 'STEM 2A', 'STEM 2B',
        // Business/ABM
        'Business 1A', 'Business 1B', 'Business 2A', 'Business 2B',
        // Humanities/HUMSS
        'Humanities 1A', 'Humanities 1B', 'Humanities 2A', 'Humanities 2B',
        // General Arts/GAS
        'General Arts 1A', 'General Arts 1B', 'General Arts 2A', 'General Arts 2B',
        // ICT
        'ICT 1A', 'ICT 1B', 'ICT 2A', 'ICT 2B',
        // Home Economics
        'Home Economics 1A', 'Home Economics 2A',
        // Arts & Design
        'Arts & Design 1A', 'Arts & Design 2A'
    ];
    
    $classInsertStmt = $conn->prepare("INSERT INTO class_levels (name) VALUES (:name)");
    $classInserted = 0;
    
    foreach ($classLevels as $className) {
        $classInsertStmt->execute([':name' => $className]);
        echo "  ✓ Created: '$className'\n";
        $classInserted++;
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "✓ Created $classInserted class levels\n\n";

    // Seed subjects
    echo "Step 6: Seeding Subjects...\n";
    echo str_repeat("-", 60) . "\n";
    
    $subjects = [
        // Core Subjects (Common to all strands)
        'Oral Communication',
        'Reading and Writing',
        'Komunikasyon at Pananaliksik',
        'Pagbasa at Pagsusuri',
        'General Mathematics',
        'Statistics and Probability',
        'Earth and Life Science',
        'Physical Science',
        'Personal Development',
        'Understanding Culture, Society and Politics',
        'Introduction to Philosophy',
        'Physical Education and Health',
        'Contemporary Philippine Arts',
        'Media and Information Literacy',
        
        // STEM Specialized Subjects
        'Pre-Calculus',
        'Basic Calculus',
        'General Biology 1',
        'General Biology 2',
        'General Physics 1',
        'General Physics 2',
        'General Chemistry 1',
        'General Chemistry 2',
        
        // ABM Specialized Subjects
        'Fundamentals of Accountancy',
        'Business Math',
        'Business Finance',
        'Business Marketing',
        'Business Ethics',
        'Organization and Management',
        'Applied Economics',
        'Business Enterprise Simulation',
        
        // HUMSS Specialized Subjects
        'Creative Writing',
        'Creative Nonfiction',
        'World Religions and Belief Systems',
        'Philippine Politics and Governance',
        'Community Engagement',
        'Disciplines and Ideas in Social Sciences',
        'Trends, Networks and Critical Thinking',
        
        // ICT Specialized Subjects
        'Computer Programming',
        'Web Development',
        'Computer Systems Servicing',
        'Animation',
        'Technical Drafting',
        
        // Home Economics
        'Bread and Pastry Production',
        'Cookery',
        'Food and Beverage Services',
        'Housekeeping',
        
        // Arts and Design
        'Contemporary Philippine Arts from the Regions',
        'Art Appreciation',
        'Creative Industries',
        'Exhibit Design'
    ];
    
    $subjectInsertStmt = $conn->prepare("INSERT INTO subjects (name) VALUES (:name)");
    $subjectInserted = 0;
    
    foreach ($subjects as $subjectName) {
        $subjectInsertStmt->execute([':name' => $subjectName]);
        $subjectInserted++;
    }
    
    echo "  ✓ Created $subjectInserted subjects\n";
    echo str_repeat("-", 60) . "\n\n";

    // Summary
    echo str_repeat("=", 60) . "\n";
    echo "✓ FRESH MIGRATION COMPLETED!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Summary:\n";
    echo "  📚 Class Levels: $classInserted created\n";
    echo "  📖 Subjects: $subjectInserted created\n\n";
    
    echo "Next steps:\n";
    echo "  1. Create teacher accounts (if needed)\n";
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
