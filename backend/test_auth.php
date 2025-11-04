<?php

/**
 * Simple test script for Authentication API
 * Run this from command line: php backend/test_auth.php
 */

require_once __DIR__ . '/controllers/AuthController.php';

// Suppress output buffering warnings
error_reporting(E_ALL & ~E_WARNING);

echo "=== Testing Authentication Controller ===\n\n";

// Test 1: Login with valid credentials
echo "Test 1: Login with valid credentials (admin/admin123)\n";

try {
    $controller = new AuthController();
} catch (Exception $e) {
    echo "✗ FAIL: Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

// Capture output
ob_start();
$controller->login(['username' => 'admin', 'password' => 'admin123']);
$output = ob_get_clean();

$response = json_decode($output, true);
if ($response && $response['success'] === true && isset($response['data']['token'])) {
    echo "✓ PASS: Login successful, token generated\n";
    echo "  Token: " . substr($response['data']['token'], 0, 50) . "...\n";
    echo "  User: " . $response['data']['user']['username'] . " (Role: " . $response['data']['user']['role'] . ")\n";
    $token = $response['data']['token'];
} else {
    echo "✗ FAIL: Login failed\n";
    echo "  Response: " . $output . "\n";
    exit(1);
}

echo "\n";

// Test 2: Login with invalid credentials
echo "Test 2: Login with invalid credentials\n";
$controller = new AuthController();

ob_start();
$controller->login(['username' => 'admin', 'password' => 'wrongpassword']);
$output = ob_get_clean();

$response = json_decode($output, true);
if ($response && $response['success'] === false && strpos($response['error'], 'Invalid username or password') !== false) {
    echo "✓ PASS: Invalid credentials rejected\n";
} else {
    echo "✗ FAIL: Should reject invalid credentials\n";
    echo "  Response: " . $output . "\n";
}

echo "\n";

// Test 3: Login with missing fields
echo "Test 3: Login with missing password field\n";
$controller = new AuthController();

ob_start();
$controller->login(['username' => 'admin']);
$output = ob_get_clean();

$response = json_decode($output, true);
if ($response && $response['success'] === false && strpos($response['error'], 'required') !== false) {
    echo "✓ PASS: Missing fields validation works\n";
} else {
    echo "✗ FAIL: Should validate required fields\n";
    echo "  Response: " . $output . "\n";
}

echo "\n";

// Test 4: Logout
echo "Test 4: Logout\n";
$controller = new AuthController();

ob_start();
$controller->logout();
$output = ob_get_clean();

$response = json_decode($output, true);
if ($response && $response['success'] === true) {
    echo "✓ PASS: Logout successful\n";
} else {
    echo "✗ FAIL: Logout failed\n";
    echo "  Response: " . $output . "\n";
}

echo "\n";

// Test 5: Verify JWT token
echo "Test 5: Verify JWT token\n";
require_once __DIR__ . '/utils/JWT.php';

$decoded = JWT::decode($token);
if ($decoded && $decoded->username === 'admin' && $decoded->role === 'admin') {
    echo "✓ PASS: JWT token is valid and contains correct user data\n";
    echo "  Decoded username: " . $decoded->username . "\n";
    echo "  Decoded role: " . $decoded->role . "\n";
} else {
    echo "✗ FAIL: JWT token verification failed\n";
}

echo "\n=== All Tests Completed ===\n";
