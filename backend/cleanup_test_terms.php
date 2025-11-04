<?php

require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Delete test terms
    $stmt = $conn->prepare("DELETE FROM terms WHERE name LIKE '%Term%' AND academic_year = '2024/2025'");
    $stmt->execute();
    
    echo "✓ Test terms cleaned up\n";
} catch (Exception $e) {
    echo "✗ Cleanup exception: " . $e->getMessage() . "\n";
}
