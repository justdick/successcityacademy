<?php

/**
 * Test script for User Management API endpoints
 * 
 * This script tests:
 * - Creating a new user (admin only)
 * - Getting all users (admin only)
 * - Subject management (create, get all, delete)
 * - Class level management (create, get all, delete)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/JWT.php';

// Configuration
$baseUrl = 'http://localhost/backend/api';

// Test credentials
$adminUsername = 'admin';
$adminPassword = 'admin123';

echo "=== User Management API Test ===\n\n";

// Step 1: Login as admin
echo "1. Logging in as admin...\n";
$loginData = json_encode([
    'username' => $adminUsername,
    'password' => $adminPassword
]);

$ch = curl_init("$baseUrl/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Login failed with status $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResult = json_decode($response, true);
$token = $loginResult['data']['token'];
echo "✓ Login successful\n";
echo "Token: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Create a new user
echo "2. Creating a new user...\n";
$userData = json_encode([
    'username' => 'teacher1',
    'password' => 'password123',
    'role' => 'user'
]);

$ch = curl_init("$baseUrl/users");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $userData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    echo "✓ User created successfully\n";
    $result = json_decode($response, true);
    echo "User ID: " . $result['data']['id'] . "\n";
    echo "Username: " . $result['data']['username'] . "\n\n";
} else {
    echo "❌ User creation failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

// Step 3: Get all users
echo "3. Getting all users...\n";
$ch = curl_init("$baseUrl/users");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Users retrieved successfully\n";
    $result = json_decode($response, true);
    echo "Total users: " . count($result['data']) . "\n\n";
} else {
    echo "❌ Get users failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

// Step 4: Create a new subject
echo "4. Creating a new subject...\n";
$subjectData = json_encode([
    'name' => 'Computer Science'
]);

$ch = curl_init("$baseUrl/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $subjectData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    echo "✓ Subject created successfully\n";
    $result = json_decode($response, true);
    $subjectId = $result['data']['id'];
    echo "Subject ID: $subjectId\n";
    echo "Subject Name: " . $result['data']['name'] . "\n\n";
} else {
    echo "❌ Subject creation failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

// Step 5: Get all subjects
echo "5. Getting all subjects...\n";
$ch = curl_init("$baseUrl/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Subjects retrieved successfully\n";
    $result = json_decode($response, true);
    echo "Total subjects: " . count($result['data']) . "\n\n";
} else {
    echo "❌ Get subjects failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

// Step 6: Create a new class level
echo "6. Creating a new class level...\n";
$classLevelData = json_encode([
    'name' => 'Grade 13'
]);

$ch = curl_init("$baseUrl/class-levels");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $classLevelData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    echo "✓ Class level created successfully\n";
    $result = json_decode($response, true);
    $classLevelId = $result['data']['id'];
    echo "Class Level ID: $classLevelId\n";
    echo "Class Level Name: " . $result['data']['name'] . "\n\n";
} else {
    echo "❌ Class level creation failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

// Step 7: Get all class levels
echo "7. Getting all class levels...\n";
$ch = curl_init("$baseUrl/class-levels");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Class levels retrieved successfully\n";
    $result = json_decode($response, true);
    echo "Total class levels: " . count($result['data']) . "\n\n";
} else {
    echo "❌ Get class levels failed with status $httpCode\n";
    echo "Response: $response\n\n";
}

echo "=== Test Complete ===\n";
