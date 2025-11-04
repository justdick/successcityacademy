<?php

/**
 * Students Seeder
 * Seeds sample students across different class levels
 */

class StudentsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding students...\n";

        // Get class levels
        $classLevels = $this->conn->query("SELECT id FROM class_levels ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($classLevels)) {
            echo "    ⚠ No class levels found, skipping students\n";
            return;
        }

        $students = [
            // Grade 7
            ['student_id' => 'STU2024001', 'name' => 'John Smith', 'class_level_idx' => 0],
            ['student_id' => 'STU2024002', 'name' => 'Emma Johnson', 'class_level_idx' => 0],
            ['student_id' => 'STU2024003', 'name' => 'Michael Brown', 'class_level_idx' => 0],
            ['student_id' => 'STU2024004', 'name' => 'Sophia Davis', 'class_level_idx' => 0],
            ['student_id' => 'STU2024005', 'name' => 'William Wilson', 'class_level_idx' => 0],
            
            // Grade 8
            ['student_id' => 'STU2024006', 'name' => 'Olivia Martinez', 'class_level_idx' => 1],
            ['student_id' => 'STU2024007', 'name' => 'James Anderson', 'class_level_idx' => 1],
            ['student_id' => 'STU2024008', 'name' => 'Ava Taylor', 'class_level_idx' => 1],
            ['student_id' => 'STU2024009', 'name' => 'Benjamin Thomas', 'class_level_idx' => 1],
            ['student_id' => 'STU2024010', 'name' => 'Isabella Moore', 'class_level_idx' => 1],
            
            // Grade 9
            ['student_id' => 'STU2024011', 'name' => 'Lucas Jackson', 'class_level_idx' => 2],
            ['student_id' => 'STU2024012', 'name' => 'Mia White', 'class_level_idx' => 2],
            ['student_id' => 'STU2024013', 'name' => 'Henry Harris', 'class_level_idx' => 2],
            ['student_id' => 'STU2024014', 'name' => 'Charlotte Martin', 'class_level_idx' => 2],
            ['student_id' => 'STU2024015', 'name' => 'Alexander Thompson', 'class_level_idx' => 2],
            
            // Grade 10
            ['student_id' => 'STU2024016', 'name' => 'Amelia Garcia', 'class_level_idx' => 3],
            ['student_id' => 'STU2024017', 'name' => 'Daniel Martinez', 'class_level_idx' => 3],
            ['student_id' => 'STU2024018', 'name' => 'Harper Robinson', 'class_level_idx' => 3],
            ['student_id' => 'STU2024019', 'name' => 'Matthew Clark', 'class_level_idx' => 3],
            ['student_id' => 'STU2024020', 'name' => 'Evelyn Rodriguez', 'class_level_idx' => 3],
            
            // Grade 11
            ['student_id' => 'STU2024021', 'name' => 'Sebastian Lewis', 'class_level_idx' => 4],
            ['student_id' => 'STU2024022', 'name' => 'Abigail Lee', 'class_level_idx' => 4],
            ['student_id' => 'STU2024023', 'name' => 'Joseph Walker', 'class_level_idx' => 4],
            ['student_id' => 'STU2024024', 'name' => 'Emily Hall', 'class_level_idx' => 4],
            ['student_id' => 'STU2024025', 'name' => 'David Allen', 'class_level_idx' => 4],
            
            // Grade 12
            ['student_id' => 'STU2024026', 'name' => 'Elizabeth Young', 'class_level_idx' => 5],
            ['student_id' => 'STU2024027', 'name' => 'Christopher King', 'class_level_idx' => 5],
            ['student_id' => 'STU2024028', 'name' => 'Sofia Wright', 'class_level_idx' => 5],
            ['student_id' => 'STU2024029', 'name' => 'Andrew Lopez', 'class_level_idx' => 5],
            ['student_id' => 'STU2024030', 'name' => 'Victoria Hill', 'class_level_idx' => 5],
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO students (student_id, name, class_level_id) 
            VALUES (:student_id, :name, :class_level_id)
            ON DUPLICATE KEY UPDATE name = :name, class_level_id = :class_level_id
        ");

        $count = 0;
        foreach ($students as $student) {
            if (isset($classLevels[$student['class_level_idx']])) {
                $stmt->execute([
                    ':student_id' => $student['student_id'],
                    ':name' => $student['name'],
                    ':class_level_id' => $classLevels[$student['class_level_idx']]
                ]);
                $count++;
            }
        }

        echo "    ✓ Seeded $count students\n";
    }
}
