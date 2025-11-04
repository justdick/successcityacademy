<?php

/**
 * Subjects Seeder
 * Seeds academic subjects
 */

class SubjectsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding subjects...\n";

        $subjects = [
            'Mathematics',
            'English',
            'Science',
            'Social Studies',
            'French',
            'Physical Education',
            'Art',
            'Music',
            'Computer Science',
            'Religious Studies'
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO subjects (name) 
            VALUES (:name)
            ON DUPLICATE KEY UPDATE name = name
        ");

        foreach ($subjects as $subject) {
            $stmt->execute([':name' => $subject]);
        }

        echo "    ✓ Seeded " . count($subjects) . " subjects\n";
    }
}
