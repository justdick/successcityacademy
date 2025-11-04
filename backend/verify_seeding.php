<?php
/**
 * Verify Seeding Results
 * Shows all class levels and subjects that were seeded
 */

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "           DATABASE SEEDING VERIFICATION\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Check class levels
    echo "📚 CLASS LEVELS:\n";
    echo str_repeat("-", 60) . "\n";
    $classes = $conn->query("SELECT id, name FROM class_levels ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($classes as $class) {
        echo sprintf("ID: %-3d | %s\n", $class['id'], $class['name']);
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "Total: " . count($classes) . " class levels\n\n";
    
    // Check subjects
    echo "📖 SUBJECTS:\n";
    echo str_repeat("-", 60) . "\n";
    $subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subjects as $subject) {
        echo sprintf("ID: %-3d | %s\n", $subject['id'], $subject['name']);
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "Total: " . count($subjects) . " subjects\n\n";
    
    echo str_repeat("=", 60) . "\n";
    echo "✓ Verification completed!\n";
    echo str_repeat("=", 60) . "\n\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
