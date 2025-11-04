<?php
/**
 * Generate password hash for seed data
 * Run this script to generate the correct password hash for the admin user
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nUpdate the seed.sql file with this hash.\n";
