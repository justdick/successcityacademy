import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getAllClassLevels } from '../services/api';
import { getActiveTerms } from '../services/termService';
import {
  getAssessmentSummary,
  getStatusColorClass,
  getStatusIcon
} from '../services/assessmentService';

const AssessmentSummary = () => {
  const navigate = useNavigate();
  const [classLevels, setClassLevels] = useState([]);
  const [terms, setTerms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [summaryLoading, setSummaryLoading] = useState(false);
  const [error, setError] = useState('');

  // Filter state
  const [selectedClassLevel, setSelectedClassLevel] = useState('');
  const [selectedTerm, setSelectedTerm] = useState('');

  // Summary data
  const [summaryData, setSummaryData] = useState(null);

  // Load initial data
  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      setError('');

      const [classLevelsData, termsData] = await Promise.all([
        getAllClassLevels(),
        getActiveTerms()
      ]);

      setClassLevels(classLevelsData);
      setTerms(termsData);

      // Auto-select first class level and term if available
      if (classLevelsData.length > 0) {
        setSelectedClassLevel(classLevelsData[0].id.toString());
      }
      if (termsData.length > 0) {
        setSelectedTerm(termsData[0].id.toString());
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Load summary when filters change
  useEffect(() => {
    if (selectedClassLevel && selectedTerm) {
      loadSummary();
    }
  }, [selectedClassLevel, selectedTerm]);

  const loadSummary = async () => {
    try {
      setSummaryLoading(true);
      setError('');

      const data = await getAssessmentSummary(selectedTerm, selectedClassLevel);
      setSummaryData(data);
    } catch (err) {
      setError(err.message);
      setSummaryData(null);
    } finally {
      setSummaryLoading(false);
    }
  };

  // Handle cell click - navigate to assessment entry
  const handleCellClick = (studentId, subjectId) => {
    // Navigate to assessment entry with pre-filled data
    navigate('/assessments/entry', {
      state: {
        studentId,
        subjectId,
        termId: selectedTerm
      }
    });
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-lg text-gray-600">Loading...</div>
      </div>
    );
  }

  return (
    <div className="max-w-full mx-auto">
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6">Assessment Summary Dashboard</h2>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {/* Filters */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          {/* Class Level Filter */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Class Level <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedClassLevel}
              onChange={(e) => setSelectedClassLevel(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Select Class Level</option>
              {classLevels.map((classLevel) => (
                <option key={classLevel.id} value={classLevel.id}>
                  {classLevel.name}
                </option>
              ))}
            </select>
          </div>

          {/* Term Filter */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Term <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedTerm}
              onChange={(e) => setSelectedTerm(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Select Term</option>
              {terms.map((term) => (
                <option key={term.id} value={term.id}>
                  {term.name} - {term.academic_year}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Summary Statistics */}
        {summaryData && (
          <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <div className="text-sm text-primary-600 font-medium">Total Students</div>
              <div className="text-2xl font-bold text-primary-900">
                {summaryData.summary.total_students}
              </div>
            </div>
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <div className="text-sm text-primary-600 font-medium">Total Subjects</div>
              <div className="text-2xl font-bold text-primary-900">
                {summaryData.summary.total_subjects}
              </div>
            </div>
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <div className="text-sm text-primary-600 font-medium">Complete</div>
              <div className="text-2xl font-bold text-primary-900">
                {summaryData.summary.complete_assessments}
              </div>
            </div>
            <div className="bg-secondary-50 border border-secondary-200 rounded-lg p-4">
              <div className="text-sm text-secondary-600 font-medium">Partial</div>
              <div className="text-2xl font-bold text-secondary-900">
                {summaryData.summary.partial_assessments}
              </div>
            </div>
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <div className="text-sm text-red-600 font-medium">Missing</div>
              <div className="text-2xl font-bold text-red-900">
                {summaryData.summary.missing_assessments}
              </div>
            </div>
          </div>
        )}

        {/* Completion Percentage */}
        {summaryData && (
          <div className="mb-6">
            <div className="flex justify-between items-center mb-2">
              <span className="text-sm font-medium text-gray-700">Overall Completion</span>
              <span className="text-sm font-bold text-gray-900">
                {summaryData.summary.completion_percentage}%
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-4">
              <div
                className="bg-gradient-to-r from-primary-500 to-primary-600 h-4 rounded-full transition-all duration-500"
                style={{ width: `${summaryData.summary.completion_percentage}%` }}
              ></div>
            </div>
          </div>
        )}

        {/* Loading State */}
        {summaryLoading && (
          <div className="flex justify-center items-center h-64">
            <div className="text-lg text-gray-600">Loading summary...</div>
          </div>
        )}

        {/* Assessment Grid */}
        {summaryData && !summaryLoading && (
          <div className="overflow-x-auto">
            <div className="mb-4">
              <h3 className="text-lg font-semibold text-gray-800 mb-2">Assessment Status Grid</h3>
              <div className="flex gap-4 text-sm text-gray-600">
                <div className="flex items-center gap-2">
                  <span className="inline-block w-4 h-4 bg-primary-100 border border-primary-300 rounded"></span>
                  <span>Complete (CA + Exam)</span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="inline-block w-4 h-4 bg-secondary-100 border border-secondary-300 rounded"></span>
                  <span>Partial (CA or Exam)</span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="inline-block w-4 h-4 bg-red-100 border border-red-300 rounded"></span>
                  <span>Missing</span>
                </div>
              </div>
            </div>

            <table className="min-w-full border-collapse border border-gray-300">
              <thead>
                <tr className="bg-gray-100">
                  <th className="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700 sticky left-0 bg-gray-100 z-10">
                    Student
                  </th>
                  {summaryData.grid.length > 0 &&
                    summaryData.grid[0].subjects.map((subject) => (
                      <th
                        key={subject.subject_id}
                        className="border border-gray-300 px-2 py-2 text-center font-semibold text-gray-700 min-w-[100px]"
                      >
                        <div className="text-xs">{subject.subject_name}</div>
                      </th>
                    ))}
                </tr>
              </thead>
              <tbody>
                {summaryData.grid.map((studentRow) => (
                  <tr key={studentRow.student_id} className="hover:bg-gray-50">
                    <td className="border border-gray-300 px-4 py-2 font-medium text-gray-800 sticky left-0 bg-white z-10">
                      <div className="text-sm">{studentRow.student_name}</div>
                      <div className="text-xs text-gray-500">{studentRow.student_id}</div>
                    </td>
                    {studentRow.subjects.map((subject) => (
                      <td
                        key={subject.subject_id}
                        className={`border border-gray-300 px-2 py-2 text-center cursor-pointer hover:opacity-80 transition-opacity ${getStatusColorClass(
                          subject.status
                        )}`}
                        onClick={() => handleCellClick(studentRow.student_id, subject.subject_id)}
                        title={`Click to ${subject.status === 'missing' ? 'add' : 'edit'} assessment`}
                      >
                        <div className="text-2xl">{getStatusIcon(subject.status)}</div>
                        <div className="text-xs mt-1">
                          {subject.has_ca && <span className="font-semibold">CA</span>}
                          {subject.has_ca && subject.has_exam && <span> + </span>}
                          {subject.has_exam && <span className="font-semibold">Ex</span>}
                          {!subject.has_ca && !subject.has_exam && <span>-</span>}
                        </div>
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>

            {summaryData.grid.length === 0 && (
              <div className="text-center py-8 text-gray-500">
                No students found in this class
              </div>
            )}
          </div>
        )}

        {/* No Data State */}
        {!summaryData && !summaryLoading && selectedClassLevel && selectedTerm && (
          <div className="text-center py-8 text-gray-500">
            No assessment data available for the selected class and term
          </div>
        )}

        {/* Instructions */}
        {summaryData && (
          <div className="mt-6 bg-primary-50 border border-primary-200 rounded-lg p-4">
            <h4 className="font-semibold text-primary-900 mb-2">How to use this dashboard:</h4>
            <ul className="text-sm text-primary-800 space-y-1">
              <li>• Click on any cell to navigate to the assessment entry form for that student and subject</li>
              <li>• Green cells indicate both CA and Exam marks are entered</li>
              <li>• Yellow cells indicate only one mark (CA or Exam) is entered</li>
              <li>• Red cells indicate no marks have been entered yet</li>
            </ul>
          </div>
        )}
      </div>
    </div>
  );
};

export default AssessmentSummary;
