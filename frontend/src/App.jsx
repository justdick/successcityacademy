import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import ErrorBoundary from './components/ErrorBoundary'
import Login from './components/Login'
import ProtectedRoute from './components/ProtectedRoute'
import Layout from './components/Layout'
import StudentList from './components/StudentList'
import StudentForm from './components/StudentForm'
import UserManagement from './components/UserManagement'
import SubjectManagement from './components/SubjectManagement'
import ClassLevelManagement from './components/ClassLevelManagement'
import TermManagement from './components/TermManagement'
import SubjectWeightingManagement from './components/SubjectWeightingManagement'
import AssessmentEntry from './components/AssessmentEntry'
import AssessmentGrid from './components/AssessmentGrid'
import AssessmentSummary from './components/AssessmentSummary'
import StudentTermReport from './components/StudentTermReport'
import ClassTermReports from './components/ClassTermReports'
import TeacherAssignments from './components/TeacherAssignments'
import MyAssignments from './components/MyAssignments'
import BackupManagement from './components/BackupManagement'

/**
 * Main App component with routing and authentication
 * 
 * Features:
 * - AuthProvider wraps the entire app for authentication state management
 * - ErrorBoundary catches and displays errors gracefully
 * - Token expiration is handled automatically by API interceptor (redirects to login on 401)
 * - ProtectedRoute guards authenticated routes
 * - Layout component provides navigation and logout functionality
 * - Admin-only routes are protected with adminOnly prop
 */
function App() {
  return (
    <ErrorBoundary>
      <AuthProvider>
        <Router>
          <Routes>
            {/* Public Route - Login */}
            <Route path="/login" element={<Login />} />

            {/* Protected Routes - Student Management */}
            <Route
              path="/students"
              element={
                <ProtectedRoute>
                  <Layout>
                    <StudentList />
                  </Layout>
                </ProtectedRoute>
              }
            />
            <Route
              path="/students/add"
              element={
                <ProtectedRoute>
                  <Layout>
                    <StudentForm />
                  </Layout>
                </ProtectedRoute>
              }
            />
            <Route
              path="/students/edit/:studentId"
              element={
                <ProtectedRoute>
                  <Layout>
                    <StudentForm />
                  </Layout>
                </ProtectedRoute>
              }
            />


            {/* Admin-Only Routes - User Management */}
            <Route
              path="/users"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <UserManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Subject Management */}
            <Route
              path="/subjects"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <SubjectManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Class Level Management */}
            <Route
              path="/class-levels"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <ClassLevelManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Term Management */}
            <Route
              path="/terms"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <TermManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Subject Weighting Management */}
            <Route
              path="/subject-weightings"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <SubjectWeightingManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Teacher Assignments */}
            <Route
              path="/teacher-assignments"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <TeacherAssignments />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Admin-Only Routes - Backup Management */}
            <Route
              path="/backup-management"
              element={
                <ProtectedRoute adminOnly={true}>
                  <Layout>
                    <BackupManagement />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Protected Routes - My Assignments (Teacher Dashboard) */}
            <Route
              path="/my-assignments"
              element={
                <ProtectedRoute>
                  <Layout>
                    <MyAssignments />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Protected Routes - Assessment Entry */}
            <Route
              path="/assessments/entry"
              element={
                <ProtectedRoute>
                  <Layout>
                    <AssessmentEntry />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Protected Routes - Assessment Grid */}
            <Route
              path="/assessments/grid"
              element={
                <ProtectedRoute>
                  <Layout>
                    <AssessmentGrid />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Protected Routes - Assessment Summary */}
            <Route
              path="/assessments/summary"
              element={
                <ProtectedRoute>
                  <Layout>
                    <AssessmentSummary />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Protected Routes - Reports */}
            <Route
              path="/reports/student"
              element={
                <ProtectedRoute>
                  <Layout>
                    <StudentTermReport />
                  </Layout>
                </ProtectedRoute>
              }
            />

            <Route
              path="/reports/class"
              element={
                <ProtectedRoute>
                  <Layout>
                    <ClassTermReports />
                  </Layout>
                </ProtectedRoute>
              }
            />

            {/* Default Routes - Redirect to students list */}
            <Route path="/" element={<Navigate to="/students" replace />} />
            <Route path="*" element={<Navigate to="/students" replace />} />
          </Routes>
        </Router>
      </AuthProvider>
    </ErrorBoundary>
  )
}

export default App
