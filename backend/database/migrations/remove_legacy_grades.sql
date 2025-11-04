-- Remove Legacy Grades Table
-- This migration removes the old grades system which has been replaced by term_assessments

USE student_management;

-- Drop the grades table
DROP TABLE IF EXISTS grades;

-- Note: The grades table has been replaced by the term_assessments table
-- which provides:
-- - Term-based tracking
-- - CA and Exam mark separation
-- - Configurable subject weightings
-- - Automatic final mark calculation
-- - Comprehensive reporting
