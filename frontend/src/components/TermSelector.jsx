import { useState, useEffect } from 'react';
import { getActiveTerms } from '../services/termService';

/**
 * TermSelector Component
 * 
 * Reusable dropdown component for selecting active terms
 * Fetches and displays active terms from the API
 * 
 * @param {number|string} value - Currently selected term ID
 * @param {function} onChange - Callback function when selection changes
 * @param {string} label - Label for the dropdown (optional)
 * @param {boolean} required - Whether selection is required (optional)
 * @param {string} className - Additional CSS classes (optional)
 */
const TermSelector = ({ 
  value, 
  onChange, 
  label = 'Select Term',
  required = false,
  className = ''
}) => {
  const [terms, setTerms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchActiveTerms();
  }, []);

  /**
   * Fetch active terms from API
   */
  const fetchActiveTerms = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getActiveTerms();
      setTerms(data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching active terms:', err);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Handle selection change
   */
  const handleChange = (e) => {
    const selectedValue = e.target.value;
    // Convert to number if it's a valid number, otherwise pass as is
    const termId = selectedValue === '' ? '' : Number(selectedValue);
    onChange(termId);
  };

  if (loading) {
    return (
      <div className={className}>
        {label && (
          <label className="block text-sm font-medium text-gray-700 mb-1">
            {label} {required && <span className="text-red-500">*</span>}
          </label>
        )}
        <div className="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
          Loading terms...
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={className}>
        {label && (
          <label className="block text-sm font-medium text-gray-700 mb-1">
            {label} {required && <span className="text-red-500">*</span>}
          </label>
        )}
        <div className="w-full px-4 py-2 border border-red-300 rounded-lg bg-red-50 text-red-600 text-sm">
          Error loading terms: {error}
        </div>
      </div>
    );
  }

  return (
    <div className={className}>
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-1">
          {label} {required && <span className="text-red-500">*</span>}
        </label>
      )}
      <select
        value={value}
        onChange={handleChange}
        required={required}
        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
      >
        <option value="">-- Select a term --</option>
        {terms.map((term) => (
          <option key={term.id} value={term.id}>
            {term.name} ({term.academic_year})
          </option>
        ))}
      </select>
      {terms.length === 0 && !loading && (
        <p className="mt-1 text-sm text-gray-500">No active terms available</p>
      )}
    </div>
  );
};

export default TermSelector;
