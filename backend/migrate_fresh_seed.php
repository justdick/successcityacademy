<?php

/**
 * Migrate Fresh and Seed
 * Drops all tables, recreates schema, and seeds data
 * 
 * Usage: php backend/migrate_fresh_seed.php
 */

echo "=== Student Management System - Migrate Fresh & Seed ===\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

try {
    // Step 1: Connect to MySQL server
    echo "Step 1: Connecting to MySQL server...\n";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n\n";

    // Step 2: Drop and recreate database
    echo "Step 2: Dropping and recreating database...\n";
    $conn->exec("DROP DATABASE IF EXISTS $database");
    echo "  ✓ Dropped existing database\n";
    
    $conn->exec("CREATE DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "  ✓ Created fresh database\n";
    
    $conn->exec("USE $database");
    echo "  ✓ Selected database\n\n";

    // Step 3: Run base schema
    echo "Step 3: Creating base schema...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'USE ') !== 0 && stripos($statement, 'CREATE DATABASE') !== 0) {
            $conn->exec($statement);
        }
    }
    echo "✓ Base schema created\n\n";

    // Step 4: Run migrations
    echo "Step 4: Running migrations...\n";
    $migrationsDir = __DIR__ . '/database/migrations';
    
    if (is_dir($migrationsDir)) {
        $migrations = glob($migrationsDir . '/*.sql');
        sort($migrations); // Run in alphabetical order
        
        foreach ($migrations as $migration) {
            $migrationName = basename($migration);
            echo "  Running: $migrationName\n";
            
            $sql = file_get_contents($migration);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && stripos($statement, 'USE ') !== 0) {
                    $conn->exec($statement);
                }
            }
        }
        
        echo "✓ All migrations completed\n\n";
    } else {
        echo "⚠ No migrations directory found\n\n";
    }

    // Step 5: Run seeders
    echo "Step 5: Seeding database...\n";
    require_once __DIR__ . '/database/seeders/DatabaseSeeder.php';
    
    $seeder = new DatabaseSeeder($conn);
    $seeder->run();

    // Step 6: Verify setup
    echo "Step 6: Verifying setup...\n";
    
    $tables = [
        'users' => 'Users',
        'subjects' => 'Subjects',
        'class_levels' => 'Class Levels',
        'students' => 'Students',
        'terms' => 'Terms',
        'subject_weightings' => 'Subject Weightings',
        'term_assessments' => 'Term Assessments'
    ];
    
    foreach ($tables as $table => $label) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch();
        echo "  - $label: " . $result['count'] . " record(s)\n";
    }
    
    echo "\n✓ Database setup completed successfully!\n\n";
    
    echo "=== Summary ===\n";
    echo "Database: $database\n";
    echo "Status: Fresh migration and seeding completed\n\n";
    
    echo "Default credentials:\n";
    echo "  Admin:\n";
    echo "    Username: admin\n";
    echo "    Password: admin123\n";
    echo "  Teacher:\n";
    echo "    Username: teacher1\n";
    echo "    Password: teacher123\n\n";
    
    echo "You can now:\n";
    echo "  - Run tests: php backend/test_termly_reports_e2e.php\n";
    echo "  - Start the application\n\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
