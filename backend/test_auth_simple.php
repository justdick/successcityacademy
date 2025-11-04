<?php

/**
 * Simple database and authentication test
 */

echo "=== Testing Database and Authentication ===\n\n";

// Test 1: Database Connection
echo "Test 1: Database Connection\n";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $conn = $db->connect();
    echo "✓ PASS: Database connected successfully\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 2: Check if admin user exists
echo "Test 2: Check if admin user exists\n";
try {
    $query = "SELECT id, username, role FROM users WHERE username = 'admin' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ PASS: Admin user found\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Role: " . $user['role'] . "\n";
    } else {
        echo "✗ FAIL: Admin user not found. Please run seed.sql\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 3: Verify password hash
echo "Test 3: Verify password hash for admin user\n";
try {
    $query = "SELECT password_hash FROM users WHERE username = 'admin' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result && password_verify('admin123', $result['password_hash'])) {
        echo "✓ PASS: Password verification works\n";
    } else {
        echo "✗ FAIL: Password verification failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 4: JWT Token Generation
echo "Test 4: JWT Token Generation\n";
try {
    require_once __DIR__ . '/utils/JWT.php';
    
    $payload = [
        'id' => 1,
        'username' => 'admin',
        'role' => 'admin'
    ];
    
    $token = JWT::encode($payload);
    
    if ($token && strlen($token) > 0) {
        echo "✓ PASS: JWT token generated\n";
        echo "  Token: " . substr($token, 0, 50) . "...\n";
    } else {
        echo "✗ FAIL: JWT token generation failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 5: JWT Token Decoding
echo "Test 5: JWT Token Decoding\n";
try {
    $decoded = JWT::decode($token);
    
    if ($decoded && $decoded->username === 'admin' && $decoded->role === 'admin') {
        echo "✓ PASS: JWT token decoded successfully\n";
        echo "  Username: " . $decoded->username . "\n";
        echo "  Role: " . $decoded->role . "\n";
        echo "  Expires: " . date('Y-m-d H:i:s', $decoded->exp) . "\n";
    } else {
        echo "✗ FAIL: JWT token decoding failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 6: Check subjects table
echo "Test 6: Check subjects table\n";
try {
    $query = "SELECT COUNT(*) as count FROM subjects";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "✓ PASS: Subjects table has " . $result['count'] . " records\n";
    } else {
        echo "⚠ WARNING: Subjects table is empty. Run seed.sql to populate.\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Check class_levels table
echo "Test 7: Check class_levels table\n";
try {
    $query = "SELECT COUNT(*) as count FROM class_levels";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "✓ PASS: Class levels table has " . $result['count'] . " records\n";
    } else {
        echo "⚠ WARNING: Class levels table is empty. Run seed.sql to populate.\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== All Tests Completed Successfully ===\n";
echo "\nYou can now test the API endpoints:\n";
echo "- POST http://localhost/backend/api/auth/login\n";
echo "- POST http://localhost/backend/api/auth/logout\n";
