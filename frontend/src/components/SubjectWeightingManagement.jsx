import { useState, useEffect } from 'react';
import { getAllSubjects } from '../services/api';
import { getWeightings, updateWeighting } from '../services/weightingService';
import WeightingForm from './WeightingForm';
import DataTable from './DataTable';

/**
 * SubjectWeightingManagement Component
 * 
 * Admin interface for managing subject weightings (CA and Exam percentages)
 * Features:
 * - Display subjects with current weightings
 * - Show CA and exam percentages for each subject
 * - Edit functionality for each subject
 * - Display default weighting indicator (40% CA, 60% Exam)
 * - Styled with Tailwind CSS
 */
const SubjectWeightingManagement = () => {
  const [subjects, setSubjects] = useState([]);
  const [weightings, setWeightings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  // Modal state
  const [showModal, setShowModal] = useState(false);
  const [editingSubject, setEditingSubject] = useState(null);

  // Default weighting values
  const DEFAULT_CA_PERCENTAGE = 40.00;
  const DEFAULT_EXAM_PERCENTAGE = 60.00;

  useEffect(() => {
    fetchData();
  }, []);

  /**
   * Fetch subjects and weightings from API
   */
  const fetchData = async () => {
    try {
      setLoading(true);
      setError('');
      
      const [subjectsData, weightingsData] = await Promise.all([
        getAllSubjects(),
        getWeightings()
      ]);
      
      setSubjects(subjectsData);
      setWeightings(weightingsData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Get weighting for a specific subject
   * Returns default if not configured
   */
  const getSubjectWeighting = (subjectId) => {
    const weighting = weightings.find(w => w.subject_id === subjectId);
    
    if (weighting) {
      return {
        ca_percentage: weighting.ca_percentage,
        exam_percentage: weighting.exam_percentage,
        is_default: false
      };
    }
    
    return {
      ca_percentage: DEFAULT_CA_PERCENTAGE,
      exam_percentage: DEFAULT_EXAM_PERCENTAGE,
      is_default: true
    };
  };

  /**
   * Handle edit weighting
   */
  const handleEdit = (subject) => {
    const weighting = getSubjectWeighting(subject.id);
    setEditingSubject({
      ...subject,
      ca_percentage: weighting.ca_percentage,
      exam_percentage: weighting.exam_percentage
    });
    setShowModal(true);
  };

  /**
   * Handle form submission
   */
  const handleSubmit = async (formData) => {
    try {
      setError('');
      setSuccess('');

      await updateWeighting(editingSubject.id, formData);
      setSuccess(`Weighting updated successfully for ${editingSubject.name}`);

      setShowModal(false);
      setEditingSubject(null);
      await fetchData();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      throw err; // Let WeightingForm handle the error display
    }
  };

  /**
   * Close modal
   */
  const handleCloseModal = () => {
    setShowModal(false);
    setEditingSubject(null);
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Loading subject weightings...</div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <div className="bg-white rounded-xl shadow-lg p-6">
        {/* Header */}
        <div className="mb-6">
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Subject Weighting Configuration</h2>
          <p className="text-sm text-gray-600">
            Configure the percentage split between Continuous Assessment (CA) and Exam marks for each subject.
            The total must equal 100%.
          </p>
        </div>

        {/* Success Message */}
        {success && (
          <div className="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {success}
          </div>
        )}

        {/* Error Message */}
        {error && (
          <div className="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        {/* Default Weighting Info */}
        <div className="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <svg className="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-blue-800">Default Weighting</h3>
              <p className="mt-1 text-sm text-blue-700">
                Subjects without custom configuration use the default weighting: {DEFAULT_CA_PERCENTAGE}% CA, {DEFAULT_EXAM_PERCENTAGE}% Exam
              </p>
            </div>
          </div>
        </div>

        {/* Subjects Table */}
        {subjects.length === 0 ? (
          <div className="text-center py-12 text-gray-500">
            <p className="text-lg">No subjects found</p>
            <p className="text-sm mt-2">Create subjects first to configure weightings</p>
          </div>
        ) : (
          <DataTable
            data={subjects.map(subject => ({
              ...subject,
              ...getSubjectWeighting(subject.id)
            }))}
            columns={[
              {
                title: 'Subject Name',
                data: 'name',
                render: (row) => (
                  <span className="font-medium">{row.name}</span>
                ),
              },
              {
                title: 'CA Percentage',
                data: 'ca_percentage',
                render: (row) => (
                  <div className="text-center">
                    <span className="text-sm font-semibold text-primary-600">
                      {row.ca_percentage}%
                    </span>
                  </div>
                ),
              },
              {
                title: 'Exam Percentage',
                data: 'exam_percentage',
                render: (row) => (
                  <div className="text-center">
                    <span className="text-sm font-semibold text-primary-600">
                      {row.exam_percentage}%
                    </span>
                  </div>
                ),
              },
              {
                title: 'Status',
                data: 'is_default',
                render: (row) => (
                  <div className="text-center">
                    {row.is_default ? (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Default
                      </span>
                    ) : (
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        Custom
                      </span>
                    )}
                  </div>
                ),
              },
              {
                title: 'Actions',
                data: null,
                render: (row) => (
                  <div className="text-center">
                    <button
                      onClick={() => handleEdit(row)}
                      className="text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-200"
                    >
                      Edit
                    </button>
                  </div>
                ),
              },
            ]}
            tableId="subjectWeightingsTable"
            options={{
              order: [[0, 'asc']],
              pageLength: 10,
            }}
          />
        )}

        {/* Summary */}
        {subjects.length > 0 && (
          <div className="mt-6 pt-4 border-t border-gray-200">
            <div className="flex justify-between text-sm text-gray-600">
              <span>Total Subjects: {subjects.length}</span>
              <span>
                Custom Weightings: {weightings.length} | 
                Default Weightings: {subjects.length - weightings.length}
              </span>
            </div>
          </div>
        )}
      </div>

      {/* Modal for Edit Weighting */}
      {showModal && editingSubject && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 className="text-xl font-bold text-gray-800 mb-2">
              Edit Weighting: {editingSubject.name}
            </h3>
            <p className="text-sm text-gray-600 mb-4">
              Configure the percentage split between CA and Exam marks
            </p>
            <WeightingForm
              weighting={editingSubject}
              onSubmit={handleSubmit}
              onCancel={handleCloseModal}
            />
          </div>
        </div>
      )}
    </div>
  );
};

export default SubjectWeightingManagement;
