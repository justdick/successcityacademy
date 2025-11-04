-- Migration: Add Termly Reports Feature
-- This migration adds support for term-based assessments with CA and exam marks
-- Date: 2024-11-02

USE student_management;

-- Create terms table
CREATE TABLE IF NOT EXISTS terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_term (name, academic_year),
    INDEX idx_academic_year (academic_year),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create subject_weightings table
CREATE TABLE IF NOT EXISTS subject_weightings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    ca_percentage DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    exam_percentage DECIMAL(5,2) NOT NULL DEFAULT 60.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_weighting (subject_id),
    CONSTRAINT chk_ca_percentage CHECK (ca_percentage >= 0 AND ca_percentage <= 100),
    CONSTRAINT chk_exam_percentage CHECK (exam_percentage >= 0 AND exam_percentage <= 100),
    CONSTRAINT chk_percentage_sum CHECK (ca_percentage + exam_percentage = 100.00),
    INDEX idx_subject_id (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create term_assessments table
CREATE TABLE IF NOT EXISTS term_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    subject_id INT NOT NULL,
    term_id INT NOT NULL,
    ca_mark DECIMAL(5,2) DEFAULT NULL,
    exam_mark DECIMAL(5,2) DEFAULT NULL,
    final_mark DECIMAL(5,2) GENERATED ALWAYS AS (COALESCE(ca_mark, 0) + COALESCE(exam_mark, 0)) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assessment (student_id, subject_id, term_id),
    INDEX idx_student_id (student_id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_term_id (term_id),
    INDEX idx_student_term (student_id, term_id),
    INDEX idx_term_subject (term_id, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default subject weightings (40% CA, 60% Exam) for all existing subjects
INSERT INTO subject_weightings (subject_id, ca_percentage, exam_percentage)
SELECT id, 40.00, 60.00
FROM subjects
WHERE id NOT IN (SELECT subject_id FROM subject_weightings);

-- Create sample terms for testing
INSERT INTO terms (name, academic_year, start_date, end_date, is_active) VALUES
('Term 1', '2024/2025', '2024-09-01', '2024-12-15', TRUE),
('Term 2', '2024/2025', '2025-01-06', '2025-04-10', FALSE),
('Term 3', '2024/2025', '2025-04-21', '2025-07-25', FALSE),
('Term 1', '2023/2024', '2023-09-01', '2023-12-15', FALSE),
('Term 2', '2023/2024', '2024-01-08', '2024-04-12', FALSE),
('Term 3', '2023/2024', '2024-04-22', '2024-07-26', FALSE);

-- Migration completed successfully
