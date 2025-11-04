<?php

/**
 * Database Seed Script
 * Seeds data without dropping tables
 * 
 * Usage: 
 *   php backend/seed.php              # Run all seeders
 *   php backend/seed.php Users        # Run specific seeder
 */

echo "=== Student Management System - Database Seeder ===\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

try {
    // Connect to database
    echo "Connecting to database...\n";
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n";

    // Load seeder
    require_once __DIR__ . '/database/seeders/DatabaseSeeder.php';
    $seeder = new DatabaseSeeder($conn);

    // Check if specific seeder requested
    if (isset($argv[1])) {
        $seederName = $argv[1];
        $seeder->runSpecific($seederName);
    } else {
        $seeder->run();
    }

    echo "=== Seeding Complete ===\n\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
