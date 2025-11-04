<?php
/**
 * Master Seed Script
 * 
 * Runs all seed files in the correct order
 */

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "           SENIOR HIGH SCHOOL DATABASE SEEDER\n";
echo str_repeat("=", 60) . "\n\n";

// Seed class levels
echo "📚 STEP 1: Seeding Class Levels\n";
echo str_repeat("-", 60) . "\n";
require_once __DIR__ . '/seed_class_levels.php';

echo "\n";

// Seed subjects
echo "📖 STEP 2: Seeding Subjects\n";
echo str_repeat("-", 60) . "\n";
require_once __DIR__ . '/seed_subjects.php';

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "           ✅ ALL SEEDING COMPLETED!\n";
echo str_repeat("=", 60) . "\n\n";

echo "Next steps:\n";
echo "1. Verify the data in your database\n";
echo "2. Create teacher accounts if needed\n";
echo "3. Assign teachers to classes and subjects\n";
echo "4. Add students to the appropriate class levels\n\n";
