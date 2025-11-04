<?php
/**
 * Seed Subjects for Senior High School
 * 
 * This script populates the subjects table with typical SHS subjects
 * organized by core, applied, and specialized subjects
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Starting subjects seeding...\n\n";
    
    // Define subjects organized by category
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
    
    // Sort subjects alphabetically for better organization
    sort($subjects);
    
    // Prepare insert statement
    $query = "INSERT INTO subjects (name) VALUES (:name)";
    $stmt = $db->prepare($query);
    
    $insertedCount = 0;
    $skippedCount = 0;
    
    foreach ($subjects as $subjectName) {
        // Check if subject already exists
        $checkQuery = "SELECT id FROM subjects WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $subjectName);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo "⚠️  Skipped: '$subjectName' (already exists)\n";
            $skippedCount++;
            continue;
        }
        
        // Insert new subject
        $stmt->bindParam(':name', $subjectName);
        
        if ($stmt->execute()) {
            echo "✅ Created: '$subjectName'\n";
            $insertedCount++;
        } else {
            echo "❌ Failed: '$subjectName'\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Seeding completed!\n";
    echo "✅ Inserted: $insertedCount subjects\n";
    echo "⚠️  Skipped: $skippedCount subjects (already exist)\n";
    echo str_repeat("=", 50) . "\n\n";
    
    // Display summary of all subjects
    echo "Current subjects in database:\n";
    echo str_repeat("-", 50) . "\n";
    
    $summaryQuery = "SELECT id, name FROM subjects ORDER BY name";
    $summaryStmt = $db->query($summaryQuery);
    $allSubjects = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allSubjects as $subject) {
        echo sprintf("ID: %-3d | %s\n", $subject['id'], $subject['name']);
    }
    
    echo str_repeat("-", 50) . "\n";
    echo "Total: " . count($allSubjects) . " subjects\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
