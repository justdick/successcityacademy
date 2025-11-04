<?php

/**
 * Migration Runner Script
 * This script runs database migrations
 * Usage: php backend/run_migration.php [migration_file]
 */

echo "=== Student Management System - Migration Runner ===\n\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_management';

// Get migration file from command line argument or use default
$migrationFile = $argv[1] ?? 'migrations/add_termly_reports.sql';
$migrationPath = __DIR__ . '/database/' . $migrationFile;

// Check if migration file exists
if (!file_exists($migrationPath)) {
    echo "✗ Error: Migration file not found: $migrationPath\n";
    exit(1);
}

try {
    // Connect to MySQL server
    echo "Step 1: Connecting to MySQL server...\n";
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database: $database\n\n";

    // Read migration file
    echo "Step 2: Reading migration file...\n";
    echo "  File: $migrationFile\n";
    $migration = file_get_contents($migrationPath);
    echo "✓ Migration file loaded\n\n";

    // Execute migration
    echo "Step 3: Executing migration...\n";
    
    // Remove comments and split by semicolon
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
            
            // Show which table/operation was executed
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Created table: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Inserted data into: {$matches[1]}\n";
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

    // Verify migration
    echo "Step 4: Verifying migration...\n";
    
    // Check terms table
    $result = $conn->query("SELECT COUNT(*) as count FROM terms")->fetch();
    echo "  - Terms: " . $result['count'] . " record(s)\n";
    
    // Check subject_weightings table
    $result = $conn->query("SELECT COUNT(*) as count FROM subject_weightings")->fetch();
    echo "  - Subject Weightings: " . $result['count'] . " record(s)\n";
    
    // Check term_assessments table
    $result = $conn->query("SELECT COUNT(*) as count FROM term_assessments")->fetch();
    echo "  - Term Assessments: " . $result['count'] . " record(s)\n";
    
    echo "\n✓ Migration completed successfully!\n\n";
    
    echo "New tables created:\n";
    echo "  - terms (for managing academic terms)\n";
    echo "  - subject_weightings (for CA/Exam percentage configuration)\n";
    echo "  - term_assessments (for storing CA and exam marks)\n\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
