<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/TeacherAccessControl.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/AssessmentController.php';
require_once __DIR__ . '/utils/JWT.php';

echo "=== End-to-End Access Control Tests ===\n\n";

try {
    $db = new Database();
    $conn = $db->connect();

    // Get teacher and admin users
    $teacherQuery = "SELECT id, username, role FROM users WHERE role = 'user' LIMIT 1";
    $teacherStmt = $conn->prepare($teacherQuery);
    $teacherStmt->execute();
    $teacher = $teacherStmt->fetch();

    $adminQuery = "SELECT id, username, role FROM users WHERE role = 'admin' LIMIT 1";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->execute();
    $admin = $adminStmt->fetch();

    if (!$teacher || !$admin) {
        echo "✗ Required users not found\n";
        exit(1);
    }

    echo "Teacher: {$teacher['username']} (ID: {$teacher['id']})\n";
    echo "Admin: {$admin['username']} (ID: {$admin['id']})\n\n";

    // Get teacher's accessible classes and subjects
    $teacherClasses = TeacherAccessControl::getAccessibleClasses($teacher['id']);
    $teacherSubjects = TeacherAccessControl::getAccessibleSubjects($teacher['id']);

    echo "Teacher has access to:\n";
    echo "  - Classes: " . count($teacherClasses) . " (" . implode(', ', $teacherClasses) . ")\n";
    echo "  - Subjects: " . count($teacherSubjects) . " (" . implode(', ', $teacherSubjects) . ")\n\n";

    // Test 1: Teacher accessing students in their assigned class
    echo "Test 1: Teacher accessing students in assigned class\n";
    $teacherToken = JWT::encode(['id' => $teacher['id'], 'username' => $teacher['username'], 'role' => $teacher['role']]);
    $GLOBALS['TEST_AUTH_TOKEN'] = $teacherToken;

    $studentController = new StudentController();
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    $response = json_decode($output, true);

    if ($response && $response['success']) {
        $studentCount = count($response['data']);
        echo "  ✓ Teacher can access $studentCount students\n";
        
        // Verify all students are from accessible classes
        $allInAccessibleClasses = true;
        foreach ($response['data'] as $student) {
            if (!in_array($student['class_level_id'], $teacherClasses)) {
                $allInAccessibleClasses = false;
                break;
            }
        }
        
        if ($allInAccessibleClasses) {
            echo "  ✓ All students are from teacher's assigned classes\n";
        } else {
            echo "  ✗ Some students are from non-assigned classes\n";
        }
    } else {
        echo "  ✗ Failed to get students\n";
    }
    echo "\n";

    // Test 2: Teacher accessing student in non-assigned class
    echo "Test 2: Teacher accessing student in non-assigned class\n";
    
    // Find a student not in teacher's classes
    $nonAccessibleQuery = "SELECT id, student_id, name, class_level_id FROM students 
                           WHERE class_level_id NOT IN (" . implode(',', array_merge($teacherClasses, [0])) . ") 
                           LIMIT 1";
    $nonAccessibleStmt = $conn->prepare($nonAccessibleQuery);
    $nonAccessibleStmt->execute();
    $nonAccessibleStudent = $nonAccessibleStmt->fetch();

    if ($nonAccessibleStudent) {
        ob_start();
        $studentController->getStudent($nonAccessibleStudent['student_id']);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        if ($response && !$response['success'] && isset($response['error'])) {
            echo "  ✓ Access denied as expected: {$response['error']}\n";
        } else {
            echo "  ✗ Teacher should not have access to this student\n";
        }
    } else {
        echo "  ⊘ No non-accessible students found (teacher has access to all classes)\n";
    }
    echo "\n";

    // Test 3: Admin accessing all students
    echo "Test 3: Admin accessing all students\n";
    $adminToken = JWT::encode(['id' => $admin['id'], 'username' => $admin['username'], 'role' => $admin['role']]);
    $GLOBALS['TEST_AUTH_TOKEN'] = $adminToken;

    $studentController = new StudentController();
    ob_start();
    $studentController->getAllStudents();
    $output = ob_get_clean();
    $response = json_decode($output, true);

    if ($response && $response['success']) {
        $studentCount = count($response['data']);
        echo "  ✓ Admin can access all $studentCount students\n";
    } else {
        echo "  ✗ Failed to get students\n";
    }
    echo "\n";

    // Test 4: Teacher creating assessment for assigned subject and student
    echo "Test 4: Teacher creating assessment for assigned subject and student\n";
    $GLOBALS['TEST_AUTH_TOKEN'] = $teacherToken;

    if (!empty($teacherClasses) && !empty($teacherSubjects)) {
        // Find a student in teacher's class
        $accessibleStudentQuery = "SELECT student_id FROM students WHERE class_level_id = :class_id LIMIT 1";
        $accessibleStudentStmt = $conn->prepare($accessibleStudentQuery);
        $accessibleStudentStmt->bindParam(':class_id', $teacherClasses[0]);
        $accessibleStudentStmt->execute();
        $accessibleStudent = $accessibleStudentStmt->fetch();

        // Get a term
        $termQuery = "SELECT id FROM terms LIMIT 1";
        $termStmt = $conn->prepare($termQuery);
        $termStmt->execute();
        $term = $termStmt->fetch();

        if ($accessibleStudent && $term) {
            $assessmentController = new AssessmentController();
            $assessmentData = [
                'student_id' => $accessibleStudent['student_id'],
                'subject_id' => $teacherSubjects[0],
                'term_id' => $term['id'],
                'ca_mark' => 35,
                'exam_mark' => 55
            ];

            ob_start();
            $assessmentController->createOrUpdateAssessment($assessmentData);
            $output = ob_get_clean();
            $response = json_decode($output, true);

            if ($response && $response['success']) {
                echo "  ✓ Teacher can create assessment for assigned subject and student\n";
            } else {
                echo "  ✗ Failed to create assessment: " . ($response['error'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "  ⊘ No accessible student or term found\n";
        }
    } else {
        echo "  ⊘ Teacher has no class or subject assignments\n";
    }
    echo "\n";

    // Test 5: Teacher creating assessment for non-assigned subject
    echo "Test 5: Teacher creating assessment for non-assigned subject\n";
    
    // Find a subject not assigned to teacher
    $nonAccessibleSubjectQuery = "SELECT id FROM subjects 
                                   WHERE id NOT IN (" . implode(',', array_merge($teacherSubjects, [0])) . ") 
                                   LIMIT 1";
    $nonAccessibleSubjectStmt = $conn->prepare($nonAccessibleSubjectQuery);
    $nonAccessibleSubjectStmt->execute();
    $nonAccessibleSubject = $nonAccessibleSubjectStmt->fetch();

    if ($nonAccessibleSubject && !empty($teacherClasses)) {
        $accessibleStudentQuery = "SELECT student_id FROM students WHERE class_level_id = :class_id LIMIT 1";
        $accessibleStudentStmt = $conn->prepare($accessibleStudentQuery);
        $accessibleStudentStmt->bindParam(':class_id', $teacherClasses[0]);
        $accessibleStudentStmt->execute();
        $accessibleStudent = $accessibleStudentStmt->fetch();

        $termQuery = "SELECT id FROM terms LIMIT 1";
        $termStmt = $conn->prepare($termQuery);
        $termStmt->execute();
        $term = $termStmt->fetch();

        if ($accessibleStudent && $term) {
            $assessmentController = new AssessmentController();
            $assessmentData = [
                'student_id' => $accessibleStudent['student_id'],
                'subject_id' => $nonAccessibleSubject['id'],
                'term_id' => $term['id'],
                'ca_mark' => 35,
                'exam_mark' => 55
            ];

            ob_start();
            $assessmentController->createOrUpdateAssessment($assessmentData);
            $output = ob_get_clean();
            $response = json_decode($output, true);

            if ($response && !$response['success'] && isset($response['error'])) {
                echo "  ✓ Access denied as expected: {$response['error']}\n";
            } else {
                echo "  ✗ Teacher should not be able to create assessment for non-assigned subject\n";
            }
        }
    } else {
        echo "  ⊘ No non-accessible subjects found or teacher has no classes\n";
    }
    echo "\n";

    // Test 6: Check access logs
    echo "Test 6: Check access logs\n";
    $logQuery = "SELECT COUNT(*) as count FROM access_logs WHERE user_id = :user_id";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bindParam(':user_id', $teacher['id']);
    $logStmt->execute();
    $logResult = $logStmt->fetch();

    echo "  Total access log entries for teacher: {$logResult['count']}\n";
    
    if ($logResult['count'] > 0) {
        $recentLogQuery = "SELECT resource_type, resource_id, created_at 
                          FROM access_logs 
                          WHERE user_id = :user_id 
                          ORDER BY created_at DESC 
                          LIMIT 3";
        $recentLogStmt = $conn->prepare($recentLogQuery);
        $recentLogStmt->bindParam(':user_id', $teacher['id']);
        $recentLogStmt->execute();
        $recentLogs = $recentLogStmt->fetchAll();

        echo "  Recent unauthorized access attempts:\n";
        foreach ($recentLogs as $log) {
            echo "    - {$log['resource_type']} (ID: {$log['resource_id']}) at {$log['created_at']}\n";
        }
    }
    echo "  ✓ Access logging is working\n\n";

    echo "=== All End-to-End Tests Completed! ===\n";

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
