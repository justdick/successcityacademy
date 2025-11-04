import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { getMyAssignments } from '../services/teacherAssignmentService';

const MyAssignments = () => {
  const { user, isAdmin } = useAuth();
  
  // State for data
  const [assignments, setAssignments] = useState({ classes: [], subjects: [] });
  
  // State for UI
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadMyAssignments();
  }, []);

  const loadMyAssignments = async () => {
    try {
      setLoading(true);
      setError('');
      
      const data = await getMyAssignments();
      setAssignments(data);
    } catch (err) {
      setError(err.message || 'Failed to load assignments');
    } finally {
      setLoading(false);
    }
  };

  const hasNoAssignments = assignments.classes.length === 0 && assignments.subjects.length === 0;

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-7xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          {/* Header */}
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-gray-800">My Teaching Assignments</h1>
            <p className="text-gray-600 mt-1">
              Welcome, {user?.username}! Here are your assigned classes and subjects.
            </p>
          </div>

          {/* Messages */}
          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
              {error}
            </div>
          )}

          {/* Loading State */}
          {loading ? (
            <div className="text-center py-12">
              <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
              <p className="mt-4 text-gray-600">Loading your assignments...</p>
            </div>
          ) : hasNoAssignments ? (
            /* No Assignments Message */
            <div className="text-center py-12">
              <svg
                className="mx-auto h-16 w-16 text-gray-400 mb-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <h3 className="text-lg font-medium text-gray-900 mb-2">No Assignments Yet</h3>
              <p className="text-gray-600 mb-4">
                You have not been assigned to any classes or subjects yet.
              </p>
              <p className="text-sm text-gray-500">
                Please contact your administrator to get assigned to classes and subjects.
              </p>
            </div>
          ) : (
            /* Assignments Display */
            <div className="space-y-6">
              {/* My Classes Section */}
              <div>
                <h2 className="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                  <svg
                    className="w-6 h-6 mr-2 text-primary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                  </svg>
                  My Classes
                </h2>
                {assignments.classes.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {assignments.classes.map((classLevel) => (
                      <div
                        key={classLevel.id}
                        className="bg-gradient-to-br from-primary-50 to-primary-100 border border-primary-200 rounded-lg p-5 hover:shadow-md transition-shadow"
                      >
                        <h3 className="text-lg font-semibold text-primary-900 mb-2">
                          {classLevel.name}
                        </h3>
                        <div className="flex items-center text-primary-700 mb-4">
                          <svg
                            className="w-5 h-5 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                          </svg>
                          <span className="text-sm font-medium">
                            {classLevel.student_count || 0} {classLevel.student_count === 1 ? 'student' : 'students'}
                          </span>
                        </div>
                        <Link
                          to={`/students?class=${classLevel.id}`}
                          className="inline-block bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors text-sm font-medium"
                        >
                          View Students
                        </Link>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-600">
                    No classes assigned yet
                  </div>
                )}
              </div>

              {/* My Subjects Section */}
              <div>
                <h2 className="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                  <svg
                    className="w-6 h-6 mr-2 text-secondary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                  </svg>
                  My Subjects
                </h2>
                {assignments.subjects.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    {assignments.subjects.map((subject) => (
                      <div
                        key={subject.id}
                        className="bg-gradient-to-br from-secondary-50 to-secondary-100 border border-secondary-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow"
                      >
                        <p className="text-secondary-900 font-semibold">{subject.name}</p>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-600">
                    No subjects assigned yet
                  </div>
                )}
              </div>

              {/* Quick Actions */}
              {!hasNoAssignments && (
                <div className="mt-8 pt-6 border-t border-gray-200">
                  <h3 className="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link
                      to="/students"
                      className="flex items-center justify-center bg-white border-2 border-primary-200 rounded-lg p-4 hover:border-primary-400 hover:shadow-md transition-all"
                    >
                      <svg
                        className="w-6 h-6 mr-3 text-primary-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                      </svg>
                      <span className="font-medium text-gray-800">View My Students</span>
                    </Link>
                    <Link
                      to="/assessments/entry"
                      className="flex items-center justify-center bg-white border-2 border-secondary-200 rounded-lg p-4 hover:border-secondary-400 hover:shadow-md transition-all"
                    >
                      <svg
                        className="w-6 h-6 mr-3 text-secondary-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                        />
                      </svg>
                      <span className="font-medium text-gray-800">Enter Assessments</span>
                    </Link>
                    <Link
                      to="/reports/student"
                      className="flex items-center justify-center bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-gray-400 hover:shadow-md transition-all"
                    >
                      <svg
                        className="w-6 h-6 mr-3 text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                      </svg>
                      <span className="font-medium text-gray-800">Generate Reports</span>
                    </Link>
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default MyAssignments;
