import { useState, useEffect } from 'react';
import { getAllStudents } from '../services/api';
import { getActiveTerms } from '../services/termService';
import { getStudentReport, downloadStudentPDF, triggerDownload } from '../services/reportService';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { useAuth } from '../context/AuthContext';
import ReportCard from './ReportCard';
import PDFExportButton from './PDFExportButton';

/**
 * StudentTermReport Component
 * 
 * Displays individual student term report with:
 * - Student info header
 * - Assessment table with CA, exam, and final marks
 * - Subject weightings
 * - Term average calculation
 * - PDF export button
 * - Teacher filtering: Teachers only see students from their assigned classes
 */
const StudentTermReport = () => {
  const { isAdmin } = useAuth();
  const [students, setStudents] = useState([]);
  const [terms, setTerms] = useState([]);
  const [selectedStudent, setSelectedStudent] = useState('');
  const [selectedTerm, setSelectedTerm] = useState('');
  const [reportData, setReportData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [loadingReport, setLoadingReport] = useState(false);
  const [error, setError] = useState('');
  const [assignments, setAssignments] = useState(null);

  // Load students and terms on mount
  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      setError('');

      // Load assignments first if teacher
      let assignmentsData = null;
      if (!isAdmin()) {
        assignmentsData = await getMyAssignments();
        setAssignments(assignmentsData);
      }

      const [studentsData, termsData] = await Promise.all([
        getAllStudents(),
        getActiveTerms()
      ]);

      // Filter students by assigned classes for teachers
      let filteredStudents = studentsData;
      if (!isAdmin() && assignmentsData) {
        const assignedClassIds = assignmentsData.classes.map(c => c.id);
        filteredStudents = studentsData.filter(student => 
          assignedClassIds.includes(student.class_level_id)
        );
      }

      setStudents(filteredStudents);
      setTerms(termsData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Load report when student and term are selected
  useEffect(() => {
    if (selectedStudent && selectedTerm) {
      loadReport();
    } else {
      setReportData(null);
    }
  }, [selectedStudent, selectedTerm]);

  const loadReport = async () => {
    try {
      setLoadingReport(true);
      setError('');

      const data = await getStudentReport(selectedStudent, selectedTerm);
      setReportData(data);
    } catch (err) {
      setError(err.message);
      setReportData(null);
    } finally {
      setLoadingReport(false);
    }
  };

  const handleDownloadPDF = async () => {
    if (!selectedStudent || !selectedTerm) {
      throw new Error('Please select a student and term');
    }

    const blob = await downloadStudentPDF(selectedStudent, selectedTerm);
    
    // Generate filename
    const student = students.find(s => s.student_id === selectedStudent);
    const term = terms.find(t => t.id === parseInt(selectedTerm));
    const filename = `${student?.name}_${term?.name}_${term?.academic_year}.pdf`.replace(/\s+/g, '_');
    
    triggerDownload(blob, filename);
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-lg text-gray-600">Loading...</div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 className="text-3xl font-bold text-primary-900 mb-6">Student Term Report</h1>

        {/* Error Message */}
        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {/* Selection Filters */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          {/* Student Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Student
            </label>
            <select
              value={selectedStudent}
              onChange={(e) => setSelectedStudent(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">-- Select Student --</option>
              {students.map((student) => (
                <option key={student.student_id} value={student.student_id}>
                  {student.name} ({student.student_id})
                </option>
              ))}
            </select>
          </div>

          {/* Term Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Term
            </label>
            <select
              value={selectedTerm}
              onChange={(e) => setSelectedTerm(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">-- Select Term --</option>
              {terms.map((term) => (
                <option key={term.id} value={term.id}>
                  {term.name} - {term.academic_year}
                </option>
              ))}
            </select>
          </div>

          {/* PDF Export Button */}
          <div className="flex items-end">
            <PDFExportButton
              onExport={handleDownloadPDF}
              disabled={!selectedStudent || !selectedTerm || !reportData}
              label="Export to PDF"
              loadingLabel="Generating PDF..."
              variant="primary"
              size="medium"
              fullWidth={true}
            />
          </div>
        </div>

        {/* Loading State */}
        {loadingReport && (
          <div className="flex justify-center items-center h-32">
            <div className="text-lg text-gray-600">Loading report...</div>
          </div>
        )}

        {/* Report Display */}
        {!loadingReport && reportData && (
          <div className="mt-6">
            <ReportCard
              student={reportData.student}
              term={reportData.term}
              assessments={reportData.assessments}
              termAverage={reportData.term_average}
            />
          </div>
        )}

        {/* No Selection Message */}
        {!loadingReport && !reportData && selectedStudent && selectedTerm && (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <p className="text-gray-600">No report data available for the selected student and term.</p>
          </div>
        )}

        {/* No Assignments Message for Teachers */}
        {!isAdmin() && assignments && assignments.classes.length === 0 && (
          <div className="text-center py-12 bg-yellow-50 rounded-lg border border-yellow-200">
            <p className="text-yellow-800 font-medium">No Class Assignments</p>
            <p className="text-yellow-700 mt-2">You have not been assigned to any classes yet. Please contact your administrator.</p>
          </div>
        )}

        {/* Initial State Message */}
        {!selectedStudent || !selectedTerm ? (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <p className="text-gray-600">
              {students.length === 0 && !isAdmin() && assignments && assignments.classes.length === 0
                ? 'No students available. You need class assignments to view reports.'
                : 'Please select a student and term to view the report.'}
            </p>
          </div>
        ) : null}
      </div>
    </div>
  );
};

export default StudentTermReport;
