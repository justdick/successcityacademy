<?php

require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();

    echo "Starting access logs table migration...\n";

    // Read and execute the migration SQL
    $sql = file_get_contents(__DIR__ . '/database/migrations/add_access_logs.sql');
    
    $conn->exec($sql);

    echo "✓ Access logs table created successfully\n";
    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
