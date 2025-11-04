<?php

/**
 * Database Setup Script
 * This script creates the database schema and seeds initial data
 */

echo "=== Student Management System - Database Setup ===\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server (without selecting a database)
    echo "Step 1: Connecting to MySQL server...\n";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n\n";

    // Read and execute schema.sql
    echo "Step 2: Creating database schema...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    echo "✓ Database schema created successfully\n\n";

    // Read and execute seed.sql
    echo "Step 3: Seeding initial data...\n";
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $seed)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'USE ') !== 0) {
            $conn->exec($statement);
        }
    }
    echo "✓ Initial data seeded successfully\n\n";

    // Verify setup
    echo "Step 4: Verifying setup...\n";
    $conn->exec("USE student_management");
    
    // Check users
    $result = $conn->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "  - Users: " . $result['count'] . " record(s)\n";
    
    // Check subjects
    $result = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch();
    echo "  - Subjects: " . $result['count'] . " record(s)\n";
    
    // Check class levels
    $result = $conn->query("SELECT COUNT(*) as count FROM class_levels")->fetch();
    echo "  - Class Levels: " . $result['count'] . " record(s)\n";
    
    echo "\n✓ Database setup completed successfully!\n\n";
    
    echo "Default admin credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    
    echo "You can now run: php backend/test_auth_simple.php\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
