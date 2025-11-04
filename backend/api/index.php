<?php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if API is in a subdirectory
$uri = str_replace('/studentmgt/backend/api', '', $uri);
$uri = str_replace('/backend/api', '', $uri);
$uri = rtrim($uri, '/');

// Parse URI into segments
$segments = explode('/', trim($uri, '/'));

// Route the request
try {
    // Auth routes
    if ($segments[0] === 'auth') {
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        
        if ($method === 'POST' && isset($segments[1])) {
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($segments[1]) {
                case 'login':
                    $controller->login($input);
                    break;
                    
                case 'logout':
                    $controller->logout();
                    break;
                    
                default:
                    sendNotFoundResponse();
                    break;
            }
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // User routes (admin only)
    elseif ($segments[0] === 'users') {
        require_once __DIR__ . '/../controllers/UserController.php';
        $controller = new UserController();
        
        if ($method === 'POST' && count($segments) === 1) {
            // POST /api/users - Create user (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createUser($input);
        } elseif ($method === 'GET' && count($segments) === 1) {
            // GET /api/users - Get all users (admin only)
            $controller->getAllUsers();
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Subject routes
    elseif ($segments[0] === 'subjects') {
        require_once __DIR__ . '/../controllers/SubjectController.php';
        $controller = new SubjectController();
        
        if ($method === 'GET' && count($segments) === 1) {
            // GET /api/subjects - Get all subjects (authenticated)
            $controller->getAllSubjects();
        } elseif ($method === 'POST' && count($segments) === 1) {
            // POST /api/subjects - Create subject (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createSubject($input);
        } elseif ($method === 'DELETE' && count($segments) === 2) {
            // DELETE /api/subjects/{id} - Delete subject (admin only)
            $controller->deleteSubject($segments[1]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Class level routes
    elseif ($segments[0] === 'class-levels') {
        require_once __DIR__ . '/../controllers/ClassLevelController.php';
        $controller = new ClassLevelController();
        
        if ($method === 'GET' && count($segments) === 1) {
            // GET /api/class-levels - Get all class levels (authenticated)
            $controller->getAllClassLevels();
        } elseif ($method === 'POST' && count($segments) === 1) {
            // POST /api/class-levels - Create class level (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createClassLevel($input);
        } elseif ($method === 'DELETE' && count($segments) === 2) {
            // DELETE /api/class-levels/{id} - Delete class level (admin only)
            $controller->deleteClassLevel($segments[1]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Student routes
    elseif ($segments[0] === 'students') {
        // Check if this is a grades request for a specific student
        if ($method === 'GET' && count($segments) === 3 && $segments[2] === 'grades') {
            // GET /api/students/{student_id}/grades - Get grades for student (authenticated)
            require_once __DIR__ . '/../controllers/GradeController.php';
            $gradeController = new GradeController();
            $gradeController->getStudentGrades($segments[1]);
        } else {
            require_once __DIR__ . '/../controllers/StudentController.php';
            $controller = new StudentController();
            
            if ($method === 'POST' && count($segments) === 1) {
                // POST /api/students - Create student (authenticated)
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->createStudent($input);
            } elseif ($method === 'GET' && count($segments) === 1) {
                // GET /api/students - Get all students (authenticated)
                $controller->getAllStudents();
            } elseif ($method === 'GET' && count($segments) === 2) {
                // GET /api/students/{student_id} - Get specific student (authenticated)
                $controller->getStudent($segments[1]);
            } elseif ($method === 'PUT' && count($segments) === 2) {
                // PUT /api/students/{student_id} - Update student (authenticated)
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->updateStudent($segments[1], $input);
            } elseif ($method === 'DELETE' && count($segments) === 2) {
                // DELETE /api/students/{student_id} - Delete student (authenticated)
                $controller->deleteStudent($segments[1]);
            } else {
                sendMethodNotAllowedResponse();
            }
        }
    }
    // Grade routes
    elseif ($segments[0] === 'grades') {
        require_once __DIR__ . '/../controllers/GradeController.php';
        $controller = new GradeController();
        
        if ($method === 'POST' && count($segments) === 1) {
            // POST /api/grades - Add grade (authenticated)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->addGrade($input);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Term routes
    elseif ($segments[0] === 'terms') {
        require_once __DIR__ . '/../controllers/TermController.php';
        $controller = new TermController();
        
        if ($method === 'GET' && count($segments) === 1) {
            // GET /api/terms - Get all terms (authenticated)
            $controller->getAllTerms();
        } elseif ($method === 'GET' && count($segments) === 2 && $segments[1] === 'active') {
            // GET /api/terms/active - Get active terms (authenticated)
            $controller->getActiveTerms();
        } elseif ($method === 'GET' && count($segments) === 2) {
            // GET /api/terms/{id} - Get specific term (authenticated)
            $controller->getTerm($segments[1]);
        } elseif ($method === 'POST' && count($segments) === 1) {
            // POST /api/terms - Create term (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createTerm($input);
        } elseif ($method === 'PUT' && count($segments) === 2) {
            // PUT /api/terms/{id} - Update term (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->updateTerm($segments[1], $input);
        } elseif ($method === 'DELETE' && count($segments) === 2) {
            // DELETE /api/terms/{id} - Delete term (admin only)
            $controller->deleteTerm($segments[1]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Subject Weighting routes
    elseif ($segments[0] === 'subject-weightings') {
        require_once __DIR__ . '/../controllers/SubjectWeightingController.php';
        $controller = new SubjectWeightingController();
        
        if ($method === 'GET' && count($segments) === 1) {
            // GET /api/subject-weightings - Get all subject weightings (authenticated)
            $controller->getAllWeightings();
        } elseif ($method === 'GET' && count($segments) === 2) {
            // GET /api/subject-weightings/{subject_id} - Get weighting for specific subject (authenticated)
            $controller->getWeighting($segments[1]);
        } elseif ($method === 'POST' && count($segments) === 1) {
            // POST /api/subject-weightings - Create or update subject weighting (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createOrUpdateWeighting($input);
        } elseif ($method === 'PUT' && count($segments) === 2) {
            // PUT /api/subject-weightings/{subject_id} - Update subject weighting (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->updateWeighting($segments[1], $input);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Assessment routes
    elseif ($segments[0] === 'assessments') {
        require_once __DIR__ . '/../controllers/AssessmentController.php';
        $controller = new AssessmentController();
        
        if ($method === 'POST' && count($segments) === 1) {
            // POST /api/assessments - Create or update assessment (authenticated)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->createOrUpdateAssessment($input);
        } elseif ($method === 'GET' && count($segments) === 5 && $segments[1] === 'student' && $segments[3] === 'term') {
            // GET /api/assessments/student/{student_id}/term/{term_id} - Get student term assessments (authenticated)
            $controller->getStudentTermAssessments($segments[2], $segments[4]);
        } elseif ($method === 'GET' && count($segments) === 5 && $segments[1] === 'term' && $segments[3] === 'class') {
            // GET /api/assessments/term/{term_id}/class/{class_level_id} - Get class term assessments (authenticated)
            $controller->getClassTermAssessments($segments[2], $segments[4]);
        } elseif ($method === 'PUT' && count($segments) === 2) {
            // PUT /api/assessments/{id} - Update assessment (authenticated)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->updateAssessment($segments[1], $input);
        } elseif ($method === 'DELETE' && count($segments) === 2) {
            // DELETE /api/assessments/{id} - Delete assessment (authenticated)
            $controller->deleteAssessment($segments[1]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Report routes
    elseif ($segments[0] === 'reports') {
        require_once __DIR__ . '/../controllers/ReportController.php';
        $controller = new ReportController();
        
        if ($method === 'GET' && count($segments) === 6 && $segments[1] === 'pdf' && $segments[2] === 'student' && $segments[4] === 'term') {
            // GET /api/reports/pdf/student/{student_id}/term/{term_id} - Generate PDF for student (authenticated)
            $controller->generateStudentPDF($segments[3], $segments[5]);
        } elseif ($method === 'POST' && count($segments) === 6 && $segments[1] === 'pdf' && $segments[2] === 'class' && $segments[4] === 'term') {
            // POST /api/reports/pdf/class/{class_level_id}/term/{term_id} - Generate batch PDFs for class (authenticated)
            $controller->generateClassPDFBatch($segments[3], $segments[5]);
        } elseif ($method === 'GET' && count($segments) === 5 && $segments[1] === 'student' && $segments[3] === 'term') {
            // GET /api/reports/student/{student_id}/term/{term_id} - Get student term report (authenticated)
            $controller->getStudentTermReport($segments[2], $segments[4]);
        } elseif ($method === 'GET' && count($segments) === 5 && $segments[1] === 'class' && $segments[3] === 'term') {
            // GET /api/reports/class/{class_level_id}/term/{term_id} - Get class term reports (authenticated)
            $controller->getClassTermReports($segments[2], $segments[4]);
        } elseif ($method === 'GET' && count($segments) === 6 && $segments[1] === 'summary' && $segments[2] === 'term' && $segments[4] === 'class') {
            // GET /api/reports/summary/term/{term_id}/class/{class_level_id} - Get assessment summary (authenticated)
            $controller->getAssessmentSummary($segments[3], $segments[5]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Teacher Assignment routes
    elseif ($segments[0] === 'teacher-assignments') {
        require_once __DIR__ . '/../controllers/TeacherAssignmentController.php';
        $controller = new TeacherAssignmentController();
        
        if ($method === 'POST' && count($segments) === 2 && $segments[1] === 'class') {
            // POST /api/teacher-assignments/class - Assign teacher to class (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->assignClass($input);
        } elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'subject') {
            // POST /api/teacher-assignments/subject - Assign teacher to subject (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->assignSubject($input);
        } elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'bulk') {
            // POST /api/teacher-assignments/bulk - Bulk assign teacher to classes and subjects (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->bulkAssign($input);
        } elseif ($method === 'GET' && count($segments) === 2 && $segments[1] === 'my-assignments') {
            // GET /api/teacher-assignments/my-assignments - Get current teacher's assignments (authenticated)
            $controller->getMyAssignments();
        } elseif ($method === 'GET' && count($segments) === 1) {
            // GET /api/teacher-assignments - Get all assignments with optional filtering (admin only)
            $filters = [];
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['class_level_id'])) {
                $filters['class_level_id'] = $_GET['class_level_id'];
            }
            if (isset($_GET['subject_id'])) {
                $filters['subject_id'] = $_GET['subject_id'];
            }
            $controller->getAllAssignments($filters);
        } elseif ($method === 'DELETE' && count($segments) === 3 && $segments[1] === 'class') {
            // DELETE /api/teacher-assignments/class/{id} - Delete class assignment (admin only)
            $controller->deleteClassAssignment($segments[2]);
        } elseif ($method === 'DELETE' && count($segments) === 3 && $segments[1] === 'subject') {
            // DELETE /api/teacher-assignments/subject/{id} - Delete subject assignment (admin only)
            $controller->deleteSubjectAssignment($segments[2]);
        } else {
            sendMethodNotAllowedResponse();
        }
    }
    // Backup routes
    elseif ($segments[0] === 'backups') {
        require_once __DIR__ . '/../controllers/BackupController.php';
        $controller = new BackupController();
        
        if ($method === 'POST' && count($segments) === 1) {
            // POST /api/backups - Create backup (admin only)
            $controller->createBackup();
        } elseif ($method === 'GET' && count($segments) === 1) {
            // GET /api/backups - List backups (admin only)
            $controller->listBackups();
        } elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'restore') {
            // POST /api/backups/restore - Restore backup (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            $controller->restoreBackup($input);
        } elseif ($method === 'GET' && count($segments) === 3 && $segments[1] === 'download') {
            // GET /api/backups/download/{filename} - Download backup (admin only)
            $controller->downloadBackup($segments[2]);
        } elseif ($method === 'DELETE' && count($segments) === 2) {
            // DELETE /api/backups/{filename} - Delete backup (admin only)
            $controller->deleteBackup($segments[1]);
        } else {
            sendMethodNotAllowedResponse();
        }
    } else {
        sendNotFoundResponse();
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}

/**
 * Send 404 Not Found response
 */
function sendNotFoundResponse() {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Endpoint not found'
    ]);
}

/**
 * Send 405 Method Not Allowed response
 */
function sendMethodNotAllowedResponse() {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
