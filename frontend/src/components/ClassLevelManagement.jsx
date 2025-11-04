import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { getAllClassLevels, createClassLevel, deleteClassLevel } from '../services/api';
import DataTable from './DataTable';

const ClassLevelManagement = () => {
  const [classLevels, setClassLevels] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [newClassLevelName, setNewClassLevelName] = useState('');
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
    loadClassLevels();
  }, []);

  const loadClassLevels = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getAllClassLevels();
      setClassLevels(data);
    } catch (err) {
      setError(err.message || 'Failed to load class levels');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFormError('');

    // Validation
    if (!newClassLevelName.trim()) {
      setFormError('Class level name is required');
      return;
    }

    if (newClassLevelName.length > 50) {
      setFormError('Class level name must not exceed 50 characters');
      return;
    }

    setSubmitting(true);

    try {
      await createClassLevel({ name: newClassLevelName.trim() });
      setNewClassLevelName('');
      setShowForm(false);
      loadClassLevels();
    } catch (err) {
      setFormError(err.message || 'Failed to create class level');
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
      await deleteClassLevel(id);
      loadClassLevels();
    } catch (err) {
      setError(err.message || 'Failed to delete class level');
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-6xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Class Level Management</h1>
            <button
              onClick={() => setShowForm(!showForm)}
              className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
            >
              {showForm ? 'Cancel' : 'Add New Class Level'}
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
                <h3 className="text-lg font-semibold text-gray-800">Create New Class Level</h3>

                {formError && (
                  <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                    {formError}
                  </div>
                )}

                <div>
                  <label htmlFor="classLevelName" className="block text-sm font-medium text-gray-700 mb-1">
                    Class Level Name *
                  </label>
                  <input
                    id="classLevelName"
                    type="text"
                    value={newClassLevelName}
                    onChange={(e) => {
                      setNewClassLevelName(e.target.value);
                      setFormError('');
                    }}
                    disabled={submitting}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Enter class level name (e.g., Grade 10)"
                  />
                </div>

                <div className="flex gap-3">
                  <button
                    type="submit"
                    disabled={submitting}
                    className="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                  >
                    {submitting ? 'Creating...' : 'Create Class Level'}
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setShowForm(false);
                      setNewClassLevelName('');
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
              <p className="mt-2 text-gray-600">Loading class levels...</p>
            </div>
          ) : classLevels.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No class levels found. Click "Add New Class Level" to create one.
            </div>
          ) : (
            <DataTable
              data={classLevels}
              columns={[
                {
                  title: 'ID',
                  data: 'id',
                },
                {
                  title: 'Class Level Name',
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
              tableId="classLevelsTable"
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

export default ClassLevelManagement;
