import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getStudentGrades } from '../services/api';

const GradeList = () => {
  const { studentId } = useParams();
  const navigate = useNavigate();

  const [student, setStudent] = useState(null);
  const [grades, setGrades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadGrades();
  }, [studentId]);

  const loadGrades = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getStudentGrades(studentId);
      setStudent(data.student);
      setGrades(data.grades);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleBack = () => {
    navigate('/students');
  };

  const handleAddGrade = () => {
    navigate('/grades/add');
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Loading grades...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
        <button
          onClick={handleBack}
          className="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition"
        >
          Back to Students
        </button>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      {/* Header */}
      <div className="mb-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-2xl font-bold text-gray-800">Student Grades</h2>
          <button
            onClick={handleAddGrade}
            className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition"
          >
            Add Grade
          </button>
        </div>
        
        {student && (
          <div className="bg-gray-50 p-4 rounded">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <span className="text-sm font-medium text-gray-600">Student ID:</span>
                <p className="text-lg font-semibold text-gray-900">{student.student_id}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-600">Name:</span>
                <p className="text-lg font-semibold text-gray-900">{student.name}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-600">Class Level:</span>
                <p className="text-lg font-semibold text-gray-900">{student.class_level_name}</p>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Grades Table */}
      {grades.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-gray-400 mb-4">
            <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p className="text-gray-500 text-lg mb-4">No grades recorded yet</p>
          <button
            onClick={handleAddGrade}
            className="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded transition"
          >
            Add First Grade
          </button>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Subject
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Mark
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date Recorded
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {grades.map(grade => (
                <tr key={grade.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {grade.subject_name}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ${
                      grade.mark >= 70 ? 'bg-primary-100 text-primary-800' :
                      grade.mark >= 50 ? 'bg-secondary-100 text-secondary-800' :
                      'bg-red-100 text-red-800'
                    }`}>
                      {grade.mark}%
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(grade.created_at).toLocaleDateString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Summary */}
          <div className="mt-6 p-4 bg-primary-50 rounded">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <span className="text-sm font-medium text-gray-600">Total Subjects:</span>
                <p className="text-xl font-bold text-gray-900">{grades.length}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-600">Average Mark:</span>
                <p className="text-xl font-bold text-gray-900">
                  {(grades.reduce((sum, g) => sum + parseFloat(g.mark), 0) / grades.length).toFixed(2)}%
                </p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-600">Highest Mark:</span>
                <p className="text-xl font-bold text-gray-900">
                  {Math.max(...grades.map(g => parseFloat(g.mark)))}%
                </p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Back Button */}
      <div className="mt-6">
        <button
          onClick={handleBack}
          className="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition"
        >
          Back to Students
        </button>
      </div>
    </div>
  );
};

export default GradeList;
