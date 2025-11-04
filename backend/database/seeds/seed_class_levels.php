<?php
/**
 * Seed Class Levels for Senior High School
 * 
 * This script populates the class_levels table with course-based sections
 * suitable for Senior High School (Grades 11-12)
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Starting class levels seeding...\n\n";
    
    // Define class levels based on courses and sections
    $classLevels = [
        // STEM (Science, Technology, Engineering, Mathematics)
        'STEM 1A',
        'STEM 1B',
        'STEM 2A',
        'STEM 2B',
        
        // ABM (Accountancy, Business, and Management)
        'Business 1A',
        'Business 1B',
        'Business 2A',
        'Business 2B',
        
        // HUMSS (Humanities and Social Sciences)
        'Humanities 1A',
        'Humanities 1B',
        'Humanities 2A',
        'Humanities 2B',
        
        // GAS (General Academic Strand)
        'General Arts 1A',
        'General Arts 1B',
        'General Arts 2A',
        'General Arts 2B',
        
        // TVL - ICT (Technical-Vocational-Livelihood - ICT)
        'ICT 1A',
        'ICT 1B',
        'ICT 2A',
        'ICT 2B',
        
        // TVL - Home Economics
        'Home Economics 1A',
        'Home Economics 2A',
        
        // Arts and Design
        'Arts & Design 1A',
        'Arts & Design 2A'
    ];
    
    // Prepare insert statement
    $query = "INSERT INTO class_levels (name) VALUES (:name)";
    $stmt = $db->prepare($query);
    
    $insertedCount = 0;
    $skippedCount = 0;
    
    foreach ($classLevels as $className) {
        // Check if class level already exists
        $checkQuery = "SELECT id FROM class_levels WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $className);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo "⚠️  Skipped: '$className' (already exists)\n";
            $skippedCount++;
            continue;
        }
        
        // Insert new class level
        $stmt->bindParam(':name', $className);
        
        if ($stmt->execute()) {
            echo "✅ Created: '$className'\n";
            $insertedCount++;
        } else {
            echo "❌ Failed: '$className'\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Seeding completed!\n";
    echo "✅ Inserted: $insertedCount class levels\n";
    echo "⚠️  Skipped: $skippedCount class levels (already exist)\n";
    echo str_repeat("=", 50) . "\n\n";
    
    // Display summary of all class levels
    echo "Current class levels in database:\n";
    echo str_repeat("-", 50) . "\n";
    
    $summaryQuery = "SELECT id, name FROM class_levels ORDER BY name";
    $summaryStmt = $db->query($summaryQuery);
    $allClasses = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allClasses as $class) {
        echo sprintf("ID: %-3d | %s\n", $class['id'], $class['name']);
    }
    
    echo str_repeat("-", 50) . "\n";
    echo "Total: " . count($allClasses) . " class levels\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
