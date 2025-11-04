<?php

/**
 * Database Seeder
 * Main seeder that runs all feature seeders in the correct order
 */

require_once __DIR__ . '/UsersSeeder.php';
require_once __DIR__ . '/SubjectsSeeder.php';
require_once __DIR__ . '/ClassLevelsSeeder.php';
require_once __DIR__ . '/StudentsSeeder.php';
require_once __DIR__ . '/TermsSeeder.php';
require_once __DIR__ . '/SubjectWeightingsSeeder.php';
require_once __DIR__ . '/TermAssessmentsSeeder.php';

class DatabaseSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "\n=== Running Database Seeders ===\n\n";

        // Run seeders in dependency order
        $seeders = [
            new UsersSeeder($this->conn),
            new SubjectsSeeder($this->conn),
            new ClassLevelsSeeder($this->conn),
            new StudentsSeeder($this->conn),
            new TermsSeeder($this->conn),
            new SubjectWeightingsSeeder($this->conn),
            new TermAssessmentsSeeder($this->conn),
        ];

        foreach ($seeders as $seeder) {
            $seeder->run();
        }

        echo "\n✓ All seeders completed successfully!\n\n";
    }

    public function runSpecific($seederName)
    {
        echo "\n=== Running Specific Seeder: $seederName ===\n\n";

        $seederClass = $seederName . 'Seeder';
        if (class_exists($seederClass)) {
            $seeder = new $seederClass($this->conn);
            $seeder->run();
            echo "\n✓ Seeder completed!\n\n";
        } else {
            echo "✗ Seeder class '$seederClass' not found!\n\n";
        }
    }
}
