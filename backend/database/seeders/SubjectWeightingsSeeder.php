<?php

/**
 * Subject Weightings Seeder
 * Seeds custom subject weightings for CA and Exam percentages
 */

class SubjectWeightingsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding subject weightings...\n";

        // Get subjects
        $subjects = $this->conn->query("
            SELECT id, name FROM subjects ORDER BY name
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($subjects)) {
            echo "    ⚠ No subjects found, skipping weightings\n";
            return;
        }

        // Define custom weightings for specific subjects
        // Others will use default 40% CA, 60% Exam
        $customWeightings = [
            'Mathematics' => ['ca' => 30, 'exam' => 70],
            'Science' => ['ca' => 35, 'exam' => 65],
            'Physical Education' => ['ca' => 60, 'exam' => 40],
            'Art' => ['ca' => 70, 'exam' => 30],
            'Music' => ['ca' => 70, 'exam' => 30],
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO subject_weightings (subject_id, ca_percentage, exam_percentage) 
            VALUES (:subject_id, :ca_percentage, :exam_percentage)
            ON DUPLICATE KEY UPDATE 
                ca_percentage = :ca_percentage, 
                exam_percentage = :exam_percentage
        ");

        $count = 0;
        foreach ($subjects as $subject) {
            if (isset($customWeightings[$subject['name']])) {
                $weighting = $customWeightings[$subject['name']];
                $stmt->execute([
                    ':subject_id' => $subject['id'],
                    ':ca_percentage' => $weighting['ca'],
                    ':exam_percentage' => $weighting['exam']
                ]);
                $count++;
            }
        }

        echo "    ✓ Seeded $count custom subject weightings\n";
        echo "    ℹ Other subjects will use default weighting (40% CA, 60% Exam)\n";
    }
}
