<?php

/**
 * Term Assessments Seeder
 * Seeds sample term assessments with CA and exam marks
 */

class TermAssessmentsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding term assessments...\n";

        // Get active terms
        $terms = $this->conn->query("
            SELECT id FROM terms WHERE is_active = 1 ORDER BY id LIMIT 2
        ")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($terms)) {
            echo "    ⚠ No active terms found, skipping assessments\n";
            return;
        }

        // Get students (first 15 for demo)
        $students = $this->conn->query("
            SELECT student_id FROM students ORDER BY student_id LIMIT 15
        ")->fetchAll(PDO::FETCH_COLUMN);

        // Get subjects with their weightings
        $subjects = $this->conn->query("
            SELECT 
                s.id,
                s.name,
                COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                COALESCE(sw.exam_percentage, 60.00) as exam_percentage
            FROM subjects s
            LEFT JOIN subject_weightings sw ON s.id = sw.subject_id
            ORDER BY s.id
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students) || empty($subjects)) {
            echo "    ⚠ No students or subjects found, skipping assessments\n";
            return;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO term_assessments (student_id, subject_id, term_id, ca_mark, exam_mark) 
            VALUES (:student_id, :subject_id, :term_id, :ca_mark, :exam_mark)
            ON DUPLICATE KEY UPDATE 
                ca_mark = :ca_mark, 
                exam_mark = :exam_mark
        ");

        $count = 0;
        $termId = $terms[0]; // Use first active term

        foreach ($students as $index => $studentId) {
            foreach ($subjects as $subject) {
                // Vary the data: some complete, some partial, some missing
                $scenario = ($index + $subject['id']) % 5;
                
                $caPercentage = floatval($subject['ca_percentage']);
                $examPercentage = floatval($subject['exam_percentage']);
                
                if ($scenario == 0) {
                    // Missing assessment - skip
                    continue;
                } elseif ($scenario == 1) {
                    // Only CA mark
                    $caMark = rand(60, 100) / 100 * $caPercentage;
                    $examMark = null;
                } elseif ($scenario == 2) {
                    // Only Exam mark
                    $caMark = null;
                    $examMark = rand(60, 100) / 100 * $examPercentage;
                } else {
                    // Complete assessment
                    $caMark = rand(70, 100) / 100 * $caPercentage;
                    $examMark = rand(70, 100) / 100 * $examPercentage;
                }
                
                $stmt->execute([
                    ':student_id' => $studentId,
                    ':subject_id' => $subject['id'],
                    ':term_id' => $termId,
                    ':ca_mark' => $caMark,
                    ':exam_mark' => $examMark
                ]);
                $count++;
            }
        }

        echo "    ✓ Seeded $count term assessments\n";
        echo "    ℹ Mix of complete, partial (CA only/Exam only), and missing assessments\n";
    }
}
