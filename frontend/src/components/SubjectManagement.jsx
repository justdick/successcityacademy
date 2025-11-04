import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { getAllSubjects, createSubject, deleteSubject } from '../services/api';
import DataTable from './DataTable';

const SubjectManagement = () => {
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [newSubjectName, setNewSubjectName] = useState('');
  const [formError, setFormError] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const { isAdmin } = useAuth();

  // Redirect if not admin
  if (!isAdmin()) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="bg-white p-8 rounded-lg shadow-md">
          <h2 className="text-xl font-bold text-red-600 mb-4">Access Denied</h2>
          <p className="text-gray-700">You do not have permission to access this page.</p>
        </div>
      </div>
    );
  }

  useEffect(() => {
    loadSubjects();
  }, []);

  const loadSubjects = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getAllSubjects();
      setSubjects(data);
    } catch (err) {
      setError(err.message || 'Failed to load subjects');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFormError('');

    // Validation
    if (!newSubjectName.trim()) {
      setFormError('Subject name is required');
      return;
    }

    if (newSubjectName.length > 255) {
      setFormError('Subject name must not exceed 255 characters');
      return;
    }

    setSubmitting(true);

    try {
      await createSubject({ name: newSubjectName.trim() });
      setNewSubjectName('');
      setShowForm(false);
      loadSubjects();
    } catch (err) {
      setFormError(err.message || 'Failed to create subject');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id, name) => {
    if (!window.confirm(`Are you sure you want to delete "${name}"?`)) {
      return;
    }

    setDeletingId(id);
    setError('');

    try {
      await deleteSubject(id);
      loadSubjects();
    } catch (err) {
      setError(err.message || 'Failed to delete subject');
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-6xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Subject Management</h1>
            <button
              onClick={() => setShowForm(!showForm)}
              className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
            >
              {showForm ? 'Cancel' : 'Add New Subject'}
            </button>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
              {error}
            </div>
          )}

          {showForm && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <form onSubmit={handleSubmit} className="space-y-4">
                <h3 className="text-lg font-semibold text-gray-800">Create New Subject</h3>

                {formError && (
                  <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                    {formError}
                  </div>
                )}

                <div>
                  <label htmlFor="subjectName" className="block text-sm font-medium text-gray-700 mb-1">
                    Subject Name *
                  </label>
                  <input
                    id="subjectName"
                    type="text"
                    value={newSubjectName}
                    onChange={(e) => {
                      setNewSubjectName(e.target.value);
                      setFormError('');
                    }}
                    disabled={submitting}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Enter subject name"
                  />
                </div>

                <div className="flex gap-3">
                  <button
                    type="submit"
                    disabled={submitting}
                    className="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                  >
                    {submitting ? 'Creating...' : 'Create Subject'}
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setShowForm(false);
                      setNewSubjectName('');
                      setFormError('');
                    }}
                    disabled={submitting}
                    className="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          )}

          {loading ? (
            <div className="text-center py-8">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              <p className="mt-2 text-gray-600">Loading subjects...</p>
            </div>
          ) : subjects.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No subjects found. Click "Add New Subject" to create one.
            </div>
          ) : (
            <DataTable
              data={subjects}
              columns={[
                {
                  title: 'ID',
                  data: 'id',
                },
                {
                  title: 'Subject Name',
                  data: 'name',
                  render: (row) => (
                    <span className="font-medium">{row.name}</span>
                  ),
                },
                {
                  title: 'Created At',
                  data: 'created_at',
                  render: (row) => new Date(row.created_at).toLocaleString(),
                },
                {
                  title: 'Actions',
                  data: null,
                  render: (row) => (
                    <div className="text-right">
                      <button
                        onClick={() => handleDelete(row.id, row.name)}
                        disabled={deletingId === row.id}
                        className="text-red-600 hover:text-red-900 disabled:text-red-300 disabled:cursor-not-allowed transition-colors"
                      >
                        {deletingId === row.id ? 'Deleting...' : 'Delete'}
                      </button>
                    </div>
                  ),
                },
              ]}
              tableId="subjectsTable"
              options={{
                order: [[1, 'asc']],
                pageLength: 10,
              }}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default SubjectManagement;
