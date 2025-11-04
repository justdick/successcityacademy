<?php

/**
 * Terms Seeder
 * Seeds academic terms for the termly exam reports feature
 */

class TermsSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding terms...\n";

        $terms = [
            [
                'name' => 'Term 1',
                'academic_year' => '2024/2025',
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-15',
                'is_active' => 1
            ],
            [
                'name' => 'Term 2',
                'academic_year' => '2024/2025',
                'start_date' => '2025-01-06',
                'end_date' => '2025-04-15',
                'is_active' => 1
            ],
            [
                'name' => 'Term 3',
                'academic_year' => '2024/2025',
                'start_date' => '2025-04-28',
                'end_date' => '2025-07-20',
                'is_active' => 0
            ],
            [
                'name' => 'Term 1',
                'academic_year' => '2023/2024',
                'start_date' => '2023-09-01',
                'end_date' => '2023-12-15',
                'is_active' => 0
            ],
            [
                'name' => 'Term 2',
                'academic_year' => '2023/2024',
                'start_date' => '2024-01-08',
                'end_date' => '2024-04-15',
                'is_active' => 0
            ],
            [
                'name' => 'Term 3',
                'academic_year' => '2023/2024',
                'start_date' => '2024-04-29',
                'end_date' => '2024-07-20',
                'is_active' => 0
            ],
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO terms (name, academic_year, start_date, end_date, is_active) 
            VALUES (:name, :academic_year, :start_date, :end_date, :is_active)
            ON DUPLICATE KEY UPDATE 
                start_date = :start_date, 
                end_date = :end_date, 
                is_active = :is_active
        ");

        foreach ($terms as $term) {
            $stmt->execute($term);
        }

        echo "    ✓ Seeded " . count($terms) . " terms\n";
    }
}
