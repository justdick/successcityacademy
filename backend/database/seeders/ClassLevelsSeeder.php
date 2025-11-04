<?php

/**
 * Class Levels Seeder
 * Seeds class levels/grades
 */

class ClassLevelsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding class levels...\n";

        $classLevels = [
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'Grade 11',
            'Grade 12'
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO class_levels (name) 
            VALUES (:name)
            ON DUPLICATE KEY UPDATE name = name
        ");

        foreach ($classLevels as $level) {
            $stmt->execute([':name' => $level]);
        }

        echo "    ✓ Seeded " . count($classLevels) . " class levels\n";
    }
}
