/**
 * ReportCard Component
 * 
 * Reusable report card component that displays a student's term report
 * with student information, assessment table, and term average.
 * Designed to be print-friendly.
 * 
 * @param {object} student - Student information {student_id, name, class_level_name}
 * @param {object} term - Term information {name, academic_year}
 * @param {Array} assessments - Array of assessment objects
 * @param {number} termAverage - Calculated term average
 */
const ReportCard = ({ student, term, assessments, termAverage }) => {
  return (
    <div className="bg-white rounded-lg shadow-lg p-8 print:shadow-none print:p-4">
      {/* Header Section */}
      <div className="border-b-2 border-primary-600 pb-4 mb-6">
        {/* School Logo */}
        <div className="flex justify-center mb-4">
          <img 
            src="/assets/sca.jpg" 
            alt="Success City Academy Logo" 
            className="h-20 w-auto object-contain"
          />
        </div>
        
        <h2 className="text-2xl font-bold text-primary-900 text-center mb-1">
          Success City Academy
        </h2>
        <h3 className="text-xl font-semibold text-primary-700 text-center mb-2">
          Student Term Report
        </h3>
        <div className="text-center text-gray-600">
          <p className="text-lg font-semibold">{term?.name} - {term?.academic_year}</p>
        </div>
      </div>

      {/* Student Information Section */}
      <div className="grid grid-cols-2 gap-4 mb-6 bg-primary-50 p-4 rounded-lg border border-primary-200">
        <div>
          <p className="text-sm text-primary-700">Student ID</p>
          <p className="font-semibold text-gray-900">{student?.student_id}</p>
        </div>
        <div>
          <p className="text-sm text-primary-700">Student Name</p>
          <p className="font-semibold text-gray-900">{student?.name}</p>
        </div>
        <div>
          <p className="text-sm text-primary-700">Class Level</p>
          <p className="font-semibold text-gray-900">{student?.class_level_name}</p>
        </div>
        <div>
          <p className="text-sm text-primary-700">Term Average</p>
          <p className="font-bold text-2xl text-secondary-600">{termAverage?.toFixed(2)}%</p>
        </div>
      </div>

      {/* Assessment Table */}
      <div className="mb-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-3">Subject Assessments</h3>
        
        {assessments && assessments.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="min-w-full border-collapse border border-gray-300">
              <thead>
                <tr className="bg-primary-100">
                  <th className="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">
                    Subject
                  </th>
                  <th className="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-900">
                    CA Mark<br />
                    <span className="text-xs font-normal text-gray-600">(Max)</span>
                  </th>
                  <th className="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-900">
                    Exam Mark<br />
                    <span className="text-xs font-normal text-gray-600">(Max)</span>
                  </th>
                  <th className="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-900">
                    Final Mark<br />
                    <span className="text-xs font-normal text-gray-600">(100)</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {assessments.map((assessment, index) => (
                  <tr key={index} className="hover:bg-gray-50">
                    <td className="border border-gray-300 px-4 py-2 font-medium text-gray-900">
                      {assessment.subject_name}
                    </td>
                    <td className="border border-gray-300 px-4 py-2 text-center">
                      <span className="font-semibold">
                        {assessment.ca_mark !== null ? parseFloat(assessment.ca_mark).toFixed(2) : '-'}
                      </span>
                      <span className="text-xs text-gray-600 ml-1">
                        / {parseFloat(assessment.ca_percentage).toFixed(0)}
                      </span>
                    </td>
                    <td className="border border-gray-300 px-4 py-2 text-center">
                      <span className="font-semibold">
                        {assessment.exam_mark !== null ? parseFloat(assessment.exam_mark).toFixed(2) : '-'}
                      </span>
                      <span className="text-xs text-gray-600 ml-1">
                        / {parseFloat(assessment.exam_percentage).toFixed(0)}
                      </span>
                    </td>
                    <td className="border border-gray-300 px-4 py-2 text-center font-bold text-secondary-600">
                      {parseFloat(assessment.final_mark).toFixed(2)}
                    </td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr className="bg-secondary-50">
                  <td colSpan="3" className="border border-gray-300 px-4 py-3 text-right font-bold text-gray-900">
                    Term Average:
                  </td>
                  <td className="border border-gray-300 px-4 py-3 text-center font-bold text-xl text-secondary-600">
                    {termAverage?.toFixed(2)}%
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        ) : (
          <div className="text-center py-8 bg-gray-50 rounded-lg">
            <p className="text-gray-600">No assessment data available for this term.</p>
          </div>
        )}
      </div>

      {/* Footer */}
      <div className="text-center text-sm text-gray-500 mt-8 pt-4 border-t border-gray-200">
        <p>Generated on {new Date().toLocaleDateString()}</p>
      </div>
    </div>
  );
};

export default ReportCard;
