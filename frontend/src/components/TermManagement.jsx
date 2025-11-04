import { useState, useEffect } from 'react';
import { getAllTerms, createTerm, updateTerm, deleteTerm } from '../services/termService';
import TermForm from './TermForm';

/**
 * TermManagement Component
 * 
 * Admin interface for managing academic terms
 * Features:
 * - Display list of all terms
 * - Create new terms
 * - Edit existing terms
 * - Delete terms
 * - Toggle term active/inactive status
 * - Sort by academic year
 */
const TermManagement = () => {
  const [terms, setTerms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  // Modal state
  const [showModal, setShowModal] = useState(false);
  const [editingTerm, setEditingTerm] = useState(null);
  
  // Sorting state
  const [sortOrder, setSortOrder] = useState('desc'); // 'asc' or 'desc'

  useEffect(() => {
    fetchTerms();
  }, []);

  /**
   * Fetch all terms from API
   */
  const fetchTerms = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getAllTerms();
      setTerms(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Sort terms by academic year
   */
  const sortedTerms = [...terms].sort((a, b) => {
    const yearA = a.academic_year.split('/')[0];
    const yearB = b.academic_year.split('/')[0];
    
    if (sortOrder === 'asc') {
      return yearA.localeCompare(yearB) || a.name.localeCompare(b.name);
    } else {
      return yearB.localeCompare(yearA) || b.name.localeCompare(a.name);
    }
  });

  /**
   * Toggle sort order
   */
  const toggleSortOrder = () => {
    setSortOrder(prev => prev === 'asc' ? 'desc' : 'asc');
  };

  /**
   * Handle create new term
   */
  const handleCreate = () => {
    setEditingTerm(null);
    setShowModal(true);
  };

  /**
   * Handle edit term
   */
  const handleEdit = (term) => {
    setEditingTerm(term);
    setShowModal(true);
  };

  /**
   * Handle form submission (create or update)
   */
  const handleSubmit = async (formData) => {
    try {
      setError('');
      setSuccess('');

      if (editingTerm) {
        await updateTerm(editingTerm.id, formData);
        setSuccess('Term updated successfully');
      } else {
        await createTerm(formData);
        setSuccess('Term created successfully');
      }

      setShowModal(false);
      setEditingTerm(null);
      await fetchTerms();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      throw err; // Let TermForm handle the error display
    }
  };

  /**
   * Handle delete term
   */
  const handleDelete = async (term) => {
    if (!window.confirm(`Are you sure you want to delete "${term.name} (${term.academic_year})"? This action cannot be undone.`)) {
      return;
    }

    try {
      setError('');
      setSuccess('');
      await deleteTerm(term.id);
      setSuccess('Term deleted successfully');
      await fetchTerms();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message);
    }
  };

  /**
   * Handle toggle active status
   */
  const handleToggleActive = async (term) => {
    try {
      setError('');
      await updateTerm(term.id, {
        ...term,
        is_active: !term.is_active
      });
      await fetchTerms();
    } catch (err) {
      setError(err.message);
    }
  };

  /**
   * Close modal
   */
  const handleCloseModal = () => {
    setShowModal(false);
    setEditingTerm(null);
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Loading terms...</div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <div className="bg-white rounded-xl shadow-lg p-6">
        {/* Header */}
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-gray-800">Term Management</h2>
          <button
            onClick={handleCreate}
            className="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition duration-200 font-medium shadow-md hover:shadow-lg"
          >
            + Create Term
          </button>
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

        {/* Sort Controls */}
        <div className="mb-4 flex justify-end">
          <button
            onClick={toggleSortOrder}
            className="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
          >
            Sort by Academic Year
            <span className="ml-1">
              {sortOrder === 'asc' ? '↑' : '↓'}
            </span>
          </button>
        </div>

        {/* Terms Table */}
        {sortedTerms.length === 0 ? (
          <div className="text-center py-12 text-gray-500">
            <p className="text-lg">No terms found</p>
            <p className="text-sm mt-2">Create your first term to get started</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="bg-primary-50 border-b border-gray-200">
                  <th className="px-4 py-3 text-left text-sm font-semibold text-primary-900">Term Name</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-primary-900">Academic Year</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-primary-900">Start Date</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-primary-900">End Date</th>
                  <th className="px-4 py-3 text-center text-sm font-semibold text-primary-900">Status</th>
                  <th className="px-4 py-3 text-center text-sm font-semibold text-primary-900">Actions</th>
                </tr>
              </thead>
              <tbody>
                {sortedTerms.map((term) => (
                  <tr key={term.id} className="border-b border-gray-100 hover:bg-primary-50">
                    <td className="px-4 py-3 text-sm text-gray-800 font-medium">{term.name}</td>
                    <td className="px-4 py-3 text-sm text-gray-600">{term.academic_year}</td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {term.start_date || '-'}
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">
                      {term.end_date || '-'}
                    </td>
                    <td className="px-4 py-3 text-center">
                      <button
                        onClick={() => handleToggleActive(term)}
                        className={`px-3 py-1 rounded-full text-xs font-medium transition duration-200 ${
                          term.is_active
                            ? 'bg-primary-100 text-primary-800 hover:bg-primary-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        }`}
                      >
                        {term.is_active ? 'Active' : 'Inactive'}
                      </button>
                    </td>
                    <td className="px-4 py-3 text-center">
                      <div className="flex justify-center space-x-2">
                        <button
                          onClick={() => handleEdit(term)}
                          className="text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-200"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDelete(term)}
                          className="text-red-600 hover:text-red-800 font-medium text-sm transition duration-200"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal for Create/Edit Term */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 className="text-xl font-bold text-gray-800 mb-4">
              {editingTerm ? 'Edit Term' : 'Create New Term'}
            </h3>
            <TermForm
              term={editingTerm}
              onSubmit={handleSubmit}
              onCancel={handleCloseModal}
            />
          </div>
        </div>
      )}
    </div>
  );
};

export default TermManagement;
