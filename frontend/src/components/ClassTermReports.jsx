import { useState, useEffect } from 'react';
import { getAllClassLevels } from '../services/api';
import { getActiveTerms } from '../services/termService';
import { getClassReports, downloadClassPDFs, triggerDownload } from '../services/reportService';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { useAuth } from '../context/AuthContext';
import ReportCard from './ReportCard';
import PDFExportButton from './PDFExportButton';

/**
 * ClassTermReports Component
 * 
 * Displays term reports for all students in a class with:
 * - Class level and term filters
 * - List of student report cards
 * - Sorting by name or average
 * - Batch PDF export button
 * - Teacher filtering: Teachers only see their assigned classes
 */
const ClassTermReports = () => {
  const { isAdmin } = useAuth();
  const [classLevels, setClassLevels] = useState([]);
  const [terms, setTerms] = useState([]);
  const [selectedClass, setSelectedClass] = useState('');
  const [selectedTerm, setSelectedTerm] = useState('');
  const [reportsData, setReportsData] = useState(null);
  const [sortBy, setSortBy] = useState('name'); // 'name' or 'average'
  const [sortOrder, setSortOrder] = useState('asc'); // 'asc' or 'desc'
  const [loading, setLoading] = useState(true);
  const [loadingReports, setLoadingReports] = useState(false);
  const [error, setError] = useState('');
  const [assignments, setAssignments] = useState(null);

  // Load class levels and terms on mount
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

      const [classLevelsData, termsData] = await Promise.all([
        getAllClassLevels(),
        getActiveTerms()
      ]);

      // Filter class levels by assigned classes for teachers
      let filteredClassLevels = classLevelsData;
      if (!isAdmin() && assignmentsData) {
        const assignedClassIds = assignmentsData.classes.map(c => c.id);
        filteredClassLevels = classLevelsData.filter(classLevel => 
          assignedClassIds.includes(classLevel.id)
        );
      }

      setClassLevels(filteredClassLevels);
      setTerms(termsData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Load reports when class and term are selected
  useEffect(() => {
    if (selectedClass && selectedTerm) {
      loadReports();
    } else {
      setReportsData(null);
    }
  }, [selectedClass, selectedTerm]);

  const loadReports = async () => {
    try {
      setLoadingReports(true);
      setError('');

      const data = await getClassReports(selectedClass, selectedTerm);
      setReportsData(data);
    } catch (err) {
      setError(err.message);
      setReportsData(null);
    } finally {
      setLoadingReports(false);
    }
  };

  const handleDownloadPDFs = async () => {
    if (!selectedClass || !selectedTerm) {
      throw new Error('Please select a class and term');
    }

    const blob = await downloadClassPDFs(selectedClass, selectedTerm);
    
    // Generate filename
    const classLevel = classLevels.find(c => c.id === parseInt(selectedClass));
    const term = terms.find(t => t.id === parseInt(selectedTerm));
    const filename = `${classLevel?.name}_${term?.name}_${term?.academic_year}_Reports.pdf`.replace(/\s+/g, '_');
    
    triggerDownload(blob, filename);
  };

  // Sort reports
  const getSortedReports = () => {
    if (!reportsData || !reportsData.reports) {
      return [];
    }

    const sorted = [...reportsData.reports].sort((a, b) => {
      if (sortBy === 'name') {
        const nameA = a.student.name.toLowerCase();
        const nameB = b.student.name.toLowerCase();
        return sortOrder === 'asc' 
          ? nameA.localeCompare(nameB)
          : nameB.localeCompare(nameA);
      } else if (sortBy === 'average') {
        const avgA = a.term_average || 0;
        const avgB = b.term_average || 0;
        return sortOrder === 'asc' 
          ? avgA - avgB
          : avgB - avgA;
      }
      return 0;
    });

    return sorted;
  };

  const toggleSort = (field) => {
    if (sortBy === field) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(field);
      setSortOrder('asc');
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-lg text-gray-600">Loading...</div>
      </div>
    );
  }

  const sortedReports = getSortedReports();

  return (
    <div className="max-w-7xl mx-auto">
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 className="text-3xl font-bold text-primary-900 mb-6">Class Term Reports</h1>

        {/* Error Message */}
        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {/* Selection Filters */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          {/* Class Level Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Class Level
            </label>
            <select
              value={selectedClass}
              onChange={(e) => setSelectedClass(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">-- Select Class --</option>
              {classLevels.map((classLevel) => (
                <option key={classLevel.id} value={classLevel.id}>
                  {classLevel.name}
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

          {/* Sort Options */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Sort By
            </label>
            <div className="flex gap-2">
              <button
                onClick={() => toggleSort('name')}
                className={`flex-1 px-4 py-2 rounded-lg transition duration-200 font-medium ${
                  sortBy === 'name'
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                }`}
              >
                Name {sortBy === 'name' && (sortOrder === 'asc' ? '↑' : '↓')}
              </button>
              <button
                onClick={() => toggleSort('average')}
                className={`flex-1 px-4 py-2 rounded-lg transition duration-200 font-medium ${
                  sortBy === 'average'
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                }`}
              >
                Average {sortBy === 'average' && (sortOrder === 'asc' ? '↑' : '↓')}
              </button>
            </div>
          </div>

          {/* Batch PDF Export Button */}
          <div className="flex items-end">
            <PDFExportButton
              onExport={handleDownloadPDFs}
              disabled={!selectedClass || !selectedTerm || !reportsData}
              label="Export All PDFs"
              loadingLabel="Generating PDFs..."
              variant="primary"
              size="medium"
              fullWidth={true}
            />
          </div>
        </div>

        {/* Summary Stats */}
        {reportsData && sortedReports.length > 0 && (
          <div className="bg-primary-50 rounded-lg p-4 mb-6">
            <div className="grid grid-cols-3 gap-4 text-center">
              <div>
                <p className="text-sm text-gray-600">Total Students</p>
                <p className="text-2xl font-bold text-primary-900">{sortedReports.length}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Class</p>
                <p className="text-2xl font-bold text-primary-900">{reportsData.class_level?.name}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Term</p>
                <p className="text-2xl font-bold text-primary-900">
                  {reportsData.term?.name} - {reportsData.term?.academic_year}
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Loading State */}
        {loadingReports && (
          <div className="flex justify-center items-center h-32">
            <div className="text-lg text-gray-600">Loading reports...</div>
          </div>
        )}

        {/* Reports Display */}
        {!loadingReports && sortedReports.length > 0 && (
          <div className="space-y-8">
            {sortedReports.map((report, index) => (
              <div key={report.student.student_id} className="print:break-after-page">
                <ReportCard
                  student={report.student}
                  term={reportsData.term}
                  assessments={report.assessments}
                  termAverage={report.term_average}
                />
              </div>
            ))}
          </div>
        )}

        {/* No Reports Message */}
        {!loadingReports && reportsData && sortedReports.length === 0 && (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <p className="text-gray-600">No reports available for the selected class and term.</p>
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
        {!selectedClass || !selectedTerm ? (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <p className="text-gray-600">
              {classLevels.length === 0 && !isAdmin() && assignments && assignments.classes.length === 0
                ? 'No classes available. You need class assignments to view reports.'
                : 'Please select a class level and term to view reports.'}
            </p>
          </div>
        ) : null}
      </div>
    </div>
  );
};

export default ClassTermReports;
