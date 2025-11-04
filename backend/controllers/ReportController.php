<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/TeacherAccessControl.php';
require_once __DIR__ . '/../utils/PDFGenerator.php';

class ReportController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get term report for a specific student
     * 
     * @param string $student_id Student ID
     * @param int $term_id Term ID
     * @return void Sends JSON response
     */
    public function getStudentTermReport($student_id, $term_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($student_id)) {
                $this->sendErrorResponse("Student ID is required", 400);
                return;
            }

            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Get student information
            $studentQuery = "SELECT s.id, s.student_id, s.name, s.class_level_id, cl.name as class_level_name
                            FROM students s
                            INNER JOIN class_levels cl ON s.class_level_id = cl.id
                            WHERE s.student_id = :student_id
                            LIMIT 1";
            
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();
            $student = $studentStmt->fetch();

            if (!$student) {
                $this->sendErrorResponse("Student not found", 404);
                return;
            }

            // Check student access
            if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $student['id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $student['id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get term information
            $termQuery = "SELECT id, name, academic_year FROM terms WHERE id = :term_id LIMIT 1";
            $termStmt = $this->conn->prepare($termQuery);
            $termStmt->bindParam(':term_id', $term_id);
            $termStmt->execute();
            $term = $termStmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Get assessments with subject weightings
            $assessmentsQuery = "SELECT 
                                    ta.subject_id,
                                    sub.name as subject_name,
                                    COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                                    COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                                    ta.ca_mark,
                                    ta.exam_mark,
                                    ta.final_mark
                                FROM term_assessments ta
                                INNER JOIN subjects sub ON ta.subject_id = sub.id
                                LEFT JOIN subject_weightings sw ON sub.id = sw.subject_id
                                WHERE ta.student_id = :student_id AND ta.term_id = :term_id
                                ORDER BY sub.name ASC";
            
            $assessmentsStmt = $this->conn->prepare($assessmentsQuery);
            $assessmentsStmt->bindParam(':student_id', $student_id);
            $assessmentsStmt->bindParam(':term_id', $term_id);
            $assessmentsStmt->execute();
            $assessments = $assessmentsStmt->fetchAll();

            // Calculate term average
            $termAverage = $this->calculateTermAverage($assessments);

            // Build response
            $response = [
                'student' => [
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'class_level_id' => $student['class_level_id'],
                    'class_level_name' => $student['class_level_name']
                ],
                'term' => [
                    'id' => $term['id'],
                    'name' => $term['name'],
                    'academic_year' => $term['academic_year']
                ],
                'assessments' => $assessments,
                'term_average' => $termAverage,
                'total_subjects' => count($assessments)
            ];

            $this->sendSuccessResponse($response, 200);

        } catch (Exception $e) {
            error_log("Get Student Term Report Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while generating report", 500);
        }
    }

    /**
     * Get term reports for all students in a class
     * 
     * @param int $class_level_id Class level ID
     * @param int $term_id Term ID
     * @return void Sends JSON response
     */
    public function getClassTermReports($class_level_id, $term_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($class_level_id) || !is_numeric($class_level_id)) {
                $this->sendErrorResponse("Valid class level ID is required", 400);
                return;
            }

            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Check class access
            if (!TeacherAccessControl::hasClassAccess($currentUser->id, $class_level_id)) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'class', $class_level_id);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get class level information
            $classQuery = "SELECT id, name FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classStmt = $this->conn->prepare($classQuery);
            $classStmt->bindParam(':class_level_id', $class_level_id);
            $classStmt->execute();
            $classLevel = $classStmt->fetch();

            if (!$classLevel) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Get term information
            $termQuery = "SELECT id, name, academic_year FROM terms WHERE id = :term_id LIMIT 1";
            $termStmt = $this->conn->prepare($termQuery);
            $termStmt->bindParam(':term_id', $term_id);
            $termStmt->execute();
            $term = $termStmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Get all students in the class
            $studentsQuery = "SELECT student_id, name FROM students 
                             WHERE class_level_id = :class_level_id 
                             ORDER BY name ASC";
            
            $studentsStmt = $this->conn->prepare($studentsQuery);
            $studentsStmt->bindParam(':class_level_id', $class_level_id);
            $studentsStmt->execute();
            $students = $studentsStmt->fetchAll();

            // Build reports for each student
            $reports = [];
            foreach ($students as $student) {
                // Get assessments for this student
                $assessmentsQuery = "SELECT 
                                        ta.subject_id,
                                        sub.name as subject_name,
                                        COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                                        COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                                        ta.ca_mark,
                                        ta.exam_mark,
                                        ta.final_mark
                                    FROM term_assessments ta
                                    INNER JOIN subjects sub ON ta.subject_id = sub.id
                                    LEFT JOIN subject_weightings sw ON sub.id = sw.subject_id
                                    WHERE ta.student_id = :student_id AND ta.term_id = :term_id
                                    ORDER BY sub.name ASC";
                
                $assessmentsStmt = $this->conn->prepare($assessmentsQuery);
                $assessmentsStmt->bindParam(':student_id', $student['student_id']);
                $assessmentsStmt->bindParam(':term_id', $term_id);
                $assessmentsStmt->execute();
                $assessments = $assessmentsStmt->fetchAll();

                // Calculate term average
                $termAverage = $this->calculateTermAverage($assessments);

                $reports[] = [
                    'student' => [
                        'student_id' => $student['student_id'],
                        'name' => $student['name']
                    ],
                    'term_average' => $termAverage,
                    'assessments' => $assessments
                ];
            }

            // Build response
            $response = [
                'class_level' => [
                    'id' => $classLevel['id'],
                    'name' => $classLevel['name']
                ],
                'term' => [
                    'id' => $term['id'],
                    'name' => $term['name'],
                    'academic_year' => $term['academic_year']
                ],
                'reports' => $reports
            ];

            $this->sendSuccessResponse($response, 200);

        } catch (Exception $e) {
            error_log("Get Class Term Reports Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while generating reports", 500);
        }
    }

    /**
     * Get assessment summary for a term and class
     * Shows completion status for all students and subjects
     * 
     * @param int $term_id Term ID
     * @param int $class_level_id Class level ID
     * @return void Sends JSON response
     */
    public function getAssessmentSummary($term_id, $class_level_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            if (empty($class_level_id) || !is_numeric($class_level_id)) {
                $this->sendErrorResponse("Valid class level ID is required", 400);
                return;
            }

            // Check class access
            if (!TeacherAccessControl::hasClassAccess($currentUser->id, $class_level_id)) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'class', $class_level_id);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get term information
            $termQuery = "SELECT id, name, academic_year FROM terms WHERE id = :term_id LIMIT 1";
            $termStmt = $this->conn->prepare($termQuery);
            $termStmt->bindParam(':term_id', $term_id);
            $termStmt->execute();
            $term = $termStmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Get class level information
            $classQuery = "SELECT id, name FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classStmt = $this->conn->prepare($classQuery);
            $classStmt->bindParam(':class_level_id', $class_level_id);
            $classStmt->execute();
            $classLevel = $classStmt->fetch();

            if (!$classLevel) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Get all students in the class
            $studentsQuery = "SELECT student_id, name FROM students 
                             WHERE class_level_id = :class_level_id 
                             ORDER BY name ASC";
            
            $studentsStmt = $this->conn->prepare($studentsQuery);
            $studentsStmt->bindParam(':class_level_id', $class_level_id);
            $studentsStmt->execute();
            $students = $studentsStmt->fetchAll();

            // Get all subjects
            $subjectsQuery = "SELECT id, name FROM subjects ORDER BY name ASC";
            $subjectsStmt = $this->conn->prepare($subjectsQuery);
            $subjectsStmt->execute();
            $subjects = $subjectsStmt->fetchAll();

            // Build grid data
            $grid = [];
            $totalPossibleAssessments = count($students) * count($subjects);
            $completeAssessments = 0;
            $partialAssessments = 0;
            $missingAssessments = 0;

            foreach ($students as $student) {
                $studentData = [
                    'student_id' => $student['student_id'],
                    'student_name' => $student['name'],
                    'subjects' => []
                ];

                foreach ($subjects as $subject) {
                    // Check if assessment exists for this student-subject-term combination
                    $assessmentQuery = "SELECT ca_mark, exam_mark 
                                       FROM term_assessments 
                                       WHERE student_id = :student_id 
                                       AND subject_id = :subject_id 
                                       AND term_id = :term_id 
                                       LIMIT 1";
                    
                    $assessmentStmt = $this->conn->prepare($assessmentQuery);
                    $assessmentStmt->bindParam(':student_id', $student['student_id']);
                    $assessmentStmt->bindParam(':subject_id', $subject['id']);
                    $assessmentStmt->bindParam(':term_id', $term_id);
                    $assessmentStmt->execute();
                    $assessment = $assessmentStmt->fetch();

                    $hasCa = false;
                    $hasExam = false;
                    $status = 'missing';

                    if ($assessment) {
                        $hasCa = $assessment['ca_mark'] !== null;
                        $hasExam = $assessment['exam_mark'] !== null;

                        if ($hasCa && $hasExam) {
                            $status = 'complete';
                            $completeAssessments++;
                        } elseif ($hasCa || $hasExam) {
                            $status = 'partial';
                            $partialAssessments++;
                        } else {
                            $missingAssessments++;
                        }
                    } else {
                        $missingAssessments++;
                    }

                    $studentData['subjects'][] = [
                        'subject_id' => $subject['id'],
                        'subject_name' => $subject['name'],
                        'status' => $status,
                        'has_ca' => $hasCa,
                        'has_exam' => $hasExam
                    ];
                }

                $grid[] = $studentData;
            }

            // Calculate completion percentage
            $completionPercentage = $totalPossibleAssessments > 0 
                ? round(($completeAssessments / $totalPossibleAssessments) * 100, 2) 
                : 0;

            // Build response
            $response = [
                'term' => [
                    'id' => $term['id'],
                    'name' => $term['name'],
                    'academic_year' => $term['academic_year']
                ],
                'class_level' => [
                    'id' => $classLevel['id'],
                    'name' => $classLevel['name']
                ],
                'summary' => [
                    'total_students' => count($students),
                    'total_subjects' => count($subjects),
                    'total_possible_assessments' => $totalPossibleAssessments,
                    'complete_assessments' => $completeAssessments,
                    'partial_assessments' => $partialAssessments,
                    'missing_assessments' => $missingAssessments,
                    'completion_percentage' => $completionPercentage
                ],
                'grid' => $grid
            ];

            $this->sendSuccessResponse($response, 200);

        } catch (Exception $e) {
            error_log("Get Assessment Summary Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while generating summary", 500);
        }
    }

    /**
     * Generate and download PDF report for a single student
     * 
     * @param string $student_id Student ID
     * @param int $term_id Term ID
     * @return void Sends PDF file
     */
    public function generateStudentPDF($student_id, $term_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($student_id)) {
                $this->sendErrorResponse("Student ID is required", 400);
                return;
            }

            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Get student information
            $studentQuery = "SELECT s.id, s.student_id, s.name, s.class_level_id, cl.name as class_level_name
                            FROM students s
                            INNER JOIN class_levels cl ON s.class_level_id = cl.id
                            WHERE s.student_id = :student_id
                            LIMIT 1";
            
            $studentStmt = $this->conn->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $student_id);
            $studentStmt->execute();
            $student = $studentStmt->fetch();

            if (!$student) {
                $this->sendErrorResponse("Student not found", 404);
                return;
            }

            // Check student access
            if (!TeacherAccessControl::hasStudentAccess($currentUser->id, $student['id'])) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'student', $student['id']);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get term information
            $termQuery = "SELECT id, name, academic_year FROM terms WHERE id = :term_id LIMIT 1";
            $termStmt = $this->conn->prepare($termQuery);
            $termStmt->bindParam(':term_id', $term_id);
            $termStmt->execute();
            $term = $termStmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Get assessments with subject weightings
            $assessmentsQuery = "SELECT 
                                    ta.subject_id,
                                    sub.name as subject_name,
                                    COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                                    COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                                    ta.ca_mark,
                                    ta.exam_mark,
                                    ta.final_mark
                                FROM term_assessments ta
                                INNER JOIN subjects sub ON ta.subject_id = sub.id
                                LEFT JOIN subject_weightings sw ON sub.id = sw.subject_id
                                WHERE ta.student_id = :student_id AND ta.term_id = :term_id
                                ORDER BY sub.name ASC";
            
            $assessmentsStmt = $this->conn->prepare($assessmentsQuery);
            $assessmentsStmt->bindParam(':student_id', $student_id);
            $assessmentsStmt->bindParam(':term_id', $term_id);
            $assessmentsStmt->execute();
            $assessments = $assessmentsStmt->fetchAll();

            // Calculate term average
            $termAverage = $this->calculateTermAverage($assessments);

            // Generate PDF
            $pdfGenerator = new PDFGenerator();
            $pdfContent = $pdfGenerator->generateStudentReport($student, $term, $assessments, $termAverage);

            // Set headers for PDF download
            $filename = 'Report_' . $student['student_id'] . '_' . $term['name'] . '_' . $term['academic_year'] . '.pdf';
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename); // Sanitize filename
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pdfContent));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $pdfContent;

        } catch (Exception $e) {
            error_log("Generate Student PDF Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while generating PDF", 500);
        }
    }

    /**
     * Generate and download batch PDF reports for a class
     * Creates a ZIP file containing individual PDFs for each student
     * 
     * @param int $class_level_id Class level ID
     * @param int $term_id Term ID
     * @return void Sends ZIP file
     */
    public function generateClassPDFBatch($class_level_id, $term_id) {
        try {
            // Verify authentication
            $currentUser = AuthMiddleware::authenticate();
            if (!$currentUser) {
                return;
            }

            // Validate parameters
            if (empty($class_level_id) || !is_numeric($class_level_id)) {
                $this->sendErrorResponse("Valid class level ID is required", 400);
                return;
            }

            if (empty($term_id) || !is_numeric($term_id)) {
                $this->sendErrorResponse("Valid term ID is required", 400);
                return;
            }

            // Check class access
            if (!TeacherAccessControl::hasClassAccess($currentUser->id, $class_level_id)) {
                TeacherAccessControl::logUnauthorizedAccess($currentUser->id, 'class', $class_level_id);
                TeacherAccessControl::sendForbiddenResponse();
                return;
            }

            // Get class level information
            $classQuery = "SELECT id, name FROM class_levels WHERE id = :class_level_id LIMIT 1";
            $classStmt = $this->conn->prepare($classQuery);
            $classStmt->bindParam(':class_level_id', $class_level_id);
            $classStmt->execute();
            $classLevel = $classStmt->fetch();

            if (!$classLevel) {
                $this->sendErrorResponse("Class level not found", 404);
                return;
            }

            // Get term information
            $termQuery = "SELECT id, name, academic_year FROM terms WHERE id = :term_id LIMIT 1";
            $termStmt = $this->conn->prepare($termQuery);
            $termStmt->bindParam(':term_id', $term_id);
            $termStmt->execute();
            $term = $termStmt->fetch();

            if (!$term) {
                $this->sendErrorResponse("Term not found", 404);
                return;
            }

            // Get all students in the class
            $studentsQuery = "SELECT student_id, name FROM students 
                             WHERE class_level_id = :class_level_id 
                             ORDER BY name ASC";
            
            $studentsStmt = $this->conn->prepare($studentsQuery);
            $studentsStmt->bindParam(':class_level_id', $class_level_id);
            $studentsStmt->execute();
            $students = $studentsStmt->fetchAll();

            if (empty($students)) {
                $this->sendErrorResponse("No students found in this class", 404);
                return;
            }

            // Create temporary directory for PDFs
            $tempDir = sys_get_temp_dir() . '/reports_' . uniqid();
            if (!mkdir($tempDir, 0777, true)) {
                $this->sendErrorResponse("Failed to create temporary directory", 500);
                return;
            }

            // Generate PDF for each student
            $pdfFiles = [];
            foreach ($students as $student) {
                // Get assessments for this student
                $assessmentsQuery = "SELECT 
                                        ta.subject_id,
                                        sub.name as subject_name,
                                        COALESCE(sw.ca_percentage, 40.00) as ca_percentage,
                                        COALESCE(sw.exam_percentage, 60.00) as exam_percentage,
                                        ta.ca_mark,
                                        ta.exam_mark,
                                        ta.final_mark
                                    FROM term_assessments ta
                                    INNER JOIN subjects sub ON ta.subject_id = sub.id
                                    LEFT JOIN subject_weightings sw ON sub.id = sw.subject_id
                                    WHERE ta.student_id = :student_id AND ta.term_id = :term_id
                                    ORDER BY sub.name ASC";
                
                $assessmentsStmt = $this->conn->prepare($assessmentsQuery);
                $assessmentsStmt->bindParam(':student_id', $student['student_id']);
                $assessmentsStmt->bindParam(':term_id', $term_id);
                $assessmentsStmt->execute();
                $assessments = $assessmentsStmt->fetchAll();

                // Calculate term average
                $termAverage = $this->calculateTermAverage($assessments);

                // Prepare student data with class level name
                $studentData = [
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'class_level_name' => $classLevel['name']
                ];

                // Generate PDF
                $pdfGenerator = new PDFGenerator();
                $pdfContent = $pdfGenerator->generateStudentReport($studentData, $term, $assessments, $termAverage);

                // Save PDF to temp file
                $filename = 'Report_' . $student['student_id'] . '_' . $student['name'] . '.pdf';
                $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
                $filepath = $tempDir . '/' . $filename;
                
                file_put_contents($filepath, $pdfContent);
                $pdfFiles[] = $filepath;
            }

            // Create ZIP file
            $zipFilename = 'Reports_' . $classLevel['name'] . '_' . $term['name'] . '_' . $term['academic_year'] . '.zip';
            $zipFilename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $zipFilename);
            $zipPath = $tempDir . '/' . $zipFilename;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                $this->sendErrorResponse("Failed to create ZIP file", 500);
                return;
            }

            foreach ($pdfFiles as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            // Send ZIP file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            header('Content-Length: ' . filesize($zipPath));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            readfile($zipPath);

            // Clean up temporary files
            foreach ($pdfFiles as $file) {
                unlink($file);
            }
            unlink($zipPath);
            rmdir($tempDir);

        } catch (Exception $e) {
            error_log("Generate Class PDF Batch Error: " . $e->getMessage());
            $this->sendErrorResponse("An error occurred while generating batch PDFs", 500);
        }
    }

    /**
     * Calculate term average from assessments
     * Only includes subjects with final marks
     * 
     * @param array $assessments Array of assessment records
     * @return float|null Term average or null if no assessments
     */
    public function calculateTermAverage($assessments) {
        if (empty($assessments)) {
            return null;
        }

        $total = 0;
        $count = 0;

        foreach ($assessments as $assessment) {
            if ($assessment['final_mark'] !== null) {
                $total += floatval($assessment['final_mark']);
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 2) : null;
    }

    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    private function sendSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    private function sendErrorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
