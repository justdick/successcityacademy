<?php

/**
 * Grades Seeder
 * Seeds sample grades for students (legacy grade system)
 */

class GradesSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding grades (legacy system)...\n";

        // Get students and subjects
        $students = $this->conn->query("SELECT student_id FROM students LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
        $subjects = $this->conn->query("SELECT id FROM subjects LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($students) || empty($subjects)) {
            echo "    ⚠ No students or subjects found, skipping grades\n";
            return;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO grades (student_id, subject_id, mark) 
            VALUES (:student_id, :subject_id, :mark)
            ON DUPLICATE KEY UPDATE mark = :mark
        ");

        $count = 0;
        foreach ($students as $studentId) {
            foreach ($subjects as $subjectId) {
                // Generate random mark between 50 and 100
                $mark = rand(50, 100);
                
                $stmt->execute([
                    ':student_id' => $studentId,
                    ':subject_id' => $subjectId,
                    ':mark' => $mark
                ]);
                $count++;
            }
        }

        echo "    ✓ Seeded $count grades\n";
    }
}
