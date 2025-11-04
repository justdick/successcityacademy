<?php

/**
 * Verify Teacher Assignments Tables
 * This script verifies the structure of the teacher assignments tables
 */

echo "=== Verifying Teacher Assignments Tables ===\n\n";

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== teacher_class_assignments structure ===\n";
    $result = $conn->query('DESCRIBE teacher_class_assignments');
    foreach($result as $row) {
        echo sprintf("%-20s | %-20s | %-5s | %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Key'], 
            $row['Extra']
        );
    }
    
    echo "\n=== teacher_subject_assignments structure ===\n";
    $result = $conn->query('DESCRIBE teacher_subject_assignments');
    foreach($result as $row) {
        echo sprintf("%-20s | %-20s | %-5s | %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Key'], 
            $row['Extra']
        );
    }
    
    echo "\n=== Foreign Keys ===\n";
    
    // Check foreign keys for teacher_class_assignments
    $result = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$database'
        AND TABLE_NAME = 'teacher_class_assignments'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "\nteacher_class_assignments:\n";
    foreach($result as $row) {
        echo "  {$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
    }
    
    // Check foreign keys for teacher_subject_assignments
    $result = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$database'
        AND TABLE_NAME = 'teacher_subject_assignments'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "\nteacher_subject_assignments:\n";
    foreach($result as $row) {
        echo "  {$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
    }
    
    echo "\n✓ Verification complete!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
